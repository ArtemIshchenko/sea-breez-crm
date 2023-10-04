<template>
  <div class="animated fadeIn">
    <data-grid
      :fields="grid.fields"
      :data-slots="grid.slots"
      no-actions
      :service="'admin/users/' + model.id + '/history'">
      <template slot="created_at" slot-scope="props">{{ timeToHuman(props.item.created_at) }}</template>
      <template slot="author" slot-scope="props">
        <router-link v-if="props.item.author" :to="{ name: 'user/details', params: { id: props.item.author.id } }">
          {{ props.item.author.first_name + ' ' + props.item.author.last_name }}
        </router-link>
      </template>
      <template slot="action" slot-scope="props">
        {{ props.item.action }}
      </template>
      <template slot="additional" slot-scope="props">
        <multi-line :text="props.item.additional"></multi-line>
      </template>

    </data-grid>
  </div>
</template>

<script>
import { DataGrid, MultiLine } from '@/components'
import { DateHelper } from '@/helpers'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'user-history',
  components: {
    DataGrid, MultiLine, FontAwesomeIcon
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      badge_variant: {
        editing: 'warning',
        deleting: 'danger',
        adding: 'success'
      },
      grid: {
        fields: {
          created_at: {
            label: this.$t('project', 'Дата и время')
          },
          author: {
            label: this.$t('project', 'Автор')
          },
          action: {
            label: this.$t('project', 'Действие')
          },
          additional: {
            label: this.$t('project', 'Дополнительно')
          }
        },
        slots: ['created_at', 'author', 'additional', 'action']
      }
    }
  },
  methods: {
    timeToHuman (time) {
      return DateHelper.toHumanStr(time, true)
    },
    shouldDownload (meta) {
      const parsedMeta = JSON.parse(meta)
      return parsedMeta && parsedMeta.fileId
    },
    generateDownloadLink (meta) {
      const parsedMeta = JSON.parse(meta)
      return '/api/admin/projects/' + this.model.id + '/files/' + parsedMeta.fileId
    }
  }
}
</script>
