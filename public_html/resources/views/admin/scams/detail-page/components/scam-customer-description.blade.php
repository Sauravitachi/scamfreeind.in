@php /** @var \App\Models\Scam $scam */ @endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">
            Customer Scam Description
        </div>
        <div class="h3 mt-3 text-secondary">
            {!! nl2br(e($scam->customer_description)) !!}
        </div>
    </div>
</div>