<template>
  <b-form-group
        :label="inputLabel"
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage">

    <multi-select
          v-model="model"
          :id="name"
          :options="selectizedOptions"
          :multiple="multiple"
          label="label"
          :placeholder="inputPlaceholder"
          :track-by="hasObjectOptions ? 'value' : null"
          :close-on-select="!multiple"
          @input="onInput"
          selectLabel=""
          deselectLabel="Убрать">
      <template slot="noResult">
        Обьект не найден.
      </template>
    </multi-select>
  </b-form-group>

</template>

<script>
import MultiSelect from 'vue-multiselect'
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-select',
  mixins: [FormFieldMixin],
  props: {
    options: {
      type: [Array, Object],
      required: true
    },
    multiple: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    // if transform to array if options is a plain object
    selectizedOptions () {
      // return ['Select option', 'options', 'selected', 'mulitple', 'label', 'searchable', 'clearOnSelect', 'hideSelected', 'maxHeight']
      // return [{ value: 'asd', label: 'asd' }, { value: 'dsa', label: 'dsa' }]
      if (Array.isArray(this.options) && this.options[0] && typeof this.options[0] === 'object' && this.options[0] !== null) {
        return this.options
      }
      let options = []
      if (Array.isArray(this.options)) {
        for (let option of this.options) {
          options.push({
            value: option,
            label: option
          })
        }
      } else if (typeof this.options === 'object' && this.options !== null) {
        for (let key in this.options) {
          options.push({
            value: key,
            label: this.options[key]
          })
        }
      }
      return options
    },
    hasObjectOptions () {
      const el = this.selectizedOptions[this.selectizedOptions.length - 1]
      return typeof el === 'object' && el !== null
    }
  },
  created () {
    this.model = this.selectizeValue(this.value)
  },
  watch: {
    options: {
      deep: true,
      handler (options) {
        // this.model = this.selectizeValue(this.model || this.value)
        this.model = this.selectizeValue(this.value)
      }
    },
    value (newValue) {
      this.model = this.selectizeValue(newValue)
    }
  },
  methods: {
    selectizeValue (val) {
      if (!val || val.length === 0) {
        return val
      }
      if (this.hasObjectOptions) {
        if (!this.multiple) {
          return this.getObjectOptionByValue(val)
        } else {
          let values = []
          val.forEach((value) => {
            values.push(this.getObjectOptionByValue(value))
          })
          return values
        }
      }
    },
    getObjectOptionByValue (val) {
      if (!this.hasObjectOptions) return val
      for (let key in this.selectizedOptions) {
        if (String(this.selectizedOptions[key].value) === String(val)) {
          return this.selectizedOptions[key]
        }
      }
      throw new Error('Value is not found among select options.')
    },
    normalizeValue (val) {
      if (!this.hasObjectOptions) return val
      if (!this.multiple) {
        return val ? val.value : null
      } else {
        if (val.length === 0) {
          this.$emit('change', [])
        } else {
          let values = []
          val.forEach((option) => {
            values.push(option.value)
          })
          return values
        }
      }
    },
    onInput (selected) {
      this.$emit('change', this.normalizeValue(selected))
    }
  },
  components: {
    MultiSelect
  }
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>
  .multiselect {
    font-size: inherit;
    color: inherit;
  }
  .multiselect__tags {
    min-height: 35px;
    border-radius: 2px;
  }
  .multiselect__option--highlight{
    background: #20a8d8;
  }
  .multiselect__single {
    color: #5c6873;
  }
</style>
