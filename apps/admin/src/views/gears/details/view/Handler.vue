<template>
  <div class="animated fadeIn">
    <!-- remove action -->
    <b-button
           variant="danger"
           block
           @click="remove()">{{ $t('project', 'Удалить оборудование') }}</b-button>
  </div>
</template>

<script>
import { LoadingRequestMixin } from '@/mixins'

export default {
  name: 'gear-handler',
  mixins: [LoadingRequestMixin],
  props: ['model'],
  data () {
    return {
    }
  },
  methods: {
    async remove () {
      if (this.isLoadingRequest('gear.remove')) {
        return
      }
      try {
        this.startLoadingRequest('gear.remove')
        const response = await this.$http.delete('admin/gears/' + this.model.id)
        if (response.status === 204) {
          this.$notifySuccess(this.$t('gear', 'Оборудование было успешно удалено.'))
          this.$router.push({ name: 'gears' })
        }
        this.stopLoadingRequest('gear.remove')
      } catch (e) {
        this.stopLoadingRequest('gear.remove')
        this.$notifyError(this.$t('gear', 'Не удалось удалить оборудование.') + ' ' + e.message)
      }
    }
  }
}
</script>
