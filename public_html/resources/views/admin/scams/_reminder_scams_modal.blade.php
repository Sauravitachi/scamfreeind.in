<div>
    <div class="modal modal-blur fade" id="reminder-scams-modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Case Status Reminder</h5>
                </div>
                <div class="modal-body">
                    @foreach ($reminderScams as $scam)
                        <x-admin.alert>
                            <x-slot:message>
                                <strong>
                                    #{{ $scam->track_id }}
                                </strong>
                                | <strong>{{ $scam->customer->full_name_with_full_phone_number }}</strong>
                                @if ($scam->scam_amount)
                                    | Amount: <strong>{{ format_amount($scam->scam_amount) }}</strong>
                                @endif
                                | Status: <strong>{{ $scam->status->title }}</strong>
                            </x-slot:message>
                        </x-admin.alert>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <x-admin.button label="Acknowledge" icon='ti ti-check' onclick="acknowledgeStatusReminders();" />
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        $(document).ready(function() {
            $('#reminder-scams-modal').modal('show');
        });

        function acknowledgeStatusReminders() {
            runAjax({
                url: "{{ route('admin.scams.acknowledge-status-reminders') }}",
                method: 'POST',
                handleToast: true,
                success: function(res) {
                    if(res.success) {
                        $('#reminder-scams-modal').modal('hide');
                    }
                }
            });
        }
    </script>
@endpush