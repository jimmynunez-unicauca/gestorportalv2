📝 Crear el manual directamente en GitHub (recomendado)
Ve a tu repositorio: https://github.com/jimmynunez-unicauca/gestorportalv2

Haz clic en "Add file" → "Create new file"

Nombra el archivo, por ejemplo: DEPLOY.md

Copia y pega el contenido del manual que te proporciono abajo.

Al final de la página, escribe un mensaje de commit (ej: "Agrega manual de despliegue") y haz clic en "Commit new file".

📋 Contenido del manual de despliegue
Copia y pega esto en tu nuevo archivo DEPLOY.md:

markdown
# Manual de despliegue - GestorPortal v2

Este documento describe el flujo de trabajo para desplegar cambios desde el entorno de desarrollo (Windows 11) hasta el servidor de preproducción (Linux), manteniendo sincronizadas las ramas `main` y `preproduccion` de GitHub.

---

## 📌 Flujo de trabajo general

1. **Desarrollo local** (Windows)
   - Realizar cambios en el código.
   - Probar localmente.
   - Commit y push a la rama `main`.

2. **Sincronización de ramas** (Windows)
   - Fusionar `main` en `preproduccion`.
   - Push de `preproduccion` a GitHub.

3. **Despliegue en preproducción** (Linux)
   - Descargar el ZIP de la rama `preproduccion`.
   - Copiar archivos (excluyendo configuraciones y datos).
   - Instalar/actualizar dependencias con Composer.

---

## 🖥️ Entorno de desarrollo (Windows 11)

### 1. Realizar cambios y subir a `main`

```bash
# Verificar rama actual
git branch

# Hacer cambios en el código...

# Agregar y commitear
git add .
git commit -m "Descripción del cambio"

# Subir a main
git push origin main
Nota: Asegúrate de que vendor/ y composer.lock estén en .gitignore para no subir archivos innecesarios.

2. Fusionar main en preproduccion
bash
# Cambiar a la rama preproduccion
git checkout preproduccion

# Si tienes composer.lock sin rastrear, muévelo temporalmente
mv composer.lock composer.lock.backup

# Fusionar cambios de main
git merge main

# Resolver conflictos si los hay, luego:
git add .
git commit -m "Merge main into preproduccion"

# Subir la rama actualizada
git push origin preproduccion

# Volver a main (opcional)
git checkout main

# Restaurar composer.lock local (si lo moviste)
mv composer.lock.backup composer.lock
🐧 Servidor de preproducción (Linux)
Requisitos previos
Acceso SSH al servidor.

PHP y Composer instalados (composer.phar en el proyecto).

Sin permisos sudo (se usa método de descarga ZIP).

1. Conectarse al servidor
bash
ssh gestorportal@testportalv2-manager
2. Navegar al directorio del proyecto
bash
cd /var/www/gestorportalv2
3. Descargar el ZIP de la rama preproduccion
bash
wget https://github.com/jimmynunez-unicauca/gestorportalv2/archive/refs/heads/preproduccion.zip -O /tmp/preproduccion.zip
Si wget no está, usar curl:

bash
curl -L https://github.com/jimmynunez-unicauca/gestorportalv2/archive/refs/heads/preproduccion.zip -o /tmp/preproduccion.zip
4. Descomprimir el ZIP
Si tienes unzip:

bash
unzip /tmp/preproduccion.zip -d /tmp/
Si no (caso común), usar Python:

bash
python3 -c "import zipfile; zipfile.ZipFile('/tmp/preproduccion.zip', 'r').extractall('/tmp/')"
5. Copiar archivos nuevos al proyecto
bash
# Hacer backup de configuraciones locales (si no existe)
cp config/autoload/local.php config/autoload/local.php.backup 2>/dev/null || echo "No local.php"

# Copiar (excluyendo vendor, composer.lock, config local y datos)
rsync -av --delete \
  --exclude='vendor/' \
  --exclude='composer.lock' \
  --exclude='config/autoload/local.php' \
  --exclude='data/cache/' \
  --exclude='data/logs/' \
  --exclude='data/session/' \
  /tmp/gestorportalv2-preproduccion/ ./
Si rsync no está, usar cp -rf (pero restaurar local.php después):

bash
cp -rf /tmp/gestorportalv2-preproduccion/* ./
6. Restaurar configuración local (si fue sobrescrita)
bash
cp config/autoload/local.php.backup config/autoload/local.php 2>/dev/null || echo "No backup found"
7. Instalar/actualizar dependencias con Composer
bash
php composer.phar install --no-dev --optimize-autoloader --ignore-platform-req=php
Si necesitas agregar una nueva dependencia específica (ej. phpspreadsheet):

bash
php composer.phar require phpoffice/phpspreadsheet --ignore-platform-req=php
8. Regenerar autoloader optimizado
bash
php composer.phar dump-autoload --optimize --ignore-platform-req=php
9. Limpiar archivos temporales
bash
rm -rf /tmp/gestorportalv2-preproduccion /tmp/preproduccion.zip
✅ Verificación final
Abrir la URL de preproducción en el navegador.

Probar funcionalidades críticas (especialmente las que usan phpspreadsheet).

Revisar logs de errores si algo falla.

🔄 Actualizaciones rápidas (solo si no hay cambios estructurales)
Si el código del controlador ya está actualizado y solo necesitas instalar una nueva librería, puedes hacer:

bash
cd /var/www/gestorportalv2
php composer.phar require <paquete> --ignore-platform-req=php
php composer.phar dump-autoload --optimize --ignore-platform-req=php
⚠️ Notas importantes
Nunca subas vendor/ a GitHub. Asegúrate de que .gitignore lo excluya.

En el servidor, siempre usa --ignore-platform-req=php porque algunos paquetes tienen restricciones de versión de PHP que no afectan el funcionamiento.

Los warnings de "Skipping" en el autoloader son problemas de nomenclatura en módulos personalizados, no afectan la librería phpspreadsheet.

📚 Referencias
Composer documentation

GitHub - Laminas Project

PhpSpreadsheet - PHPOffice

Última actualización: 2026-06-24

text

---

## 🚀 Cómo subir este manual a GitHub

Desde tu máquina local (Windows), puedes crear el archivo y subirlo:

```bash
# Crear el archivo manualmente o con tu editor favorito
echo "# Manual de despliegue" > DEPLOY.md

# Agregar el contenido (copiar y pegar el texto de arriba)

# Subir a main
git add DEPLOY.md
git commit -m "Agrega manual de despliegue"
git push origin main
Luego, si quieres que también esté disponible en preproduccion, fusiona como siempre:

bash
git checkout preproduccion
git merge main
git push origin preproduccion
💡 Alternativa: Crear una carpeta docs/
Si prefieres organizar mejor la documentación, crea una carpeta docs/ y coloca allí el manual:

bash
mkdir docs
mv DEPLOY.md docs/deployment.md
git add docs/
git commit -m "Mueve manual a carpeta docs"
git push origin main
