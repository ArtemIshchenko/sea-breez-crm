<template>
  <form @submit="onSubmit($event)">

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
</template>

<script>
import { FormAgileMixin } from '@/mixins'
import { fileType, fileSize, fileExtension, filesNumber } from '@/validators'
import { FormDropzone } from '@/components'

export default {
  name: 'files-form',
  components: {
    FormDropzone
  },
  mixins: [FormAgileMixin],
  props: {
    service: {
      type: String,
      required: true
    },
    downloadService: [String, Function],
    deleteService: [String, Function],
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
    currentFiles: [String, Array],
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
      dragAndDropCapable: false
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
        this.$emit('change', response.data[this.name])
        this.form.saved.files = true
        setTimeout(() => {
          this.form.saved.files = false
        }, 3000)
      } catch (e) {
        if (e.response && e.response.status === 422 && Array.isArray(e.response.data)) {
          this.addError('files', e.response.data[0].message)
        } else {
          this.$notifyError(this.$t('project', 'Файлы не были сохранены.') + e.message)
        }
      }
    }
  }
}
</script>
