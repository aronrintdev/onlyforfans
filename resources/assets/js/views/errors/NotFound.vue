<template>
  <ErrorDisplay title="404 - Page Does Not Exist" message="The page you are trying to locate does not exist." />
</template>

<script>
/**
 * 404 Not Found Page
 */
import ErrorDisplay from '../../components/common/errors/Display'
export default {
  components: {
    ErrorDisplay,
  },
}
</script>
