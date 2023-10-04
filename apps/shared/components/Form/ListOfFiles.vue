<template>
  <div v-if="normalizedFiles">
    <!-- <a download :href="getDownloadService(normalizedFiles[0])" v-if="normalizedFiles.length === 1">{{ normalizedFiles[0] }}</a> -->
    <table class="table table-striped table-borderless">
      <tbody class="w-100">
        <tr v-for="(file, key) in normalizedFiles" :key="key">
          <td>
            {{ getFileName(file) }}
          </td>
          <td class="text-right">
            <a v-if="!hideDownload" download :href="getDownloadService(file)" class="btn btn-sm btn-primary">
              <font-awesome-icon icon="download" />
            </a>
            <b-button v-if="!hideDelete" @click="deleteFile(file)" size="sm" variant="danger" class="ml-2">
              <font-awesome-icon icon="trash-alt" />
            </b-button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
export default {
  name: 'file-list',
  components: {
    FontAwesomeIcon
  },
  props: {
    files: [String, Array],
    downloadService: [String, Function],
    deleteService: [String, Function],
    hideDownload: {
      type: Boolean,
      default: false
    },
    hideDelete: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    getDownloadService (file) {
      return (typeof this.downloadService === 'function') ? this.downloadService(file) : this.downloadService
    },
    getDeleteService (file) {
      return (typeof this.deleteService === 'function') ? this.deleteService(file) : this.deleteService
    },
    async deleteFile (file) {
      if (!this.deleteService) {
        this.$emit('delete-file', file)
        return
      }
      try {
        const response = await this.$http.delete(this.getDeleteService(file))
        if (response.status === 204) {
          this.$emit('file-deleted', { file, response: response.data })
          this.$notifySuccess('Файл ' + file.filename + ' успешно удален.')
        }
      } catch (e) {
        this.$notifyError('Не удалось удалить файл ' + file.filename + '. ' + e.message)
      }
    },
    getFileName (file) {
      return (typeof file === 'string') ? file : file.filename
    }
  },
  computed: {
    normalizedFiles () {
      return typeof this.files === 'string' ? [ this.files ] : this.files
    }
  }
}
</script>
