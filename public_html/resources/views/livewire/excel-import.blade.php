<div>
    @if(empty($previewData))
        {{-- Upload Section --}}
        <div class="alert alert-info">
            <h5><i class="ti ti-info-circle"></i> Facebook Lead Import</h5>
            <p>Expected columns: <strong>phone</strong>, <strong>first_name</strong>, <strong>last_name</strong> (or <strong>full_name</strong>), <strong>your_loss_amount</strong>, <strong>ad_id</strong></p>
            <p class="mb-0">Phone numbers in Facebook format (p:+916001138202) will be automatically normalized.</p>
        </div>

        <div class="mb-3">
            <a href="{{ asset('downloads/fb-lead-import-template.csv') }}" download class="btn btn-outline-info">
                <i class="ti ti-download"></i> Download Template File
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Upload Excel/CSV File</label>
                    <input type="file" wire:model="file" class="form-control" accept=".xlsx,.csv">
                    @error('file') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="unique_phone_number" id="unique_phone_number">
                            <label class="form-check-label" for="unique_phone_number">
                                Unique Phone Numbers (Skip if customer already has a scam)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="skip_existing_phone_number" id="skip_existing_phone_number">
                            <label class="form-check-label" for="skip_existing_phone_number">
                                Skip Existing Phone Numbers
                            </label>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button wire:click="processFile" class="btn btn-primary" wire:loading.attr="disabled" wire:target="file,processFile">
                        <i class="ti ti-automation"></i>
                        <span wire:loading.remove wire:target="processFile">Process File</span>
                        <span wire:loading wire:target="processFile">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @else
        {{-- Preview Section --}}
        <div class="card">
            <div class="card-body">
                @if($faultyCount > 0)
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-triangle"></i>
                        There are <strong>{{ $faultyCount }}</strong> row(s) with faulty phone numbers. These will be skipped during import.
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="ti ti-info-circle"></i>
                    Total rows: <strong>{{ $totalCount }}</strong>, 
                    Valid: <strong>{{ $totalCount - $faultyCount - $skippedCount }}</strong>, 
                    Faulty: <strong>{{ $faultyCount }}</strong>, 
                    Skipped (Existing): <strong>{{ $skippedCount }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <button wire:click="$set('previewData', [])" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left"></i> Back to Upload
                        </button>
                    </div>
                    <div>
                        @if(count($selectedRows) > 0)
                            <button wire:click="import" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="ti ti-upload"></i>
                                <span wire:loading.remove wire:target="import">Import Selected ({{ count($selectedRows) }})</span>
                                <span wire:loading wire:target="import">Importing...</span>
                            </button>
                        @else
                            <button wire:click="importAll" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="ti ti-upload"></i>
                                <span wire:loading.remove wire:target="importAll">Import All Valid Rows</span>
                                <span wire:loading wire:target="importAll">Importing...</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" wire:model="selectAll" class="form-check-input">
                                </th>
                                <th width="50">#</th>
                                <th width="50">Status</th>
                                <th>Phone</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Loss Amount</th>                                
                                <th>Ad ID</th>
                                @if($showEditModal)
                                <th width="80">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($previewData as $index => $row)
                                <tr class="{{ $row['faulty'] ? 'table-danger' : ($row['skip'] ? 'table-warning' : '') }}">
                                    <td>
                                        @if(!$row['faulty'])
                                            <input type="checkbox" wire:model="selectedRows" value="{{ $index }}" class="form-check-input">
                                        @endif
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        @if($row['faulty'])
                                            <i class="ti ti-alert-triangle text-danger fs-3" title="Invalid phone number"></i>
                                        @elseif($row['skip'])
                                            <i class="ti ti-ban text-warning fs-3" title="Already exists"></i>
                                        @else
                                            <i class="ti ti-check text-success fs-3" title="Valid"></i>
                                        @endif
                                    </td>
                                    <td>{{ $row['phone'] ?? '-' }}</td>
                                    <td>{{ $row['first_name'] ?? '-' }}</td>
                                    <td>{{ $row['last_name'] ?? '-' }}</td>
                                    <td>{{ $row['your_loss_amount'] ? '₹ ' . number_format($row['your_loss_amount']) : '-' }}</td>
                                    <td>{{ $row['created_at'] ?? '-' }}</td>
                                    <td>{{ $row['ad_id'] ?? '-' }}</td>
                                    @if($showEditModal)
                                    <td>
                                        @if($row['faulty'])
                                            <button wire:click="editFaultyRow({{ $index }})" class="btn btn-sm btn-warning">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No data to preview</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal for Faulty Rows --}}
    @if($showEditModal && $editIndex !== null)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Row {{ $editIndex + 1 }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" wire:model="editRow.phone" class="form-control">
                            @error('editRow.phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" wire:model="editRow.first_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" wire:model="editRow.last_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Loss Amount</label>
                            <input type="number" wire:model="editRow.your_loss_amount" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showEditModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveFaultyRow">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>