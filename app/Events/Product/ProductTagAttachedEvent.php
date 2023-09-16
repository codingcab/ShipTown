<?php

namespace App\Events\Product;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductTagAttachedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Product $product;

    public string $tag;

    public function __construct(Product $product, string $tag)
    {
        $this->product = $product;
        $this->tag = $tag;
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function tag(): string
    {
        return $this->tag;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
