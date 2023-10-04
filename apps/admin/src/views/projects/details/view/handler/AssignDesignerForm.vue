<template>
  <form @submit="onSubmit">
    <form-simple-select
          name="designer_id"
          :label="$t('project', 'Проектировщик')"
          :value="form.model.designer_id"
          :error-message="form.errors.designer_id"
          :options="designers"
          @change="onChange('designer_id', $event)"
          @input="onInput('designer_id', $event)"
          :placeholder="isChangingDesigner ? [model.designer.last_name, model.designer.first_name].join(' ') : null"></form-simple-select>

    <form-date-picker
          name="designing_deadline"
          :label="$t('project', 'Дедлайн по проектированию')"
          :value="form.model.designing_deadline"
          :error-message="form.errors.designing_deadline"
          timestamp
          :config="{ minDate: minDeadline }"
          @change="onChange('designing_deadline', $event)"
          @input="onInput('designing_deadline', $event)"></form-date-picker>

    <b-button type="submit" variant="primary">{{ $t('project', 'Отправить') }}</b-button>
  </form>
</template>

<script>
import { FormSimpleSelect, FormDatePicker } from '@/components'
import { FormMixin } from '@/mixins'
import { required } from '@/validators'

export default {
  name: 'assign-designer-form',
  mixins: [ FormMixin ],
  components: { FormSimpleSelect, FormDatePicker },
  props: [ 'model' ],
  data () {
    return {
      designers: [],
      form: {
        model: {
          designer_id: null,
          designing_deadline: null,
          status: 'designing'
        },
        service: 'admin/projects/' + this.model.id + (this.model.status === 'designing' ? '/designer' : '/status'),
        serviceMethod: 'patch',
        afterSave: (data) => {
          this.$notifySuccess(this.$t('project', 'Проектировщик назначен.'))
          this.$emit('designer-assigned', [
            { attribute: 'status', value: data.status },
            { attribute: 'designer', value: data.designer },
            { attribute: 'designing_deadline', value: data.designing_deadline }
          ])
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          designer_id: {
            required
          },
          designing_deadline: {
            required
          }
        }
      }
    }
  },
  created () {
    this.getDesigners()
  },
  methods: {
    async getDesigners () {
      try {
        const response = await this.$http.get('admin/users/list', {
          params: {
            role: 'designer'
          }
        })
        if (response.data) {
          // if changing existing designer remove him from list
          if (this.isChangingDesigner && response.data[this.model.designer.id]) {
            delete response.data[this.model.designer.id]
          }
          this.designers = response.data
        }
      } catch (e) {
        this.$notifyError(this.$t('project', 'Не удалось получить список проектировщиков. ') + e.message)
      }
    }
  },
  computed: {
    minDeadline () {
      const date = new Date()
      date.setDate((new Date()).getDate() + 7)
      date.setHours(0, 0, 0, 0)
      return date
    },
    isChangingDesigner () {
      return this.model.status === 'designing'
    }
  }
}
</script>
