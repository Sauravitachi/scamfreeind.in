@php
    $data = $data ?? new \stdClass;

    // Helper to find existing indices
    $indices = [];
    foreach ($data as $key => $value) {
        if (preg_match('/^expert_section_title_(\d+)$/', $key, $matches)) {
            $indices[] = (int)$matches[1];
        }
    }
    sort($indices);
    if (empty($indices)) {
        $indices = [0];
    }
@endphp

<div class="card shadow-sm rounded-3 border-0">
    <div class="card-header bg-white border-0 pt-6 px-6">
        <h3 class="fw-bold text-dark m-0">🎓 Expert Section Settings</h3>
        <p class="text-muted small mt-1 mb-0">Manage multiple experts with their names, designations, photos, and personalized colors.</p>
    </div>

    <div class="card-body px-6 pb-6 pt-4">
        <div id="expert-repeater">
            <div class="expert-items" id="expert-items-container">
                @foreach($indices as $index)
                    <div class="expert-block border rounded p-6 mb-8 position-relative bg-light-lighten shadow-sm" data-index="{{ $index }}">
                        <button type="button" class="btn btn-icon btn-sm btn-light-danger position-absolute top-0 end-0 m-2 remove-expert-btn" style="z-index: 10;">
                            <i class="ki-duotone ki-trash fs-2"></i>
                        </button>

                        <div class="row g-6">
                            <!-- Photo Section -->
                            <div class="col-lg-3 border-end">
                                <label class="fw-bold fs-6 mb-4 d-block text-gray-700">Expert Photo</label>
                                <div class="d-flex flex-column align-items-center">
                                    @php
                                        $imgKey = "expert_section_image_$index";
                                        $imageUrl = (isset($data->$imgKey) && !empty($data->$imgKey)) ? $appUiData->getImage($data->$imgKey) : asset('not-found-image.png');
                                    @endphp
                                    <div class="rounded-3 border border-dashed border-primary shadow-sm mb-4" 
                                         style="width: 140px; height: 140px; background-image: url('{{ $imageUrl }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                                    </div>
                                    <div class="w-100 px-3">
                                        <input type="file" name="expert_section_image_{{ $index }}" class="form-control form-control-sm form-control-solid" accept="image/*" />
                                        <div class="form-text fs-8 text-center text-muted">Recommended: Square ratio</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Section -->
                            <div class="col-lg-9">
                                <div class="row g-6">
                                    <!-- Expert Name (Title) -->
                                    <div class="col-md-8">
                                        <label class="fw-bold fs-6 mb-2 text-gray-700">Expert Name (Title)</label>
                                        <input type="text" name="expert_section_title_{{ $index }}" 
                                               class="form-control form-control-lg form-control-solid border-hover-primary" 
                                               placeholder="e.g., Dr. Rajesh Kumar" 
                                               value="{{ $data->{"expert_section_title_$index"} ?? '' }}" />
                                    </div>
                                    
                                    <!-- Title Color -->
                                    <div class="col-md-4">
                                        <label class="fw-bold fs-6 mb-2 text-gray-700">Name Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control form-control-color w-45px h-45px rounded picker" 
                                                   value="{{ $data->{"expert_section_title_color_$index"} ?? '#1B2124' }}" />
                                            <input type="text" name="expert_section_title_color_{{ $index }}" 
                                                   class="form-control form-control-solid hex-input" 
                                                   placeholder="#000000"
                                                   value="{{ $data->{"expert_section_title_color_$index"} ?? '#1B2124' }}" maxlength="7" />
                                        </div>
                                    </div>

                                    <!-- Designation (Subtitle) -->
                                    <div class="col-md-8">
                                        <label class="fw-bold fs-6 mb-2 text-gray-700">Designation (Subtitle)</label>
                                        <input type="text" name="expert_section_subtitle_{{ $index }}" 
                                               class="form-control form-control-lg form-control-solid border-hover-primary" 
                                               placeholder="e.g., Cyber Security Expert" 
                                               value="{{ $data->{"expert_section_subtitle_$index"} ?? '' }}" />
                                    </div>

                                    <!-- Subtitle Color -->
                                    <div class="col-md-4">
                                        <label class="fw-bold fs-6 mb-2 text-gray-700">Designation Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control form-control-color w-45px h-45px rounded picker" 
                                                   value="{{ $data->{"expert_section_subtitle_color_$index"} ?? '#6C757D' }}" />
                                            <input type="text" name="expert_section_subtitle_color_{{ $index }}" 
                                                   class="form-control form-control-solid hex-input" 
                                                   placeholder="#6C757D"
                                                   value="{{ $data->{"expert_section_subtitle_color_$index"} ?? '#6C757D' }}" maxlength="7" />
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label class="fw-bold fs-6 mb-2 text-gray-700">Email Address</label>
                                        <input type="email" name="expert_section_email_{{ $index }}" 
                                               class="form-control form-control-lg form-control-solid" 
                                               placeholder="expert@example.com" 
                                               value="{{ $data->{"expert_section_email_$index"} ?? '' }}" />
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-md-6">
                                        <label class="fw-bold fs-6 mb-2 text-gray-700">Phone Number</label>
                                        <input type="text" name="expert_section_phone_{{ $index }}" 
                                               class="form-control form-control-lg form-control-solid" 
                                               placeholder="+91 98765 43210" 
                                               value="{{ $data->{"expert_section_phone_$index"} ?? '' }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <button type="button" class="btn btn-flex btn-light-primary px-8 py-3 fw-bold shadow-sm" id="add-expert-btn">
                    <i class="ki-duotone ki-plus-square fs-1 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    Add Another Expert
                </button>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
