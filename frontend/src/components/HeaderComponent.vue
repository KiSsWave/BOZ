<template>
  <header>
    <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
    <h1>Boz - Dépensez n'importe où !</h1>
    <div class="icons-container">
      <font-awesome-icon :icon="['fas', 'gear']" style="color: #000000;" class="param" v-if="userStore.isAuthenticated && isNotModification"
        @click="modification" />
      <font-awesome-icon :icon="['fas', 'comments']" @click="openChat" class="chat-icon"
        v-if="userStore.isAuthenticated" title="Messages" />
      <font-awesome-icon icon="reply" class="back-icon" @click="index" title="Retour à l'accueil" v-if="isNotHome && isNotAdmin && isNotVendeur" />
      <font-awesome-icon :icon="['fas', 'user']" @click="login" alt="User Login" class="user-icon"
        v-if="!userStore.isAuthenticated" />
      <font-awesome-icon :icon="['fas', 'right-from-bracket']" v-if="userStore.isAuthenticated" class="exit"
        @click="logout" />
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
    const isNotModification = computed(() => route.path !== '/modification');
    const isNotVendeur = computed(() => route.path !== '/vendeur');
    const isNotAdmin = computed(() => route.path !== '/admin');

    return {
      userStore,
      isNotHome,
      isNotModification,
      isNotVendeur,
      isNotAdmin
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
      if (this.userStore.isAuthenticated) {
        const userRole = this.userStore.user?.role;

        switch (userRole) {
          case '2':
            this.$router.push('/vendeur');
            break;
          case '3':
            this.$router.push('/admin');
            break;
          default:
            this.$router.push('/');
            break;
        }
      } else {
        this.$router.push('/');
      }
    },
    openChat() {
      this.$router.push('/conversations');
    },
    modification() {
      this.$router.push('/modification');
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
  width: 70px;
  height: auto;
}

/* Conteneur pour les icônes */
.icons-container {
  display: flex;
  align-items: center;
  gap: 15px;
}

/* Styles des icônes */
.user-icon,
.exit,
.param,
.back-icon,
.chat-icon {
  width: 24px;
  height: 24px;
  cursor: pointer;
  transition: transform 0.2s ease;
  margin-left: 15px;
}

.user-icon:hover,
.exit:hover,
.param:hover,
.chat-icon:hover,
.back-icon:hover {
  transform: scale(1.1);
}

.chat-icon {
  color: #000000;
}

.chat-icon:hover {
  color: #2980b9;
}

.exit:hover {
  color: #e74c3c;
}

.back-icon {
  color: #000000;
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
