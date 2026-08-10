set -e
cd /home/ilham/Documents/hackathon/backend
php artisan key:generate
php artisan config:clear
php artisan route:clear
php artisan migrate --force
pkill -f "artisan serve" || true
sleep 1
php artisan serve --host=127.0.0.1 --port=8000 > /tmp/backend-test.log 2>&1 &
SERVER_PID=$!
sleep 15
if curl -f http://127.0.0.1:8000/api/health; then
kill $SERVER_PID || true
else
kill $SERVER_PID || true
cat /tmp/backend-test.log
exit 1
fi
