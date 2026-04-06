@pushOnce('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
@endPushOnce

@pushOnce('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function initDatePicker($selector) {
            const $container = $selector ?? $(document);

            $container.find(".datetime_range_picker").flatpickr({
                mode: "range",
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: false,
            });

            $container.find(".date_range_picker").flatpickr({
                mode: "range",
                enableTime: false,
                dateFormat: "Y-m-d",
                time_24hr: false,
            });

            $container.find(".time_picker").flatpickr({
                noCalendar: true,
                enableTime: true,
                dateFormat: "H:i",
                time_24hr: false,
            });

            $container.find(".datepicker").flatpickr({
                dateFormat: "Y-m-d",
            });
        }

        $(document).ready(function() {
            initDatePicker();
        });
    </script>
@endPushOnce
