<template>
  <div v-if="!isLoading">
      <b-row class="justify-content-end mt-0">
        <b-col md="6" class="text-right">

          <b-dropdown no-caret right ref="listCtrls" variant="transparent" id="feed-ctrl-dropdown" class="tag-ctrl">
            <template #button-content>
              <b-icon icon="filter" scale="1.8" variant="primary"></b-icon>
            </template>
            <b-dropdown-form>
              <b-form-group label="Subscription">
                <b-form-radio v-model="accessLevel" size="sm" name="access-level" value="all">All</b-form-radio>
                <b-form-radio v-model="accessLevel" size="sm" name="access-level" value="premium">Paid</b-form-radio>
                <b-form-radio v-model="accessLevel" size="sm" name="access-level" value="default">Free</b-form-radio>
              </b-form-group>
              <b-dropdown-divider></b-dropdown-divider>
              <b-form-group label="Online Status">
                <b-form-radio v-model="onlineStatus" size="sm" name="online-status" value="all">All</b-form-radio>
                <b-form-radio v-model="onlineStatus" size="sm" name="online-status" value="online">Online</b-form-radio>
                <b-form-radio v-model="onlineStatus" size="sm" name="online-status" value="offline">Offline</b-form-radio>
              </b-form-group>
            </b-dropdown-form>
          </b-dropdown>

          <b-dropdown no-caret right ref="feedCtrls" variant="transparent" id="feed-ctrl-dropdown" class="tag-ctrl">
            <template #button-content>
              <b-icon icon="arrow-down-up" scale="1.3" variant="primary"></b-icon>
            </template>
            <b-dropdown-form>
              <b-form-group label="">
                <b-form-radio v-model="sortBy" size="sm" name="sort-by" value="activity">Last Activity</b-form-radio>
                <b-form-radio v-model="sortBy" size="sm" name="sort-by" value="name">Name</b-form-radio>
                <b-form-radio v-model="sortBy" size="sm" name="sort-by" value="start_date">Started</b-form-radio>
              </b-form-group>
              <b-dropdown-divider></b-dropdown-divider>
              <b-form-group label="">
                <b-form-radio v-model="sortDir" size="sm" name="sort-dir" value="asc">Ascending</b-form-radio>
                <b-form-radio v-model="sortDir" size="sm" name="sort-dir" value="desc">Descending</b-form-radio>
              </b-form-group>
            </b-dropdown-form>
          </b-dropdown>

        </b-col>
      </b-row>

  </div>
</template>

<script>
//import Vuex from 'vuex';
import { eventBus } from '@/app'

export default {

  props: {
    //session_user: null,
  },

  computed: {
    isLoading() {
      return false;
      //return !this.session_user || !this.shareables || !this.meta
    },
  },

  data: () => ({
    sortBy: null,
    sortDir:  'asc',

    // filters
    accessLevel: 'all',
    onlineStatus: 'all',
  }),

  methods: { 
  },

  watch: {
    accessLevel (newVal) {
      console.log('lists.CtrlBar::accessLevel()', { 
        newVal,
      })
      this.$refs.listCtrls.hide(true)
      this.$emit('apply-filters', {
        filters: {
          accessLevel: this.accessLevel,
          onlineStatus: this.onlineStatus,
        },
        sort: {
          by: this.sortBy,
          dir: this.sortDir,
        },
      })
      //this.reloadFromFirstPage();
    },
  },

  mounted() { },

  created() { },

  components: { },
}
</script>

<style lang="scss" scoped>
</style>