$(document).ready(function() {
    let expertIndex = {{ max($indices) + 1 }};

    // Image Preview Helper
    $(document).on('change', 'input[type="file"]', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            const $preview = $(this).closest('.col-lg-3').find('.rounded-3');
            reader.onload = function(e) {
                $preview.css('background-image', `url(${e.target.result})`);
            }
            reader.readAsDataURL(file);
        }
    });

    // Color sync helper (delegated)
    $(document).on('input', '.picker', function() {
        $(this).next('.hex-input').val($(this).val());
    });
    
    $(document).on('input', '.hex-input', function() {
        let val = $(this).val();
        if(/^#[0-9A-F]{3,6}$/i.test(val)) {
            if (val.length === 4) {
                // Expand 3-digit hex
                val = '#' + val[1] + val[1] + val[2] + val[2] + val[3] + val[3];
            }
            $(this).prev('.picker').val(val);
        }
    });

    $('#add-expert-btn').click(function() {
        let newIndex = expertIndex++;
        let template = `
            <div class="expert-block border rounded p-6 mb-8 position-relative bg-light-lighten shadow-sm" data-index="${newIndex}" style="display:none;">
                <button type="button" class="btn btn-icon btn-sm btn-light-danger position-absolute top-0 end-0 m-2 remove-expert-btn" style="z-index: 10;">
                    <i class="ki-duotone ki-trash fs-2"></i>
                </button>

                <div class="row g-6">
                    <div class="col-lg-3 border-end">
                        <label class="fw-bold fs-6 mb-4 d-block text-gray-700">Expert Photo</label>
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-3 border border-dashed border-primary shadow-sm mb-4" 
                                 style="width: 140px; height: 140px; background-image: url('{{ asset('not-found-image.png') }}'); background-size: cover; background-position: center;">
                            </div>
                            <div class="w-100 px-3">
                                <input type="file" name="expert_section_image_${newIndex}" class="form-control form-control-sm form-control-solid" accept="image/*" />
                                <div class="form-text fs-8 text-center text-muted">Recommended: Square ratio</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="row g-6">
                            <div class="col-md-8">
                                <label class="fw-bold fs-6 mb-2 text-gray-700">Expert Name (Title)</label>
                                <input type="text" name="expert_section_title_${newIndex}" class="form-control form-control-lg form-control-solid border-hover-primary" placeholder="Enter name" />
                            </div>
                            
                            <div class="col-md-4">
                                <label class="fw-bold fs-6 mb-2 text-gray-700">Name Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color w-45px h-45px rounded picker" value="#1B2124" />
                                    <input type="text" name="expert_section_title_color_${newIndex}" class="form-control form-control-solid hex-input" value="#1B2124" maxlength="7" />
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label class="fw-bold fs-6 mb-2 text-gray-700">Designation (Subtitle)</label>
                                <input type="text" name="expert_section_subtitle_${newIndex}" class="form-control form-control-lg form-control-solid border-hover-primary" placeholder="Enter designation" />
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold fs-6 mb-2 text-gray-700">Designation Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color w-45px h-45px rounded picker" value="#6C757D" />
                                    <input type="text" name="expert_section_subtitle_color_${newIndex}" class="form-control form-control-solid hex-input" value="#6C757D" maxlength="7" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold fs-6 mb-2 text-gray-700">Email Address</label>
                                <input type="email" name="expert_section_email_${newIndex}" class="form-control form-control-lg form-control-solid" placeholder="expert@example.com" />
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold fs-6 mb-2 text-gray-700">Phone Number</label>
                                <input type="text" name="expert_section_phone_${newIndex}" class="form-control form-control-lg form-control-solid" placeholder="+91 98765 43210" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        
        let $newItem = $(template);
        $('#expert-items-container').append($newItem);
        $newItem.fadeIn(400);
        
        // Scroll to new item
        $('html, body').animate({
            scrollTop: $newItem.offset().top - 100
        }, 500);
    });

    $(document).on('click', '.remove-expert-btn', function() {
        const $block = $(this).closest('.expert-block');
        if ($('.expert-block').length > 1) {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This expert section will be removed from the form.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $block.fadeOut(300, function() { $(this).remove(); });
                    }
                });
            } else {
                if (confirm("Are you sure you want to remove this expert section?")) {
                    $block.fadeOut(300, function() { $(this).remove(); });
                }
            }
        } else {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: 'Cannot Remove',
                    text: 'At least one expert is required in this section.',
                    icon: 'info'
                });
            } else {
                alert("At least one expert is required in this section.");
            }
        }
    });
});
</script>
@endpush
