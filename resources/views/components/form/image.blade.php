@props(['name', 'label' => null, 'value' => null, 'required' => false, 'readonly' => false])

<div x-data="{{ $name }}">
  <div class="label">
    <label
      class="{{ $required ? 'required' : null }} {{ $readonly ? 'pointer-events-none' : null }} label-text font-medium"
      for="{{ $name }}">
      {{ $label }}
    </label>
  </div>

  <div class="h-32 rounded-md border border-dashed border-base-content/30 p-4">
    <label class="flex h-full items-center justify-center" x-show="!value" x-cloak for="{{ $name }}">
      <p
        class="{{ $readonly ? 'pointer-events-none' : null }} block flex-col items-center justify-center gap-1 text-center text-base-content/70">
        <x-gmdi-image-r class="size-12 mx-auto" />
        <span class="text-sm">Image Preview</span>
      </p>
    </label>

    <label class="{{ $readonly ? 'pointer-events-none' : null }} h-full w-full" for="{{ $name }}">
      <img class="mx-auto h-full object-contain" x-show="value" x-cloak :src="value" alt="Image Preview">
    </label>
  </div>

  <div class="{{ $readonly ? 'hidden' : null }} mt-2">
    <input class="@error($name) {{ 'file-input-error' }} @enderror file-input file-input-bordered file-input-sm w-full"
      id="{{ $name }}" type="file" name="{{ $name }}" accept=".jpg,.jpeg,.png,.webp"
      x-on:change="previewImage" @required($required)>
    @error($name)
      <div class="label">
        <span class="label-text-alt text-error">{{ $message }}</span>
      </div>
    @enderror
  </div>
</div>

<script title="alpine.js">
  function {{ $name }}() {
    return {
      value: "{{ $value }}",
      previewImage(event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.value = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      }
    }
  }
</script>
