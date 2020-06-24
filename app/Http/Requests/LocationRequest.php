<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'latitude' => 'required|numeric|lte:90|gte:-90',
            'longitude' => 'required|numeric|lte:180|gte:-180',
            'country' => 'required',
            'elevation' => 'required|numeric|lte:8888|gte:-200',
            'timezone' => 'required|timezone',
            'lm' => 'numeric|lte:8.0|gte:-1.0|nullable',
            'sqm' => 'numeric|lte:22.0|gte:10.0|nullable',
            'bortle' => 'numeric|lte:9|gte:1|nullable',
        ];
    }
}
