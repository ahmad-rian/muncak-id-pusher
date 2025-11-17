@props(['name', 'label' => null, 'placeholder' => null, 'value' => null, 'required' => false, 'readonly' => false])

<div>
  <div class="label">
    <label class="{{ $required ? 'required' : null }} label-text font-medium"
      for="{{ $name }}">{{ $label ?? $name }}</label>
  </div>

  <textarea class="@error($name) {{ 'textarea-error' }} @enderror textarea textarea-bordered textarea-sm w-full"
    id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder ?? ($label ?? $name) }}" rows="3"
    @required($required) @readonly($readonly)>{{ $value }}</textarea>

  @error($name)
    <div class="label">
      <span class="label-text-alt text-error">{{ $message }}</span>
    </div>
  @enderror
</div>
