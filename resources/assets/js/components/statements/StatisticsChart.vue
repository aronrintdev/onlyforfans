<template>
  <div class="statistics-chart wrapper">
    <canvas ref="chart" id="statistics-chart" width="400" height="400"></canvas>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/statements/StatisticsChart.vue
 */
import Vue from 'vue'
import _ from 'lodash'
import Chart from 'chart.js/auto'
import 'chartjs-adapter-luxon';
// import { Chart, LinearScale } from 'chart.js'
// Chart.register(LinearScale)
import { DateTime } from 'luxon'

export default {
  name: 'StatisticsChart',

  props: {
    data: { type: Object, default: () => ({
      ago: 7,
      ago_unit: 'day',
      summaries: []
    }) },
    active: { type: Array, default: () => ([ 'earnings' ]) },
  },

  /* -------------------------------------------------------------------------- */
  /*                                  COMPUTED                                  */
  /* -------------------------------------------------------------------------- */
  computed: {

    unit() {
      return this.data.ago_unit
    },

    dates() {
      var dates = []
      for(var i = this.data.ago; i > 0; i-- ) {
        dates.push(DateTime.now().minus({ [this.unit]: i }))
      }
      return dates
    },

    labels() {
      return this.dates


      // unit: this.data.ago_unit,
      // displayFormats: {
      //   day: this.data.ago > 7 ? 'MMM d' : 'EEE d',
      //   month: 'MMM yyyy'
      // },
      // tooltipFormat: 'MMM dd yyyy'
    },

    datasets() {
      const summaries = this.data.summaries
        ? this.data.summaries.map(summary => ({ ...summary, date: DateTime.fromSQL(summary.from) }))
        : []
      const creditTypes = [
        'sale',
        'tip',
        'subscription',
      ]
      const debitTypes = [
        'fee',
        'chargeback',
        'refund',
        'payout',
      ]
      const dataSetKeys = [
        'earnings',
        'expenses',
        'sale',
        'tip',
        'subscription',
        'fee',
        'chargeback',
        'refund',
        'payout',
      ]
      var sets = {}

      const setsCommon = {
        // If common items are needed
      }

      for (var key of dataSetKeys) {
        sets[key] = {
          ...setsCommon,
          key,
          label: this.$t(`label.${key}`),
          borderColor: this.$t(`color.${key}`),
          backgroundColor: this.$t(`color.${key}`),
        }
      }

      var data = [];

      for( var item of this.dates) {
        var dataPoint = { x: item }

        // Find summary that has same unit
        const index = _.findIndex(summaries, summary => (item.hasSame(summary.date, this.unit)))
        if (index === -1) {
          for( var key in sets ) {
            dataPoint[key] = 0
          }
        } else {
          dataPoint.earnings = summaries[index].credit_sum
          dataPoint.expenses = summaries[index].debit_sum
          const payoutAmount = summaries[index].stats.payout ? summaries[index].stats.payout.debit_sum : 0
          dataPoint.net = summaries[index].credit_sum - summaries[index].debit_sum + payoutAmount

          for( var type of creditTypes ) {
            dataPoint[type] = summaries[index].stats[type] ? parseInt(summaries[index].stats[type].credit_sum) : 0
          }
          for( var type of debitTypes ) {
            dataPoint[type] = summaries[index].stats[type] ? parseInt(summaries[index].stats[type].debit_sum) : 0
          }
        }

        data.push(dataPoint)
      }

      return _.flatMap(sets, o => ({ ...o, data, parsing: { yAxisKey: o.key } }))
    },

    filteredDatasets() {
      return _.filter(this.datasets, set => ( _.indexOf(this.active, set.key) > -1 ))
    },

    chartData() {
      return {
        // labels: this.labels,
        datasets: this.filteredDatasets
      }
    },

    options() {
      return {
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            position: 'nearest',
            callbacks: {
              label: (context) => {
                var label = context.dataset.label || ''

                if (label) {
                    label += ': '
                }
                if (context.parsed.y !== null) {
                    label += Vue.options.filters.niceCurrency(context.parsed.y)
                }
                return label;
                // return Vue.options.filters.niceCurrency(tooltipItem.yLabel.toString())
              }
            }
          },
        },

        interaction: {
          intersect: false,
          mode: 'index',
        },

        cubicInterpolationMode: 'monotone',
        // tension: 0.25,

        scales: {
          y: {
            type: 'linear',
            beginAtZero: true,
            // min: 0,
            suggestedMax: 100,
            grid: {
              display: false,
            },
            ticks: {
              // major: { enabled: true },
              // Nice Currency on Y axis
              callback: function(value, index, values) {
                  return Vue.options.filters.niceCurrencyRounded(value)
              }
            }
          },
          x: {
            type: 'timeseries',
            align: 'right',
            time: {
              unit: this.data.ago_unit,
              displayFormats: {
                day: this.data.ago > 7 ? 'MMM d' : 'EEE d',
                month: 'MMM yyyy'
              },
              tooltipFormat: 'MMM dd yyyy'
            },
            grid: {
              display: false,
            },
          },
          tooltips: {
            mode: 'index',
          },
        },
        responsive: true,
        maintainAspectRatio: false, // WARNING: Only set to false if the canvas is wrapped in a set height value
        onResize: (chart, newSize) => {
          // If needed
          // this.$log.debug('Chart onResize', { newSize })
        },
        resizeDelay: 200, // Eases resize update by denouncing call
      }
    },
  },

  data: () => ({
    chart: null,
  }),

  methods: {

    renderChart() {
      if ( this.chart ) {
        this.chart.data = this.chartData
        this.chart.options = this.options
        this.chart.update()
        return
      }
      this.$log.debug('StatisticsChart renderChart')
      try {
        const ctx = document.getElementById('statistics-chart')
        this.chart = new Chart(ctx, {
          type: 'line',
          data: this.chartData,
          options: this.options,
        })
      } catch(e) {
        this.$log.error('StatisticsChart renderChart caught error', { e })
      }
    },
  },

  watch: {
    data: {
      deep: true,
      handler() {
        // if (this.chart) {
        //   this.chart.destroy()
        // }
        this.renderChart()
      }
    },
    active() {
      // if (this.chart) {
      //   this.chart.destroy()
      // }
      this.renderChart()
    },
  },

  mounted() {
    this.renderChart()
  }
}
</script>

<style lang="scss" scoped>
.wrapper {
  max-height: 30rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "color": {
      "earnings": "#28a745",
      "expenses": "#dc3545",
      "net": "#28a745",
      "sale": "#0ebae6",
      "tip": "#2a5ebe",
      "subscription": "#961ece",
      "fee": "#dc3545",
      "chargeback": "#dc3545",
      "refund": "#dc3545",
      "payout": "#dc3545"
    },
    "label": {
      "earnings": "Earnings",
      "expenses": "Expenses",
      "net": "Net",
      "sale": "Sales",
      "tip": "Tips",
      "subscription": "Subscriptions",
      "fee": "Fees",
      "chargeback": "Chargebacks",
      "refund": "Refunds",
      "payout": "Payouts"
    },
  }
}
</i18n>
