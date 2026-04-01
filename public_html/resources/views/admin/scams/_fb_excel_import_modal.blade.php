<div>
    <div class="modal fade" id="fb-excel-import-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Facebook Excel Import</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5">
                    @livewire('excel-import')
                </div>
            </div>
        </div>
    </div>


@push('script')
    <script>
        $(document).ready(function() {
            $('.__fb_excel_import_btn').on('click', function() {
                $('#fb-excel-import-modal').modal('show');
            });

            // Listen for Livewire events to refresh the main table
            window.addEventListener('import-completed', event => {
                if (typeof dtTable !== 'undefined' && dtTable !== null) {
                    dtTable.draw();
                }
                $('#fb-excel-import-modal').modal('hide');
            });
        });
    </script>
@endpush
