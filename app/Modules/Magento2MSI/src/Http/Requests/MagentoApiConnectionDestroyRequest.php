<?php

namespace App\Modules\Magento2MSI\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionDestroyRequest extends FormRequest
{
    public function authorize():bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
