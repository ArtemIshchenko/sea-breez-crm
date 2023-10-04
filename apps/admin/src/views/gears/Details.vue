<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-12">
        <h3 class="mb-3" v-if="model">
          {{ model.title }}
        </h3>
        <div class="card">
          <div class="card-header">
            <card-menu :links="detailsLinks"></card-menu>
          </div>
          <div class="card-body">
            <router-view
                  v-if="model"
                  :model="model"
                  @updated="onUpdated($event)"></router-view>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { DetailsMixin } from '@/mixins'
import { CardMenu } from '@/components'

export default {
  name: 'gear-details',
  mixins: [DetailsMixin],
  methods: {
    async getData () {
      try {
        const response = await this.$http.get('admin/gears/' + this.id)
        this.model = response.data
      } catch (e) {
        this.$notifyError(this.$t('gear', 'Не удалось загрузить детали оборудования. ') + e.message)
      }
    }
  },
  computed: {
    detailsLinks () {
      return [
        {
          to: { name: 'gear/details', params: { id: this.id } },
          label: this.$t('project', 'Детальнее')
        },
        {
          to: { name: 'gear/edit', params: { id: this.id } },
          label: this.$t('project', 'Редактировать')
        }
      ]
    }
  },
  components: {
    CardMenu
  }
}
</script>
