#!/bin/bash

trap quitjobs INT
quitjobs() {
  echo ""
  pkill -P $$
  echo "Killed all running jobs".
  scriptCancelled="true"
  trap - INT
  exit
}

scriptCancelled="false"
waitforcancel() {
  while :
  do
    if [ "$scriptCanceled" == "true"]; then
      return
    fi
    sleep 1
  done
}

php artisan serve & \
php artisan queue:listen & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low & \
php artisan websockets:serve & \
npm run hot

waitforcancel
return 0
