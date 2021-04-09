<template>
  <b-form-group>
    <template #label>
      <b-skeleton />
    </template>
    <slot>
      <b-skeleton type="input" />
    </slot>
  </b-form-group>
</template>

<script>
export default {
  name: "FormGroupSkeleton",
}
</script>