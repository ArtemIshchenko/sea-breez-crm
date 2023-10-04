<template>
  <b-form-group
      :state="state"
      :invalid-feedback="inputErrorMessage"
      :valid-feedback="inputSuccessMessage"
      :description="hint">
    <div class="d-flex">
      <label :for="'field_' + name">{{inputLabel}}<span v-if="required" class="text-danger">*</span></label>
      <div v-if="hasAdditionalItem" v-html="hasAdditionalItem"/>
    </div>
    <the-mask
        :name="name"
        :state="state"
        :id="'field_' + name"
        v-model="model"
        :placeholder="inputPlaceholder ? inputPlaceholder: '+38 (000) 000-00-00'"
        :type="type"
        class="form-control"
        @change="$emit('change', model)"
        @input="$emit('input', model)"
        :masked="true"
        :mask="['+38 (###) ###-##-##']" />
  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'
// import MaskedInput from 'vue-masked-input'
import { TheMask } from 'vue-the-mask'
export default {
  name: 'form-masked',
  mixins: [FormFieldMixin],
  components: {
    // MaskedInput,
    TheMask
  },
  props: {
    value: [String, Number],
    type: {
      type: String,
      default: 'text'
    },
    required: Boolean,
    prepend: String,
    append: String
  },
  watch: {
    default (newValue) {
      this.model = newValue
    }
  }
}
</script>
