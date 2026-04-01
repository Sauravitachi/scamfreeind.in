<div id="action-blocker" style="display: none;"></div>

<x-admin.alert id="no-internet-alert" variant="secondary" icon="ti ti-world-off" message="The internet is currently not connected. All actions are blocked until it is restored." style="display: none;" important />

@push('script')
<script>
    
    function updateOnlineStatus() {
        const $offlineAlert = $('#no-internet-alert');
        if (navigator.onLine) {
            $offlineAlert.hide();
            enableActionBlocker(false);
        } else {
            $offlineAlert.show();
            enableActionBlocker(true);
        }
    }

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);

    $(document).ready(function () {
        updateOnlineStatus();

        $('#action-blocker').on('click', function() {
            toast.open({ type: "error", message: "Action blocked: No internet connection." });
        });
    });
</script>
@endpush