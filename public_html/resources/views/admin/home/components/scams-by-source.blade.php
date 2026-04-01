<div class="card">
  <div class="card-body">
    <h4 class="card-title fs-2 fw-normal mb-4">
        <i class="ti ti-location-pin me-2"></i>
        Cases by source
    </h4>
      <div id="scams-by-source-pie-chart" style="height: 320px;"></div>
  </div>
</div>

@push('script')
  <script>
    document.addEventListener("DOMContentLoaded", async function () {

      $chart = $('#scams-by-source-pie-chart');

      $chart.html(Loader.centerSpinnerLoader('Loading chart'));
      let data = await DASHBOARD.ajax('scams-by-source');
      $chart.empty();

      const scamsIndexUrl = @js(route('admin.scams.index', ['scam_source' => '_source_id_']));

      // Sort by count descending and get top 4
      const topSources = data.sort((a, b) => b.count - a.count).slice(0, 4);

      // Prepare series and labels
      const series = [];
      const labels = [];
      const colors = [];

      topSources.forEach(source => {
        series.push(source.count);
        labels.push(source.title);
        colors.push(source.indicator_color || getRandomColor());
      });

      window.ApexCharts &&
        new ApexCharts(document.getElementById("scams-by-source-pie-chart"), {
          chart: {
            type: "donut",
            fontFamily: "inherit",
            height: 350,
            sparkline: {
              enabled: true,
            },
            animations: {
              enabled: false,
            },
            events: {
              dataPointSelection: function (event, chartContext, config) {
                const selectedIndex = config.dataPointIndex;
                const selectedSource = topSources[selectedIndex];
                if (selectedSource) {
                  const url = scamsIndexUrl.replace('_source_id_', selectedSource.id);
                  redirect(url);
                }
              },
            },
          },
          fill: {
            opacity: 1,
          },
          series: series,
          labels: labels,
          tooltip: {
            theme: "dark",
            fillSeriesColor: false,
          },
          grid: {
            strokeDashArray: 4,
          },
          colors: colors,
          legend: {
            show: true,
            position: "bottom",
            offsetY: 12,
            markers: {
              width: 10,
              height: 10,
              radius: 100,
            },
            itemMargin: {
              horizontal: 8,
              vertical: 8,
            },
          },
        }).render();

    });
  </script>
@endpush