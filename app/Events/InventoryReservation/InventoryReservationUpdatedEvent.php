<?php

namespace App\Events\InventoryReservation;

use App\Models\InventoryReservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryReservationUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public InventoryReservation $inventoryReservation;

    public function __construct(InventoryReservation $inventoryReservation)
    {
        $this->inventoryReservation = $inventoryReservation;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
