<template>

  <b-form-group
        :label="inputLabel"
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage"
        :description="hint">

    <list-of-files
          :files="defaultFiles"
          :download-service="downloadService"
          :delete-service="deleteService"
          :hide-download="hideDownload"
          :hide-delete="hideDelete"
          class="mb-2"
          @file-deleted="$emit('file-deleted', $event)"></list-of-files>

    <div class="dropzone" ref="dropzone">
      <span class="text-muted">{{ internalDropLabel }}</span>
      <input type="file" ref="fileInput" :multiple="multiple" />
    </div>

  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'
import ListOfFiles from './ListOfFiles'

export default {
  name: 'dropzone',
  mixins: [FormFieldMixin],
  components: {
    ListOfFiles
  },
  props: {
    value: [String, Array],
    multiple: {
      type: Boolean,
      default: false
    },
    downloadService: [String, Function],
    deleteService: [String, Function],
    hideDownload: [Boolean],
    hideDelete: [Boolean],
    dropLabel: String
  },
  computed: {
    defaultFiles () {
      return this.value
    },
    internalDropLabel () {
      return this.dropLabel ||
        (this.multiple
          ? this.$t('service', 'Перетащите сюда файлы или кликните для выбора')
          : this.$t('service', 'Перетащите сюда файл или кликните для выбора'))
    }
  },
  mounted () {
    this.initDropzone()
  },
  methods: {
    initDropzone () {
      const dropzone = this.$refs.dropzone
      const fileInput = this.$refs.fileInput;
      // stop default drag events handling
      ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
          e.preventDefault()
          e.stopPropagation()
        })
      })

      // set drop event listener
      dropzone.addEventListener('drop', function (e) {
        const files = []
        for (let i = 0; i < e.dataTransfer.files.length; i++) {
          files.push(e.dataTransfer.files[i])
        }
        this.$emit('input', files)
        dropzone.classList.remove('dragging')
      }.bind(this))

      // set click event
      dropzone.addEventListener('click', function (e) {
        fileInput.click()
      })
      fileInput.addEventListener('change', function (e) {
        this.$emit('input', e.target.files)
      }.bind(this))

      // add and remove `dragging` class
      dropzone.addEventListener('dragenter', function (e) {
        dropzone.classList.add('dragging')
      })
      dropzone.addEventListener('dragleave', function (e) {
        dropzone.classList.remove('dragging')
      })
    }
  }
}
</script>

<style lang="scss">
.dropzone {
  display: block;
  height: 250px;
  width: 100%;
  padding: 100px 20px 0 20px;
  background-color: #f3f3f3;
  text-align: center;
  font-size: 1.25rem;
  border-radius: 15px;
  border: 2px solid #ccc;
  cursor: pointer;

  &.dragging {
    cursor: move !important;
  }

  &:hover {
    background-color: #eee;
  }

  input[type="file"] {
    width: 1px;
    height: 1px;
    position: absolute;
    left: -3000px;
  }
}
</style>
