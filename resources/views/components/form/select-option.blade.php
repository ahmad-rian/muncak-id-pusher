@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'required' => false,
    'readonly' => false,
    'option' => [],
])

<div>
  @if ($label ?? false)
    <div class="label">
      <label class="{{ $required ? 'required' : null }} label-text font-medium" for="{{ $name }}">
        {{ $label ?? $name }}
      </label>
    </div>
  @endif

  <select class="@error($name) {{ 'select-error' }} @enderror select select-bordered select-sm w-full"
    id="{{ $name }}" name="{{ $name }}" @required($required) @readonly($readonly)>
    @if ($placeholder ?? ($label ?? false))
      <option value>{{ $placeholder ?? ($label ?? $name) }}</option>
    @endif
    @foreach ($option as $key => $item)
      <option value="{{ $key }}" @selected($key == $value)>{{ $item }}</option>
    @endforeach
  </select>

  @error($name)
    <div class="label">
      <span class="label-text-alt text-error">{{ $message }}</span>
    </div>
  @enderror
</div>
