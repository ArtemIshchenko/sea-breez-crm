gear<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-xl-3 col-lg-4 col-md-5 order-md-last p-4">
        <!-- gear actions -->
        <handler :model="model" @updated="$emit('updated', $event)"></handler>
      </div>

      <!-- user details -->
      <div class="col-xl-9 col-lg-8 col-md-7 order-md-first">
        <dl class="row">
          <dt class="col-xl-3">ID</dt>
          <dd class="col-xl-9">{{ model.id }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Модель') }}</dt>
          <dd class="col-xl-9">{{ model.title }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Количество проектов') }}</dt>
          <dd class="col-xl-9">
            <router-link :to="{ name: 'projects', query: { filter: { gear: model.id } } }">
              {{ model.projectsNumber }}
            </router-link>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Производитель') }}</dt>
          <dd class="col-xl-9">{{ model.producer }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Описание') }}</dt>
          <dd class="col-xl-9">
            <multi-line :text="model.description"></multi-line>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Дата создания') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.created_at) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Дата последнего изменения') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.updated_at) }}</dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script>
import { DateHelper } from '@/helpers'
import { MultiLine } from '@/components'
import Handler from './view/Handler'

export default {
  name: 'gear-view',
  components: {
    MultiLine, Handler
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  methods: {
    toDate (timestamp, hM = true) {
      return DateHelper.toHumanStr(timestamp, hM)
    }
  }
}
</script>
