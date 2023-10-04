<template>
  <div v-if="text">
    <template v-for="(line, index) in text.split('\n')">
      {{ line }}
      <br :key="index">
    </template>
  </div>
</template>

<script>
export default {
  name: 'multi-line',
  props: {
    text: String
  }
}
</script>
