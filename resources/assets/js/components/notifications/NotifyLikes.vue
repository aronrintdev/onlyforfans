<template>
  <div v-if="!isLoading">
    <b-card title="Likes Received">
      <hr />
      <b-card-text>
        <ul class="list-unstyled">
          <b-media v-for="(l,idx) in likeables" tag="li" class="mb-0">
            <template #aside>
              <b-img width="48" height="48" rounded="circle" :src="l.liker.avatar.filepath" :alt="l.liker.slug" :title="l.liker.name" />
            </router-link>
          </template>
          <h6 class="mt-0 mb-1">{{ l.liker.name }}  <small class="text-muted">@{{ l.liker.username}}</small></h6>
          <p class="mb-0">liked your 
            <router-link :to="{ name: 'posts.show', params: { slug: l.likeable.slug } }">{{ toSingular(l.likeable_type) }}</router-link>
          </p>
          <small>{{ moment(l.created_at).format('MMM DD, YYYY') }}</small>
          <hr class="mt-2 mb-3" />
        </b-media>
      </ul>
    </b-card-text>
  </b-card>
</div>
</template>

<script>
//import Vuex from 'vuex';
  //import { DateTime } from 'luxon'
import moment from 'moment'

export default {

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.likeables
    },
  },

  data: () => ({
    likeables: null,
    moment: moment,
  }),

  methods: {
    toSingular(str) {
      switch (str) {
        case 'posts':
        case 'comments':
        case 'mediafiles':
          return str.slice(0, -1)
        default:
          return ''
      }
    },
  },

  watch: { },

  mounted() { },

  created() {
    //this.currentFolderPKID = this.vaultfolder_pkid;
    //this.$store.dispatch('getVaultfolder', this.vaultfolder_pkid);
    //this.$store.dispatch('getVault', this.vault_pkid);
    axios.get( route('likeables.index') ).then( response => {
      //const data = response.data
      //console.log('NotifyLikes', {  data })
      this.likeables = response.data.data
    })
  },

  components: {
  },
}
</script>

<style scoped>
</style>

