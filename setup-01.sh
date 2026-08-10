set -e
export DEBIAN_FRONTEND=noninteractive
cd /home/ilham/Documents/hackathon
sudo apt update
sudo apt install -y ca-certificates php php-cli php-mbstring php-xml php-curl php-zip php-pgsql php-bcmath unzip curl git postgresql postgresql-contrib
sudo service postgresql start
if [ ! -f /usr/local/bin/composer ]; then
    curl -sS https://getcomposer.org/installer -o composer-setup.php
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
fi
if [ ! -f backend/artisan ]; then
    php -d memory_limit=-1 /usr/local/bin/composer create-project laravel/laravel:^10.0 backend --no-interaction --prefer-dist
fi
sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='hackathon'" | grep -q 1 || sudo -u postgres psql -c "CREATE USER hackathon WITH PASSWORD 'hackathon_secret';"
sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='hackathon'" | grep -q 1 || sudo -u postgres psql -c "CREATE DATABASE hackathon OWNER hackathon;"
sudo -u postgres psql -c "ALTER USER hackathon WITH PASSWORD 'hackathon_secret';"
chmod -R 775 backend/storage backend/bootstrap/cache
