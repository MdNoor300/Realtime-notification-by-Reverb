<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuardExitedGeofence implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $guardId;
    public int $areaId;
    public array $payload;

    public function __construct(int $guardId, int $areaId, array $payload = [])
    {
        $this->guardId = $guardId;
        $this->areaId = $areaId;
        $this->payload = $payload;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('area.' . $this->areaId);
    }

    public function broadcastAs(): string
    {
        return 'guard.exited_geofence';
    }
}
