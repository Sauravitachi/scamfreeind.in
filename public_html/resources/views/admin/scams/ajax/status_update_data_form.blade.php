@php /** @var \App\Models\Scam $scam */ @endphp
@php /** @var \App\Models\ScamStatus $scamStatus */ @endphp

<h2 class="fw-normal">
    Update data for <strong>'{{ $scamStatus->title }}'</strong> status.
</h2>

@php
    $totalFields = $scamStatus->statusUpdateFields->count();
@endphp

<form id="status-data-update-form" method="POST" action="{{ route('admin.scams.update-status-data', [$scam, $scamStatus]) }}">
    @csrf
    <div class="row mt-5">
        @foreach ($scamStatus->statusUpdateFields as $statusUpdateField)
            @php 
                $type = $statusUpdateField->status_field_type;
                $columnClass = $totalFields === 1 ? 'col-12' : $type->columnClass();
            @endphp
            <div class="{{ $columnClass }}">
                {!! $type->inputField($statusUpdateField, $scam) !!}
            </div>    
        @endforeach
    </div>
    <div class="text-end mt-3">
        <x-admin.button class="submit-btn" label='Submit' icon='ti ti-send' submit />
    </div>
</form>