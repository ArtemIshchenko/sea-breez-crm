<template>
  <b-badge pill variant="primary">{{ label }}</b-badge>
</template>

<script>
// This component needed for proper menu width calculation after the badge is mounted (see `checkMenuWidth` method of `CardMenu` component)
export default {
  name: 'card-menu',
  props: {
    label: [String, Number]
  },
  mounted () {
    this.$emit('mounted')
  }
}
</script>
