set -e

sudo service postgresql start || sudo pg_ctlcluster 17 main start || true
sleep 2

sudo -u postgres psql -c "ALTER SYSTEM SET password_encryption = 'scram-sha-256';"
sudo -u postgres psql -c "SELECT pg_reload_conf();"
sleep 1

sudo -u postgres psql -v ON_ERROR_STOP=1 <<'SQL'
DO
$$
BEGIN
   IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'hackathon') THEN
      CREATE ROLE hackathon WITH LOGIN PASSWORD 'hackathon';
   ELSE
      ALTER ROLE hackathon WITH LOGIN PASSWORD 'hackathon';
   END IF;
END
$$;
SQL

sudo -u postgres createdb -O hackathon hackathon || true
sudo -u postgres createdb -O hackathon hackathon_test || true

sudo -u postgres psql -c "ALTER DATABASE hackathon OWNER TO hackathon;" || true
sudo -u postgres psql -c "ALTER DATABASE hackathon_test OWNER TO hackathon;" || true

sudo -u postgres psql -d hackathon -c "ALTER SCHEMA public OWNER TO hackathon;" || true
sudo -u postgres psql -d hackathon_test -c "ALTER SCHEMA public OWNER TO hackathon;" || true

sudo -u postgres psql -d hackathon -c "GRANT ALL ON SCHEMA public TO hackathon;" || true
sudo -u postgres psql -d hackathon_test -c "GRANT ALL ON SCHEMA public TO hackathon;" || true

PGPASSWORD=hackathon psql -h 127.0.0.1 -p 5432 -U hackathon -d hackathon -c "SELECT 1;"
PGPASSWORD=hackathon psql -h 127.0.0.1 -p 5432 -U hackathon -d hackathon_test -c "SELECT 1;"
