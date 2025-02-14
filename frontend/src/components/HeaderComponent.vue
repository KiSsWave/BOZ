<template>
  <header>
    <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
    <h1>Boz - Dépensez n'importe où !</h1>
    <font-awesome-icon :icon="['fas', 'user']" @click="login" alt="User Login" class="user-icon" v-if="!userStore.isAuthenticated" />
    <font-awesome-icon icon="reply" class="back-icon" @click="index" title="Retour à l'accueil" v-if="isNotHome" />
    <font-awesome-icon :icon="['fas', 'right-from-bracket']" v-if="userStore.isAuthenticated" class="exit" @click="logout" />
  </header>
</template>

<script>
import { useUserStore } from '@/stores/userStore';
import { useRoute } from 'vue-router';
import { computed } from 'vue';

export default {
  name: 'HeaderComponent',
  setup() {
    const route = useRoute();
    const userStore = useUserStore();
    const isNotHome = computed(() => route.path !== '/');

    return {
      userStore,
      isNotHome
    };
  },
  methods: {
    login() {
      this.$router.push('/login');
    },
    logout() {
      this.userStore.logout();
      this.$router.push('/');
    },
    index() {
      this.$router.push('/');
    },
  },
};
</script>



<style scoped>
.back-icon {
  cursor: pointer;
  color: #3498db;
  font-size: 24px;
  transition: color 0.3s ease;
}

.back-icon:hover {
  color: #2980b9;
}
</style>
