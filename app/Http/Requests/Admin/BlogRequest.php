<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => [
                'required',
                'string',
                'max:255',
                Rule::unique('blog', 'slug')->ignore($this->route('blog')?->id),
                'regex:/^[a-zA-Z0-9]+(?:[-_][a-zA-Z0-9]+)*$/'
            ],
            'deskripsi_singkat' => ['nullable', 'string'],
            'content'           => ['required', 'string', 'max:16777215'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published'      => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title'             => 'Judul',
            'slug'              => 'Slug',
            'deskripsi_singkat' => 'Deskripsi Singkat',
            'content'           => 'Konten',
            'image'             => 'Gambar',
            'is_published'      => 'Dipublikasikan',
        ];
    }

    protected function passedValidation()
    {
        $this->merge([
            'user_id'      => Auth::user()->id,
            'is_published' => (bool) $this->is_published,
        ]);
    }
}
