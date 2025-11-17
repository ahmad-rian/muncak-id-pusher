<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RuteTingkatKesulitanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "nama"      => ["required", "string", "max:255", Rule::unique("rute_tingkat_kesulitan")->ignore($this->route("id"))],
            "deskripsi" => ["nullable", "string"],
        ];
    }

    public function attributes()
    {
        return [
            "nama"      => "Nama",
            "deskripsi" => "Deskripsi",
        ];
    }
}
