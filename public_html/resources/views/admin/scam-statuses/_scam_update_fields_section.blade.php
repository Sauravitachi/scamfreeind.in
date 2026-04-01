@use(App\Enums\ScamStatusFieldType)

@php /** @var null|\App\Models\ScamStatus $scamStatus */ @endphp

@section('single-update-field-area')
    <div class="row align-items-center border rounded p-3 single-field-area my-3 position-relative">
        <x-admin.button variant='close' class="position-absolute top-0 end-0 m-2 btn-close" aria-label="Close" />

        <div class="col-xl-2 col-lg-3">
            <x-admin.select class="select2" name="updatable_fields[__INDEX__][status_field_type]" label="Status Field Type" placeholder='Select' :options="ScamStatusFieldType::selectArray()" disablePlaceholder selectPlaceholder required />
        </div>

        <div class="col-xl-2 col-lg-3">
            <div class="mt-4 d-flex justify-content-center">
                <input type="hidden" name="updatable_fields[__INDEX__][is_required]" value="0">
                <x-admin.checkbox name="updatable_fields[__INDEX__][is_required]" label="Is Required?" value="1" />
            </div>
        </div>

        <div class="col-xl-2 col-lg-3">
            <div class="mt-4 d-flex justify-content-center">
                <input type="hidden" name="updatable_fields[__INDEX__][prefill_previous_value]" value="0">
                <x-admin.checkbox name="updatable_fields[__INDEX__][prefill_previous_value]" label="Prefill Previous Value?" value="1" />
            </div>
        </div>
    </div>
@endsection

<div>
    <h3>
        <i class="ti ti-corner-up-right"></i>
        Manage Scam Update Fields
        <x-admin.button label='Add Field' class="btn-sm float-end add-field-btn" variant='warning' icon='ti ti-plus' />
    </h3>
    <div class="my-4" id="update-field-section">
    </div>
</div>


@push('script')
    <script>
        const UPDATE_FIELD_SELECTION = {
            fieldTypes: @js(ScamStatusFieldType::selectArray()),

            register() {
                this.$container = $('#update-field-section');
                this.rawTemplate = @js(view()->yieldContent('single-update-field-area'));
                this.maxFields = Object.keys(this.fieldTypes).length;

                this.existingFields = @json($scamStatus?->statusUpdateFields ?? []);

                this.bindEvents();
                this.populateExistingFields();
                this.updateAddButtonState();
            },

            bindEvents() {
                $(document).on('click', '.add-field-btn', () => this.addField());

                this.$container.on('click', '.btn-close', (e) => {
                    $(e.currentTarget).closest('.single-field-area').remove();
                    this.updateAddButtonState();
                    this.updateFieldNames(); 
                });
            },

            addField() {
                const currentCount = this.$container.find('.single-field-area').length;

                if (currentCount < this.maxFields) {
                    let newFieldHtml = this.rawTemplate.replace(/__INDEX__/g, currentCount);
                    this.$container.append(newFieldHtml);
                    this.reinitializePlugins();
                    this.updateAddButtonState();
                } else {
                    alert(`You can add up to ${this.maxFields} fields only.`);
                }
            },

            reinitializePlugins() {
                initSelect2(this.$container.find('.select2'));
            },

            updateAddButtonState() {
                const currentCount = this.$container.find('.single-field-area').length;
                if (currentCount >= this.maxFields) {
                    $('.add-field-btn').prop('disabled', true);
                } else {
                    $('.add-field-btn').prop('disabled', false);
                }
            },

            updateFieldNames() {
                this.$container.find('.single-field-area').each((index, el) => {
                    $(el).find('select[name], input[name]').each((_, field) => {
                        let name = $(field).attr('name');
                        if (!name) return;

                        // Replace the index number dynamically
                        const newName = name.replace(/updatable_fields\[\d+\]/, `updatable_fields[${index}]`);
                        $(field).attr('name', newName);
                    });
                });

                this.reinitializePlugins();
            },

            populateExistingFields() {
                this.$container.empty(); 
                this.existingFields.forEach((field, index) => {
                    let newFieldHtml = this.rawTemplate.replace(/__INDEX__/g, index);
                    const $html = $('<div>').html(newFieldHtml);
                    $html.find('select[name*="status_field_type"]').val(field.status_field_type);
                    $html.find('input[name*="is_required"]').prop('checked', !!field.is_required);
                    $html.find('input[name*="prefill_previous_value"]').prop('checked', !!field.prefill_previous_value);
                    this.$container.append($html);
                });
                this.reinitializePlugins();
            }
        };

        $(document).ready(function () {
            UPDATE_FIELD_SELECTION.register();
        });
    </script>
@endpush
