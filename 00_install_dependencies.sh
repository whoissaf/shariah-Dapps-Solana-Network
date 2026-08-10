set -e
export DEBIAN_FRONTEND=noninteractive

sudo apt update
sudo apt install -y ca-certificates apt-transport-https curl gnupg lsb-release git unzip

CODENAME=$(lsb_release -cs)
if [ -z "$CODENAME" ]; then CODENAME=bookworm; fi

sudo mkdir -p /etc/apt/keyrings /usr/share/keyrings

curl -fsSL https://packages.sury.org/debsuryorg-archive-keyring.deb -o /tmp/debsuryorg-archive-keyring.deb
sudo dpkg -i /tmp/debsuryorg-archive-keyring.deb

echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $CODENAME main" | sudo tee /etc/apt/sources.list.d/php.list

curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo gpg --dearmor --yes -o /etc/apt/keyrings/postgresql.gpg

echo "deb [signed-by=/etc/apt/keyrings/postgresql.gpg] http://apt.postgresql.org/pub/repos/apt $CODENAME-pgdg main" | sudo tee /etc/apt/sources.list.d/pgdg.list

sudo apt update

sudo apt install -y php8.4-cli php8.4-pgsql php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl php8.4-opcache php8.4-readline postgresql-17 postgresql-client-17

php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
php -r "copy('https://composer.github.io/installer.sig', '/tmp/composer-setup.sig');"
php -r "if (hash_file('sha384', '/tmp/composer-setup.php') === trim(file_get_contents('/tmp/composer-setup.sig'))) { echo 'valid'; exit(0); } echo 'invalid'; exit(1);"

sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
