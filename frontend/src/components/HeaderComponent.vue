<template>
  <header>
    <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
    <h1>Boz - Dépensez n'importe où !</h1>
    <div class="icons-container">
      <font-awesome-icon
        :icon="['fas', 'gear']"
        style="color: #000000;"
        class="param"
        v-if="userStore.isAuthenticated"
        @click="modification"
      />
      <font-awesome-icon
        :icon="['fas', 'comments']"
        @click="openChat"
        class="chat-icon"
        v-if="userStore.isAuthenticated"
        title="Messages"
      />
      <font-awesome-icon
        icon="reply"
        class="back-icon"
        @click="index"
        title="Retour à l'accueil"
        v-if="isNotHome"
      />
      <font-awesome-icon
        :icon="['fas', 'user']"
        @click="login"
        alt="User Login"
        class="user-icon"
        v-if="!userStore.isAuthenticated"
      />
      <font-awesome-icon
        :icon="['fas', 'right-from-bracket']"
        v-if="userStore.isAuthenticated"
        class="exit"
        @click="logout"
      />
    </div>
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
    openChat() {
      this.$router.push('/chat');
    },
    modification() {
      this.$router.push('/account');
    }
  },
};
</script>

<style scoped>
/* Styles existants */
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

.BOZ {
  width: 80px;
  height: auto;
}

/* Conteneur pour les icônes */
.icons-container {
  display: flex;
  align-items: center;
  gap: 15px;
}

/* Styles des icônes */
.user-icon, .exit, .param, .back-icon, .chat-icon {
  width: 24px;
  height: 24px;
  cursor: pointer;
  transition: transform 0.2s ease;
  margin-left: 15px;
}

.user-icon:hover, .exit:hover, .param:hover, .chat-icon:hover, .back-icon:hover {
  transform: scale(1.1);
}

.chat-icon {
  color: #3498db;
}

.chat-icon:hover {
  color: #2980b9;
}

.exit:hover {
  color: #e74c3c;
}

.back-icon {
  color: #3498db;
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

  .icons-container {
    gap: 10px;
  }
}
</style>
