@props([
    'type' => 'text',
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'required' => false,
    'readonly' => false,
])

<div>
  <div class="label">
    <label class="{{ $required ? 'required' : null }} label-text font-medium" for="{{ $name }}">
      {{ $label ?? $name }}
    </label>
  </div>

  <input
    class="@error($name) {{ 'input-error' }} @enderror {{ $readonly ?? false ? 'bg-base-200' : '' }} input input-sm input-bordered w-full"
    id="{{ $name }}" value="{{ $value }}" type="{{ $type }}"
    placeholder="{{ $placeholder ?? ($label ?? $name) }}" name="{{ $name }}" @required($required)
    @readonly($readonly) />

  @error($name)
    <div class="label">
      <span class="label-text-alt text-error">{{ $message }}</span>
    </div>
  @enderror
</div>
