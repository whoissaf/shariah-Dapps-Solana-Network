set -e
cd /home/ilham/Documents/hackathon/backend
php artisan migrate:fresh --force
TABLES=$(sudo -u postgres psql -d hackathon -tAc "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename;")
echo "Tabel yang terbuat di PostgreSQL:"
echo "$TABLES"
if echo "$TABLES" | grep -q "users" && \
   echo "$TABLES" | grep -q "wallets" && \
   echo "$TABLES" | grep -q "identities" && \
   echo "$TABLES" | grep -q "claims" && \
   echo "$TABLES" | grep -q "documents" && \
   echo "$TABLES" | grep -q "proofs" && \
   echo "$TABLES" | grep -q "blockchain_logs"; then
    echo "TAHAP 2 OK"
else
    echo "Gagal membuat tabel"
    exit 1
fi
