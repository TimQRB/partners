@echo off
cd /d "%~dp0"
echo Starting Docker (build + up)...
docker compose -f docker/compose.yml -f docker/dev/compose.yml build 2>nul
docker compose -f docker/compose.yml -f docker/dev/compose.yml up -d --remove-orphans
echo.
echo App: http://localhost
echo Stop: docker compose -f docker/compose.yml -f docker/dev/compose.yml down
pause
