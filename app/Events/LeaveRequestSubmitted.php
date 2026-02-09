<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $employeeId;
    public array $payload;

    public function __construct(int $employeeId, array $payload = [])
    {
        $this->employeeId = $employeeId;
        $this->payload = $payload;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->employeeId);
    }

    public function broadcastAs(): string
    {
        return 'leave.submitted';
    }
}
