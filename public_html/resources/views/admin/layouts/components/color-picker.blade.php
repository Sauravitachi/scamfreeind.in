@pushOnce('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css"/>

    <style>
        .pickr > button {
            width: 150px !important;
            height: 35px !important;
            border: 1px solid #ccc;
            border-radius: 6px !important;
            cursor: pointer;
            background-color: #ffffff;
            box-shadow: inset 0 0 0 1px #999;
        }
    </style>
@endPushOnce

@pushOnce('script')
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.es5.min.js"></script>
@endpushOnce