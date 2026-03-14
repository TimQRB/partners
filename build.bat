@echo off
cd /d "%~dp0"
echo Running build (composer install)...
if exist "composer.phar" (
    php composer.phar install --no-interaction --prefer-dist
) else (
    composer install --no-interaction --prefer-dist
)
if errorlevel 1 (
    echo Build failed. Run: composer install
    exit /b 1
)
echo Build finished.
