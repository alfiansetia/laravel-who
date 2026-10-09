<?php

namespace App\Http\Requests\Bast;

use Illuminate\Foundation\Http\FormRequest;

class ReorderDetailBastRequest extends FormRequest
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
            'type' => 'required|string|in:up,down',
        ];
    }
}
