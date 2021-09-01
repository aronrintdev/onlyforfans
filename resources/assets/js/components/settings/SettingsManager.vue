<template>
  <div>
    <b-card v-if="manager" class="mb-3">
      <b-card-text>
        <div class="d-flex align-items-center mb-4">
          <a class="text-primary text-decoration-none link-btn" @click="$router.go(-1)">
            <fa-icon :icon="['far', 'arrow-left']" />
          </a>
          <div class="h3 mb-0 ml-3 text-center">{{ `${manager.first_name || ''} ${manager.last_name || ''}` }}</div>
        </div>
        <b-form @submit.prevent="submitManagerSettings($event)">
          <div class="manager-setttings row">
            <div class="col-12 col-md-6">
              <b-form-group label="Percentage of Gross Earnings" label-for="settings.earnings">
                <b-input-group class="mb-2 mr-sm-2 mb-sm-0">
                  <b-form-input 
                    v-model="settings.earnings.value"
                    :formatter="validateData"
                    :state="verrors['earnings']"
                    type="text"
                  ></b-form-input>
                  <template #append>
                    <b-input-group-text>%</b-input-group-text>
                  </template>
                  <b-form-invalid-feedback id="input-live-feedback">{{ verrors['earnings'] }}</b-form-invalid-feedback>
                </b-input-group>
              </b-form-group>
            </div>
          </div>
          <b-row class="mt-3">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button class="w-25 ml-3" type="submit" :disabled="isSubmitting" variant="primary">
                  <b-spinner class="mr-1" v-if="isSubmitting" small />
                  Save
                </b-button>
              </div>
            </b-col>
          </b-row>
        </b-form>
      </b-card-text>
    </b-card>
    <b-modal id="modal-delete-earnings-setting" v-model="isDeleteEarnings" size="md" title="Delete Earnings Setting" >
      <p>Are you sure you want to delete the percentage of earnings setting?</p>
      <template #modal-footer>
        <b-button @click="isDeleteEarnings=false" type="cancel" variant="secondary">Cancel</b-button>
        <b-button @click="isDeleteEarnings=false; updateSettings()" variant="danger">Delete</b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import Vuex from 'vuex'
import FormTextInput from '@components/forms/elements/FormTextInput'

export default {
  computed: {
    ...Vuex.mapState([ 'mobile' ]),
  },
  
  components: {
    FormTextInput,
  },

  data: () => ({
    manager: null,
    settings: {
      earnings: {
        value: null,
      }
    },
    verrors: {},
    earningsVal: null,
    isSubmitting: false,
    isDeleteEarnings: false,
  }),

  created() {
    axios.get(this.$apiRoute('staff.getManager', this.$route.params.id))
      .then(res => {
        this.manager = res.data
        this.settings = { ...this.manager.settings }
      })
  },

  methods: {
    submitManagerSettings() {
      if (!this.settings.earnings.value) {
        this.isDeleteEarnings = true
        return
      }
      this.updateSettings()
    },
    async updateSettings() {
      this.isSubmitting = true
      axios.patch(this.$apiRoute('staff.updateManagerSettings', this.$route.params.id), {
        settings: {
          earnings: this.settings.earnings.value,
        },
      })
        .then(() => {
          this.$root.$bvToast.toast('Settings successfully updated!', {
            toaster: 'b-toaster-top-center',
            title: 'Success',
            variant: 'success',
          })
          this.isSubmitting = false
        })
        .catch((err) => {
          this.$root.$bvToast.toast(err.message, {
            toaster: 'b-toaster-top-center',
            title: 'Failed',
            variant: 'danger',
          })
          this.isSubmitting = false
        })
    },
    validateData(value) {
      let numV = parseInt(value, 10);
      if (numV > 100) {
        numV = 100;
      } else if(numV < 0) {
        numV = 0;
      } else if (isNaN(numV)) {
        numV = null;
      }
      return numV
    }
  },

}
</script>

<style lang="scss" scoped>
.setting-status {
  position: absolute;
  top: 5px;
  right: 15px;
}
</style>


