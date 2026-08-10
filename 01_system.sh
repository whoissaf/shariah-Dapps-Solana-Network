set -e
sudo apt update
sudo apt install -y php-cli php-pgsql php-xml php-mbstring php-curl php-zip php-bcmath unzip git curl postgresql postgresql-contrib composer
sudo service postgresql start || sudo /etc/init.d/postgresql start || true
for i in $(seq 1 60); do
if sudo -u postgres psql -c "SELECT 1" > /dev/null 2>&1; then break; fi
sleep 1
done
sudo -u postgres psql -tc "SELECT 1 FROM pg_roles WHERE rolname='hackathon'" | grep -q 1 || sudo -u postgres psql -c "CREATE ROLE hackathon WITH LOGIN PASSWORD 'secret123';"
sudo -u postgres psql -c "ALTER ROLE hackathon WITH LOGIN PASSWORD 'secret123';"
sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='hackathon'" | grep -q 1 || sudo -u postgres createdb -O hackathon hackathon
sudo -u postgres psql -c "ALTER DATABASE hackathon OWNER TO hackathon;"
