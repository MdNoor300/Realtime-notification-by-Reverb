<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Role based channel (e.g., admin, guard)
Broadcast::channel('role.{role}', function ($user, $role) {
    return (int) $user->role === (int) $role;
});

// Guard private channel
Broadcast::channel('guard.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id || (int) $user->role === ADMIN;
});

// Area channel (authorized server-side depending on your logic)
Broadcast::channel('area.{id}', function ($user, $id) {
    // Adjust authorization as needed. For now, allow authenticated users.
    return isset($user->id);
});

// Device channel for offline sync events
Broadcast::channel('device.{id}', function ($user, $id) {
    // If devices are associated to users, validate accordingly.
    return isset($user->id);
});
