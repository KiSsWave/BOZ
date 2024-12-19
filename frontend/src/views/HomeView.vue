<script setup>
import IndexHeader from '../components/IndexHeader.vue'
import IndexFooter from '../components/IndexFooter.vue'
import IndexBody from '../components/IndexBody.vue'
</script>

<template>

  <IndexHeader />



  <IndexBody />



  <IndexFooter />

</template>

<style scoped>
main {
  width: 100%;
  height: 100%;
  background-color: white;
  padding: 0;



  header {
    width: 100%;
    height: 10%;
    top: 0;
  }

  body {
    width: 100%;
  }



  footer {
    width: 100%;
    bottom: 0;
  }
}
</style>
