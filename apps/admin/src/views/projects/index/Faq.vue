<template>
  <div v-html="info.text" class="faq-info">
  </div>
</template>

<script>
export default {
  name: 'faq',
  props: ['info']
}
</script>
<style>
  .faq-info {
    max-height: 70vh;
  }
  button.close {
    font-size: 1.9rem;
  }
</style>
