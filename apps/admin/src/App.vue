<template>
  <router-view v-if="isAuthenticated"></router-view>
</template>

<script>
import { mapState, mapMutations } from 'vuex'
import Vue from 'vue'
import * as VueGoogleMaps from 'vue2-google-maps'

export default {
  name: 'app',
  computed: {
    ...mapState('user', {
      isAuthenticated: state => state.isAuthenticated,
      isAdmin: state => {
        return state.role === 'administrator'
      }
    }),
    ...mapState('app', {
      googleApiKey: state => state.googleApiKey
    })
  },
  created () {
    // check if user authenticated
    this.initUser()
    // add translation function
    Vue.prototype.$t = (category, string) => this.$store.getters['page/translate'](category, string)
    // reset Page state on each router navigating
    this.$router.afterEach((to, from) => {
      this.resetPageTitle()
    })
  },
  methods: {
    ...mapMutations({
      resetPageTitle: 'page/resetTitle'
    }),
    async initUser () {
      try {
        await this.$store.dispatch('user/init')
        if (!this.isAdmin) {
          window.location.href = this.$const.LOGIN_URL
        }
        await this.initTranslations()
      } catch (e) {
        this.$notifyError(this.$t('user', 'Не удалось идентифицировать пользователя.') + ' ' + e.message)
      }
    },
    async initTranslations () {
      try {
        // add translations
        await this.$store.dispatch('page/attachTranslations')
      } catch (e) {
        this.$notifyWarning(this.$t('user', 'Не удалось загрузить файл перевода.') + ' ' + e.message)
      }
    }
  },
  watch: {
    isAuthenticated: function (isAuthenticated) {
      if (!isAuthenticated) {
        window.location.href = this.$const.LOGIN_URL
      }
    },
    googleApiKey: function (key) {
      if (key) {
        Vue.use(VueGoogleMaps, {
          load: {
            key,
            libraries: 'drawing,places',
            language: this.$store.state.user.lang
          }
        })
      }
    }
  }
}
</script>

<style lang="scss">
  // Import Main styles for this application
  @import 'assets/scss/style';
</style>
