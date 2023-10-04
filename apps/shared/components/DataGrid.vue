<template>
  <div class="data-grid-container">

    <b-alert variant="warning" :show="items !== null && !items.length">No items found.</b-alert>

    <b-table striped hover
          v-if="items && items.length"
          :items="items"
          :fields="columns"
          no-local-sorting
          @sort-changed="sortingChanged">

      <template v-for="name in dataSlots" #[getCellNotation(name)]="data">
        <slot :name="name" :item="data.item"></slot>
      </template>

      <template #cell(actions)="data">
        <slot name="actions" :item="data.item"></slot>
      </template>
    </b-table>

    <b-pagination
          v-if="total"
          v-show="total > perPage"
          :total-rows="total"
          :per-page="perPage"
          v-model="page"
          @change="paginationChanged" />
  </div>
</template>

<script>
export default {
  name: 'data-grid',
  props: {
    fields: Object,
    service: {
      type: String,
      required: true
    },
    dataSlots: {
      type: Array,
      default: () => []
    },
    filter: {
      type: Object,
      default: () => ({
        model: {},
        conditions: {}
      })
    },
    renewItemsTrigger: {
      type: Boolean,
      default: () => false
    },
    noActions: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      items: null,
      page: null,
      perPage: null,
      total: null
    }
  },
  computed: {
    columns () {
      let columns = typeof this.fields === 'object' && this.fields !== null
        ? Object.keys(this.fields).map(key => ({ ...this.fields[key], key })).filter(key => { return !key.disabled })
        : this.fields
      if (!this.noActions) {
        columns.push({ key: 'actions', sortable: false, label: ' ', tdClass: 'data-grid-actions text-right' })
      }
      return columns
    },
    query () {
      return this.$route.query
    }
  },
  created () {
    this.getItems(this.query)
  },
  methods: {
    getCellNotation (name) {
      return `cell(${name})`
    },
    async getItems (params) {
      try {
        const response = await this.$http.get(this.service, {
          params: params
        })
        this.items = response.data
        this.page = +response.headers['x-pagination-current-page']
        this.perPage = +response.headers['x-pagination-per-page']
        this.total = +response.headers['x-pagination-total-count']
        this.$emit('loaded', this.items)
      } catch (e) {
        this.$notifyError('Не удалось загрузить данные. ' + e.message)
      }
    },
    pushData (query) {
      const path = this.$route.path
      this.$router.replace({
        path,
        query
      })
    },
    sortingChanged ({ sortBy, sortDesc }) {
      this.pushData(Object.assign({}, this.query, {
        sort: (sortDesc ? '' : '-') + sortBy
      }))
    },
    paginationChanged (page) {
      this.pushData(Object.assign({}, this.query, {
        page
      }))
    }
  },
  watch: {
    renewItemsTrigger (newValue) {
      if (newValue) {
        this.getItems()
        this.$emit('items-renewed')
      }
    },
    query: {
      deep: true,
      handler (query) {
        this.getItems(query)
      }
    }
  }
}
</script>
