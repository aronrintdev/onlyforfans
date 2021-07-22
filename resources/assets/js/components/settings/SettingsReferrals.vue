<template>
  <div v-if="!isLoading">
    <b-card class="mb-3">
      <div class="border rounded-lg pt-4 pr-3 pl-3 mt-2">
        <b-row>
          <b-col>
            <h6 class="text-secondary font-weight-bold">
              Your Personal Referral URL
            </h6>
            <p class="font-weight-normal mt-2">{{ referralUrl }}</p>
          </b-col>
          <b-col>
            <h6 class="text-info text-right font-weight-bold mt-2 mr-2 clickable" @click="copyTextToClipboard">
              COPY
            </h6>
            <p class="text-info text-right font-weight-normal mt-5 mr-2 clickable" @click="enableViewReferrals = !enableViewReferrals">
              {{!enableViewReferrals ? 'View Referred Users' : 'Hide Referred Users'}}
            </p>
          </b-col>
        </b-row>
      </div>
      <div v-if="enableViewReferrals" class="mt-4">
        <h5 class="text-secondary font-weight-bold">
          YOUR REFERRALS
        </h5>
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
    enableViewReferrals: false,
    perPage: 9,
    currentPage: 1,
  }),

  created() {
    this.referralUrl = `${window.location.origin}/register?ref=${this.session_user.id}`
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

    copyTextToClipboard() {
      let textArea = document.createElement('textarea');
      textArea.style.position = 'fixed';
      textArea.style.top = 0;
      textArea.style.left = 0;
      textArea.style.width = '2em';
      textArea.style.height = '2em';
      textArea.style.border = 'none';
      textArea.style.outline = 'none';
      textArea.style.boxShadow = 'none';
      textArea.style.background = 'transparent';
      textArea.value = this.referralUrl;

      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();

      try {
        document.execCommand('copy');
      } catch (err) {
        console.log(err);
      }

      document.body.removeChild(textArea);
    },
  },

  components: {
    WigdetReferral,
  }

}
</script>

<style scoped>
.clickable {
  cursor: pointer;
}
</style>
