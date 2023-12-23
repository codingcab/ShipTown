<?php

namespace App\Modules\Magento2MSI\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'base_url'                      => 'required|url',
            'magento_store_id'              => 'required|numeric',
            // 'access_token_encrypted'        => 'required',
        ];
    }
}
