<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => ["required", "string", "max:255", Rule::unique("roles")->ignore($this->route("role"))],
        ];
    }

    public function passedValidation()
    {
        if ($this->isMethod("POST")) $this->merge(["guard_name" => "web"]);
    }

    public function attributes()
    {
        return [
            "name" => "Nama",
        ];
    }
}
