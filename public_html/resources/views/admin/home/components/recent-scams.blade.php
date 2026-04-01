<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title fs-2 fw-normal">
            <i class="ti ti-clock-hour-2 me-2"></i>
            Recent cases
        </h4>
        <a href="{{ route('admin.scams.index') }}">
            <i class="ti ti-link"></i>
            Show All
        </a>
    </div>
    <div class="card-table table-responsive">
        <table class="table table-vcenter">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th class="text-center">Scam</th>
                    <th class="text-end">Assignee</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentScams as $scam)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="javascript:;" class="ms-1" onclick="ScamDetailModule.open({{ $scam->id }})">
                                {{ $scam->customer->full_name_with_full_phone_number }}
                            </a>
                        </td>
                        <td class="text-center text-secondary">
                            {{ $scam->scamType?->title }}
                            @if ($scam->scam_amount)
                                ({{ format_amount($scam->scam_amount) }})
                            @endif
                        </td>
                        <td class="text-end text-secondary fs-5">
                            @if ($scam->salesAssignee)
                                Sales Executive : <span class="fw-bold">{{ $scam->salesAssignee->name_with_username }}</span>
                            @endif
                            <br />
                            @if ($scam->draftingAssignee)
                                Drafting Executive : <span class="fw-bold">{{ $scam->draftingAssignee->name_with_username }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('admin.scams._scam_file_upload_modal')
@include('admin.scams._scam_details_offcanvas')
@include('admin.scams.detail-page.components._reject_status_modal')

@push('script')
    <script>
        function handleScamReview(scamId, type, status) {

            const $modal = $('#sales-status-reject-modal');

            const url = '{{ route('admin.scams.change-scam-status-review', ':id') }}'.replace(':id', scamId);

            function handleSuccess(res) {
                if(res.success) {
                $modal.modal('hide');
                if (typeof dtTable !== 'undefined' && dtTable !== null) {
                    dtTable.draw(false);
                }
                if (typeof ScamDetailModule !== "undefined" && ScamDetailModule.refresh) {
                    ScamDetailModule.refresh();
                }
            }
            }

            if(status === 'approved') {

                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-alert-circle',
                    message: `You are about to approve this case ${type} status.`,
                    onConfirm: async function() {
                        await $.post(url, {
                            type,
                            review: status,
                        }, handleSuccess);
                    }
                });

            } else if(status === 'reject') {
                
                $modal.find('.modal-body').html($('#reject-status-card-template').html());

                const $form = $modal.find('form#sales-status-reject-form');

                $form.attr('action', url);
                $form.find('input[name="type"]').val(type);
                
                $form.validate().destroy();
                $modal.trigger('run-validation');

                ajaxForm('#sales-status-reject-form', {
                    handleToast: true,
                    success: handleSuccess
                });
                
                $modal.modal('show');
                
            }
        }
    </script>
@endpush