<template>
  <b-button @click="$emit('click')" size="sm" variant="danger">
    <font-awesome-icon icon="trash-alt" :class="{'mr-1': !shrinkOnMobile && !noLabel, 'mr-sm-1': shrinkOnMobile && !noLabel}" />
    <span v-if="!noLabel" :class="{'d-none d-sm-inline': shrinkOnMobile}">Удалить</span>
  </b-button>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
export default {
  name: 'delete-button',
  props: {
    shrinkOnMobile: {
      type: Boolean,
      default: true
    },
    noLabel: {
      type: Boolean,
      default: false
    }
  },
  components: {
    FontAwesomeIcon
  }
}
</script>
