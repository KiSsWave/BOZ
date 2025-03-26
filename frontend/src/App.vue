<script setup>
import { RouterView, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { useAppStore } from '@/stores/appStore'
import { onMounted, onBeforeUnmount, watch } from 'vue'
import { isTokenExpired } from './services/authProvider.js'

const userStore = useUserStore()
const appStore = useAppStore()
const router = useRouter()

let tokenCheckInterval

onMounted(() => {
  if (userStore.isAuthenticated) {
    appStore.loadInitialData()
    startTokenExpirationCheck()
  }
})


function startTokenExpirationCheck() {
  checkTokenExpiration()
  tokenCheckInterval = setInterval(checkTokenExpiration, 60000)
}


function checkTokenExpiration() {
  if (userStore.isAuthenticated && isTokenExpired()) {
    userStore.logout()
    router.push('/login')

    alert('Votre session a expiré. Veuillez vous reconnecter.')
  }
}

watch(
  () => userStore.isAuthenticated,
  (newValue, oldValue) => {
    if (newValue && !oldValue) {
      appStore.loadInitialData()
      startTokenExpirationCheck()
    } else if (!newValue && oldValue) {
      appStore.resetStore()
      clearInterval(tokenCheckInterval)
    }
  }
)


onBeforeUnmount(() => {
  if (tokenCheckInterval) {
    clearInterval(tokenCheckInterval)
  }
})
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
