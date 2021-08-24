/**
 * resources/assets/js/routes/statements.routes.js
 */

import Statistics from '@components/statements/Statistics.vue'
import Transactions from '@components/statements/Transactions'
import Payouts from '@components/statements/Payouts'
import Chargebacks from '@components/statements/Chargebacks'

export default [
  {
    name: 'statements.statistics',
    component: Statistics,
    path: 'stats',
  },
  {
    name: 'statements.transactions',
    component: Transactions,
    path: 'transactions',
  },
  {
    name: 'statements.payouts',
    component: Payouts,
    path: 'payouts',
  },
  {
    name: 'statements.chargebacks',
    component: Chargebacks,
    path: 'chargebacks',
  },
]