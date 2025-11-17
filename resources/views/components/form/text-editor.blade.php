@props(['name', 'label' => null, 'placeholder' => null, 'value' => null, 'required' => false, 'readonly' => false])

<div>
  <div class="label">
    <label class="{{ $required ? 'required' : null }} label-text font-medium" for="{{ $name }}">
      {{ $label ?? $name }}
    </label>
  </div>

  <div class="rounded-lg border border-base-300">
    <input id="{{ $name }}" value="{{ $value }}" type="hidden" name="{{ $name }}" />
    <trix-editor
      class="{{ $attributes->get('class') ?? 'h-auto' }} prose !max-w-full overflow-y-auto dark:prose-invert"
      input="{{ $name }}" placeholder="{{ $placeholder ?? ($label ?? $name) }}"></trix-editor>
  </div>

  @error($name)
    <div class="label">
      <span class="label-text-alt text-error">{{ $message }}</span>
    </div>
  @enderror
</div>
