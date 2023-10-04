<template>
  <div class="card-menu" :class="{minimized: tooWide}" ref="menuContainer">
    <ul class="nav nav-tabs" ref="menuList">

      <li v-for="(link, index) in links" class="nav-item" :key="index">
        <router-link :to="link.to" class="nav-link" exact>
          {{ link.label }}
          <badge v-if="link.badge" :label="link.badge" @mounted="checkMenuWidth" />
        </router-link>
      </li>

    </ul>

    <b-dropdown no-caret variant="transparent" class="card-menu-toggler">
      <template slot="button-content">
        <span class="toggler-icon"></span>
      </template>
      <b-dropdown-item v-for="(link, index) in links" class="" :key="index">
        <router-link :to="link.to" class="nav-link">
          {{ link.label }}
          <b-badge v-if="link.badge" pill variant="primary">{{ link.badge }}</b-badge>
        </router-link>
      </b-dropdown-item>
    </b-dropdown>
  </div>
</template>

<script>

import Badge from './CardMenuBadge'

export default {
  name: 'card-menu',
  props: {
    // `Links` may be set as array of objects.
    // Every link object must have `to` and `label` properties.
    // Optional link proerties: `badge`
    links: {
      type: Array,
      validator: value => value.some(
        link =>
          typeof link === 'object' && link.hasOwnProperty('to') && link.hasOwnProperty('label')
      )
    }
  },
  data () {
    return {
      tooWide: false
    }
  },
  mounted () {
    this.checkMenuWidth()
    window.addEventListener('resize', this.checkMenuWidth)
  },
  beforeDestroy: function () {
    window.removeEventListener('resize', this.checkMenuWidth)
  },
  methods: {
    checkMenuWidth () {
      let menuWidth = 0
      for (let link of this.$refs.menuList.querySelectorAll('li > a')) {
        menuWidth += link.offsetWidth
      }
      this.tooWide = menuWidth > this.$refs.menuContainer.clientWidth - 25
    }
  },
  components: {
    Badge
  }
}
</script>
