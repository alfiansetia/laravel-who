<?php

namespace App\Http\Requests\Bast;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDetailBastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'qty' => 'required|numeric|min:0',
            'lot' => 'nullable|string|max:250',
            'satuan' => 'required|string|in:Pcs,Pck,Unit,EA,Box,Btl,Vial',
        ];
    }
}
