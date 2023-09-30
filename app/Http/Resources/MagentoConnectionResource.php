<?php

namespace App\Http\Resources;

use App\Modules\MagentoApi\src\Models\MagentoConnection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MagentoConnection
 */
class MagentoConnectionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'base_url' => $this->base_url,
            'magento_store_id' => $this->magento_store_id,
            'magento_store_code' => $this->magento_store_code,
            'magento_inventory_source_code' => $this->magento_inventory_source_code,
            'inventory_totals_tag_id' => $this->inventory_totals_tag_id,
            'pricing_source_warehouse_id' => $this->pricing_source_warehouse_id,
            'inventory_totals_tag' => $this->whenLoaded('inventoryTotalsTag', TagResource::make($this->inventoryTotalsTag)),
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'warehouse' => $this->whenLoaded('warehouse', WarehouseResource::make($this->warehouse)),
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
