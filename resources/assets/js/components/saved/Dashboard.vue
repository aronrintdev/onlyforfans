<template>
  <div class="container vault-container">
    <main class="row">
      <section class="col-sm-12">
        <b-card title="Saved Posts">

          <b-tabs pills content-class="OFF-mt-3">

            <b-tab title="Saved">
              <ul class="list-unstyled mt-3">
                <li class="mb-3" v-for="(sv) in this.saves" :key="sv.guid">
                  <b-media class="mb-1">
                    <template #aside>
                      <b-img class="tag-avatar" rounded="circle" :src="sv.user.avatar" width="64" height="64" alt="avatar"></b-img>
                    </template>
                    <h6 class="mt-0">{{ sv.user.name }}</h6>
                  </b-media>
                  <!--
                  <b-card :title="sv.name" :img-src="sv.filepath" :img-alt="sv.name" img-bottom class="mb-5"></b-card>
                  -->
                  <article>
                    <table>
                      <tr>
                        <td>Name:</td>
                        <td>{{ sv.user.name }}</td>
                      </tr>
                      <tr>
                        <td>Images:</td>
                        <td>
                          <ul>
                          <li v-for="(i) in sv.images" :key="i.id">
                            <img :key="i.id" :src="i.source" :alt="i.source" />
                          </li>
                          </ul>
                        </td>
                      </tr>
                    </table>
                  </article>
                  <hr />
                </li>
              </ul>
            </b-tab>

            <b-tab title="Purchased">
              <p>Purchased items TBD</p>
            </b-tab>

            <b-tab active title="Shared">

              <ul>
                <li v-for="(vf) in this.shareables.vaultfolders" :key="vf.guid">{{ vf.name }}</li>
              </ul>

              <ul class="list-unstyled mt-3">
                <li v-for="(mf) in this.shareables.mediafiles" :key="mf.guid">
                  <b-media class="mb-1">
                    <template #aside>
                      <b-img class="tag-avatar" rounded="circle" :src="mf.owner.avatar" width="64" height="64" alt="avatar"></b-img>
                    </template>
                    <h6 class="mt-0">{{ mf.owner.name }}</h6>
                  </b-media>
                  <b-card
                    :title="mf.name"
                    :img-src="mf.filepath"
                    :img-alt="mf.name"
                    img-bottom
                    tag="article"
                    class="mb-5"
                    >
                  </b-card>
                </li>
              </ul>

            </b-tab>

            <b-tab title="Disabled" disabled><p>disabled tab ex</p></b-tab>

          </b-tabs>

        </b-card>
      </section>
    </main>
  </div>
</template>


<script>
import Vuex from 'vuex';
//import { eventBus } from '../../app';

export default {

  props: {
    vault_pkid: {
      required: true,
      type: Number,
    },
    vaultfolder_pkid: { // init value
      required: true,
      type: Number,
    },
  },

  computed: {
    ...Vuex.mapState(['shareables']),
    ...Vuex.mapState(['saves']),
  },

  data: () => ({
    show: true,
  }),

  created() {
    this.$store.dispatch('getSaves');
    this.$store.dispatch('getShareables');
  },

  methods: {
  },

  components: {
    //vueDropzone: vue2Dropzone,
  },
}
</script>

<style scoped>
.tag-avatar {
  width: 64px;
  height: 64px;
}
</style>
