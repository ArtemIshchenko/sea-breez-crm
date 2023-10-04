<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-12">
        <h3 class="mb-3" v-if="model">
          {{ model.title }}
          <sup>
            <b-badge pill :variant="['canceled', 'rejected'].includes(model.status) ? 'danger' : 'primary'">{{ $t('project', $params.project.statuses[model.status]) }}</b-badge>
          </sup>
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
  name: 'project-details',
  mixins: [DetailsMixin],
  methods: {
    async getData () {
      try {
        const response = await this.$store.state.http.client.get('admin/projects/' + this.id)
        this.model = response.data
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось загрузить детали проекта. ') + e.message)
      }
    }
  },
  computed: {
    detailsLinks () {
      return [
        {
          to: { name: 'project/details', params: { id: this.id } },
          label: this.$t('project', 'Детальнее')
        },
        {
          to: { name: 'project/edit', params: { id: this.id } },
          label: this.$t('project', 'Редактировать')
        },
        {
          to: { name: 'project/gear', params: { id: this.id } },
          label: this.$t('project', 'Оборудование')
        },
        {
          to: { name: 'project/location', params: { id: this.id } },
          label: this.$t('project', 'Место установки')
        },
        {
          to: { name: 'project/files', params: { id: this.id } },
          label: this.$t('project', 'Загрузка файлов')
        },
        {
          to: { name: 'project/history', params: { id: this.id } },
          label: this.$t('project', 'История проекта')
        }
      ]
    }
  },
  components: {
    CardMenu
  }
}
</script>
