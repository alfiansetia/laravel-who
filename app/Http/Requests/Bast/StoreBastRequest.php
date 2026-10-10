<?php

namespace App\Http\Requests\Bast;

use Illuminate\Foundation\Http\FormRequest;

class StoreBastRequest extends FormRequest
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
            'name' => 'required|string|max:250',
            'address' => 'required|string|max:250',
            'city' => 'required|string|max:250',
            'do' => 'required|string|max:250',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'do.required' => 'Nomor DO wajib diisi.',
        ];
    }
}
