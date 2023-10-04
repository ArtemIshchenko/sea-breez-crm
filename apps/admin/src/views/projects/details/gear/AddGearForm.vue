<template>
  <form @submit="onSubmit">
    <form-select
          name="id"
          :label="$t('project', 'Модель оборудования')"
          :error-message="form.errors.id"
          :options="gearList"
          @change="onChange('id', $event)"
          @input="onInput('id', $event)"></form-select>

    <b-button type="submit" variant="primary">{{ $t('project', 'Отправить') }}</b-button>
  </form>
</template>

<script>
import { FormSelect } from '@/components'
import { FormMixin } from '@/mixins'
import { required } from '@/validators'

export default {
  name: 'add-gear-form',
  mixins: [ FormMixin ],
  components: { FormSelect },
  props: [ 'model', 'gearList' ],
  data () {
    return {
      form: {
        model: {
          id: null
        },
        service: 'admin/projects/' + this.model.id + '/gears',
        afterSave: (data) => {
          this.$notifySuccess(this.$t('project', 'Оборудование добавлено'))
          this.$emit('gear-added', data)
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          id: {
            required
          }
        }
      }
    }
  }
}
</script>
