<template>
  <div class="container static-page-layout">
    <b-card class="legal-card mt-5" :title="$t('header')">
      <div class="mt-5">
        <p>
          The following information serves as a guideline for law enforcement officials seeking
          disclosure of records and is subject to change at any time.
        </p>

        <p>
          If you are seeking disclosure of information related to the identity of a user, account
          information or content related data, you may have a valid subpoena, search warrant,
          preservation order or judicial or administrative order issued and served to AllFans’
          operating entity and address:
        </p>

        <p>
          Hitzke & Ferran<br />
          Erick Ferran Esq.<br />
          2110 East Flamingo Road, Suite 206<br />
          Las Vegas, Nevada 89119<br />
          legal@allfans.com
        </p>

        <p>
          We may raise or waive any legal objection or right available to it, in its sole
          discretion. For additional information, please contact our legal team at legal@allfans.com
        </p>
      </div>
    </b-card>

    <div class="mt-auto mb-3">
      <LinkBar />
    </div>
  </div>
</template>

<script>
import LinkBar from './LinkBar'
export default {
  components: {
    LinkBar,
  },
}
</script>

<i18n lang="json5">
{
  "en": {
    "header": "Legal",
  },
}
</i18n>
