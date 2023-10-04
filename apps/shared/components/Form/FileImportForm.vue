<template>
  <div class="file-import-modal animated fadeIn">
    <div class="row" v-if="grid.items.length > 0">
      <div class="col-lg-12 file-import-modal-first-row">
        <div>
          <span>
            <strong>1.</strong>
          </span>
          <b-form-checkbox
              v-model="ignoreFirstRow"
              switch
              size="lg">
            <span>Ігнорувати перший рядок</span>
          </b-form-checkbox>
        </div>
        <br>
        <div>
          <span>
            <strong>2. </strong>
          </span>
          Задайте відповідність полів.<br><span class="prompt-requirement-fields">*</span> Потрібно обов'язково вказати стовбчики, в яких знаходяться модель і кількість обладнання.
        </div>
        <br>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-12">
        <!-- Items grid -->
        <div v-if="grid.items.length > 0">
          <data-grid-file-import
            :items="grid.items"
            :fields="grid.fields"
            :selectFields="selectFields"
            :ignoreFirstRow="ignoreFirstRow"
            :selectValues="selectValues"
            @update="setTuneParams">
          </data-grid-file-import>
        </div>
        <form @submit="onSubmit($event)"
              v-else>
          <form-dropzone
              :name="name"
              :label="label"
              :multiple="multiple"
              :value="currentFiles"
              :isValid="form.saved.files"
              :error-message="form.errors.files"
              :download-service="downloadService"
              :delete-service="deleteService"
              @input="onChange($event)"
              @file-deleted="$emit('file-deleted', $event)"></form-dropzone>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { FormAgileMixin } from '@/mixins'
import { fileType, fileSize, fileExtension, filesNumber } from '@/validators'
import FormDropzone from './Dropzone'
import { DataGridFileImport } from '../../components'

