<?php

namespace App\Http\Requests\Admin;

use App\Models\Rute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gunung_id'                 => ['required', Rule::exists('gunung', 'id')],
            'negara_id'                 => ['nullable', 'numeric', Rule::exists('negara', 'id')],
            'kode_desa'                 => ['nullable', Rule::exists('desa', 'kode')],
            'lokasi'                    => ['nullable', 'string', 'max:255'],
            'nama'                      => ['required', 'max:255'],
            'deskripsi'                 => ['nullable'],
            'informasi'                 => ['nullable'],
            'aturan_dan_larangan'       => ['nullable'],
            'is_verified'               => ['sometimes', 'boolean'],
            'image'                     => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
            'is_cuaca_siap'             => ['sometimes', 'boolean'],
            'is_kalori_siap'            => ['sometimes', 'boolean'],
            'is_kriteria_jalur_siap'    => ['sometimes', 'boolean'],
            'segmentasi'                => ['required', 'integer'],
            'rute_tingkat_kesulitan_id' => ['nullable', Rule::exists('rute_tingkat_kesulitan', 'id')],
            'gallery'                   => ['nullable', 'array'],
            'gallery.*'                 => ['image', 'mimes:jpeg,png,jpg', 'max:10240'],
            'a_k'                       => ['nullable', 'numeric'],
            'b_k'                       => ['nullable', 'numeric'],
            'c_k'                       => ['nullable', 'numeric'],
            'd_k'                       => ['nullable', 'numeric'],
            'a_wt'                      => ['nullable', 'numeric'],
            'b_wt'                      => ['nullable', 'numeric'],
            'c_wt'                      => ['nullable', 'numeric'],
            'd_wt'                      => ['nullable', 'numeric'],
            'e_wt'                      => ['nullable', 'numeric'],
            'f_wt'                      => ['nullable', 'numeric'],
            'g_wt'                      => ['nullable', 'numeric'],
            'h_wt'                      => ['nullable', 'numeric'],
            'i_wt'                      => ['nullable', 'numeric'],
            'j_wt'                      => ['nullable', 'numeric'],
            'k_wt'                      => ['nullable', 'numeric'],
            'a_cps'                     => ['nullable', 'numeric'],
            'b_cps'                     => ['nullable', 'numeric'],
            'c_kr'                      => ['nullable', 'numeric'],
            'd_kr'                      => ['nullable', 'numeric'],
            'e_kr'                      => ['nullable', 'numeric'],
            'f_kr'                      => ['nullable', 'numeric'],
            'g_kr'                      => ['nullable', 'numeric'],
            'h_kr'                      => ['nullable', 'numeric'],
            'gallery_new'               => ['nullable', 'array'],
            'gallery_new.*'             => ['image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ];
    }

    protected function passedValidation()
    {
        $this->merge([
            'is_verified'            => (bool) $this->is_verified,
            'is_cuaca_siap'          => (bool) $this->is_cuaca_siap,
            'is_kalori_siap'         => (bool) $this->is_kalori_siap,
            'is_kriteria_jalur_siap' => (bool) $this->is_kriteria_jalur_siap,
        ]);
    }

    public function attributes()
    {
        return [
            'gunung_id'              => 'ID',
            'negara_id'              => 'Negara',
            'kode_desa'              => 'Desa',
            'lokasi'                 => 'Lokasi',
            'nama'                   => 'Nama',
            'deskripsi'              => 'Deskripsi',
            'informasi'              => 'Informasi',
            'aturan_dan_larangan'    => 'Aturan dan Larangan',
            'is_verified'            => 'Terverifikasi',
            'image'                  => 'Gambar',
            'is_cuaca_siap'          => 'Kesiapan Cuaca',
            'is_kalori_siap'         => 'Kesiapan Kalori',
            'is_kriteria_jalur_siap' => 'Kriteria Jalur',
            'segmentasi'             => 'Segmentasi',
            'gallery'                => 'Galeri',
            'gallery.*'              => 'Item Galeri',
            'a_k'                    => 'Variabel Kalori A',
            'b_k'                    => 'Variabel Kalori B',
            'c_k'                    => 'Variabel Kalori C',
            'd_k'                    => 'Variabel Kalori D',
            'a_wt'                   => 'Variabel Waktu Tempuh A',
            'b_wt'                   => 'Variabel Waktu Tempuh B',
            'c_wt'                   => 'Variabel Waktu Tempuh C',
            'd_wt'                   => 'Variabel Waktu Tempuh D',
            'e_wt'                   => 'Variabel Waktu Tempuh E',
            'f_wt'                   => 'Variabel Waktu Tempuh F',
            'g_wt'                   => 'Variabel Waktu Tempuh G',
            'h_wt'                   => 'Variabel Waktu Tempuh H',
            'i_wt'                   => 'Variabel Waktu Tempuh I',
            'j_wt'                   => 'Variabel Waktu Tempuh J',
            'k_wt'                   => 'Variabel Waktu Tempuh K',
            'a_cps'                  => 'Variabel Cuaca Per Segmen A',
            'b_cps'                  => 'Variabel Cuaca Per Segmen B',
            'c_kr'                   => 'Variabel Kriteria Jalur C',
            'd_kr'                   => 'Variabel Kriteria Jalur D',
            'e_kr'                   => 'Variabel Kriteria Jalur E',
            'f_kr'                   => 'Variabel Kriteria Jalur F',
            'g_kr'                   => 'Variabel Kriteria Jalur G',
            'h_kr'                   => 'Variabel Kriteria Jalur H',
            'gallery_new'            => 'Galeri Baru',
            'gallery_new.*'          => 'Item Galeri Baru',
        ];
    }
}
