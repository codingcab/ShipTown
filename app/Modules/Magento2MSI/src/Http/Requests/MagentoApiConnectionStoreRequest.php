<?php

namespace App\Modules\Magento2MSI\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_url'                      => 'required|url',
            'magento_store_id'              => 'required|numeric',
            'tag'                           => 'nullable',
            'pricing_source_warehouse_id'   => 'nullable|exists:warehouses,id',
            'api_access_token'              => 'required',
        ];
    }
}
