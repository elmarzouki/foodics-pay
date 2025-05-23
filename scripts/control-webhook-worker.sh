#!/bin/bash

cd /var/www

FLAG_STATUS=$(php artisan app:check webhook_ingestion)

echo "Flag status: $FLAG_STATUS" >> storage/logs/control-worker.log


if [ "$FLAG_STATUS" = "enabled" ]; then
    echo "Enabling queue worker..." >> storage/logs/control-worker.log
    /usr/bin/supervisorctl -s http://localhost:9001 start queue_worker
else
    echo "Disabling queue worker..." >> storage/logs/control-worker.log
    /usr/bin/supervisorctl -s http://localhost:9001 stop queue_worker
fi
