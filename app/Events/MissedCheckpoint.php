<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MissedCheckpoint implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $guardId;
    public int $checkpointId;
    public array $payload;

    public function __construct(int $guardId, int $checkpointId, array $payload = [])
    {
        $this->guardId = $guardId;
        $this->checkpointId = $checkpointId;
        $this->payload = $payload;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->guardId);
    }

    public function broadcastAs(): string
    {
        return 'checkpoint.missed';
    }
}
