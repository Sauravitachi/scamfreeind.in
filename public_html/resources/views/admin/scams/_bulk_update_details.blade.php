<div>
    <div class="modal modal-blur fade" id="bulk-update-modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <form class="modal-content" method="POST" action="{{ route('admin.scams.bulk-update') }}" id="bulk-update-form">
                <div class="modal-header">
                    <h5 class="modal-title">Update Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="file-card-container">
                    </div>
                </div>
                <div class="modal-footer">
                    <x-admin.button label="Update" icon='ti ti-device-floppy' submit />
                </div>
            </form>
        </div>
    </div>
</div>

@section('bulk_update_body_section')
    <div class="row">
        <div class="col-12">
            <x-admin.input type='number' class="scam_amount_inp" name='scam_amount' placeholder='Enter scam amount'>
                <x-slot:label>
                   <div class="d-flex gap-2">
                        Scam Amount
                        <x-admin.checkbox class="scam_amount_enabled" />
                   </div>
                </x-slot:label>
            </x-admin.input>
        </div>
        <div class="col-12">
            @php($options = $scamTypes->pluck('title', 'id')->toArray())
            <x-admin.select name='scam_type_id' label='Scam Type' class="select2" :options="$options"
                placeholder="Same as before 🟢" />
        </div>
        <div class="col-12">
            <x-admin.select name='scam_source_id' class="select2" label='Scam Source' placeholder='Search Scam Source'  placeholder="Same as before 🟢" :options="$scamSources->pluck('title', 'id')->toArray()" />
        </div>
    </div>
@endsection

@push('script')
    {!! js_validation_custom_event(
        formRequestClass: \App\Http\Requests\Admin\BulkUpdateScamRequest::class,
        formSelector: '#bulk-update-form',
        eventTargetSelector: '#bulk-update-modal',
        event: 'validate',
    ) !!}
    <script>

        function updateBulk() {

            const selectedScams = dtSelectedRows(dtTable, 'id');

            if (selectedScams.length <= 0) {
                toast.open({
                    type: 'warning',
                    message: 'No records are selected!'
                });
                return;
            }

            const body = @js(view()->yieldContent('bulk_update_body_section'));
            const $modal = $('#bulk-update-modal');
            const $modalBody = $modal.find(".modal-body");
            $modalBody.html(body);
            initSelect2($modalBody.find('select.select2'), {
                dropdownParent: $modal
            });

            ajaxForm('#bulk-update-form', {
                handleToast: true,
                formData: function() {
                    const fd = new FormData(document.querySelector('#bulk-update-form'));
                    selectedScams.forEach((item, index) => fd.append('scams[]', item));
                    return fd;
                },
                beforeSend: function() {
                    overlayLoader.show();
                },
                success: function(res) {
                    console.log(res);
                    pageButtonVisibility('.__bulk_update_btn', false);
                    dtTable.draw(false);
                    $modal.modal('hide');
                },
                complete: function() {
                    overlayLoader.hide();
                }
            });

            $modal.modal('show');

            $modal.find('input.scam_amount_enabled').on('change', function() {
                $modal.find('input.scam_amount_inp').prop('disabled', !$(this).is(':checked'));
            }).trigger('change');

            $modal.trigger('validate');
        }

        $(document).ready(function() {

            // Bulk Assign button click handler
            $('.__bulk_update_btn').on('click', updateBulk);

        });
    </script>
@endpush
