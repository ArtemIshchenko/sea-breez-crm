<template>
  <div class="data-grid-file-import-container">
    <b-table striped hover
      :items="items"
      :fields="fields">
      <template v-for="field in fields" #[getHeadNotation(field.key)]="data">
        <b-form-select
            :key="field.key"
            v-model="selectValues[field.key]"
            :options="selectFields"
            @change="$emit('update', { field: field.key, value: $event })">
        </b-form-select>
        <span v-if="ignoreFirstRow" :key="field.key">{{ field.label }}</span>
      </template>
    </b-table>
  </div>
</template>

<script>

export default {
  name: 'data-grid-file-import',
  props: {
    fields: {
      type: Array,
      default: () => []
    },
    items: {
      type: Array,
      default: () => []
    },
    selectFields: {
      type: Array,
      default: () => []
    },
    ignoreFirstRow: Boolean,
    selectValues: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    getHeadNotation (name) {
      return `head(${name})`
    },
    notify (key, field, event) {
      let s = key + '; ' + JSON.stringify(field) + '; ' + JSON.stringify(event)
      this.$notifySuccess(s)
    }
  },
  watch: {
    ignoreFirstRow: function (ignoreFirstRow) {
      if (ignoreFirstRow) {
        this.items = this.items.shift()
      } else {
        const firstRow = {}
        this.fields.forEach((el, index) => {
          firstRow[el.key] = el.label
        })
        this.items = this.items.unshift(firstRow)
      }
    }
  }
}
</script>
<style>
  .data-grid-file-import-container {
    max-height: 80vh;
  }
  .data-grid-file-import-container table td {
    min-width: 7em;
  }
  .data-grid-file-import-container table th select {
    color: #000;
    font-weight: bold;
  }
  .data-grid-file-import-container table th option {
    font-weight: normal;
  }
  .data-grid-file-import-container table th option:checked {
    font-weight: bold;
  }
  .data-grid-file-import-container table th span {
    display: inline-block;
    min-height: 3em;
    font-weight: normal;
    color: #7e7e7e;
    padding-left: 12px;
  }
</style>
