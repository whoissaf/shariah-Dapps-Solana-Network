set -e

sudo service postgresql start || sudo pg_ctlcluster 17 main start || true
sleep 2

sudo -u postgres psql -c "ALTER SYSTEM SET shared_buffers = '128MB';"
sudo -u postgres psql -c "ALTER SYSTEM SET work_mem = '4MB';"
sudo -u postgres psql -c "ALTER SYSTEM SET maintenance_work_mem = '64MB';"
sudo -u postgres psql -c "ALTER SYSTEM SET effective_cache_size = '512MB';"
sudo -u postgres psql -c "ALTER SYSTEM SET max_connections = '30';"

sudo pg_ctlcluster 17 main restart || sudo service postgresql restart
sleep 2
