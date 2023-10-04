<template>
  <div class="file-import-block">
    <label for="file-import" :class="currentFiles.length > 0 ? 'file-import-label' : ''">{{ label }}</label>
    <list-of-files
        v-if="currentFiles.length > 0"
        :files="currentFiles"
        :download-service="downloadService"
        :delete-service="deleteService"
        :hide-download="false"
        :hide-delete="false"
        class="mb-2"
        @file-deleted="$emit('file-deleted', $event)"></list-of-files>
    <button id="file-import" class="file-import-button" @click="$emit('click')">
      <img src="../../images/import.png" alt="..." width="117" height="117">
      <span class="title">{{ title }}</span>
      <span class="file-type-list">{{ fileTypeList }}</span>
    </button>
  </div>
</template>

<script>
import { ListOfFiles } from './../Form'

export default {
  name: 'file-import-button',
  components: { ListOfFiles },
  props: {
    title: String,
    fileTypeList: String,
    label: String,
    downloadService: [String, Function],
    deleteService: [String, Function],
    currentFiles: [String, Array]
  }
}

</script>

<style lang="scss">
  .file-import-block > .file-import-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 250px;
    width: 100%;
    padding: 45px 35px 30px;
    margin-right: 20px;
    background-color: #f3f3f3;
    box-shadow: 0 1px 5px #00000046;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    outline: none!important;
    box-sizing: border-box;
  }
  .file-import-block > .file-import-button > img {
    margin-bottom: 5px;
    vertical-align: middle;
    box-sizing: border-box;
    cursor: pointer;
    outline: none!important;
  }
  .file-import-block > .file-import-button > .title {
    font-size: 16px;
    font-weight: 500;
    color: #73818f;
  }
  .file-import-block > .file-import-button > .file-type-list {
    font-size: 13px;
    font-style: italic;
    color: #73818f;
  }
  .file-import-block > label {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 1.2rem;
  }
  .file-import-block > label.file-import-label {
    margin-bottom: 4px;
  }
</style>
