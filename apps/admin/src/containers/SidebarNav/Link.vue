<template>
  <div v-if="isExternalLink">
    <a :href="url" :class="classList">
      <icon v-if="icon" :icon="icon" class="nav-icon"></icon> {{name}}
      <b-badge v-if="badge && badge.text" :variant="badge.variant">{{badge.text}}</b-badge>
    </a>
  </div>
  <div v-else>
    <router-link :to="url" :class="classList">
      <icon v-if="icon" :icon="icon" class="nav-icon"></icon> {{name}}
      <b-badge v-if="badge && badge.text" :variant="badge.variant">{{badge.text}}</b-badge>
    </router-link>
  </div>
</template>

<script>
import { Icon } from '@/components'

export default {
  name: 'sidebar-nav-link',
  components: {
    Icon
  },
  props: {
    name: {
      type: String,
      default: ''
    },
    url: {
      type: [String, Object],
      default: ''
    },
    icon: {
      type: String,
      default: ''
    },
    badge: {
      type: Object,
      default: () => {}
    },
    variant: {
      type: String,
      default: ''
    },
    classes: {
      type: String,
      default: ''
    }
  },
  computed: {
    classList () {
      return [
        'nav-link',
        this.linkVariant,
        ...this.itemClasses
      ]
    },
    linkVariant () {
      return this.variant ? `nav-link-${this.variant}` : ''
    },
    itemClasses () {
      return this.classes ? this.classes.split(' ') : []
    },
    isExternalLink () {
      if (typeof this.url === 'string' && this.url.substring(0, 4) === 'http') {
        return true
      } else {
        return false
      }
    }
  }
}
</script>
