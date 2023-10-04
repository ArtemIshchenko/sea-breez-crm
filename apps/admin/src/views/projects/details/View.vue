<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-xl-3 col-lg-4 col-md-5 order-md-last p-4">
        <!-- project actions -->
        <handler :model="model" @updated="$emit('updated', $event)"></handler>
      </div>

      <!-- user details -->
      <div class="col-xl-9 col-lg-8 col-md-7 order-md-first">
        <dl class="row">
          <dt class="col-xl-3">ID</dt>
          <dd class="col-xl-9">{{ model.id }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Название') }}</dt>
          <dd class="col-xl-9">{{ model.title }}</dd>

          <dt v-if="model.status_message" class="col-xl-3">
            {{ $t('project', 'Сообщение при изменении статуса') }}
            <font-awesome-icon icon="exclamation-triangle" class="text-danger ml-1"></font-awesome-icon>
          </dt>
          <dd v-if="model.status_message" class="col-xl-9">
            {{ model.status_message }}
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Автор') }}</dt>
          <dd class="col-xl-9">
            <router-link v-if="model.author" :to="{ name: 'user/details', params: { id: model.author.id } }">
              {{ [model.author.first_name, model.author.last_name].join(' ') }}
            </router-link>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Менеджер') }}</dt>
          <dd class="col-xl-9">
            <router-link v-if="model.author && model.author.manager" :to="{ name: 'user/details', params: { id: model.author.manager.id } }">
              {{ [model.author.manager.first_name, model.author.manager.last_name].join(' ') }}
            </router-link>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Проектировщик') }}</dt>
          <dd class="col-xl-9">
            <router-link v-if="model.designer" :to="{ name: 'user/details', params: { id: model.designer.id } }">
              {{ [model.designer.first_name, model.designer.last_name].join(' ') }}
            </router-link>
          </dd>

          <dt v-if="model.status === 'designing'" class="col-xl-3">{{ $t('project', 'Дедлайн по проектированию') }}</dt>
          <dd v-if="model.status === 'designing'" class="col-xl-9">{{ toDate(model.designing_deadline, false) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Срок сдачи проекта') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.date, false) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Адрес') }}</dt>
          <dd class="col-xl-9">{{ model.address }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Координаты') }}</dt>
          <dd class="col-xl-9">
            {{ model.coordinates }}
            <b-link v-if="model.coordinates" href="#" @click="showMap = true" class="ml-2">{{ $t('project', 'Карта') }}</b-link>
            <b-modal
                  v-if="model.coordinates"
                  :title="$t('project', 'Местонахождение обьекта')"
                  class="modal-primary"
                  hide-footer
                  v-model="showMap">
              <embed-map :marker="model.coordinates"></embed-map>
            </b-modal>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Заказчик') }}</dt>
          <dd class="col-xl-9">{{ model.client }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Ссылка на тендер или аукцион') }}</dt>
          <dd class="col-xl-9"><a :href="model.auction_link" target="_blank">{{ model.auction_link }}</a></dd>

          <dt class="col-xl-3">{{ $t('project', 'Спецификация') }}</dt>
          <dd class="col-xl-9">
            <template v-if="model.specifications.length > 0">
              <p v-for="(file, key) in model.specifications" :key="key">
                <a download :href="'/api/admin/projects/' + model.id + '/files/' + file.id" :key="key">
                  {{ file.filename }}
                </a>
              </p>
            </template>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Техническое задание') }}</dt>
          <dd class="col-xl-9">
            <a v-if="model.technical_task" download :href="'/api/admin/projects/' + model.id + '/files/' + model.technical_task.id">
              {{ model.technical_task.filename }}
            </a>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Другие файлы') }}</dt>
          <dd class="col-xl-9">
            <template v-for="(file, key) in model.other_files">
              <a download :href="'/api/admin/projects/' + model.id + '/files/' + file.id" :key="key">
                {{ file.filename }}
              </a>
              <template v-if="key < model.other_files.length - 1">, </template>
            </template>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Условия поставки') }}</dt>
          <dd class="col-xl-9"><multi-line :text="model.delivery_conditions"></multi-line></dd>

          <dt class="col-xl-3">{{ $t('project', 'Субподрядчик') }}</dt>
          <dd class="col-xl-9">{{ model.subcontractor }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Описание проекта') }}</dt>
          <dd class="col-xl-9"><multi-line :text="model.revision_description"></multi-line></dd>

          <dt class="col-xl-3">{{ $t('project', 'Описание перспектив и доработок') }}</dt>
          <dd class="col-xl-9"><multi-line :text="model.development_prospects"></multi-line></dd>

          <dt class="col-xl-3">{{ $t('project', 'Дата создания') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.created_at) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Дата последнего изменения') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.updated_at) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Комментарии') }}</dt>
          <dd class="col-xl-9">
            <p v-for="(comment, i) in model.comments" :class="{'comment-author-hidden': !comment.author_visible}" :key="i">
              {{ toDate(comment.created_at) }}
              <br>
              {{ (comment.author.first_name + ' ' + comment.author.last_name).trim() }}
              <br>
              <multi-line :text="comment.body"></multi-line>
            </p>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script>
import { DateHelper } from '@/helpers'
import { MultiLine, EmbedMap } from '@/components'
import Handler from './view/Handler'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'project-view',
  components: {
    MultiLine, EmbedMap, Handler, FontAwesomeIcon
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      showMap: false
    }
  },
  methods: {
    toDate (timestamp, hM = true) {
      return DateHelper.toHumanStr(timestamp, hM)
    }
  }
}
</script>
