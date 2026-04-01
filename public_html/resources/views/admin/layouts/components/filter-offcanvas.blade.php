@php
    /**
     * Offcanvas filter component.
     *
     * This Blade component renders an offcanvas sidebar containing filter options.
     * The 'filters-body' section is yielded, allowing customization of the filter content.
     */
@endphp


<div class="offcanvas offcanvas-end {{ $filters['offcanvas-class'] ?? '' }}" tabindex="-1" id="filter-offcanvas" aria-labelledby="filter-offcanvas-label">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title" id="filter-offcanvas-label">Filters</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form class="offcanvas-body" id="filter-offcanvas-form">
        <div>
            @yield('filters-body')
        </div>
        <div class="mt-3 text-end">
            <x-admin.button label='Clear' variant='secondary' icon='ti ti-restore' onclick="FilterModule.clear();" />
            <x-admin.button label='Apply' icon='ti ti-filter-check' onclick="FilterModule.apply();" />
        </div>
    </form>
</div>

@pushOnce('script')
    <script>
        const FilterModule = {

            $filterOffcanvas: null,
            
            $filterOffcanvasBody: null,

            $form: null,

            $dtTable: null,

            reverseMode: false,

            applied: 0,

            prepare: function() {
                FilterModule.$filterOffcanvas = $('#filter-offcanvas');

                FilterModule.$filterOffcanvasBody = FilterModule.$filterOffcanvas.find('.offcanvas-body');

                FilterModule.$form = $('#filter-offcanvas-form');

                FilterModule.$filterOffcanvas.on('show.bs.offcanvas', function() {

                    const $filterSelect = $('.filter-select2');

                    if ($filterSelect.length > 0) {
                        
                        $filterSelect.each(function() {
                            // Check if the select element is multiple
                            const isMultiple = $(this).attr('multiple') !== undefined;

                            // Initialize Select2 with the appropriate options
                            initSelect2($(this), {
                                dropdownParent: FilterModule.$filterOffcanvasBody,
                                closeOnSelect: isMultiple ? false : true  // Apply closeOnSelect based on multiple attribute
                            });
                        });
                    }

                });
            },

            registerDatatable: function(dtTable) {
                FilterModule.$dtTable = dtTable;
            },

            register: function() {
                FilterModule.prepare();
            },

            apply: function() {

                if(!FilterModule.$filterOffcanvas) {
                    return;
                }

                if(FilterModule.$dtTable) {

                    FilterModule.$dtTable.one('draw', function() {
                        FilterModule.applied = true;
                        FilterModule.dispatchFilterUpdateStatus();
                    });

                     FilterModule.$dtTable.draw();
                }

                FilterModule.$filterOffcanvas.offcanvas('hide');
            },

            clear: function() {

                clearFilterData();

                if(!FilterModule.$filterOffcanvas) {
                    return;
                }

                if(FilterModule.$dtTable) {

                    FilterModule.$dtTable.one('draw', function() {
                        FilterModule.applied = false;
                        FilterModule.dispatchFilterUpdateStatus();
                    });

                     FilterModule.$dtTable.draw();
                }

                FilterModule.$filterOffcanvas.offcanvas('hide');
            },

            isAppliedAny: function() {

                if(!FilterModule.applied) {
                    return false;
                }

                let isApplied = false;

                FilterModule.$form.find('input, select, textarea').each(function() {
                    let $el = $(this);

                    // For checkboxes/radios, check if any is checked
                    if ($el.is(':checkbox') || $el.is(':radio')) {
                        if ($el.is(':checked')) {
                            isApplied = true;
                            return false; // break out of each loop
                        }
                    } else {
                        if ($el.val() && $el.val().toString().trim() !== '') {
                            isApplied = true;
                            return false;
                        }
                    }
                });

                return isApplied;
            },

            dispatchFilterUpdateStatus: function() {
                const status = FilterModule.isAppliedAny();
                $(document).trigger('app:page_filter_updated', { 
                    status: status,
                    filteredRecordsCount: FilterModule.$dtTable.ajax.json().recordsFiltered
                });
            }

        };


        $(document).ready(function() {

            FilterModule.register();
            
        });
    </script>
@endPushOnce
