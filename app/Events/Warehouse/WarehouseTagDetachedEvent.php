<?php

namespace App\Events\Warehouse;

use App\Models\Warehouse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseTagDetachedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Warehouse $warehouse;

    public string $tag;

    public function __construct(Warehouse $warehouse, string $tag)
    {
        $this->warehouse = $warehouse;
        $this->tag = $tag;
    }

    public function broadcastOn(): Channel|PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
