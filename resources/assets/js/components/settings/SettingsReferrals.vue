<template>
  <div v-if="!isLoading">
    <b-card class="mb-3">
      <div class="border rounded-lg pt-4 pr-3 pl-3 mt-2">
        <b-row>
          <b-col>
            <h6 class="text-secondary font-weight-bold">
              Your Personal Referral URL
            </h6>
            <p class="font-weight-normal mt-2 text-primary">{{ referralUrl }}</p>
          </b-col>
          <b-col>
            <h6
              class="text-primary text-right font-weight-bold mt-2 mr-2 clickable"
              v-clipboard:copy="referralUrl"
              v-clipboard:success="onCopySuccess"
              v-clipboard:error="onCopyError"
            >
              Copy Link
            </h6>
            <p class="text-primary text-right font-weight-normal mt-5 mr-2 clickable" @click="enableViewReferrals = !enableViewReferrals">
              {{!enableViewReferrals ? 'View Referred Users' : 'Hide Referred Users'}}
            </p>
          </b-col>
        </b-row>
      </div>
      <div v-if="enableViewReferrals" class="mt-4">
        <h5 class="text-secondary font-weight-bold">
          YOUR REFERRALS ({{ totalRows }})
        </h5>

        <hr />

        <b-row class="mt-2">
          <b-col lg="4" v-for="(r, idx) in referrals" :key="r.id">
            <WigdetReferral :referral="r.creator" :slug="r.referral.slug" />
          </b-col>
        </b-row>

      <b-pagination
        v-model="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="referrals-list"
        v-on:page-click="pageClickHandler"
        class="mt-3"
      ></b-pagination>
      </div>
    </b-card>

  </div>
</template>

<script>
import Vue from 'vue' // needed to use niceCurrency filter in table formatter below
import Vuex from 'vuex'
import moment from 'moment'
import WigdetReferral from '@components/settings/WidgetReferral';

export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },
    totalRows() {
      return this.meta ? this.meta.total : 1
    },
  },

  watch: { },

  data: () => ({
    referralUrl: null,
    referrals: null,
    meta: null,
    enableViewReferrals: true,
    perPage: 9,
    currentPage: 1,
  }),

  created() {
    this.getPagedData()
  },

  methods: {
    getPagedData(type=null) {
      const params = {
        page: this.currentPage,
        take: this.perPage,
      }

      axios.get( route('referrals.index'), { params } ).then( response => {
        this.referrals = response.data.data
        this.meta = response.data.meta
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },

    onCopySuccess() {
      alert("Copy to Clipboard has been succeed");
      this.showCopyToClipboardModal = false;
    },

    onCopyError() {
      this.showCopyToClipboardModal = false;
      alert("Copy to Clipboard has been failed. Please try again later.");
    },

    async checkReferralCode() {
      await axios.get(route('users.checkReferralCode')).then(res => {
        this.referralUrl = `${window.location.origin}/register?ref=${res.data.referralCode}`
      })
    },
  },

  components: {
    WigdetReferral,
  },

  mounted() {
    this.checkReferralCode()
  },

}
</script>

<style scoped>
.clickable {
  cursor: pointer;
}
</style>
