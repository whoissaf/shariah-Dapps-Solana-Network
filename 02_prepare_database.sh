set -e

sudo service postgresql start || sudo pg_ctlcluster 17 main start || true
sleep 2

sudo -u postgres psql -v ON_ERROR_STOP=1 <<'SQL'
DO
$$
BEGIN
   IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'hackathon') THEN
      CREATE ROLE hackathon WITH LOGIN PASSWORD 'hackathon';
   END IF;
END
$$;
SQL

sudo -u postgres createdb -O hackathon hackathon || true
sudo -u postgres createdb -O hackathon hackathon_test || true
