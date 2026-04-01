@php
    $image = $data->bg_image_url ?? asset('not-found-image.png');

    $titleColor = $data->video_section_title_color ?? '#6c757d';
    $subtitleColor = $data->video_section_subtitle_color ?? '#6c757d';
@endphp

<div class="card shadow-sm rounded-3 border-0">
    <!-- Header -->
    <div class="card-header bg-white border-0 pt-6 px-6">
        <h3 class="fw-bold text-dark m-0">🎬 Video Section Settings</h3>
        <p class="text-muted small mt-1 mb-0">Manage video section content & appearance</p>
    </div>

    <div class="card-body px-6 pb-6 pt-4">

        <!-- Background Image -->
        <div class="mb-10">
            <label class="fw-semibold fs-6 mb-4 d-block required">Background Image</label>

            <div class="d-flex flex-wrap gap-5 align-items-center">
                <!-- Preview -->
                <div
                    class="rounded-3 border shadow-sm"
                    style="
                        width: 160px;
                        height: 120px;
                        background-image: url('{{ $image }}');
                        background-size: cover;
                        background-position: center;
                    ">
                </div>

                <!-- Upload -->
                <div class="flex-grow-1" style="max-width: 420px;">
                    <input
                        type="file"
                        name="bg_image"
                        class="form-control form-control-solid"
                        accept=".png,.jpg,.jpeg,.svg,.avif"
                    />
                    <div class="form-text">PNG, JPG, SVG, AVIF (recommended: 16:9 ratio)</div>
                </div>
            </div>
        </div>

        <!-- Title + Subtitle -->
        <div class="row g-6 mb-10">
            <div class="col-lg-6">
                <label class="fw-semibold fs-6 mb-2">Section Title</label>
                <input
                    type="text"
                    name="video_section_title"
                    class="form-control form-control-lg form-control-solid"
                    placeholder="Enter title"
                    value="{{ $data->video_section_title ?? '' }}"
                />
            </div>

            <div class="col-lg-6">
                <label class="fw-semibold fs-6 mb-2">Subtitle</label>
                <input
                    type="text"
                    name="video_section_subtitle"
                    class="form-control form-control-lg form-control-solid"
                    placeholder="Enter subtitle"
                    value="{{ $data->video_section_subtitle ?? '' }}"
                />
            </div>
        </div>

        <!-- Colors -->
        <div class="row g-6 mb-10">

            <!-- Title Color -->
            <div class="col-lg-6">
                <label class="fw-semibold fs-6 mb-3">Title Color</label>

                <div class="d-flex align-items-center gap-3">
                    <input
                        type="color"
                        id="titleColorPicker"
                        value="{{ $titleColor }}"
                        class="form-control form-control-color"
                    />

                    <input
                        type="text"
                        id="titleColorHex"
                        name="video_section_title_color"
                        class="form-control form-control-solid"
                        value="{{ $titleColor }}"
                        maxlength="7"
                        style="max-width: 200px;"
                    />
                </div>
            </div>

            <!-- Subtitle Color -->
            <div class="col-lg-6">
                <label class="fw-semibold fs-6 mb-3">Subtitle Color</label>

                <div class="d-flex align-items-center gap-3">
                    <input
                        type="color"
                        id="subtitleColorPicker"
                        value="{{ $subtitleColor }}"
                        class="form-control form-control-color"
                    />

                    <input
                        type="text"
                        id="subtitleColorHex"
                        name="video_section_subtitle_color"
                        class="form-control form-control-solid"
                        value="{{ $subtitleColor }}"
                        maxlength="7"
                        style="max-width: 200px;"
                    />
                </div>
            </div>

        </div>

        <!-- Video Upload -->
        <div class="mb-6">
            <label class="fw-semibold fs-6 mb-3 d-block">Upload Video</label>

            <input
                type="file"
                name="video_section_video"
                class="form-control form-control-solid"
                accept="video/mp4,video/avi,video/mov,video/wmv"
            />

            <div class="form-text">
                Allowed: MP4, MOV, AVI, WMV (Max: 20MB)
            </div>

            @if(!empty($data->video_section_video_url))
                <div class="mt-4">
                    <video width="260" controls class="rounded shadow-sm">
                        <source src="{{ $data->video_section_video_url }}" type="video/mp4">
                    </video>
                </div>
            @endif
        </div>

    </div>
</div>

@push('script')
<script>
function setupColorSync(pickerId, inputId, fallback) {
    const picker = document.getElementById(pickerId);
    const input = document.getElementById(inputId);

    if (!picker || !input) return;

    // Fix initial value
    let value = normalizeHex(input.value) || fallback;
    picker.value = value;
    input.value = value;

    // Picker → Input
    picker.addEventListener('input', function () {
        input.value = picker.value;
    });

    // Input → Picker
    input.addEventListener('input', function () {
        const normalized = normalizeHex(input.value);
        if (normalized) {
            picker.value = normalized;
        }
    });

    // Fix invalid input on blur
    input.addEventListener('blur', function () {
        const normalized = normalizeHex(input.value) || fallback;
        input.value = normalized;
        picker.value = normalized;
    });
}

function normalizeHex(value) {
    if (!value) return null;

    let hex = value.trim();

    if (!hex.startsWith('#')) {
        hex = '#' + hex;
    }

    if (/^#([0-9A-Fa-f]{3})$/.test(hex)) {
        hex = '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
    }

    if (!/^#([0-9A-Fa-f]{6})$/.test(hex)) {
        return null;
    }

    return hex.toLowerCase();
}

setupColorSync('titleColorPicker', 'titleColorHex', '#6c757d');
setupColorSync('subtitleColorPicker', 'subtitleColorHex', '#6c757d');
</script>
@endpush