<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EyepieceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:6',
            'brand' => 'required',
            'type' => 'required',
            'focalLength' => 'required|numeric|gte:1|lte:99',
            'apparentFOV' => 'required|numeric|gte:20|lte:150',
            'maxFocalLength' => 'gte:1|lte:99',
        ];
    }
}
