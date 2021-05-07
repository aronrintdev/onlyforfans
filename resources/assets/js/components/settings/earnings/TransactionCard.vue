<template>
  <b-card>
    <div>{{ fields.id.label }}: {{ fields.id.formatter(value.id, 'id', value) }}</div>
    <div>{{ fields.created_at.formatter(value.created_at, 'created_at', value) }}</div>
    <div class="my-3">
      <div class="d-flex justify-content-between">
        <div>{{ fields.credit_amount.label }}</div>
        <div>{{ fields.fees.label }}</div>
        <div>{{ fields.net_amount.label }}</div>
      </div>
      <div class="d-flex justify-content-between">
        <div>{{ fields.credit_amount.formatter(value.credit_amount, 'credit_amount', value) }}</div>
        <div>{{ fields.fees.formatter(value.fees, 'fees', value) }}</div>
        <div>{{ fields.net_amount.formatter(value.net_amount, 'net_amount', value) }}</div>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <b-btn v-if="value.purchasable_type === 'posts'" variant="outline-primary" @click="$emit('preview', value)" >
          <fa-icon icon="external-link-alt" />
        </b-btn>
      </div>
      <div>
        <div class="d-flex align-items-center">
          <UserAvatar :user="value.purchaser" size="sm" class="mr-2" />
          {{ value.purchaser.username }}
        </div>
      </div>
    </div>
  </b-card>
</template>

<script>
import UserAvatar from '@components/user/Avatar'

export default {
  name: 'Transaction Card',

  components: {
    UserAvatar,
  },

  props: {
    value: { type: Object, default: () => ({})},
    fields: { type: Object, default: () => ({})},
  },
}
</script>

<style lang="scss" scoped>

</style>
