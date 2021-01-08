<?php

// Needed for standardized Broadcasting security route
Broadcast::routes();

Broadcast::channel('user.status.{userId}', \App\Broadcasting\UserStatusChannel::class);
