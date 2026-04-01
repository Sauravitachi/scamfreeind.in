<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div class="fs-3 subheader">Total Cases</div>
            <x-admin.select class="select2" id="case-count-date-select" :options="[10 => 'Last 10 Days', 30 => 'Last 30 Days', 90 => 'Last 90 Days']" selected="30" />
        </div>
        <div id="scam-count-chart-container" style="height: 170px;">

        </div>
    </div>
</div>
<div style="display: none;" id="scam-count-chart-container-template">
    <div class="d-flex align-items-baseline">
        <div class="h1 mb-0 me-2 main-count"></div>
    </div>
    <div id="scam-count-chart" class="rounded-bottom chart-sm mt-3"></div>
</div>
@push('script')
<script>

    var SCAM_COUNT_CHART = {

        register: function() {

            SCAM_COUNT_CHART.$container = $('#scam-count-chart-container');
            SCAM_COUNT_CHART.$select = $('#case-count-date-select');

            SCAM_COUNT_CHART.$select.on('change', function() {
                SCAM_COUNT_CHART.render();
            }).trigger('change');
        },

        render: async function() {

            const lastXDays = SCAM_COUNT_CHART.$select.val();
    
            const containerHtml = $('#scam-count-chart-container-template').html();
            
            SCAM_COUNT_CHART.$container.css('height', '170px');
            SCAM_COUNT_CHART.$container.html(Loader.centerSpinnerLoader('Loading chart'));

            let data = await DASHBOARD.ajax('case-chart', { last_x_days: lastXDays });

            SCAM_COUNT_CHART.$container.html(containerHtml);
            SCAM_COUNT_CHART.$container.css('height', '');


            SCAM_COUNT_CHART.$container.find('.main-count').html(data.count);

            // $select = $('#case-count-date-select');
            
            // initSelect2($select);

            // $select.val(lastXDays).trigger('change');

            // $select.off().on('change', function() {
            //     SCAM_COUNT_CHART.render();
            // });
        
            const labels = data.chart_data.map(item => item.date);
            const seriesData = data.chart_data.map(item => item.count);

            window.ApexCharts &&
                new ApexCharts(document.getElementById("scam-count-chart"), {
                chart: {
                    type: "bar",
                    fontFamily: "inherit",
                    height: 120,
                    sparkline: {
                    enabled: true,
                    },
                    animations: {
                    enabled: false,
                    },
                },
                plotOptions: {
                    bar: {
                    columnWidth: "50%",
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                fill: {
                    opacity: 1,
                },
                series: [
                    {
                    name: "Cases",
                    data: seriesData,
                    },
                ],
                tooltip: {
                    theme: "dark",
                    y: {
                        formatter: function(value) {
                            return Math.round(value);
                        }
                    }
                },
                grid: {
                    strokeDashArray: 4,
                },
                xaxis: {
                    labels: {
                    padding: 0,
                    },
                    tooltip: {
                    enabled: false,
                    },
                    axisBorder: {
                    show: false,
                    },
                    type: "datetime",
                },
                yaxis: {
                    labels: {
                    padding: 4,
                    },
                },
                labels: labels,
                colors: [tabler.getColor("primary")],
                legend: {
                    show: false,
                },
            }).render();

        }


    };

    $(document).ready(function() {
        SCAM_COUNT_CHART.register();
    });
</script>
@endpush