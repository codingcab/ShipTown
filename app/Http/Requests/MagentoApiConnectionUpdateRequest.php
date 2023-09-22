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
            'api_access_token'                  => 'required|string',
            'magento_store_id'                  => 'sometimes|integer|nullable',
            'magento_store_code'                => 'sometimes|string|nullable',
            'base_url'                          => 'sometimes|url',
            'magento_inventory_source_code'     => 'sometimes|string|nullable',
            'inventory_source_warehouse_tag_id' => 'sometimes|nullable|exists:tags,id',
            'pricing_source_warehouse_id'       => 'sometimes|nullable|exists:warehouses,id',
        ];
    }
}
