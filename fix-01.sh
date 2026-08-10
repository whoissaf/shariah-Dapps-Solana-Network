set -e
export DEBIAN_FRONTEND=noninteractive
cd /home/ilham/Documents/hackathon
rm -rf backend
sudo service postgresql start
php -d memory_limit=-1 /usr/local/bin/composer create-project laravel/laravel backend --no-interaction --prefer-dist
sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='hackathon'" | grep -q 1 || sudo -u postgres psql -c "CREATE USER hackathon WITH PASSWORD 'hackathon_secret';"
sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='hackathon'" | grep -q 1 || sudo -u postgres psql -c "CREATE DATABASE hackathon OWNER hackathon;"
sudo -u postgres psql -c "ALTER USER hackathon WITH PASSWORD 'hackathon_secret';"
chmod -R 775 backend/storage backend/bootstrap/cache
