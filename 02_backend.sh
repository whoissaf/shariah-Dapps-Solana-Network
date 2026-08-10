set -e
cd /home/ilham/Documents/hackathon
if [ -d backend ]; then mv backend backend_old_$(date +%s); fi
COMPOSER_MEMORY_LIMIT=-1 COMPOSER_PROCESS_TIMEOUT=1800 composer create-project laravel/laravel:^9.0 backend --prefer-dist --no-interaction
cd backend
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' .env
sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/^DB_PORT=.*/DB_PORT=5432/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=hackathon/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=hackathon/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=secret123/' .env
cat <<'APIEOF' > routes/api.php
<?php
use Illuminate\Support\Facades\Route;
Route::get('/health', fn () => response()->json(['status' => 'ok', 'db' => config('database.default')]));
APIEOF
php artisan migrate --force
