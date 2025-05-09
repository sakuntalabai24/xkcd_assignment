
CRON_FILE_PATH="$(pwd)/cron.php"

PHP_PATH=$(which php)

CRON_JOB="0 9 * * * $PHP_PATH $CRON_FILE_PATH >> $(pwd)/cron.log 2>&1"

(crontab -l 2>/dev/null | grep -F "$CRON_FILE_PATH") && echo "✅ Cron already set." && exit

(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo " Cron job set successfully!"
