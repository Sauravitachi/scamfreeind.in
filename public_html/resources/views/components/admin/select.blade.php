<div class="select-wrapper mb-3">
    @isset($label)
        <label class="form-label {{ isset($required) ? 'required' : '' }}">
            {{ $label }}
        </label>
    @endisset

    <select
        {{ $attributes->class(['form-select'])->except(['options', 'required', 'placeholder', 'disablePlaceholder', 'disabled', 'selected']) }}
        {{ ($disabled ?? false ? 'disabled' : '') }}
    >
        @if (isset($slot) && $slot->isNotEmpty())
            {{ $slot }}
        @else
            @if (isset($placeholder))
                <option value @isset($disablePlaceholder) disabled @endisset @isset($selectPlaceholder) selected @endisset>
                    <span class="text-danger">{{ $placeholder }}</span>
                </option>
            @endif

            @if (isset($options) && is_array($options) && !empty($options))
                @foreach ($options as $value => $label)
                    @php
                        if(isset($selected)) {
                            $isSelected = is_array($selected) ? in_array($value, $selected) : $value == $selected;
                        } else {
                            $isSelected = false;
                        }
                    @endphp
                    <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            @endif
        @endif
    </select>

    @isset($name)
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endisset
</div>
