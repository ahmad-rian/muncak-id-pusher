<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KabupatenKotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "kode"          => [
                "required",
                Rule::unique("kabupaten_kota")->ignore($this->route("kabupatenKota"), "kode")
            ],
            "kode_provinsi" => ["required", "string", "max:255", "exists:provinsi,kode"],
            "nama"          => ["required", "string", "max:255"],
            "nama_lain"     => ["required", "string", "max:255"],
            "slug"          => [
                "required",
                "string",
                "max:255",
                Rule::unique("kabupaten_kota")->ignore($this->route("kabupatenKota"), "kode")
            ],
            "lat"           => ["nullable", "numeric"],
            "long"          => ["nullable", "numeric"],
            "timezone"      => ["nullable", "string", "max:255"],
        ];
    }

    public function attributes()
    {
        return [
            "kode"          => "Kode",
            "kode_provinsi" => "Provinsi",
            "nama"          => "Nama",
            "nama_lain"     => "Nama Lain",
            "slug"          => "Slug",
            "lat"           => "Latitude",
            "long"          => "Longitude",
            "timezone"      => "Timezone",
        ];
    }
}
