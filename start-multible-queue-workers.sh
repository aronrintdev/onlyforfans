#!/bin/bash

################################################################################
# Use this script if you are running queue heavy tasks in your development
# enviroment.
################################################################################

### ctrl-c Trap ###
trap quitjobs INT
quitjobs() {
  echo ""
  pkill -P $$
  echo " => Killed all running jobs".
  scriptCancelled="true"
  trap - INT
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

# Starts a bunch of paralel queue workers to speed up tasks on multithread systems

### Starting Laravel Queue Workers ###
php artisan queue:listen --tries=3 --backoff=3 & \
php artisan queue:listen --tries=3 --backoff=3 & \
php artisan queue:listen --tries=3 --backoff=3 & \
php artisan queue:listen --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 & \
php artisan queue:listen --queue=financial-summaries-urgent,financial-summaries-high,financial-summaries-mid,financial-summaries-low --tries=3 --backoff=3 &


waitforcancel
return 0
