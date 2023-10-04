<template>
  <div class="animated fadeIn">

    <!-- Buttons block -->
    <div class="d-flex justify-content-end flex-wrap mb-4">
      <b-button class="mt-1" v-if="!filter.isOpen" type="button" variant="outline-primary" @click="toggleFilter" key="openF">{{ $t('project', 'Открыть фильтр') }}</b-button>
      <b-button class="mt-1" v-else type="button" variant="outline-primary" @click="toggleFilter" key="closeF">{{ $t('project', 'Закрыть фильтр') }}</b-button>
    </div>

    <!-- Filter -->
    <div class="index-filter" v-if="filter.isOpen">
      <div class="row my-3">
        <div class="col-6 col-md-4 col-xl-3">
          <form-select
                name="id"
                :label="$t('project', 'Фамилия, имя')"
                :value="filter.model.id"
                :options="customersList"
                allow-empty
                @change="filterChanged('id', $event)"></form-select>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
          <form-input
                name="email"
                label="E-mail"
                @change="filterChanged('email', $event)"></form-input>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
          <form-input
                name="company"
                :label="$t('project', 'Компания')"
                @change="filterChanged('company', $event)"></form-input>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
          <form-select
                name="manager_id"
                :label="$t('project', 'Менеджер')"
                :value="filter.model.manager_id"
                :options="managersList"
                allow-empty
                @change="filterChanged('manager_id', $event)"></form-select>
        </div>
      </div>
    </div>

    <!-- Items grid -->
    <data-grid
          :fields="grid.fields"
          :data-slots="grid.slots"
          service="admin/users?role=customer"
          no-actions
          :filter="filter">
      <template #name="{ item }">
        <router-link :to="{ name: 'user/details', params: { id: item.id } }">
          {{ [item.last_name, item.first_name].join(' ') }}
        </router-link>
      </template>
      <template #status="{ item }">{{  $t('user', $params.user.statuses[item.status]) }}</template>
      <template #one_c_status="{ item }">{{ item.contact_guid ? $t('project', 'Связан') : '' }}</template>
      <template #company="{ item }">{{ truncate(item.company) }}</template>
      <template #created_at="{ item }">{{ timeToHuman(item.created_at) }}</template>
      <template #entered_at="{ item }">{{ timeToHuman(item.entered_at) }}</template>
      <template #projects_number="{ item }">
        <router-link :to="{ name: 'projects', query: { filter: { author: item.id } } }">
          {{ item.projects_number }}
        </router-link>
      </template>
    </data-grid>

  </div>
</template>

<script>
import { DataGrid, FormInput, FormSelect } from '@/components'
import { DateHelper, StringHelper } from '@/helpers'
import { FilterMixin } from '@/mixins'

export default {
  name: 'customers',
  mixins: [FilterMixin],
  components: {
    DataGrid, FormInput, FormSelect
  },
  data () {
    return {
      filter: {
        model: {
          id: null,
          email: null,
          company: null,
          manager_id: null
        },
        conditions: {
          email: this.$const.CONDITIONS.LIKE,
          company: this.$const.CONDITIONS.LIKE
        }
      },

      grid: {
        fields: {
          name: {
            sortable: true,
            label: this.$t('project', 'Фамилия, имя')
          },
          email: {
            sortable: true,
            label: 'Email',
            class: 'd-none d-lg-table-cell'
          },
          company: {
            sortable: true,
            label: this.$t('project', 'Компания'),
            class: 'd-none d-lg-table-cell'
          },
          one_c_status: {
            disabled: true,
            sortable: true,
            label: 'guid статус',
            class: 'd-none d-lg-table-cell'
          },
          projects_number: {
            sortable: true,
            label: this.$t('project', 'Проекты'),
            class: 'd-none d-md-table-cell'
          },
          status: {
            sortable: true,
            label: 'Статус',
            class: 'd-none d-md-table-cell'
          },
          created_at: {
            sortable: true,
            label: this.$t('project', 'Дата регистрации'),
            class: 'd-none d-xl-table-cell'
          },
          entered_at: {
            sortable: true,
            label: this.$t('project', 'Дата входа'),
            class: 'd-none d-xl-table-cell'
          }
        },
        slots: ['name', 'created_at', 'entered_at', 'status', 'projects_number', 'company', 'one_c_status']
      },
      customersList: [],
      managersList: []
    }
  },
  created () {
    this.getUserLists()
  },
  methods: {
    async getUserLists () {
      try {
        const response = await this.$http.get('admin/users/list', {
          params: {
            role: ['customer', 'manager']
          }
        })
        if (response.data) {
          this.customersList = response.data.customer
          this.managersList = response.data.manager
        }
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось получить список пользователей.') + ' ' + e.message)
      }
    },
    timeToHuman (time) {
      return DateHelper.toHumanStr(time)
    },
    truncate (str, words = 5) {
      return StringHelper.truncateWords(str, words)
    }
  }
}
</script>
