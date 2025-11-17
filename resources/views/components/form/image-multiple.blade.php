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
    <label class="flex h-full items-center justify-center" x-show="!value.length" x-cloak for="{{ $name }}">
      <p
        class="{{ $readonly ? 'pointer-events-none' : null }} block flex-col items-center justify-center gap-1 text-center text-base-content/70">
        <x-gmdi-image-r class="size-12 mx-auto" />
        <span class="text-sm">Image Preview</span>
      </p>
    </label>

    <label class="no-scrollbar flex items-center gap-4 overflow-x-auto" for="{{ $name }}"
      x-show="value.length > 0" x-cloak>
      <template x-for="(img, index) in value" :key="index">
        <div class="shrink-0 grow-0 rounded-sm border border-base-300">
          <img class="h-24 w-40 object-contain" :src="img" alt="Image Preview">
        </div>
      </template>
    </label>
  </div>

  <div class="{{ $readonly ? 'hidden' : null }} mt-2">
    <input
      class="@error($name) {{ 'file-input-error' }} @enderror file-input file-input-bordered file-input-sm w-full"
      id="{{ $name }}" type="file" name="{{ "{$name}[]" }}" accept=".jpg,.jpeg,.png,.webp"
      x-on:change="previewImages" multiple @required($required)>
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
      value: @json($value ?? []),
      previewImages(event) {
        const files = Array.from(event.target.files);
        const reader = new FileReader();
        this.value = [];

        const readNextFile = (index) => {
          if (index < files.length) {
            const file = files[index];
            reader.onload = (e) => {
              this.value.push(e.target.result);
              readNextFile(index + 1);
            };
            reader.readAsDataURL(file);
          }
        };

        readNextFile(0);
      }
    }
  }
</script>
