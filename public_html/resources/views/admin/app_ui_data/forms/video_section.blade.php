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

        <!-- Titles + Subtitles -->
        <div class="row g-6 mb-10">
            <div class="col-lg-6">
                <label class="fw-semibold fs-6 mb-2">Section Titles</label>
                <div id="title-wrapper">
                    @php
                        $titles = (array) ($data->video_section_title ?? []);
                    @endphp
                    @if(!empty($titles))
                        @foreach($titles as $title)
                            <div class="title-item mb-3 d-flex gap-2">
                                <input type="text" name="video_section_title[]" 
                                    class="form-control form-control-lg form-control-solid"
                                    value="{{ $title }}" placeholder="Enter title">
                                <button type="button" class="btn btn-danger remove-item">X</button>
                            </div>
                        @endforeach
                    @else
                        <div class="title-item mb-3 d-flex gap-2">
                            <input type="text" name="video_section_title[]" 
                                class="form-control form-control-lg form-control-solid"
                                placeholder="Enter title">
                            <button type="button" class="btn btn-danger remove-item">X</button>
                        </div>
                    @endif
                </div>
                <button type="button" id="add-title" class="btn btn-light-primary btn-sm mt-2 bg-primary text-white">+ Add Title</button>
            </div>

            <div class="col-lg-6">
                <label class="fw-semibold fs-6 mb-2">Subtitles</label>
                <div id="subtitle-wrapper">
                    @php
                        $subtitles = (array) ($data->video_section_subtitle ?? []);
                    @endphp
                    @if(!empty($subtitles))
                        @foreach($subtitles as $subtitle)
                            <div class="subtitle-item mb-3 d-flex gap-2">
                                <input type="text" name="video_section_subtitle[]" 
                                    class="form-control form-control-lg form-control-solid"
                                    value="{{ $subtitle }}" placeholder="Enter subtitle">
                                <button type="button" class="btn btn-danger remove-item">X</button>
                            </div>
                        @endforeach
                    @else
                        <div class="subtitle-item mb-3 d-flex gap-2">
                            <input type="text" name="video_section_subtitle[]" 
                                class="form-control form-control-lg form-control-solid"
                                placeholder="Enter subtitle">
                            <button type="button" class="btn btn-danger remove-item">X</button>
                        </div>
                    @endif
                </div>
                <button type="button" id="add-subtitle" class="btn btn-light-primary btn-sm mt-2 bg-primary text-white">+ Add Subtitle</button>
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

        <!-- Video URLs -->
        <div class="mb-6 mt-4">
            <label class="fw-semibold fs-6 mb-2">Video URLs</label>
            <div id="video-wrapper">
                @php
                    $videos = (array) ($data->video_section_video ?? []);
                @endphp

                @if(!empty($videos))
                    @foreach($videos as $video)
                        <div class="video-item mb-3 d-flex gap-2">
                            <input type="text" name="video_section_video[]" 
                                class="form-control form-control-lg form-control-solid"
                                value="{{ $video }}" placeholder="Enter video URL">
                            <button type="button" class=" remove-item">X</button>
                        </div>
                    @endforeach
                @else
                    <div class="video-item mb-3 d-flex gap-2">
                        <input type="text" name="video_section_video[]" 
                            class="form-control form-control-lg form-control-solid"
                            placeholder="Enter video URL">
                        <button type="button" class="btn btn-danger remove-item">X</button>
                    </div>
                @endif
            </div>

            <button type="button" id="add-video" class="btn btn-light-primary btn-sm mt-2 bg-primary text-white">
                + Add Video
            </button>
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

function createItemRow(name, placeholder) {
    const div = document.createElement('div');
    div.classList.add('mb-3', 'd-flex', 'gap-2');
    div.innerHTML = `
        <input type="text" name="${name}[]" 
            class="form-control form-control-lg form-control-solid"
            placeholder="${placeholder}">
        <button type="button" class="btn btn-danger remove-item">X</button>
    `;
    return div;
}

document.getElementById('add-title')?.addEventListener('click', function () {
    document.getElementById('title-wrapper').appendChild(createItemRow('video_section_title', 'Enter title'));
});

document.getElementById('add-subtitle')?.addEventListener('click', function () {
    document.getElementById('subtitle-wrapper').appendChild(createItemRow('video_section_subtitle', 'Enter subtitle'));
});

document.getElementById('add-video')?.addEventListener('click', function () {
    document.getElementById('video-wrapper').appendChild(createItemRow('video_section_video', 'Enter video URL'));
});

// Remove
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-item')) {
        e.target.parentElement.remove();
    }
});
</script>
@endpush