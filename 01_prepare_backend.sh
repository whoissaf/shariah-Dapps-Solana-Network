set -e
export COMPOSER_MEMORY_LIMIT=-1
export DEBIAN_FRONTEND=noninteractive
sudo apt update
sudo apt install -y php-cli php-mbstring php-xml php-curl php-zip php-pgsql php-bcmath php-intl php-sqlite3 unzip git postgresql postgresql-client composer
sudo service postgresql start || sudo /etc/init.d/postgresql start || true
sudo -u postgres psql -tc "SELECT 1 FROM pg_roles WHERE rolname='hackathon'" | grep -q 1 || sudo -u postgres psql -c "CREATE USER hackathon WITH PASSWORD 'secret';"
sudo -u postgres psql -c "ALTER USER hackathon WITH PASSWORD 'secret';"
sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='hackathon'" | grep -q 1 || sudo -u postgres psql -c "CREATE DATABASE hackathon OWNER hackathon;"
sudo -u postgres psql -c "ALTER DATABASE hackathon OWNER TO hackathon;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE hackathon TO hackathon;"
sudo -u postgres psql -d hackathon -c "GRANT ALL ON SCHEMA public TO hackathon;"
sudo -u postgres psql -d hackathon -c "ALTER SCHEMA public OWNER TO hackathon;"
cd /home/ilham/Documents/hackathon
if [ ! -f backend/artisan ]; then
  if [ -e backend ]; then
    mv backend backend.bak.$(date +%s)
  fi
  if php -r "exit(version_compare(PHP_VERSION,'8.2','>=')?0:1);"; then
    LARAVEL_PACKAGE="laravel/laravel"
  elif php -r "exit(version_compare(PHP_VERSION,'8.1','>=')?0:1);"; then
    LARAVEL_PACKAGE="laravel/laravel:^10.0"
  else
    LARAVEL_PACKAGE="laravel/laravel:^9.0"
  fi
  composer create-project $LARAVEL_PACKAGE backend --no-interaction --prefer-dist
fi
cd backend
php artisan --version
