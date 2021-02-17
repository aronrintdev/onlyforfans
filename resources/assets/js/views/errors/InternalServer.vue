<template>
  <ErrorDisplay title="500 - Internal Server Error" message="Oops something went wrong on our end." />
</template>

<script>
/**
 * Internal Server Error Page
 */
import ErrorDisplay from '../../components/common/errors/Display'
export default {
  components: {
    ErrorDisplay,
  },
}
</script>
