<template>
  <div>
    Test TransactionsTable
  </div>
</template>

<script>
import _ from 'lodash'
import Vue from 'vue'
import Vuex from 'vuex'
//import { eventBus } from '@/app'
import { eventBus } from '@/eventBus'
import moment from 'moment'

//import LoadingOverlay from '@components/common/LoadingOverlay'
//import PostDisplay from '@components/posts/Display'
//import UserAvatar from '@components/user/Avatar'
//import TransactionCard from './Card'

export default {
  name: 'TransactionsTable',

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "error": {
      "load": "Unable to load transactions",
      "preview": "Failed to Load Preview"
    },
    "table": {
      "label": {
        "id": "ID",
        "date": "Date",
        "gross": "Gross",
        "fees": "Fees",
        "net": "Net",
        "type": "Trans Type",
        "itemType": "Item Type",
        "view": "View Item",
        "purchaser": "Purchaser"
      }
    }
  }
}
</i18n>
