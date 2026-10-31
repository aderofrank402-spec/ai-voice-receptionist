<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Crawler Private Channel Authorization
 * Channel name is crawler.{userId}. Only the authenticated user can listen to their own crawler updates.
 */
Broadcast::channel('crawler.{sessionId}', function ($user, $sessionId) {
    return (int) $user->id === (int) $sessionId;
});
