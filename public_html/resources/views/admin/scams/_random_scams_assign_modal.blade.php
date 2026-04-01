<div>
    <div class="modal modal-blur fade" id="random-scam-assign-modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Random Case Assign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
    <div id="random-scam-assign-modal-content" style="display: none;">
        <form action="{{ route('admin.scams.random-scam-assign') }}" method="POST" id="random-scam-assign-form">
            @csrf
            <input type="hidden" name="filtered_scams_count" class="filtered_scams_count_inp">
            <div class="row">
                <div class="col-12">
                    <h3>
                        Total filtered cases : <span class="filtered-scams-text"></span>
                    </h3>
                </div>
                <div class="col-12">
                    <x-admin.input type='number' name='count' placeholder='Enter case count' required>
                        <x-slot:label>
                            <span class="fw-bold">Count</span> <span class="text-muted">(For each assignee)</span>
                        </x-slot:label>
                    </x-admin.input>
                </div>
                <div class="col-12">
                    <x-admin.select name='assignees[]' label='Sales Assignee' class="modal-select2" multiple>
                       
                        @foreach ($salesUsers->where('status', true)->where('has_today_activity', true) as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </x-admin.select>
                </div>
                <div class="col-lg-6">
                    <x-admin.input type='number' name='scam_amount_lb' label='Scam Amount (<=)' placeholder='Enter lower bound amount' />
                </div>  
                <div class="col-lg-6">
                    <x-admin.input type='number' name='scam_amount_ub' label='Scam Amount (<=)' placeholder='Enter upper bound amount' />
                </div>  
                <div class="col-012">
                    <x-admin.checkbox name='include_null_amount' label='Include Null Amount?' value='1' />
                </div>
            </div>
            <div class="text-end">
                <x-admin.button label="Assign" icon='ti ti-bolt' class="mt-3" submit />
            </div>
        </form>
    </div>
</div>


@push('script')

    {!! js_validation_custom_event(
        formRequestClass: \App\Http\Requests\Admin\RandomScamAssignRequest::class,
        formSelector: '#random-scam-assign-form',
        eventTargetSelector: '#random-scam-assign-modal',
        event: 'validate',
    ) !!}

    <script>
 
        var RANDOM_SCAM_ASSIGN_MODULE = {

            isFilterApplied: false,

            filteredRecordsCount: 0,

            register: function() {

                this.$modal = $('#random-scam-assign-modal');
                this.$modalBody = this.$modal.find('.modal-body');
                this.$button = $('.__random_assign_btn');
                this.modalBodyTemplate = $('#random-scam-assign-modal-content').html();

                this.registerButton();
             
                $(document).on('app:page_filter_updated', function (e, data) {
                    const filteredRecordsCount = data.filteredRecordsCount;

                    if(filteredRecordsCount > 0) {
                        RANDOM_SCAM_ASSIGN_MODULE.filteredRecordsCount = filteredRecordsCount;
                        RANDOM_SCAM_ASSIGN_MODULE.isFilterApplied = data.status;
                        RANDOM_SCAM_ASSIGN_MODULE.toggleAssignButton();
                    }
                });
            },

            registerButton: function() {
                this.$button.on('click', async function() {
                    if(await isOfficeTiming()) {
                        RANDOM_SCAM_ASSIGN_MODULE.open();
                    } else {
                        toast.open({ type: 'error', message: 'This feature is only for office time!' });
                    }
                });
            },

            toggleAssignButton: function() {
                if(RANDOM_SCAM_ASSIGN_MODULE.isFilterApplied) {
                    this.$button.parent().show();
                } else {
                    this.$button.parent().hide();
                }
            },

            open: function() {

                this.$modalBody.html(this.modalBodyTemplate);
                this.$modalBody.find('.filtered-scams-text').html(RANDOM_SCAM_ASSIGN_MODULE.filteredRecordsCount);
                this.$modalBody.find('input.filtered_scams_count_inp').val(RANDOM_SCAM_ASSIGN_MODULE.filteredRecordsCount);
                this.populateFilterInput();

                //filter-offcanvas
                
                this.$modal.modal('show');

                initSelect2(this.$modalBody.find('select.modal-select2'), {
                    dropdownParent: this.$modalBody
                });

                this.$modal.trigger('validate');

                ajaxForm('#random-scam-assign-form', {
                    handleToast: true,
                    success: function(res) {
                        if(res.success) {
                            RANDOM_SCAM_ASSIGN_MODULE.close();
                            $(document).trigger('app:main_table_redraw');
                        }
                    }
                });
            },

            close: function() {
                this.$modal.modal('hide');
            },

            populateFilterInput: function() {

                $form = RANDOM_SCAM_ASSIGN_MODULE.$modalBody.find('form');

                $('form#filter-offcanvas-form')
                .find('input, select, textarea')
                .each(function () {
                    const $el = $(this);
                    const name = $el.attr('name');
                    if (!name) return;

                    let value;

                    if ($el.is(':checkbox') || $el.is(':radio')) {
                        if (!$el.is(':checked')) return;
                        value = $el.val();
                    } else {
                        value = $el.val();
                    }

                    if (Array.isArray(value)) {
                        value.forEach(val => {
                            $form.append(`<input type="hidden" name="${name}[]" value="${val}">`);
                        });
                    } else if (value !== null && value !== undefined && value !== '') {
                        $form.append(`<input type="hidden" name="${name}" value="${value}">`);
                    }
                });

                const recordsType = $('#records_type_select').val();
                $form.append(`<input type="hidden" name="records_type" value="${recordsType}">`);
            }

        };



        $(document).ready(function() {
            RANDOM_SCAM_ASSIGN_MODULE.register();
        });

    </script>
@endpush