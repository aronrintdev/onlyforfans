<template>
  <b-card no-body>
    <b-card-header>
      <section class="user-avatar">
        <router-link :to="{ name: 'timeline.show', params: { slug } }">
          <b-img-lazy :src="avatarImage" :title="timeline.name" />
        </router-link>
      </section>
      <section class="user-details">
        <div>
          <router-link :to="{ name: 'timeline.show', params: { slug } }" data-toggle="tooltip" data-placement="top" class="username">
            {{ timeline.name }}
          </router-link>
          <span v-if="timeline.verified" class="verified-badge">
            <fa-icon icon="check-circle" class="text-primary" />
          </span>
        </div>
        <div>
          <span class="text-secondary">@{{ timeline.slug }}</span>
        </div>
      </section>
    </b-card-header>
    <transition name="quick-fade" mode="out-in">
      <div>
        <b-form-group class="flex-fill mt-3 px-3">
          <b-form-textarea v-model="notesInput" placeholder="Notes" maxlength="500" rows="4" />
          <small class="text-muted float-right mt-1">{{ notesInput.length }} / 500</small>
        </b-form-group>
        <b-card-footer class="text-right">
          <b-button @click="onClose" type="cancel" variant="secondary">Cancel</b-button>
          <b-button v-if="notes" @click="clearNotes" type="cancel" variant="danger">Clear</b-button>
          <b-button @click="saveNotes" :disabled="!notesInput" variant="primary">Save</b-button>
        </b-card-footer>
      </div>
    </transition>
  </b-card>
</template>

<script>
/**
 * Add Notes Modal Content
 */
import { eventBus } from '@/eventBus'

export default {
  name: 'AddNotes',

  props: {
    timeline: null,
    notes: null,
    onClose: { type: Function },
    onUpdate: { type: Function },
  },

  computed: {
    slug() {
      return this.timeline.slug
    },

    avatarImage() {
      const { avatar } = this.timeline
      return avatar ? avatar.filepath : '/images/default_avatar.png'
    },
  },

  data: () => ({
    notesInput: '',
  }),

  created() {
    this.notesInput = this.notes?.notes || '';
  },

  methods: {
    async clearNotes() {
      await this.axios.delete(`/notes/${this.notes.id}`)
      if (this.onUpdate) {
        this.onUpdate(null)
      }

      this.onClose()
      this.$root.$bvToast.toast('Notes was successfully removed.', {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
        variant: 'success',
      })
    },

    async saveNotes() {
      let isEdit = this.notes ? true : false
      let res = null
      if (!this.notes) {
        res = await this.axios.post(route('notes.store'), {
          noticed_id: this.timeline.id,
          notes: this.notesInput,
        })
      } else {
        res = await this.axios.patch(`/notes/${this.notes.id}`, {
          notes: this.notesInput,
        })
      }
      if (this.onUpdate) {
        this.onUpdate(res.data.note)
      }

      this.onClose()

      const msg = isEdit ? 'Notes was successfully updated.' : 'Notes was successfully added.'
      this.$root.$bvToast.toast(msg, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
        variant: 'success',
      })
    },
  },
}
</script>

<style scoped>
ul {
  margin: 0;
}

header.card-header,
footer.card-footer {
  background-color: #fff;
}


body .user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}
body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.tip-modal-text {
  border: solid 1px #dfdfdf;
}

button {
  width: 6rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
