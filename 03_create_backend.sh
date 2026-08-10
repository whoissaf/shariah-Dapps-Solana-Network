set -e

export COMPOSER_MEMORY_LIMIT=-1

cd /home/ilham/Documents/hackathon

if [ ! -d backend ]; then
  /usr/local/bin/composer create-project laravel/laravel backend
fi

cd backend
/usr/local/bin/composer install --optimize-autoloader
