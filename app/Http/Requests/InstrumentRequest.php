<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstrumentRequest extends FormRequest
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
            'type' => 'required',
            'diameter' => 'required|numeric|gt:0',
            'fd' => 'nullable|gte:1|required_without:fixedMagnification',
            'fixedMagnification' => 'nullable|gte:0|required_without:fd',
        ];
    }
}
