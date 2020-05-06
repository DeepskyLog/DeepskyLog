<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LensRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'user_id' => 'required',
            'name' => ['required', 'min:6'],
            'factor' => ['required', 'numeric', 'min:0', 'max:10'],
        ];
    }
}
