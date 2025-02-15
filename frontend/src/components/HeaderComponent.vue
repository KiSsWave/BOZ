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
/* Styles du header */
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 10;
  height: 70px;
}

/* Style fixe pour le logo */
.BOZ {
  width: 80px;
  height: auto;
}

/* Styles des icônes */
.user-icon, .exit {
  width: 24px;
  height: 24px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.user-icon:hover, .exit:hover {
  transform: scale(1.1);
}

.exit:hover {
  color: #e74c3c;
}

.back-icon {
  cursor: pointer;
  color: #3498db;
  font-size: 24px;
  transition: color 0.3s ease;
}

.back-icon:hover {
  color: #2980b9;
}

h1 {
  font-size: 1.2rem;
  color: #2c3e50;
  margin: 0;
  text-align: center;
  flex-grow: 1;
}

@media (max-width: 480px) {
  h1 {
    font-size: 1rem;
  }

  .BOZ {
    width: 60px;
  }
}
</style>
