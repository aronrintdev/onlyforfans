#!/bin/bash

### ctrl-c Trap ###
trap quitjobs INT
quitjobs() {
  echo ""
  pkill -P $$
  echo " => Killed all running jobs".
  scriptCancelled="true"
  trap - INT
  ## Update: exit was exiting open terminal
  # exit
}

### Wait for cancceled trap loop ###
scriptCancelled="false"
waitforcancel() {
  while :
  do
    if [ "$scriptCanceled" == "true" ]; then
      return
    fi
    sleep 1
  done
}

### Starting Laravel Server ###
php artisan serve & \

### Starting Laravel Queue Workers ###
php artisan queue:listen --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-transactions --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \

### Starting Websockets Server ###
php artisan websockets:serve & \

### Starting Meilisearch Server ###
./meilisearch &

### Import Searchable Models ###
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Timeline"
php artisan scout:import "App\Models\Post"
php artisan scout:import "App\Models\Story"
php artisan scout:import "App\Models\Mycontact"
php artisan scout:import "App\Models\Chatthread"
php artisan scout:import "App\Models\Chatmessage"

### Run webpack hot server ###
npm run hot

waitforcancel
return 0
