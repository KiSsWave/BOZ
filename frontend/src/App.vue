<script setup>
import { RouterView } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { useAppStore } from '@/stores/appStore'
import { onMounted, watch } from 'vue'

const userStore = useUserStore()
const appStore = useAppStore()

onMounted(() => {
  if (userStore.isAuthenticated) {
    appStore.loadInitialData()
  }
})

watch(
  () => userStore.isAuthenticated,
  (newValue, oldValue) => {
    if (newValue && !oldValue) {
      appStore.loadInitialData()
    } else if (!newValue && oldValue) {
      appStore.resetStore()
    }
  }
)
</script>

<template>
  <RouterView />
</template>

<style scoped>
main{
  width: 100%;
  height: 100%;
  padding:0;
  margin:0;
}
</style>
