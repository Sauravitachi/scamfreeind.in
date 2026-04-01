@pushOnce('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endPushOnce


@pushOnce('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function initSelect2($selector, options = {}) {
            const $select = $selector ?? $('.select2');
            const isMultiple = $select.attr('multiple') !== undefined;
            return $select.select2({
                theme: "bootstrap-5",
                closeOnSelect: !isMultiple,
                ...options
            });
        }

        function initSelect2Ajax($selector, options = {}) {
            const $select = $selector ?? $('.select2-ajax');
            
            $select.each(function() {
                const $el = $(this);
                const route = $el.data('route');
                const placeholder = $el.attr('placeholder') || $el.data('placeholder') || 'Search...';
                const minimumInputLength = $el.data('minimum-input-length') !== undefined ? $el.data('minimum-input-length') : 1;
                const dropdownParent = options.dropdownParent || null;

                const paginate = $el.data('paginate') !== undefined ? !!$el.data('paginate') : true;

                $el.select2({
                    theme: "bootstrap-5",
                    placeholder: placeholder,
                    allowClear: true,
                    minimumInputLength: minimumInputLength,
                    dropdownParent: dropdownParent,
                    ajax: {
                        url: route,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            const data = {
                                search: params.term,
                            };
                            if (paginate) {
                                data.page = params.page || 1;
                            }
                            return data;
                        },
                        processResults: function(res, params) {
                            params.page = params.page || 1;
                            const resData = res.data;
                            const data = {
                                results: resData.records,
                            };
                            if (paginate) {
                                data.pagination = {
                                    more: resData.has_more_pages
                                };
                            }
                            return data;
                        },
                        cache: true
                    },
                    ...options
                });
            });
        }

        initSelect2();
        initSelect2Ajax();

        $('.select2, .select2-ajax').each(function() {
            const selectElement = $(this);
            const select2Container = selectElement.next(".select2-container");
            selectElement.insertAfter(select2Container);
        });
    </script>
@endPushOnce
