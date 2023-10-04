<template>
  <div class="animated fadeIn">
    <form @submit="onSubmit($event)" class="row">
      <div class="col-lg-6">
        <form-input
              name="title"
              :label="$t('project', 'Модель оборудования')"
              :value="form.model.title"
              :required="!!$v.form.model.title && !!$v.form.model.title.$params.required"
              :error-message="form.errors.title"
              :isValid="form.saved.title"
              @change="onChange('title', $event)"
              @input="onInput('title', $event)"></form-input>
      </div>
      <div class="col-lg-6">
        <form-select
              name="producer"
              :label="$t('project', 'Производитель')"
              :value="form.model.producer"
              :options="producers"
              :error-message="form.errors.producer"
              :isValid="form.saved.producer"
              @change="onChange('producer', $event)"
              @input="onInput('producer', $event)"></form-select>
      </div>
      <div class="col-lg-6">
        <form-textarea
              name="description"
              :label="$t('project', 'Описание')"
              :value="form.model.description"
              :error-message="form.errors.description"
              :isValid="form.saved.description"
              @change="onChange('description', $event)"
              @input="onInput('description', $event)"></form-textarea>
      </div>
    </form>
  </div>
</template>

<script>
import { FormInput, FormTextarea, FormSelect } from '@/components'
import { required, maxLength } from '@/validators'
import { FormAgileMixin } from '@/mixins'

export default {
  name: 'gear-edit',
  mixins: [ FormAgileMixin ],
  components: {
    FormInput, FormTextarea, FormSelect
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      form: {
        model: {
          title: this.model.title,
          producer: this.model.producer,
          description: this.model.description
        },
        service: 'admin/gears/' + this.model.id,
        serviceMethod: 'patch'
      }
    }
  },
  computed: {
    producers () {
      return this.$store.state.gear.list
    }
  },
  validations () {
    return {
      form: {
        model: {
          title: {
            required,
            maxLength: maxLength(255)
          },
          producer: {
            maxLength: maxLength(255)
          }
        }
      }
    }
  }
}
</script>
