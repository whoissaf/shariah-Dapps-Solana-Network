set -e
cd /home/ilham/Documents/hackathon/backend

cat <<'ENVEOF' > .env
APP_NAME=Hackathon
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hackathon
DB_USERNAME=hackathon
DB_PASSWORD=hackathon_secret
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=Hackathon
ENVEOF

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
