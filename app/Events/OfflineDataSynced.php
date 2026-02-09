<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfflineDataSynced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $deviceId;
    public array $payload;

    public function __construct(int $deviceId, array $payload = [])
    {
        $this->deviceId = $deviceId;
        $this->payload = $payload;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('device.' . $this->deviceId);
    }

    public function broadcastAs(): string
    {
        return 'device.synced';
    }
}
