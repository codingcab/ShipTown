<?php

return [
    'description' => 'Placed in Last 28 Days or Active Orders',
    'conditions' => [
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\IsActiveCondition::class,
            'description' => 'Is Active',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition::class,
            'description' => 'Status Code is',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\StatusCodeInCondition::class,
            'description' => 'Status Code is in',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\StatusCodeNotInCondition::class,
            'description' => 'Status Code is NOT in',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition::class,
            'description' => 'Can Fulfill from Warehouse Code (0 for all)',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\CanNotFulfillFromLocationCondition::class,
            'description' => 'Can NOT Fulfill Warehouse Code (0 for all)',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition::class,
            'description' => 'Shipping Method Code equals',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeInCondition::class,
            'description' => 'Shipping Method Code is in',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodNameEqualsCondition::class,
            'description' => 'Shipping Method Name equals',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\ShippingAddressCountryCodeInCondition::class,
            'description' => 'Shipping Address Country Code is in',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\ShippingAddressCountryCodeNotInCondition::class,
            'description' => 'Shipping Address Country Code is NOT in',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\LabelTemplateInCondition::class,
            'description' => 'Courier label template is in',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\CourierLabelTemplateIsNotInCondition::class,
            'description' => 'Courier label template is NOT in',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\LineCountEqualsCondition::class,
            'description' => 'Line count equals',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\TotalQuantityToShipEqualsCondition::class,
            'description' => 'Total Quantity To Ship equals',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\IsPartiallyPaidCondition::class,
            'description' => 'Is Partially Paid',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\IsFullyPaidCondition::class,
            'description' => 'Is Fully Paid',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\IsFullyPickedCondition::class,
            'description' => 'Is Fully Picked',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\IsFullyPackedCondition::class,
            'description' => 'Is Fully Packed',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\OrderNumberEqualsCondition::class,
            'description' => 'Order Number equals',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\HasTagsCondition::class,
            'description' => 'Has tags',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\HasAnyShipmentCondition::class,
            'description' => 'Has any shipment',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\DoesntHaveTagsCondition::class,
            'description' => 'Does not have tags',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\HoursSincePlacedAtCondition::class,
            'description' => 'Hours since placed',
        ],
        [
            'class' => \App\Modules\Automations\src\Conditions\Order\HoursSinceLastUpdatedAtCondition::class,
            'description' => 'Hours since last updated',
        ],
    ],
    'actions' => [
        [
            'class' => \App\Modules\Automations\src\Actions\Order\SetStatusCodeAction::class,
            'description' => 'Set Status Code',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\AddOrderCommentAction::class,
            'description' => 'Add order comment',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\LogMessageAction::class,
            'description' => 'Add log message',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\SetLabelTemplateAction::class,
            'description' => 'Set courier label template',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\SplitOrderToWarehouseCodeAction::class,
            'description' => 'Split Order to warehouse code',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\ShipRemainingProductsAction::class,
            'description' => 'Ship all from warehouse code',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\PushToBoxTopOrderAction::class,
            'description' => 'Create Warehouse Shipment in BoxTop Software',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\SendOrderEmailAction::class,
            'description' => 'Send email template to customer',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\SplitBundleSkuAction::class,
            'description' => 'Split bundle SKU (format: BundleSKU,SKU1,SKU2...)',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\AttachTagsAction::class,
            'description' => 'Attach tags',
        ],
        [
            'class' => \App\Modules\Automations\src\Actions\Order\DetachTagsAction::class,
            'description' => 'Detach tags',
        ],
        [
            'class' => \App\Modules\Slack\src\Automations\SendSlackNotificationAction::class,
            'description' => 'Send Slack notification',
        ],
    ]
];
