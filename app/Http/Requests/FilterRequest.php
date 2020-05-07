<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'min:6'],
            'type' => ['required'],
            'color' => [], 'wratten' => ['max:5'],
            'schott' => [],
        ];
    }
}
