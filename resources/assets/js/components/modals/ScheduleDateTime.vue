<template>
  <b-card no-body>
    <div class="py-4 px-3">
      <b-form-datepicker
        v-model="selectedDateTime.date"
        class="mb-3"
        ref="schedule_date"
        :state="selectedDateTime.date ? true : null"
        :min="new Date()"
      />
      <b-form-timepicker
        v-model="selectedDateTime.time"
        :state="selectedDateTime.timeState"
        locale="en"
        @input="onChangePostScheduleTime"
      ></b-form-timepicker>
    </div>
    <b-card-footer>
      <div class="text-right">
        <b-btn class="px-3 mr-1" variant="secondary" @click="closePicker">{{ $t('action_btns.cancel') }}</b-btn>
        <b-btn
          class="px-3"
          variant="primary"
          :disabled="!selectedDateTime.date || !selectedDateTime.time || !selectedDateTime.timeState"
          @click="apply"
        >
          {{ $t('action_btns.apply') }}
        </b-btn>
      </div>
    </b-card-footer>
  </b-card>
</template>

<script>
// Schedule Datetime Modal
// event: @apply

import moment from 'moment';
import { eventBus } from '@/app';

export default {
  props: {
    scheduled_at: {
      type: Object,
      required: false,
    }
  },
  data: () => ({ selectedDateTime: {} }),
  mounted() {
    this.selectedDateTime = {
      date: moment.utc(this.scheduled_at ?? moment.utc()).local().format('YYYY-MM-DD'),
      time: moment.utc(this.scheduled_at ?? moment.utc()).local().format('HH:mm:ss'),
      timeState: true,
    };
  },
  methods: {
    onChangePostScheduleTime(event) {
      this.selectedDateTime.timeState = true;
      if (moment().format('YYYY-MM-DD') === this.$refs.schedule_date.value) {
        if (moment().format('HH:mm:ss') > event) {
          this.selectedDateTime.timeState = false;
        }
      }
      this.selectedDateTime = { ...this.selectedDateTime };
    },
    closePicker() {
      this.$bvModal.hide('modal-schedule-datetime');
    },
    apply() {
      const date = moment(`${this.selectedDateTime.date} ${this.selectedDateTime.time}`).utc();
      eventBus.$emit('apply-schedule', date);
      this.closePicker();
    }
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "action_btns": {
      "apply": "Apply",
      "cancel": "Cancel"
    },
  }
}
</i18n>
