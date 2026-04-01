<div class="offcanvas offcanvas-end" tabindex="-1" id="bulkExcelExportOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Excel Export</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <form id="bulk-excel-export-form" method="POST" action="{{ route('admin.scams.bulk-excel-export') }}">
            @csrf

            <input type="hidden" name="scam_ids" id="export_scam_ids">

            <div class="mb-3">
                <label class="form-label">Export Type</label>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export_type" value="selected" checked>
                    <label class="form-check-label">Export Selected Records</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export_type" value="all">
                    <label class="form-check-label">Export All Records (With Filters)</label>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">
                <i class="ti ti-download"></i> Export Excel
            </button>
        </form>
    </div>
</div>
