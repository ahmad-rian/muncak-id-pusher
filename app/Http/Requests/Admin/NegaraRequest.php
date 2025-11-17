<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NegaraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "nama"      => ["required", "string", "max:255"],
            "nama_lain" => ["nullable", "string", "max:255"],
            "slug"      => [
                "required",
                "string",
                "max:255",
                Rule::unique("negara")->ignore($this->route("negara"))
            ],
            "kode"      => ["nullable", "string", "max:255"],
        ];
    }

    public function attributes()
    {
        return [
            "nama"      => "Nama",
            "nama_lain" => "Nama Lain",
            "slug"      => "Slug",
            "kode"      => "Kode",
        ];
    }
}