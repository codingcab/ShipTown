<?php

namespace App\Modules\MagentoApi\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionStoreRequest extends FormRequest
{
    public function authorize():bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
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
