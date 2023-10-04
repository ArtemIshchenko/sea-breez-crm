<template>
  <div>
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'gears',
  created () {
    this.fetchProducers()
  },
  methods: {
    async fetchProducers () {
      try {
        const response = await this.$http.get('admin/gears/producer-list')
        this.$store.commit('gear/setList', response.data || [])
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось получить список производителей.') + ' ' + e.message)
      }
    }
  }
}
</script>
