set -e

export COMPOSER_MEMORY_LIMIT=-1

cd /home/ilham/Documents/hackathon/backend

/usr/local/bin/composer require laravel/sanctum --no-interaction

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --no-interaction

php artisan config:clear

php artisan migrate --force
php artisan migrate --force --env=testing
