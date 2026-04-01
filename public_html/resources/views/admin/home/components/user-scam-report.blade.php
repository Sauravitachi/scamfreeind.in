@php
    throw_if(!isset($type), \Exception::class, 'type is required!');
    throw_if(!in_array($type, ['sales', 'drafting']), \InvalidArgumentException::class, 'Invalid type provided!');
@endphp

<div class="card">
    <div class="card-body px-5">
        <h3 class="card-title fs-2 fw-normal d-flex align-items-center">
            <span><i class="ti ti-hierarchy me-2"></i> {{ ucfirst($type) }} Status Stats</span>
            <div class="ms-auto lh-1 d-flex align-items-center gap-3">
                <x-admin.select class="select2" id="{{ $type }}-status-stats-user-select" :options="$users->pluck('name', 'id')->toArray()" placeholder='Select Assignee' />
                <x-admin.select class="select2" id="{{ $type }}-status-stats-date-select" :options="[0 => 'Today', 7 => 'Last 7 Days', 30 => 'Last 30 Days', null => 'All Time']" />
            </div>
        </h3>
        <div id="{{ $type }}-status-scam-count-table-container" style="height: 350px; overflow: auto;"></div>
    </div>
</div>

@pushOnce('script')
    <script>
        async function handleSelectChange(type) {

            const lastXDays = $(`#${type}-status-stats-date-select`).val();
            const userId = $(`#${type}-status-stats-user-select`).val();

            const $tableContainer = $(`#${type}-status-scam-count-table-container`);

            $tableContainer.html(Loader.centerSpinnerLoader('Loading data'));

            const data = await DASHBOARD.ajax(`${type}-status-scam-count`, { last_x_days: lastXDays, user_id: userId });

            const $table = $('<table>').addClass('table table-borderless fixed-header fixed-footer');
            const $thead = $('<thead>');
            const $tbody = $('<tbody>');
            const $tfoot = $('<tfoot>').addClass('fixed-color-tfoot');

            const $headerRow = $('<tr>').addClass('fw-bold');
            $headerRow.append($('<td>').text('Status'));
            $headerRow.append($('<td>').text('Count').addClass('text-end'));
            $thead.append($headerRow);

            $table.append($thead);

            let grandTotal = 0;

            data.forEach(item => {
                const $row = $('<tr>');
                $row.append($('<td>').text(item.title));
                $row.append($('<td>').text(item.scam_count ?? 0).addClass('text-end'));
                $tbody.append($row);
                grandTotal += item.scam_count ?? 0;
            });

            // 🔥 Append the "Grand Total" row at the bottom
            const $totalRow = $('<tr>').addClass('fw-bold border-top');
            $totalRow.append($('<td>').text('Grand Total'));
            $totalRow.append($('<td>').text(grandTotal).addClass('text-end'));
            $tfoot.append($totalRow);

            $table.append($tbody);
            $table.append($tfoot);

            $tableContainer.empty().append($table);
        }
    </script>
@endPushOnce

@push('script')
    <script>
        $(document).ready(function() {
            const type = '{{ $type }}';
            $(`#${type}-status-stats-user-select`).on('change', () => handleSelectChange(type));
            $(`#${type}-status-stats-date-select`).on('change', () => handleSelectChange(type)).trigger('change');
        });
    </script>
@endpush