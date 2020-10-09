<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|unique|min:2',
            'name' => 'required|max:64|min:4',
            'email' => 'required|email|unique:users,email',
            'type' => 'required',
        ];
    }
}
