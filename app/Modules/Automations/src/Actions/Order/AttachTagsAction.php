<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;

class AttachTagsAction extends BaseOrderActionAbstract
{
    /**
     * @param string $options
     * @return void
     */
    public function handle(string $options = '')
    {
        if (trim($options) === '') {
            return;
        }

        $tags = explode(',', $options);

        $this->order->attachTags($tags);
    }
}
