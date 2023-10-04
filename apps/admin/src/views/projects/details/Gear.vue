<template>
  <div class="animated fadeIn">
    <!-- Buttons block -->
    <div class="d-flex justify-content-between flex-wrap mb-4">
      <b-button class="mt-1" variant="primary" @click="showAddGearForm = true">{{ $t('project', 'Добавить оборудование') }}</b-button>
    </div>
    <!-- Add gear modal -->
    <b-modal
          :title="$t('gear', 'Добавить оборудование к проекту')"
          class="modal-primary"
          hide-footer
          v-model="showAddGearForm">
      <add-gear-form :model="model" :gear-list="availableGearList" @gear-added="afterGearAdded"></add-gear-form>
    </b-modal>

    <data-grid
          :fields="grid.fields"
          :data-slots="grid.slots"
          :service="'admin/projects/' + model.id + '/gears'"
          :key="gridKey"
          @loaded="afterGearsLoaded">
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

      <template #actions="{ item }">
        <delete-button no-label @click="deleteGear(item.id)"></delete-button>
      </template>
    </data-grid>
  </div>
</template>

<script>
import { DataGrid, DeleteButton } from '@/components'
import AddGearForm from './gear/AddGearForm'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'project-gear',
  components: {
    DataGrid, AddGearForm, DeleteButton, FontAwesomeIcon
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      grid: {
        fields: {
          title: {
            label: this.$t('project', 'Модель')
          },
          producer: {
            label: this.$t('project', 'Производитель')
          }
        },
        slots: ['title']
      },

      showAddGearForm: false,
      gears: [],
      allGearList: {},
      gridKey: 0
    }
  },
  created () {
    this.getAllGearList()
  },
  computed: {
    availableGearList () {
      return this.gears.reduce((acc, val) => {
        const key = val.id.toString()
        if (acc[key]) {
          delete acc[key]
        }
        return acc
      }, { ...this.allGearList })
    }
  },
  methods: {
    async getAllGearList () {
      try {
        const response = await this.$http.get('admin/gears/list')
        this.allGearList = response.data
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось загрузить детали проекта. ') + e.message)
      }
    },
    afterGearsLoaded (gears) {
      this.gears = gears
    },
    afterGearAdded (gear) {
      this.gridKey++
      this.showAddGearForm = false
    },
    async deleteGear (id) {
      try {
        await this.$http.delete('admin/projects/' + this.model.id + '/gears/' + id)
        this.gridKey++
        this.$notifyWarning(this.$t('gear', 'Оборудование успешно удалено.'))
      } catch (e) {
        this.$notifyError(this.$t('gear', 'Не удалось удалить оборудование.') + e.message)
      }
    }
  }
}
</script>
