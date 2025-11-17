<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "kode"           => [
                "required",
                Rule::unique("desa")->ignore($this->route("desa"), "kode")
            ],
            "kode_kecamatan" => ["required", "string", "max:255", "exists:kecamatan,kode"],
            "nama"           => ["required", "string", "max:255"],
            "nama_lain"      => ["required", "string", "max:255"],
            "slug"           => [
                "required",
                "string",
                "max:255",
                Rule::unique("desa")->ignore($this->route("desa"), "kode")
            ],
            "lat"            => ["nullable", "numeric"],
            "long"           => ["nullable", "numeric"],
            "timezone"       => ["nullable", "string", "max:255"],
        ];
    }

    public function attributes()
    {
        return [
            "kode"           => "Kode",
            "kode_kecamatan" => "Kecamatan",
            "nama"           => "Nama",
            "nama_lain"      => "Nama Lain",
            "slug"           => "Slug",
            "lat"            => "Latitude",
            "long"           => "Longitude",
            "timezone"       => "Timezone",
        ];
    }
}
