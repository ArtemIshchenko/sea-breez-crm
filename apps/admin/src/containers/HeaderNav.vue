<template>
  <b-navbar-nav class="ml-auto">
    <b-nav-item :to="{ name: 'profile' }" v-b-popover.hover.bottom="$t('user', 'Редактировать профиль')">
      <icon icon="user" size="lg"></icon>
    </b-nav-item>
    <b-nav-item @click="logout" v-b-popover.hover.bottom="$t('user', 'Выйти')">
      <icon icon="power-off" size="lg"></icon>
    </b-nav-item>
  </b-navbar-nav>
</template>

<script>
import { Icon } from '@/components'

export default {
  name: 'header-nav',
  components: {
    Icon
  },
  methods: {
    async logout () {
      try {
        await this.$store.dispatch('user/logout')
      } catch (e) {
        this.$notifyError(this.$t('project', 'Выход из программы не удался.') + ' ' + e.message)
      }
    }
  }
}
</script>
