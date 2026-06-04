<?php

namespace Inicio\Modelo\DAO;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Expression;

class PortalDAO
{
    private Adapter $adapter;
    private Sql $sql;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->sql = new Sql($adapter);
    }

    private function countTable(string $table, $where = null): int
    {
        $select = $this->sql->select();
        $select->from($table)
            ->columns(['total' => new Expression('COUNT(*)')]);

        if ($where) {
            $select->where($where);
        }

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $row = $result->current();

        return $row ? (int)$row['total'] : 0;
    }

    private function fetchRows(string $table, array $columns, $where = null, int $limit = 5): array
    {
        $select = $this->sql->select();
        $select->from($table)->columns($columns)->order('1 DESC')->limit($limit);

        if ($where) {
            $select->where($where);
        }

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $rows = [];
        foreach ($result as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function getSummary(): array
    {
        return [
            'posts' => $this->countTable('wp_posts', [
                'post_type' => 'post',
                'post_status' => 'publish',
            ]),
            'pages' => $this->countTable('wp_posts', [
                'post_type' => 'page',
                'post_status' => 'publish',
            ]),
            'draft_posts' => $this->countTable('wp_posts', [
                'post_type' => 'post',
                'post_status' => 'draft',
            ]),
            'scheduled_posts' => $this->countTable('wp_posts', [
                'post_type' => 'post',
                'post_status' => 'future',
            ]),
            'comments' => $this->countTable('wp_comments'),
            'pending_comments' => $this->countTable('wp_comments', ['comment_approved' => '0']),
            'users' => $this->countTable('wp_users'),
            'categories' => $this->countTable('wp_term_taxonomy', ['taxonomy' => 'category']),
            'events' => $this->countTable('wp_tec_events'),
        ];
    }

    public function getRecentPosts(int $limit = 5): array
    {
        $select = $this->sql->select();
        $select->from(['p' => 'wp_posts'])
            ->columns([
                'ID',
                'post_title',
                'post_type',
                'post_status',
                'post_date',
                'post_author',
            ])
            ->join(['u' => 'wp_users'], 'p.post_author = u.ID', ['display_name'])
            ->where([
                'post_status' => ['publish', 'future', 'draft'],
            ])
            ->order('post_date DESC')
            ->limit($limit);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return iterator_to_array($result);
    }

    public function getRecentComments(int $limit = 5): array
    {
        $select = $this->sql->select();
        $select->from('wp_comments')
            ->columns([
                'comment_ID',
                'comment_author',
                'comment_content',
                'comment_post_ID',
                'comment_date',
                'comment_approved',
            ])
            ->order('comment_date DESC')
            ->limit($limit);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return iterator_to_array($result);
    }

    public function getRecentUsers(int $limit = 5): array
    {
        $subSelect = $this->sql->select();
        $subSelect->from('wp_login_history')
            ->columns([
                'user_id',
                'last_login' => new \Laminas\Db\Sql\Expression('MAX(login_time)')
            ])
            ->group('user_id');

        $select = $this->sql->select();
        $select->from(['h' => 'wp_login_history'])
            ->join(['u' => 'wp_users'], 'h.user_id = u.ID', ['display_name'])
            ->join(['latest' => $subSelect], 'h.user_id = latest.user_id AND h.login_time = latest.last_login', [])
            ->order('h.login_time DESC')
            ->limit($limit);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return iterator_to_array($result);
    }

    public function getUpcomingEvents(int $limit = 5): array
    {
        $select = $this->sql->select();
        $select->from(['e' => 'wp_tec_events'])
            ->columns([
                'event_id',
                'start_date',
                'end_date',
                'timezone',
            ])
            ->join(['p' => 'wp_posts'], 'e.post_id = p.ID', ['post_title'])
            ->where(["e.start_date >= '" . date('Y-m-d') . "'"])
            ->order('e.start_date ASC')
            ->limit($limit);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return iterator_to_array($result);
    }
}
