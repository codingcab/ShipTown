<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Events;

use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigurationUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Configuration $order;

    public function __construct(Configuration $configuration)
    {
        $this->order = $configuration;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
