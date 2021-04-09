<template>
  <div>
    <b-row>
      <b-col lg="6">
        <FormGroupSkeleton />
      </b-col>
      <b-col lg="6">
        <FormGroupSkeleton />
      </b-col>
    </b-row>
    <b-row>
      <b-col>
        <div class="d-flex align-items-center mb-3">
          <CardBrandIcon class="mr-3" size="2x" />
          <b-skeleton type="input" width="100%" />
        </div>
      </b-col>
    </b-row>
    <b-row>
      <b-col cols="6">
        <FormGroupSkeleton>
          <div class="d-flex align-items-center">
            <b-skeleton type="input" width="100%" />
            <span class="mx-3" style="font-size:150%;" v-text="'/'" />
            <b-skeleton type="input" width="100%" />
          </div>
        </FormGroupSkeleton>
      </b-col>
      <b-col cols="6">
        <FormGroupSkeleton />
      </b-col>
    </b-row>

    <b-row>
      <b-col lg="6">
        <FormGroupSkeleton />
      </b-col>
      <b-col lg="6">
        <FormGroupSkeleton />
      </b-col>
    </b-row>

    <b-row>
      <b-col cols="12">
        <b-skeleton type="button" width="100%" class="rounded-pill" />
      </b-col>
    </b-row>
  </div>
</template>

<script>
import CardBrandIcon from './CardBrandIcon'
import FormGroupSkeleton from '@components/common/skeletons/FormGroupSkeleton'

export default {
  name: 'SegpayNewSkeleton',
  components: {
    CardBrandIcon,
    FormGroupSkeleton,
  },
}
</script>
