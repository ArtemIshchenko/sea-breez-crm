<template>
  <div class="animated fadeIn">
    <form @submit="onSubmit($event)" class="row">
      <div class="col-lg-6">
        <form-input
              name="title"
              :label="$t('project', 'Навание проекта')"
              :value="form.model.title"
              :required="!!$v.form.model.title && !!$v.form.model.title.$params.required"
              :error-message="form.errors.title"
              :isValid="form.saved.title"
              @change="onChange('title', $event)"
              @input="onInput('title', $event)"></form-input>
      </div>
      <div class="col-lg-6">
        <form-date-picker
              name="date"
              :label="$t('project', 'Срок сдачи проекта')"
              :value="form.model.date"
              :required="!!$v.form.model.date && !!$v.form.model.date.$params.required"
              :error-message="form.errors.date"
              timestamp
              :config="{ minDate: minDate }"
              :isValid="form.saved.date"
              @change="onChange('date', $event)"
              @input="onInput('date', $event)"></form-date-picker>
      </div>
      <div class="col-lg-6">
        <form-input
              name="client"
              :label="$t('project', 'Заказчик')"
              :value="form.model.client"
              :required="!!$v.form.model.client && !!$v.form.model.client.$params.required"
              :error-message="form.errors.client"
              :isValid="form.saved.client"
              @change="onChange('client', $event)"
              @input="onInput('client', $event)"></form-input>
      </div>
      <div class="col-lg-6">
        <form-input
              name="auction_link"
              :label="$t('project', 'Ссылка на тендер или аукцион')"
              :value="form.model.auction_link"
              :required="!!$v.form.model.auction_link && !!$v.form.model.auction_link.$params.required"
              :error-message="form.errors.auction_link"
              :isValid="form.saved.auction_link"
              @change="onChange('auction_link', $event)"
              @input="onInput('auction_link', $event)"></form-input>
      </div>
      <div class="col-lg-6">
        <form-textarea
              name="delivery_conditions"
              :label="$t('project', 'Условия поставки')"
              :value="form.model.delivery_conditions"
              :required="!!$v.form.model.delivery_conditions && !!$v.form.model.delivery_conditions.$params.required"
              :error-message="form.errors.delivery_conditions"
              :isValid="form.saved.delivery_conditions"
              @change="onChange('delivery_conditions', $event)"
              @input="onInput('delivery_conditions', $event)"></form-textarea>
      </div>
      <div class="col-lg-6">
        <form-input
              name="subcontractor"
              :label="$t('project', 'Субподрядчик')"
              :value="form.model.subcontractor"
              :required="!!$v.form.model.subcontractor && !!$v.form.model.subcontractor.$params.required"
              :error-message="form.errors.subcontractor"
              :isValid="form.saved.subcontractor"
              @change="onChange('subcontractor', $event)"
              @input="onInput('subcontractor', $event)"></form-input>
      </div>
      <div class="col-lg-6">
        <form-textarea
              name="revision_description"
              :label="$t('project', 'Описание проекта')"
              :value="form.model.revision_description"
              :required="!!$v.form.model.revision_description && !!$v.form.model.revision_description.$params.required"
              :error-message="form.errors.revision_description"
              :isValid="form.saved.revision_description"
              @change="onChange('revision_description', $event)"
              @input="onInput('revision_description', $event)"></form-textarea>
      </div>
      <div class="col-lg-6">
        <form-textarea
              name="development_prospects"
              :label="$t('project', 'Описание перспектив развития проекта и требуемых доработок')"
              :value="form.model.development_prospects"
              :required="!!$v.form.model.development_prospects && !!$v.form.model.development_prospects.$params.required"
              :error-message="form.errors.development_prospects"
              :isValid="form.saved.development_prospects"
              @change="onChange('development_prospects', $event)"
              @input="onInput('development_prospects', $event)"></form-textarea>
      </div>
    </form>
  </div>
</template>

<script>
import { FormInput, FormTextarea, FormDatePicker } from '@/components'
import { required, maxLength, url } from '@/validators'
import { FormAgileMixin } from '@/mixins'

export default {
  name: 'project-edit',
  mixins: [ FormAgileMixin ],
  components: {
    FormInput, FormTextarea, FormDatePicker
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
          date: this.model.date,
          client: this.model.client,
          auction_link: this.model.auction_link,
          delivery_conditions: this.model.delivery_conditions,
          subcontractor: this.model.subcontractor,
          revision_description: this.model.revision_description,
          development_prospects: this.model.development_prospects
        },
        service: 'admin/projects/' + this.model.id,
        serviceMethod: 'patch'
      }
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
          date: {
            required
          },
          client: {
            required,
            maxLength: maxLength(255)
          },
          auction_link: {
            maxLength: maxLength(255),
            url
          },
          subcontractor: {
            maxLength: maxLength(255)
          }
        }
      }
    }
  },
  computed: {
    minDate () {
      const date = new Date()
      date.setDate((new Date()).getDate() + 10)
      date.setHours(0, 0, 0, 0)
      return date
    }
  }
}
</script>
