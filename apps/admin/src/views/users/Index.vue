<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <card-menu :links="[
              {
                to: { name: 'users/customers' },
                label: this.$t('project', 'Клиенты')
              },
              {
                to: { name: 'users/managers' },
                label: this.$t('project', 'Менеджеры')
              },
              {
                to: { name: 'users/designers' },
                label: this.$t('project', 'Проектировщики')
              },
              {
                to: { name: 'users/administrators' },
                label: this.$t('project', 'Администраторы')
              }
            ]"></card-menu>
          </div>
          <div class="card-body">
            <router-view></router-view>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { CardMenu } from '@/components'

export default {
  name: 'index',
  components: {
    CardMenu
  }
}
</script>
