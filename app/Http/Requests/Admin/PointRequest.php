<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'                     => ['nullable', 'string'],
            'deskripsi'                => ['nullable', 'string'],
            'is_lokasi_prediksi_cuaca' => ['sometimes', 'boolean'],
            'is_waypoint'              => ['sometimes', 'boolean'],
            'gallery'                  => ['nullable', 'array'],
            'gallery.*'                => ['image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ];
    }

    protected function passedValidation()
    {
        $this->merge([
            'is_lokasi_prediksi_cuaca' => (bool) $this->is_lokasi_prediksi_cuaca,
            'is_waypoint'              => (bool) $this->is_waypoint,
        ]);
    }

    public function attributes()
    {
        return [
            'nama'                     => 'Nama',
            'deskripsi'                => 'Deskripsi',
            'is_lokasi_prediksi_cuaca' => 'Prediksi Cuaca',
            'is_waypoint'              => 'Waypoint',
            'gallery'                  => 'Galeri',
            'gallery.*'                => 'Item Galeri',
        ];
    }
}
