<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GunungRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'negara_id'           => ['nullable', 'numeric', 'exists:negara,id'],
            'kode_kabupaten_kota' => ['nullable', 'numeric', 'exists:kabupaten_kota,kode'],
            'lokasi'              => ['nullable', 'max:255'],
            'nama'                => ['required', 'max:255'],
            'deskripsi'           => ['nullable'],
            'image'               => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
            'lat'                 => ['nullable', 'numeric'],
            'long'                => ['nullable', 'numeric'],
            'elev'                => ['required', 'numeric'],
        ];
    }

    public function attributes()
    {
        return [
            'negara_id'           => 'Negara',
            'kode_kabupaten_kota' => 'Kabupaten Kota',
            'lokasi'              => 'Lokasi',
            'nama'                => 'Nama',
            'deskripsi'           => 'Deskripsi',
            'image'               => 'Image',
            'lat'                 => 'Latitude',
            'long'                => 'Longitude',
            'elev'                => 'Elevasi',
        ];
    }
}
