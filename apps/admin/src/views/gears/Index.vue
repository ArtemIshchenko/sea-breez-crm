<template>
  <div class="animated fadeIn">
    <div class="card">
      <div class="card-body">
        <!-- Buttons block -->
        <div class="d-flex justify-content-between flex-wrap mb-4">
          <b-button class="mt-1" variant="primary" @click="form.show = true">{{ $t('project', 'Добавить оборудование') }}</b-button>

          <b-button class="mt-1" v-if="!filter.isOpen" variant="outline-primary" @click="toggleFilter" key="openF">{{ $t('project', 'Открыть фильтр') }}</b-button>
          <b-button class="mt-1" v-else variant="outline-primary" @click="toggleFilter" key="closeF">{{ $t('project', 'Закрыть фильтр') }}</b-button>
        </div>

        <!-- Filter -->
        <div class="index-filter" v-if="filter.isOpen">
          <div class="row my-3">
            <div class="col-6 col-md-4 col-xl-3">
              <form-input
                    name="id"
                    :label="$t('project', 'ID оборудования')"
                    :value="filter.model.id"
                    @change="filterChanged('id', $event)"></form-input>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-input
                    name="title"
                    :label="$t('project', 'Модель')"
                    :value="filter.model.title"
                    @change="filterChanged('title', $event)"></form-input>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
              <form-select
                    name="producer"
                    :label="$t('project', 'Производитель')"
                    :value="filter.model.producer"
                    :options="producers"
                    @change="filterChanged('producer', $event)"></form-select>
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
          </div>
        </div>

        <!-- Items grid -->
        <data-grid
              :fields="grid.fields"
              :data-slots="grid.slots"
              service="admin/gears"
              no-actions
              :filter="filter">
          <template #created_at="{ item }">{{ timeToHuman(item.created_at) }}</template>
          <template #updated_at="{ item }">{{ timeToHuman(item.updated_at) }}</template>
          <template #title="{ item }">
            <router-link
              :to="{ name: 'gear/details', params: { id: item.id } }">
              {{ item.title }}
            </router-link>
            <font-awesome-icon
                  v-if="item.description"
                  icon="question-circle"
                  class="text-primary ml-1"
                  v-b-popover.hover="item.description"></font-awesome-icon>
          </template>

          <template #producer="{ item }">
            <span
              v-if="shouldBeTruncated(item.producer)"
              v-b-tooltip
              :title="item.producer">
              {{ truncate(item.producer) }}
            </span>
            <span v-else>
              {{ item.producer }}
            </span>
          </template>

          <template #projectsNumber="{ item }">
            <router-link :to="{ name: 'projects', query: { filter: { gear: item.id } } }">
              {{ item.projectsNumber }}
            </router-link>
          </template>
        </data-grid>
      </div>
    </div>

    <!-- Form modal -->
    <b-modal
          :title="$t('gear', 'Добавить новое оборудование')"
          class="modal-primary"
          v-model="form.show"
          hide-footer
          @shown="$event.target.querySelector('input').focus()">
      <form @submit="onSubmit">
        <form-input
              name="title"
              :label="$t('project', 'Модель оборудования')"
              :hint="$t('project', 'Вы сможете изменить модель позже.')"
              :error-message="form.errors.title"
              @change="onChange('title', $event)"
              @input="onInput('title', $event)"></form-input>

        <button type="submit" class="btn btn-primary">{{ $t('service', 'Сохранить') }}</button>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { DataGrid, FormInput, FormSelect, FormDatePicker } from '@/components'
import { DateHelper, StringHelper } from '@/helpers'
import { FilterMixin, FormMixin } from '@/mixins'
import { required } from '@/validators'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const TITLE_MAX_WORDS = 5

export default {
  name: 'index',
  mixins: [
    FilterMixin, FormMixin
  ],
  components: {
    DataGrid, FormInput, FormDatePicker, FormSelect, FontAwesomeIcon
  },
  data () {
    return {
      form: {
        model: {
          title: null
        },
        service: 'admin/gears',
        afterSave: (data) => {
          this.$router.push({ name: 'gear/edit', params: { id: data.id } })
        },
        show: false
      },

      filter: {
        model: {
          id: null,
          title: null,
          producers: null,
          created_at: null,
          updated_at: null
        },
        conditions: {
          title: this.$const.CONDITIONS.LIKE,
          created_at: [this.$const.CONDITIONS.GREATER_THAN_EQUAL, this.$const.CONDITIONS.LESS_THAN_EQUAL],
          updated_at: [this.$const.CONDITIONS.GREATER_THAN_EQUAL, this.$const.CONDITIONS.LESS_THAN_EQUAL]
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
            label: this.$t('project', 'Модель')
          },
          producer: {
            sortable: true,
            label: this.$t('project', 'Производитель'),
            class: 'd-none d-md-table-cell'
          },
          projectsNumber: {
            sortable: false,
            label: this.$t('project', 'Проекты'),
            class: 'd-none d-md-table-cell'
          },
          created_at: {
            sortable: true,
            label: this.$t('project', 'Дата создания'),
            class: 'd-none d-xl-table-cell'
          },
          updated_at: {
            sortable: true,
            label: this.$t('project', 'Дата обновления'),
            class: 'd-none d-xxl-table-cell'
          }
        },
        slots: ['title', 'producer', 'created_at', 'updated_at', 'projectsNumber']
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          title: {
            required
          }
        }
      }
    }
  },
  computed: {
    producers () {
      return this.$store.state.gear.list
    }
  },
  methods: {
    timeToHuman (time) {
      return DateHelper.toHumanStr(time)
    },
    shouldBeTruncated (str, maxWords = TITLE_MAX_WORDS) {
      return (str || '').split(' ').length > maxWords
    },
    truncate (str, words = TITLE_MAX_WORDS) {
      return StringHelper.truncateWords((str || ''), words)
    }
  }
}
</script>
