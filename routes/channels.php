<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Dashboard channel - accessible to all authenticated users
Broadcast::channel('dashboard', function ($user) {
    return $user !== null;
});

// User-specific notifications channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
