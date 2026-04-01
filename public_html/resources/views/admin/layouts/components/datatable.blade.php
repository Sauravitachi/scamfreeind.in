@pushOnce('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.bootstrap5.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.bootstrap5.css">
    

    <style>
        div.dt-container .dt-paging .dt-paging-button:hover {
            border: none;
            background: inherit;
        }

        div.dt-container .dt-paging .dt-paging-button:active {
            box-shadow: inherit;
        }

        div.dt-container .dt-paging .dt-paging-button {
            border: none;
            margin: 0;
            padding: 0;
        }

        @media screen and (max-width: 767px) {

            div.dt-container div.dt-length,
            div.dt-container div.dt-search,
            div.dt-container div.dt-info,
            div.dt-container div.dt-paging {
                margin-bottom: 10px;
            }
        }

        td,
        th {
            vertical-align: middle;
        }
    </style>
@endpushOnce

@pushOnce('script')
    <script src="https://cdn.datatables.net/2.1.7/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.7/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.1.0/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.1.0/js/select.dataTables.js"></script>
@endpushOnce

@isset($fullFeatures)
    @pushOnce('script')
        <!-- Buttons -->
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/dataTables.buttons.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.dataTables.js"></script>

        <!-- Export functionality -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.print.min.js"></script>
    @endPushOnce
@endisset
