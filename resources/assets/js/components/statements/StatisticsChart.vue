<template>
  <div class="statistics-chart wrapper">
    <canvas ref="chart" id="statistics-chart" width="400" height="200"></canvas>
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
  },

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

      var sets = {
        earnings: {
          key: 'earnings',
          label: this.$t('label.earnings'),
          borderColor: this.$t('color.earnings'),
          backgroundColor: this.$t('color.earnings'),
          data: []
        },
        expenses: {
          key: 'expenses',
          label: this.$t('label.expenses'),
          borderColor: this.$t('color.expenses'),
          backgroundColor: this.$t('color.expenses'),
          data: []
        },
        net: {
          key: 'net',
          label: this.$t('label.net'),
          borderColor: this.$t('color.net'),
          backgroundColor: this.$t('color.net'),
          data: []
        },
        sale: {
          key: 'sale',
          label: this.$t('label.sale'),
          borderColor: this.$t('color.sale'),
          backgroundColor: this.$t('color.sale'),
          data: []
        },
        tip: {
          key: 'tip',
          label: this.$t('label.tip'),
          borderColor: this.$t('color.tip'),
          backgroundColor: this.$t('color.tip'),
          data: []
        },
        subscription: {
          key: 'subscription',
          label: this.$t('label.subscription'),
          borderColor: this.$t('color.subscription'),
          backgroundColor: this.$t('color.subscription'),
          data: []
        },
        fee: {
          key: 'fee',
          label: this.$t('label.fee'),
          borderColor: this.$t('color.fee'),
          backgroundColor: this.$t('color.fee'),
          data: []
        },
        chargeback: {
          key: 'chargeback',
          label: this.$t('label.chargeback'),
          borderColor: this.$t('color.chargeback'),
          backgroundColor: this.$t('color.chargeback'),
          data: []
        },
        refund: {
          key: 'refund',
          label: this.$t('label.refund'),
          borderColor: this.$t('color.refund'),
          backgroundColor: this.$t('color.refund'),
          data: []
        },
        payout: {
          key: 'payout',
          label: this.$t('label.payout'),
          borderColor: this.$t('color.payout'),
          backgroundColor: this.$t('color.payout'),
          data: []
        }
      }
      for( var item of this.dates) {
        // Find summary that has same unit
        const index = _.findIndex(summaries, summary => (item.hasSame(summary.date, this.unit)))
        if (index === -1) {
          for( var i in sets ) {
            sets[i].data.push(0)
          }
        } else {
          sets.earnings.data.push(summaries[index].credit_sum)
          sets.expenses.data.push(summaries[index].debit_sum)
          const payoutAmount = summaries[index].stats.payout ? summaries[index].stats.payout.debit_sum : 0
          sets.net.data.push((summaries[index].credit_sum - summaries[index].debit_sum + payoutAmount))

          for( var type of creditTypes ) {
            sets[type].data.push(summaries[index].stats[type] ? parseInt(summaries[index].stats[type].credit_sum) : 0)
          }
          for( var type of debitTypes ) {
            sets[type].data.push(summaries[index].stats[type] ? parseInt(summaries[index].stats[type].debit_sum) : 0)
          }
        }
      }
      return _.flatMap(sets, o => o)
    },

    filteredDatasets() {
      return _.filter(this.datasets, set => ( _.indexOf(this.shownTypes, set.key) > -1 ))
    },

    chartData() {
      return {
        labels: this.labels,
        datasets: this.filteredDatasets
      }
    },

    options() {
      return {
        plugins: {

        },
        scales: {
            y: {
              type: 'linear',
              beginAtZero: true,
              suggestedMax: 100,
              stacked: 'single',
              title: 'Amount',
              ticks: {
                  // major: { enabled: true },
                  // Nice Currency on Y axis
                  callback: function(value, index, values) {
                      return Vue.options.filters.niceCurrencyRounded(value)
                  }
              }
            },
            x: {
              type: 'time',
              time: {
                unit: this.data.ago_unit,
                displayFormats: {
                  day: this.data.ago > 7 ? 'MMM d' : 'EEE d',
                  month: 'MMM yyyy'
                },
                tooltipFormat: 'MMM dd yyyy'
              },
            },
            tooltips: {
              callbacks: {
                label: (tooltipItem, data) => {
                  console.log({tooltipItem})
                  return Vue.options.filters.niceCurrency(tooltipItem.yLabel)
                }
              }
            },
          },
          responsive: true,
          maintainAspectRatio: false, // WARNING: Only set to false if the canvas is wrapped in a set height value
          onResize: (chart, newSize) => {
            this.$log.debug('Chart onResize', { newSize })
          },
          resizeDelay: 200, // Eases resize update by denouncing call
      }
    },
  },

  data: () => ({
    shownTypes: [
      'earnings',
      'sale',
      'tip',
      'subscription',

      // 'expenses',
    ],
    chart: null,
  }),

  methods: {

    renderChart() {
      // if ( this.chart ) {
      //   return
      // }
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
        if (this.chart) {
          this.chart.destroy()
        }
        this.renderChart()
      }
    }
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
