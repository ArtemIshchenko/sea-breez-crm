<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-12">
        <h3 class="mb-3" v-if="model">
          {{ [model.first_name, model.middle_name, model.last_name].join(' ') }}
          <sup>
            <b-badge pill :variant="model.status === 'suspended' ? 'danger' : 'primary'">{{ $t('user', $params.user.statuses[model.status]) }}</b-badge>
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
  name: 'user-details',
  mixins: [DetailsMixin],
  methods: {
    async getData () {
      try {
        const response = await this.$store.state.http.client.get('admin/users/' + this.id)
        this.model = response.data
      } catch (e) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: this.$t('project', 'Не удалось загрузить данные пользователя.') + ' ' + e.message,
          duration: -1
        })
      }
    }
  },
  computed: {
    detailsLinks () {
      const links = [
        {
          to: { name: 'user/details', params: { id: this.id } },
          label: this.$t('project', 'Детальнее')
        },
        {
          to: { name: 'user/edit', params: { id: this.id } },
          label: this.$t('project', 'Редактировать')
        },
        {
          to: { name: 'user/history', params: { id: this.id } },
          label: this.$t('project', 'История')
        }
      ]
      if (this.model && this.model.id === this.$store.state.user.id) {
        links.splice(1, 1)
      }
      return links
    }
  },
  components: {
    CardMenu
  }
}
</script>
