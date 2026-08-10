set -e

cd /home/ilham/Documents/hackathon/backend

php artisan config:clear
php artisan route:clear

php artisan migrate:fresh --force
php artisan migrate:fresh --force --env=testing

php artisan test