export default {
  name: 'file-import-form',
  components: {
    FormDropzone,
    DataGridFileImport
  },
  mixins: [FormAgileMixin],
  props: {
    service: {
      type: String,
      required: true
    },
    importService: {
      type: String,
      required: true
    },
    downloadService: [String, Function],
    deleteService: [String, Function],
    currentFiles: [String, Array],
    specificationImport: {
      type: Boolean,
      default: false
    },
    name: {
      type: String,
      required: true
    },
    label: String,
    hint: String,
    multiple: {
      type: Boolean,
      default: false
    },
    fileType: [String, Array],
    fileSize: Number,
    fileExtension: [String, Array],
    filesMaxNumber: Number
  },
  data () {
    return {
      form: {
        model: {
          files: null
        }
      },
      dragAndDropCapable: false,
      grid: {
        fields: [],
        items: [],
        slots: [],
        colNums: {}
      },
      jobId: '',
      sheetNum: 0,
      fileId: 0,
      selectFields: [
        { value: 0, text: 'Пропустити' },
        { value: 1, text: 'Модель*' },
        { value: 2, text: 'Бренд' },
        { value: 3, text: 'Кількість*' }
      ],
      tuneParams: {},
      selectValues: {},
      ignoreFirstRow: true
    }
  },
  validations () {
    let form = {
      model: {
        files: {
          fileSize: fileSize(this.fileSize)
        }
      }
    }
    if (this.fileType) {
      form.model.files.fileType = fileType(this.fileType)
    }
    if (this.fileExtension) {
      form.model.files.fileExtension = fileExtension(this.fileExtension)
    }
    if (this.filesNumber) {
      form.model.files.filesNumber = filesNumber(this.filesMaxNumber)
    }
    return {
      form
    }
  },
  methods: {
    async onChange (files) {
      this.resetValidation()

      // ensure model's not empty
      if (!files) return

      // update model
      this.form.model.files = files

      // validate files
      if (!this.validateAttribute('files')) return

      // prepare form data
      let data = new FormData()
      for (let file of this.form.model.files) {
        data.append('files[]', file)
      }

      // submit data
      try {
        const response = await this.$store.state.http.client.post(this.service, data)
        this.form.saved.files = true
        setTimeout(() => {
          this.form.saved.files = false
        }, 3000)
        if (response.data.sheets) {
          const selectValuesArray = []
          response.data.sheets[0].rows.forEach((el, index) => {
            if (index === 0) {
              el.forEach((el1) => {
                this.grid.fields.push({
                  key: 'field_' + el1.col_num,
                  sortable: false,
                  label: el1.origin_cell_value
                })
                this.grid.colNums['field_' + el1.col_num] = el1.col_num
                selectValuesArray.push(['field_' + el1.col_num, 0])
              })
            } else {
              if (this.grid.fields.length > 0) {
                let field
                const row = {}
                el.forEach((el1) => {
                  field = 'field_' + el1.col_num
                  row[field] = el1.origin_cell_value
                })
                this.grid.items.push(row)
              }
            }
          })
          this.selectValues = Object.fromEntries(new Map(selectValuesArray))
          this.jobId = response.data.job_id ? response.data.job_id : ''
          this.fileId = response.data.file_id ? response.data.file_id : 0
          this.tuneParams['sheet_num'] = response.data.sheet_num ? response.data.sheet_num : 0
        } else {
          throw new Error('Parameter sheets is empty')
        }
      } catch (e) {
        if (e.response && e.response.status === 422 && Array.isArray(e.response.data)) {
          this.addError('files', e.response.data[0].message)
        } else {
          this.addError('files', this.$t('project', 'Файлы не были сохранены.'))
        }
        this.$notifyError(this.$t('project', 'Файлы не были сохранены.') + e.message)
      }
    },
    getField (name) {
      return `${name}`
    },
    async requestImportSpecification () {
      try {
        const response = await this.$http.get(this.importService, {
          params: {
            file_id: this.fileId,
            job_id: this.jobId,
            tune_params: this.tuneParams
          }
        })
        if (response.data && response.data.sheets) {
          this.$notifySuccess(this.$t('project', 'Специфікація імпортована успішно.'))
          this.$emit('change')
        } else {
          throw new Error('Parameter sheets is empty')
        }
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не вдалось виконати імпорт специфікаціі.') + ' ' + e.message)
      }
    },
    importSpecification () {
      if (!this.fileId) {
        this.$notifyWarning(this.$t('project', 'Ви не вибрали файл'))
        return
      }
      if (!('column_with_model' in this.tuneParams) && !('column_with_quantity' in this.tuneParams)) {
        this.$notifyWarning(this.$t('project', 'Ви не вибрали обов\'язкових полів "Модель" та "Кількість"'))
        return
      } else if (!('column_with_model' in this.tuneParams)) {
        this.$notifyWarning(this.$t('project', 'Ви не вибрали обов\'язкового поля "Модель"'))
        return
      } else if (!('column_with_quantity' in this.tuneParams)) {
        this.$notifyWarning(this.$t('project', 'Ви не вибрали обов\'язкового поля "Кількість"'))
        return
      }

      if (this.ignoreFirstRow) {
        this.tuneParams['row_with_header'] = 1
      } else {
        if ('row_with_header' in this.tuneParams) {
          delete this.tuneParams['row_with_header']
        }
      }
      this.requestImportSpecification()
    },
    setTuneParams (event) {
      if (event.field in this.grid.colNums) {
        let colNumsItem
        switch (event.value) {
          case 0:
            if (('column_with_model' in this.tuneParams) && (this.tuneParams['column_with_model'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_model']
            }
            if (('column_with_brand' in this.tuneParams) && (this.tuneParams['column_with_brand'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_brand']
            }
            if (('column_with_quantity' in this.tuneParams) && (this.tuneParams['column_with_quantity'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_quantity']
            }
            break
          case 1:
            if ('column_with_model' in this.tuneParams) {
              colNumsItem = Object.entries(this.grid.colNums).find(item => item[1] === this.tuneParams['column_with_model'])
            }
            this.tuneParams['column_with_model'] = this.grid.colNums[event.field]
            if (('column_with_brand' in this.tuneParams) && (this.tuneParams['column_with_brand'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_brand']
            }
            if (('column_with_quantity' in this.tuneParams) && (this.tuneParams['column_with_quantity'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_quantity']
            }
            break
          case 2:
            if ('column_with_brand' in this.tuneParams) {
              colNumsItem = Object.entries(this.grid.colNums).find(item => item[1] === this.tuneParams['column_with_brand'])
            }
            this.tuneParams['column_with_brand'] = this.grid.colNums[event.field]
            if (('column_with_model' in this.tuneParams) && (this.tuneParams['column_with_model'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_model']
            }
            if (('column_with_quantity' in this.tuneParams) && (this.tuneParams['column_with_quantity'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_quantity']
            }
            break
          case 3:
            if ('column_with_quantity' in this.tuneParams) {
              colNumsItem = Object.entries(this.grid.colNums).find(item => item[1] === this.tuneParams['column_with_quantity'])
            }
            this.tuneParams['column_with_quantity'] = this.grid.colNums[event.field]
            if (('column_with_model' in this.tuneParams) && (this.tuneParams['column_with_model'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_model']
            }
            if (('column_with_brand' in this.tuneParams) && (this.tuneParams['column_with_brand'] === this.grid.colNums[event.field])) {
              delete this.tuneParams['column_with_brand']
            }
            break
        }
        if (colNumsItem && colNumsItem[0]) {
          this.selectValues[colNumsItem[0]] = 0
        }
        this.selectValues[event.field] = event.value
      }
    }
  },
  watch: {
    specificationImport: function (specificationImport) {
      if (specificationImport) {
        this.$emit('import')
        this.importSpecification()
      }
    }
  }
}
</script>
<style>
  .file-import-modal .custom-switch {
    display: inline-block;
  }
  .file-import-modal .file-import-modal-first-row {
    line-height: 3em;
  }
  .file-import-modal .file-import-modal-first-row > div {
    line-height: 1.5em;
  }
  .file-import-modal .file-import-modal-first-row > div > span {
    vertical-align: bottom;
  }
  .file-import-modal .file-import-modal-first-row .prompt-requirement-fields {
    color: red;
  }
  .file-import-modal .custom-switch.b-custom-control-lg .custom-control-label {
    font-size: 1em;
  }
  .file-import-modal .custom-switch.b-custom-control-lg .custom-control-label:before {
    top: 0;
    left: -2.0rem;
  }
  .file-import-modal .custom-switch.b-custom-control-lg .custom-control-label:after {
    top: 2px;
    left: calc(-2.0rem + 2px);
  }
  .file-import-modal .custom-switch.b-custom-control-lg .custom-control-label span {
    left: 1rem;
    position: relative;
  }
</style>
