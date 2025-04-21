<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => 'nullable|email',
            'name' => 'required|string',
            'password' => 'string|min:5|max:20',
            'password_confirmation' => 'string|min:5|max:20|same:password',
            'phone_number' => 'required|string|min:10|max:12|regex:/^[0-9]+$/',
            'address' => 'string|nullable',
            'city' => 'string|nullable',
            'state' => 'string|nullable',
            'country' => 'string|nullable',
            'postcode' => 'string|nullable',
            'newsletter' => 'boolean|nullable'
        ];
    }
}
