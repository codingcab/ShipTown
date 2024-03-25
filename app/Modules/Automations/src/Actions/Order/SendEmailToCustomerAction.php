<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Mail\OrderMail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Facades\Mail;

/**
 *
 */
class SendEmailToCustomerAction extends BaseOrderActionAbstract
{
    /**
     * @param string $options
     * @return bool
     */
    public function handle(string $options = ''): bool
    {
        parent::handle($options);

        /** @var Order $order */
        $order = Order::query()
            ->whereKey($this->order->getKey())
            ->with('orderShipments', 'orderProducts', 'shippingAddress')
            ->first();

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', $options)
            ->where('mailable', OrderMail::class)
            ->first();

        $email = new OrderMail($template, [
            'order' => $order->toArray(),
            'shipments' => $order->orderShipments->toArray(),
            'shipping_address' => $order->shippingAddress->toArray(),
        ]);

        Mail::to($this->order->shippingAddress->email)
            ->send($email);

        return true;
    }
}
