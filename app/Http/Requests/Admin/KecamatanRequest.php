<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KecamatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "kode"                => [
                "required",
                Rule::unique("kecamatan")->ignore($this->route("kecamatan"), "kode")
            ],
            "kode_kabupaten_kota" => ["required", "string", "max:255", "exists:kabupaten_kota,kode"],
            "nama"                => ["required", "string", "max:255"],
            "nama_lain"           => ["required", "string", "max:255"],
            "slug"                => [
                "required",
                "string",
                "max:255",
                Rule::unique("kecamatan")->ignore($this->route("kecamatan"), "kode")
            ],
            "lat"                 => ["nullable", "numeric"],
            "long"                => ["nullable", "numeric"],
            "timezone"            => ["nullable", "string", "max:255"],
        ];
    }

    public function attributes()
    {
        return [
            "kode"                => "Kode",
            "kode_kabupaten_kota" => "Kabupaten Kota",
            "nama"                => "Nama",
            "nama_lain"           => "Nama Lain",
            "slug"                => "Slug",
            "lat"                 => "Latitude",
            "long"                => "Longitude",
            "timezone"            => "Timezone",
        ];
    }
}
