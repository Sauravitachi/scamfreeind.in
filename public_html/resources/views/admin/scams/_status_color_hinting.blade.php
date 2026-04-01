@use(App\Constants\Status)

@push('style')
    <style>
        .c-pill {
            align-items: center;
            font-family: "Open Sans", Arial, Verdana, sans-serif;
            font-weight: bold;
            font-size: 11px;
            display: inline-block;
            height: 100%;
            white-space: nowrap;
            width: auto;
            position: relative;
            border-radius: 100px;
            line-height: 1;
            overflow: hidden;
            padding: 0px 12px 0px 20px;
            text-overflow: ellipsis;
            line-height: 1.25rem;
            color: #595959;
            word-break: break-word;
        }
        .c-pill:before {
            border-radius: 50%;
            content: "";
            height: 10px;
            left: 6px;
            margin-top: -5px;
            position: absolute;
            top: 50%;
            width: 10px;
        }

        .c-pill--approved {
            background: {{ Status::APPROVED->fadedColor() }};
        }
        .c-pill--approved::before {
            background: {{ Status::APPROVED->color() }};
        }

        .c-pill--pending {
            background: {{ Status::PENDING->fadedColor() }};
        }
        .c-pill--pending::before {
            background: {{ Status::PENDING->color() }};
        }

        .c-pill--rejected {
            background: {{ Status::REJECTED->fadedColor() }};
        }
        .c-pill--rejected::before {
            background: {{ Status::REJECTED->color() }};
        }
    </style>
@endpush

<div class="mb-2 text-md-end text-start">

    <span class="c-pill c-pill--approved">{{ ucfirst(Status::APPROVED->label()) }}</span>

    <span class="c-pill c-pill--pending">{{ ucfirst(Status::PENDING->label()) }}</span>

    <span class="c-pill c-pill--rejected">{{ ucfirst(Status::REJECTED->label()) }}</span>
    
</div>