<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'base_url'                          => 'required|url',
            'magento_store_code'                => 'required|string',
            'magento_inventory_source_code'     => 'required|string',
            'inventory_source_warehouse_tag_id' => 'sometimes|nullable|exists:tags,id',
            'pricing_source_warehouse_id'       => 'sometimes|nullable|exists:warehouses,id',
            'api_access_token'                  => 'required|string',
        ];
    }
}
