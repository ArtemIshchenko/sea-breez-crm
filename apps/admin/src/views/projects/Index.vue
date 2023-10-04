<template>
  <div class="animated fadeIn">
    <div class="card">
      <div class="card-body">
        <!-- Buttons block -->
        <div class="d-flex justify-content-between flex-wrap mb-4">
          <div class="d-flex justify-content-between" style="width: 370px;">
            <b-button class="mt-1" variant="outline-primary" @click="showMap = true">{{ $t('project', 'Посмотреть на карте') }}</b-button>
            <b-modal
                  :title="$t('project', 'Карта обьектов')"
                  class="modal-primary"
                  hide-footer
                  size="lg"
                  lazy
                  v-model="showMap">
              <projects-map :projects="loadedProjects"></projects-map>
            </b-modal>
            <b-button
                class="mt-1"
                variant="outline-primary"
                @click="showFaqInfo = true">
              {{ $t('project', 'FAQ - Что такое проект?') }}
            </b-button>
            <b-modal
                :title="$t('project', 'FAQ - Что такое проект?')"
                class="modal-primary"
                size="xl"
                ok-only
                scrollable
                lazy
                v-model="showFaqInfo">
              <faq :info="faqInfo"></faq>
            </b-modal>
          </div>

          <b-button class="mt-1" v-if="!filter.isOpen" variant="outline-primary" @click="toggleFilter" key="openF">{{ $t('project', 'Открыть фильтр') }}</b-button>
          <b-button class="mt-1" v-else variant="outline-primary" @click="toggleFilter" key="closeF">{{ $t('project', 'Закрыть фильтр') }}</b-button>
        </div>

        <!-- Filter -->
        <div class="index-filter" v-if="filter.isOpen">
          <div class="row my-3">
            <div class="col-6 col-md-4 col-xl-1">
              <form-input
                    name="id"
                    label="ID"
                    :value="filter.model.id"
                    @change="filterChanged('id', $event)"></form-input>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
              <form-select
                    name="status"
                    :label="$t('project', 'Статус')"
                    :value="filter.model.status"
                    :options="projectStatuses"
                    allow-empty
                    @change="filterChanged('status', $event)"></form-select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-input
                    name="title"
                    :label="$t('project', 'Название проекта')"
                    :value="filter.model.title"
                    @change="filterChanged('title', $event)"></form-input>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-date-picker
                    name="date"
                    :label="$t('project', 'Срок сдачи проекта')"
                    range
                    timestamp
                    :value="filter.model.date"
                    @change="filterChanged('date', $event)"></form-date-picker>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-date-picker
                    name="created_at"
                    :label="$t('project', 'Дата создания')"
                    range
                    timestamp
                    :value="filter.model.created_at"
                    @change="filterChanged('created_at', $event)"></form-date-picker>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-date-picker
                    name="updated_at"
                    :label="$t('project', 'Дата обновления')"
                    range
                    timestamp
                    :value="filter.model.updated_at"
                    @change="filterChanged('updated_at', $event)"></form-date-picker>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-select
                    name="author"
                    :label="$t('project', 'Автор')"
                    :value="filter.model.author"
                    :options="customersList"
                    allow-empty
                    @change="filterChanged('author', $event)"></form-select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-select
                    name="manager"
                    :label="$t('project', 'Менеджер')"
                    :options="managersList"
                    allow-empty
                    :value="filter.model.manager"
                    @change="filterChanged('manager', $event)"></form-select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-select
                    name="designer"
                    :label="$t('project', 'Проектировщик')"
                    :options="designersList"
                    allow-empty
                    :value="filter.model.designer"
                    @change="filterChanged('designer', $event)"></form-select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-input
                    name="client"
                    :label="$t('project', 'Заказчик')"
                    :value="filter.model.client"
                    @change="filterChanged('client', $event)"></form-input>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-input
                    name="company"
                    :label="$t('project', 'Компания автора')"
                    :value="filter.model.company"
                    @change="filterChanged('company', $event)"></form-input>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-select
                    name="gear"
                    :label="$t('project', 'Оборудование')"
                    :options="gearsList"
                    allow-empty
                    :value="filter.model.gear"
                    @change="filterChanged('gear', $event)"></form-select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-select
                    name="provider"
                    :label="$t('project', 'Провайдер замовника')"
                    :options="providerList"
                    allow-empty
                    :value="filter.model.provider"
                    @change="filterChanged('provider', $event)"></form-select>
            </div>
          </div>
        </div>

        <!-- Items grid -->
        <data-grid
              :fields="gridFields"
              :data-slots="grid.slots"
              service="admin/projects"
              no-actions
              :filter="filter"
              @loaded="loadedProjects = $event">
          <template #status="{ item }">
            <span v-if="item.status_message" v-b-tooltip :title="item.status_message">
              {{ $t('project', $params.project.statuses[item.status]) }}
            </span>
            <span v-else>
              {{ $t('project', $params.project.statuses[item.status]) }}
            </span>
          </template>
          <template #created_at="{ item }">{{ timeToHuman(item.created_at) }}</template>
          <template #updated_at="{ item }">{{ timeToHuman(item.updated_at) }}</template>
          <template #title="{ item }">
            <router-link
              v-if="shouldBeTruncated(item.title)"
              :to="{ name: 'project/details', params: { id: item.id } }"
              v-b-tooltip
              :class="{'text-muted': isFinishedProject(item)}"
              :title="item.title">
              {{ truncate(item.title) }}
            </router-link>
            <router-link
              v-else
              :to="{ name: 'project/details', params: { id: item.id } }"
              :class="{'text-muted': isFinishedProject(item)}">
              {{ item.title }}
            </router-link>
          </template>
          <template #date="{ item }">
            <font-awesome-icon
                  v-if="showDeadlineAlert(item)"
                  icon="exclamation-triangle"
                  class="text-danger"
                  v-b-tooltip.hover.bottom="$t('project', 'Меньше 10 дней до дедлайна')"></font-awesome-icon>
            {{ timeToHuman(item.date) }}
          </template>
          <template #author="{ item }">
            <template v-if="item.author.company">
              <span>{{ item.author.company }}</span>
              <br>
            </template>
            <router-link
              v-if="item.author"
              :to="{ name: 'user/details', params: { id: item.author.id } }"
              v-b-tooltip
              :title="item.author.last_name + ' ' + item.author.first_name + ' ' + item.author.middle_name"
            >
              {{ item.author.last_name }}
            </router-link>
          </template>
          <template #manager="{ item }">
            <router-link
              v-if="item.author && item.author.manager"
              :to="{ name: 'user/details', params: { id: item.author.manager.id } }"
              v-b-tooltip
              :title="item.author.manager.last_name + ' ' + item.author.manager.first_name + ' ' + item.author.manager.middle_name"
            >
              {{ item.author.manager.last_name }}
            </router-link>
          </template>
          <template #designer="{ item }">
            <router-link
              v-if="item.designer"
              :to="{ name: 'user/details', params: { id: item.designer.id } }"
              v-b-tooltip
              :title="item.designer.last_name + ' ' + item.designer.first_name + ' ' + item.designer.middle_name"
            >
              {{ item.designer.last_name }}
            </router-link>
          </template>
        </data-grid>
      </div>
    </div>
  </div>
