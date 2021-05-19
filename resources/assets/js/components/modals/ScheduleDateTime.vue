<template>
  <div class="block-modal">
    <div class="header d-flex align-items-center">
      <h4 class="pt-1 pb-1">SCHEDULED POST</h4>
    </div>
    <div class="content">
      <b-form-datepicker
        v-model="postSchedule.date"
        class="mb-3 mt-1"
        ref="schedule_date"
        :state="postSchedule.date ? true : null"
        :min="new Date()"
      />
      <b-form-timepicker
        v-model="postSchedule.time"
        :state="postSchedule.timeState"
        class="mb-2"
        locale="en"
        @input="onChangePostScheduleTime"
      ></b-form-timepicker>
    </div>
    <div class="d-flex align-items-center justify-content-end action-btns">
      <button class="link-btn" @click="closeSchedulePicker">Cancel</button>
      <button
        class="link-btn"
        @click="applySchedule"
        :disabled="!postSchedule.date || !postSchedule.time || !postSchedule.timeState"
      >Apply</button>
    </div>
  </div>
</template>

<script>
// Schedule Datetime Modal
// event: @apply

import moment from 'moment';

export default {
  data: () => ({
    moment,
    postSchedule: {},
  }),
  mounted: function() {
    this.postSchedule = {};
  },
  methods: {
    onChangePostScheduleTime(event) {
      this.postSchedule.timeState = true;
      if (moment().format('YYYY-MM-DD') === this.$refs.schedule_date.value) {
        if (moment().format('HH:mm:ss') > event) {
          this.postSchedule.timeState = false;
        }
      }
      this.postSchedule = { ...this.postSchedule };
    },
    closeSchedulePicker() {
      this.$bvModal.hide('modal-schedule-datetime');
    },
    applySchedule() {
      const postScheduleDate = moment(`${this.postSchedule.date} ${this.postSchedule.time}`).utc().unix();
      this.closeSchedulePicker();
      this.$emit('apply', postScheduleDate);
    }
  },
}
</script>

<style lang="scss" scoped>

</style>
