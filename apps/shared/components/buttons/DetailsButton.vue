<template>
  <router-link :to="to" class="btn btn-sm btn-primary">
    <font-awesome-icon icon="cube" :class="{'mr-1': !shrinkOnMobile, 'mr-sm-1': shrinkOnMobile}" />
    <span :class="{'d-none d-sm-inline': shrinkOnMobile}">Детальнее</span>
  </router-link>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
export default {
  name: 'detais-button',
  props: {
    to: {
      type: [String, Object],
      required: true
    },
    shrinkOnMobile: {
      type: Boolean,
      default: true
    }
  },
  components: {
    FontAwesomeIcon
  }
}
</script>