</template>

<script>
import { DataGrid, FormInput, FormSelect, FormDatePicker } from '@/components'
import { DateHelper, StringHelper } from '@/helpers'
import { FilterMixin } from '@/mixins'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import ProjectsMap from './index/ProjectsMap'
import Faq from './index/Faq'

const TITLE_MAX_WORDS = 5

export default {
  name: 'index',
  mixins: [
    FilterMixin
  ],
  components: {
    DataGrid, FormInput, FormDatePicker, FontAwesomeIcon, ProjectsMap, FormSelect, Faq
  },
  data () {
    return {
      showMap: false,
      showFaqInfo: false,
      loadedProjects: null,
      filter: {
        model: {
          id: null,
          title: null,
          date: null,
          client: null,
          status: null,
          author: null,
          manager: null,
          designer: null,
          created_at: null,
          updated_at: null,
          company: null,
          gear: null,
          provider: null
        },
        conditions: {
          title: this.$const.CONDITIONS.LIKE,
          date: [this.$const.CONDITIONS.GREATER_THAN_EQUAL, this.$const.CONDITIONS.LESS_THAN_EQUAL],
          client: this.$const.CONDITIONS.LIKE,
          created_at: [this.$const.CONDITIONS.GREATER_THAN_EQUAL, this.$const.CONDITIONS.LESS_THAN_EQUAL],
          updated_at: [this.$const.CONDITIONS.GREATER_THAN_EQUAL, this.$const.CONDITIONS.LESS_THAN_EQUAL],
          company: this.$const.CONDITIONS.LIKE
        }
      },

      grid: {
        fields: {
          id: {
            sortable: true,
            label: 'ID'
          },
          title: {
            sortable: true,
            label: 'Название'
          },
          date: {
            sortable: true,
            label: 'Срок сдачи',
            class: 'd-none d-lg-table-cell'
          },
          author: {
            sortable: true,
            label: 'Автор',
            class: 'd-none d-md-table-cell'
          },
          manager: {
            sortable: true,
            label: 'Менеджер',
            class: 'd-none d-xl-table-cell'
          },
          designer: {
            sortable: true,
            label: 'Проектировщик',
            class: 'd-none d-xl-table-cell'
          },
          client: {
            sortable: true,
            label: 'Заказчик',
            class: 'd-none d-xxl-table-cell'
          },
          status: {
            sortable: true,
            label: 'Статус',
            class: 'd-none d-md-table-cell'
          },
          created_at: {
            sortable: true,
            label: 'Дата создания',
            class: 'd-none d-xl-table-cell'
          },
          updated_at: {
            sortable: true,
            label: 'Дата обновления',
            class: 'd-none d-xxl-table-cell'
          }
        },
        slots: ['date', 'title', 'status', 'created_at', 'updated_at', 'author', 'manager', 'designer']
      },

      customersList: [],
      managersList: [],
      designersList: [],
      gearsList: [],
      faqInfo: []
    }
  },
  created () {
    this.getUsersLists()
    this.getGearsList()
    this.getFaqInfo()
  },
  computed: {
    projectStatuses () {
      const result = {}
      Object.entries(this.$params.project.statuses).forEach(([key, value]) => {
        result[key] = this.$t('project', value)
      })
      return result
    },
    gridFields () {
      const result = []
      Object.entries(this.grid.fields).forEach(([key, value]) => {
        value.label = this.$t('project', value.label)
        result[key] = value
      })
      return result
    },
    providerList () {
      const result = {}
      Object.entries(this.$params.user.providers).forEach(([key, value]) => {
        result[key] = this.$t('project', value)
      })
      return result
    }
  },
  methods: {
    timeToHuman (time) {
      return DateHelper.toHumanStr(time)
    },
    showDeadlineAlert (project) {
      if (this.isFinishedProject(project)) {
        return false
      }
      const diff = project.date - Math.floor(Date.now() / 1000)
      return diff > 0 && diff < 60 * 60 * 24 * 10
    },
    async getUsersLists () {
      try {
        const response = await this.$http.get('admin/users/list', {
          params: {
            role: ['manager', 'customer', 'designer']
          }
        })
        if (response.data) {
          this.managersList = response.data.manager
          this.customersList = response.data.customer
          this.designersList = response.data.designer
        }
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось получить список пользователей.') + ' ' + e.message)
      }
    },
    async getGearsList () {
      try {
        const response = await this.$http.get('admin/gears/list')
        if (response.data) {
          this.gearsList = response.data
        }
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось получить список оборудования.') + ' ' + e.message)
      }
    },
    async getGearProducersList () {
      try {
        const response = await this.$http.get('admin/gears/producer-list')
        if (response.data) {
          this.gearProducersList = response.data
        }
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось получить список производителей оборудования.') + ' ' + e.message)
      }
    },
    async getFaqInfo () {
      try {
        const response = await this.$http.get('admin/projects/faq-info/2')
        if (response.data) {
          this.faqInfo = response.data
        }
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось получить FAQ информацию.') + ' ' + e.message)
      }
    },
    shouldBeTruncated (str, maxWords = TITLE_MAX_WORDS) {
      return str.split(' ').length > maxWords
    },
    truncate (str, words = TITLE_MAX_WORDS) {
      return StringHelper.truncateWords(str, words)
    },
    isFinishedProject (project) {
      return ['rejected', 'canceled', 'finished'].includes(project.status)
    }
  }
}
</script>
