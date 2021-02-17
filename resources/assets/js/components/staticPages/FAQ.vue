<template>
  <div class="container static-page-layout">
    <b-card class="faq-card mt-5" :title="$t('header')">
      <div class="mt-5">
        <div v-for="(item, i) in items" :key="i" class="mb-2">
          <div class="font-weight-bold" v-text="$t(item.question)" />
          <div v-text="$t(item.answer)" />
        </div>
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
    LinkBar
  },
  data: () => ({
    items: [
      {
        question: 'Where is your company located?',
        answer: 'Our mailing address is 1027 S. Rainbow Blvd. #119, Las Vegas, NV 89145',
      },
      {
        question: 'What is your company phone number if I need support?',
        answer: 'Our phone number is (702) 420-2919.',
      },
      {
        question: 'What is the refund policy?',
        answer: 'All purchases made on allfans.com are final and non-refundable.',
      },
      {
        question: 'Can I cancel a subscription?',
        answer: 'Yes, you are entitled to cancel any subscription, for any reason and at any time, thereby ensuring that you will not be billed again for that subscription.',
      },
      {
        question: 'What is the chargeback policy?',
        answer: 'Any purchase made on AllFans resulting in a chargeback will cause the infringing User’s account to be flagged for manual review and permanently excluded from AllFans. Additionally, the chargeback amount, plus a per incident chargeback fee, will be removed from the earning Creator\'s balance.',
      },
    ]
  }),
}
</script>


<i18n lang="json5">
{
  "en": {
    "header": "Frequently Asked Questions",
  },
}
</i18n>
