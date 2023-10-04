<template>
  <div class="animated fadeIn">
    <div class="row import-file-prompt">
      <div class="col-lg-12">
        If you have a file with a ready specification, use the Specification Import Assistant. This will help to process your project request faster.
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2 assistant-file-import">
        <file-import-button
            title="Завантажити файл"
            fileTypeList="xls, xlsx, csv, txt"
            label="Specification import assistant"
            :current-files="importedSpecifications"
            :download-service="downloadService"
            :delete-service="deleteService"
            @click="showFileImportForm = true"
            @file-deleted="onDeleted('specifications', $event)"></file-import-button>
        <!-- file import modal -->
        <b-modal
            :title="$t('project', 'Specification import assistant')"
            class="modal-primary"
            id="modal-assistant-file-import"
            size="xl"
            scrollable
            v-model="showFileImportForm">
          <file-import-form
              :service="'admin/projects/' + model.id + '/files?type=specification&status=4'"
              :importService="'admin/projects/' + model.id + '/file-tune'"
              name="specifications"
              label="Специфікація"
              multiple
              :current-files="importedSpecifications"
              :download-service="downloadService"
              :delete-service="deleteService"
              :specificationImport="specificationImport"
              @change="onChangeImportSpecification"
              @import="specificationImport = false"
              @file-deleted="onDeleted('specifications', $event)"></file-import-form>

          <template #modal-footer="{ importBtn }">
            <b-button size="sm" variant="primary" @click="importSpecification">
              Імпортувати
            </b-button>
          </template>
        </b-modal>
      </div>
      <div class="col-lg-5">
        <files-form
              :service="'admin/projects/' + model.id + '/files?type=technical_task'"
              name="technical_task"
              :label="$t('project', 'Техническое задание')"
              :filesMaxNumber="1"
              multiple
              :current-files="model.technical_task ? [model.technical_task] : []"
              :download-service="downloadService"
              :delete-service="deleteService"
              @change="$emit('updated', { attribute: 'technical_task', value: $event })"
              @file-deleted="onDeleted('technical_task', $event)"></files-form>
      </div>
      <div class="col-lg-5">
        <files-form
              :service="'admin/projects/' + model.id + '/files?type=general'"
              name="other_files"
              :label="$t('project', 'Другие файлы')"
              multiple
              :current-files="model.other_files"
              :download-service="downloadService"
              :delete-service="deleteService"
              @change="$emit('updated', { attribute: 'other_files', value: $event })"
              @file-deleted="onDeleted('other_files', $event)"></files-form>
      </div>
    </div>
  </div>
</template>

<script>
import FilesForm from './files/FilesForm'
import { FileImportButton, FileImportForm } from '@/components'

export default {
  name: 'project-files',
  components: {
    FilesForm,
    FileImportButton,
    FileImportForm
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      downloadService: (file) => '/api/admin/projects/' + this.model.id + '/files/' + file.id,
      deleteService: (file) => '/admin/projects/' + this.model.id + '/files/' + file.id,
      showFileImportForm: false,
      specificationImport: false,
      importedSpecifications: []
    }
  },
  created () {
    this.getImportedSpecifications()
  },
  methods: {
    onDeleted (type, e) {
      switch (type) {
        case 'technical_task':
          this.$emit('updated', {
            attribute: 'technical_task',
            value: null
          })
          break
        case 'other_files':
          const files = this.model.other_files
          const i = files.indexOf(e.file)
          if (i > -1) {
            files.splice(i, 1)
          }
          this.$emit('updated', {
            attribute: 'other_files',
            value: files
          })
          break
        case 'specifications':
          const specifications = this.model.specifications
          const j = specifications.indexOf(e.file)
          if (j > -1) {
            specifications.splice(j, 1)
          }
          this.$emit('updated', {
            attribute: 'specifications',
            value: specifications
          })
          break
      }
      this.getImportedSpecifications()
    },
    onChangeImportSpecification (e) {
      this.getImportedSpecifications()
      this.$emit('updated', e)
      this.showFileImportForm = false
    },
    importSpecification () {
      this.specificationImport = true
    },
    async getImportedSpecifications () {
      try {
        const response = await this.$store.state.http.client.get('admin/projects/' + this.model.id + '/imported-files')
        if (response.data && response.data.result === 'success') {
          this.importedSpecifications = response.data.files
        }
      } catch (e) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: this.$t('project', 'Не удалось загрузить список импортированных спецификаций.') + ' ' + e.message,
          duration: -1
        })
      }
    }
  }
}
</script>
<style lang="scss">
  #modal-assistant-file-import > .modal-dialog {
    max-width: 89%;
  }
  .import-file-prompt {
    margin-bottom: 2em;
  }
  .assistant-file-import table td.text-right {
    width: 6.7em;
  }
</style>
