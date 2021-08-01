<template>
  <div class="p-3">
    <div class="h1">User Beta Program</div>

    <b-alert variant="warning" show>
      <b-media>
        <template #aside>
          <fa-icon icon="exclamation-triangle" size="2x" />
        </template>
        Make sure data is correct and that you are only including email of people that you want to join the beta.
      </b-media>
    </b-alert>

    <b-form-group
      label="Send Beta Testers access emails"
      description="Data must be valid json array in form: [ { email: 'example@allfans.com', name: 'Example Man' }, ... ]"
    >
      <b-textarea v-model="testers" class="text-monospace"></b-textarea>
    </b-form-group>
    <div class="d-flex">
      <b-btn class="ml-auto" variant="primary" :disabled="working" @click="send">
        <fa-icon v-if="working" icon="spinner" spin />
        Send Emails
      </b-btn>
    </div>

    <div class="h2 mt-5">Beta Tokens</div>
    <b-pagination
      v-model="page"
      :total-rows="total"
      :per-page="take"
      aria-controls="users-table"
    ></b-pagination>

    <b-table hover
      id="tokens-table"
      :items="getTokens"
      :per-page="take"
      :current-page="page"
      :fields="fields"
      small
    >
      <template #cell(id)="data">
        <span class="">{{ data.item.id }}</span>
      </template>
      <template #cell(used_at)="data">
        <span class="">{{ !data.item.used_at ? '' : data.item.used_at | niceDateTimeShort}}</span>
      </template>
      <template #cell(used)="data">
        <b-badge :variant="data.item.used_at ? 'success' : 'secondary'">
          <fa-icon :icon="data.item.used_at ? 'check' : 'times'" size="lg" fixed-width />
        </b-badge>
      </template>
      <template #cell(used_by_id)="data">
        <span class="text-monospace">{{ data.item.used_by_id }}</span>
      </template>
      <template #cell(token)="data">
        <span class="text-monospace">{{ data.item.token }}</span>
      </template>
      <template #cell(sent_to)="data">
        <span class="text-monospace">{{ data.item.custom_attributes ? data.item.custom_attributes.sentTo : '' }}</span>
      </template>
      <template #cell(created_at)="data">
        <span class="">{{ data.item.created_at | niceDateTimeShort }}</span>
      </template>
      <template #cell(updated_at)="data">
        <span class="">{{ data.item.updated_at | niceDateTimeShort }}</span>
      </template>
    </b-table>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/admin/components/users/BetaProgram.vue
 */
import Vuex from 'vuex'

export default {
  name: 'BetaProgram',

  components: {},

  props: {},

  computed: {
    fields() {
      return [
        { key: 'id', label: 'Id', },
        { key: 'used', label: 'Used?', },
        { key: 'token', label: 'Token', },
        { key: 'sent_to', label: 'Email Sent To',  },
        { key: 'used_by_id', label: 'Used By', },
        { key: 'used_at', label: 'Used At', },
        { key: 'created_at', label: 'Created At', },
        { key: 'updated_at', label: 'Updated At', },
      ]
    },
  },

  data: () => ({
    testers: '',
    working: false,
    page: 1,
    take: 20,
    total: 0,
  }),

  methods: {
    send() {
      this.working = true,
      this.axios.post(this.$apiRoute('admin.beta-program.add-tokens'), { testers: this.testers })
        .then()
        .catch(error => {})
        .finally(() => {
          this.working = false
          this.testers = ''
        })
    },

    async getTokens(ctx) {
      try {
        const response = await this.axios.get(this.$apiRoute('admin.beta-program.tokens'), {
          params: { page: ctx.currentPage, take: ctx.perPage }
        })
        this.total = response.data.total
        return response.data.data
      } catch (e) {
        console.error(e)
        return []
      }
    },
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
