<template>
  <form @submit="onSubmit($event)">

    <form-dropzone
          v-if="drop && dragAndDropCapable"
          key="dropinput"
          :options="dropzoneOptions"
          :name="name"
          :label="label"
          :multiple="multiple"
          :hint="hint"
          :value="currentFiles"
          :isValid="form.saved.files"
          :error-message="form.errors.files"
          :download-service="downloadService"
          :delete-service="deleteService"
          @input="onChange($event)"
          @file-deleted="onDeleted($event)"></form-dropzone>

    <form-file-input
          v-else
          key="fileinput"
          :name="name"
          :label="label"
          :multiple="multiple"
          :hint="hint"
          :value="currentFiles"
          :isValid="form.saved.files"
          :error-message="form.errors.files"
          :download-service="downloadService"
          :delete-service="deleteService"
          @input="onChange($event)"></form-file-input>
  </form>
</template>

<script>
import { FormAgileMixin } from '@/mixins'
import { fileType, fileSize, fileExtension, filesNumber } from '@/validators'
import FormFileInput from './FileInput'
import FormDropzone from './Dropzone'

export default {
  name: 'files-upload-form',
  components: {
    FormFileInput, FormDropzone
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
    drop: {
      type: Boolean,
      default: true
    },
    dropzoneOptions: Object,
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
  created () {
    if (this.drop) {
      this.setDragAndDropCapable()
    }
  },
  methods: {
    onChange (files) {
      this.resetValidation()

      // ensure model's not empty
      if (!files) return

      // update model
      this.form.model.files = files

      // validate image
      if (!this.validateAttribute('files')) return

      // prepare form data
      let data = new FormData()
      if (!this.multiple) {
        data.append(this.name, this.form.model.files[0])
      } else {
        for (let file of this.form.model.files) {
          data.append(this.name + '[]', file)
        }
      }

      // submit data
      this.$store.state.http.client.post(this.service, data).then(response => {
        this.$emit('change', response.data[this.name])
        this.form.saved.files = true
        setTimeout(() => {
          this.form.saved.files = false
        }, 3000)
      }).catch(e => {
        if (e.response && e.response.status === 422 && Array.isArray(e.response.data) && e.response.data[0].field === this.name) {
          this.addError('files', e.response.data[0].message)
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'Data was not saved.<br>' + e.message,
            duration: -1
          })
        }
      })
    },
    setDragAndDropCapable () {
      const div = document.createElement('div')
      this.dragAndDropCapable = ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)
    },
    onDeleted ({ file, response }) {
      this.$emit('change', response[this.name])
    }
  }
}
</script>
