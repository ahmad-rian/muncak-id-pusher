<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "name"     => ["required", "string", "max:255"],
            "email"    => ["required", "string", "max:255", "email", Rule::unique("users")->ignore($this->route("user"))],
            "username" => ["nullable", "string", "max:255", Rule::unique("users")->ignore($this->route("user"))],
            "bio"      => ["nullable", "string"],
        ];

        if ($this->isMethod("POST")) {
            $rules  = array_merge($rules, [
                "password" => ["required", "string", "confirmed", "min:8", "max:255"],
                "role_id"  => ["required", "string", "exists:roles,id"],
            ]);
        }

        if ($this->isMethod("PUT")) {
            $rules = array_merge($rules, [
                "old_password" => ["nullable", Rule::requiredIf($this->new_password != null), "string", "min:8", "max:255"],
                "new_password" => ["nullable", Rule::requiredIf($this->old_password != null), "string", "confirmed", "min:8", "max:255"],
            ]);
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            "name"     => "Name",
            "email"    => "Email",
            "password" => "Password",
            "role_id"  => "Role",
            "username" => "Username",
            "bio"      => "Bio",
        ];
    }

    public function passedValidation()
    {
        if ($this->isMethod("POST")) {
            if (!$this->username) $this->merge(["username" => uniqid("user")]);

            $this->merge(["email_verified_at" => now()]);
        }

        if ($this->isMethod("PUT") && $this->old_password) {
            if (!Hash::check($this->old_password, $this->route("user")->password)) {
                $this->validator->errors()->add("old_password", "The old password is incorrect");
                throw new ValidationException($this->validator);
            }
        }
    }
}
