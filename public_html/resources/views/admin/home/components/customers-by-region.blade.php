<div class="card">
    <div class="card-body">
        <h4 class="card-title fs-2 fw-normal mb-4">
            <i class="ti ti-map me-2"></i>
            Customers By Region
        </h4>
        <div class="ratio ratio-21x9">
            <div>
                <div id="map-world" class="w-100 h-100"></div>
            </div>
        </div>
    </div>
</div>

@push('script')

    <script src="{{ asset('assets/theme/libs/jsvectormap/dist/js/jsvectormap.min.js') }}" ></script>
    <script src="{{ asset('assets/theme/libs/jsvectormap/dist/maps/world.js') }}" ></script>
    <script src="{{ asset('assets/theme/libs/jsvectormap/dist/maps/world-merc.js') }}" ></script>

    <script>
        document.addEventListener("DOMContentLoaded", async function () {

            $('#map-world').html(Loader.centerSpinnerLoader('Loading map'));
            let values = await DASHBOARD.ajax('customers-by-region');
            $('#map-world').empty();

            values = Object.fromEntries(
                Object.entries(values).map(([key, value]) => [key.toUpperCase(), value])
            );


            const map = new jsVectorMap({
                selector: "#map-world",
                map: "world",
                backgroundColor: "transparent",
                regionStyle: {
                    initial: {
                        fill: tabler.getColor("body-bg"),
                        stroke: tabler.getColor("border-color"),
                        strokeWidth: 2,
                    },
                },
                zoomOnScroll: false,
                zoomButtons: false,
                // -------- Series --------
                visualizeData: {
                    scale: ['#cbe6ff', tabler.getColor("primary")],
                    values: values,
                },
                onRegionTooltipShow: function (event, tooltip, code) {
                    const value = values[code] ?? "No data";
                    tooltip.text(`${tooltip.text()} — ${value} customers`);
                },
            });
        
        
        
        
            window.addEventListener("resize", () => {
                map.updateSize();
            });
        });
        
    </script>

@endpush