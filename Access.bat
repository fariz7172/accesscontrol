@echo off
cd /d D:\fariz\laravel\accesscontrol

echo Clearing routes...
start /B php artisan route:clear

echo Clearing cache...
start /B php artisan cache:clear

echo Clearing config...
start /B php artisan config:clear

echo Starting server...
start /B php artisan serve --host=0.0.0.0 --port=0000

echo Starting Laravel scheduler...
start /B php artisan tcp:server

echo Starting Laravek scheduler
start /B php artisan schedule:work

echo Opening browser...
start "" "http://localhost:8000/"

exit