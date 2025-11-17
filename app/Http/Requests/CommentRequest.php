<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'point_id'     => ['nullable', 'exists:point,id'],
            'content'      => ['required', 'string'],
            'rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'kondisi_rute' => ['nullable', 'json'],
            'gallery'      => ['nullable', 'array', 'max:3'],
            'gallery.*'    => ['image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ];
    }

    public function attributes()
    {
        return [
            'point_id'     => 'Titik',
            'content'      => 'Ulasan',
            'rating'       => 'Rating',
            'kondisi_rute' => 'Kondisi Rute',
        ];
    }
}
