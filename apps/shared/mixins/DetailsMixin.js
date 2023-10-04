// Mixin used in Details components
export default {
  data () {
    return {
      model: null
    }
  },
  computed: {
    id () {
      return this.$route.params.id
    }
  },
  created () {
    this.getData()
  },
  methods: {
    getData () {
      throw new Error('DetailsMixin getData method should be overwritten.')
    },
    onUpdated (data) {
      if (Array.isArray(data)) {
        data.forEach((item) => {
          this.model[item.attribute] = item.value
        })
      }
      if (typeof data === 'object' && data !== null) {
        if (data.attribute && typeof data.value !== 'undefined') {
          // agile forms return single attribute-value pair
          this.model[data.attribute] = data.value
        } else {
          // normal forms return whole model
          this.model = Object.assign(this.model, data)
        }
      }
    }
  },
  beforeRouteUpdate (to, from, next) {
    this.getData()
    next()
  },
  watch: {
    id: function (newID, oldID) {
      if (newID !== oldID) {
        this.getData()
      }
    },
    model: {
      handler (model) {
        if (model) {
          if (model.title) {
            this.$store.commit('page/setTitle', model.title)
          } else if (model.name) {
            this.$store.commit('page/setTitle', model.name)
          }
        }
      },
      deep: true
    }
  }
}
