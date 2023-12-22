<?php

namespace App\Modules\Integrations\Magento2MSI\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            // 'access_token_encrypted'        => 'required',
        ];
    }
}
