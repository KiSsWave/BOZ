<template>
  <header>
    <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
    <h1>Boz - Dépensez n'importe où !</h1>
    <div class="icons-container">
      <font-awesome-icon :icon="['fas', 'gear']" style="color: #000000;" class="param" v-if="userStore.isAuthenticated && isNotModification"
                         @click="modification" title="Paramètres" />
      <font-awesome-icon :icon="['fas', 'comments']" @click="openChat" class="chat-icon"
                         v-if="userStore.isAuthenticated" title="Messages" />
      <font-awesome-icon icon="reply" class="back-icon" @click="index" title="Retour à l'accueil" v-if="isNotHome" />
      <font-awesome-icon :icon="['fas', 'user']" @click="login" alt="User Login" class="user-icon"
                         v-if="!userStore.isAuthenticated" title="Connexion" />
      <font-awesome-icon :icon="['fas', 'right-from-bracket']" v-if="userStore.isAuthenticated" class="exit"
                         @click="logout" title="Déconnexion" />
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

    return {
      userStore,
      isNotHome,
      isNotModification
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
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: clamp(8px, 2vw, 15px) clamp(10px, 3vw, 20px);
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 10;
  min-height: 60px;
  height: auto;
  max-height: 80px;
  flex-wrap: nowrap;
}

.BOZ {
  width: clamp(40px, 10vw, 80px);
  height: auto;
  flex-shrink: 0;
}

/* Conteneur pour les icônes */
.icons-container {
  display: flex;
  align-items: center;
  gap: clamp(8px, 2vw, 15px);
  flex-shrink: 0;
}

/* Styles des icônes */
.user-icon,
.exit,
.param,
.back-icon,
.chat-icon {
  width: clamp(18px, 5vw, 24px);
  height: clamp(18px, 5vw, 24px);
  cursor: pointer;
  transition: transform 0.2s ease, color 0.2s ease;
}

.user-icon:hover,
.exit:hover,
.param:hover,
.chat-icon:hover,
.back-icon:hover {
  transform: scale(1.1);
}

.chat-icon {
  color: #3498db;
}

.chat-icon:hover {
  color: #2980b9;
}

.exit {
  color: #555;
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

.param {
  color: #555;
}

.param:hover {
  color: #2c3e50;
}

h1 {
  font-size: clamp(0.75rem, 3vw, 1.2rem);
  color: #2c3e50;
  margin: 0 clamp(5px, 2vw, 20px);
  text-align: center;
  flex-grow: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Responsive breakpoints spécifiques */
@media (max-width: 600px) {
  h1 {
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  header {
    padding: 8px 12px;
  }

  h1 {
    font-size: 0.8rem;
  }

  .BOZ {
    width: 50px;
  }

  .icons-container {
    gap: 8px;
  }

  .user-icon,
  .exit,
  .param,
  .back-icon,
  .chat-icon {
    width: 20px;
    height: 20px;
  }
}

@media (max-width: 360px) {
  h1 {
    font-size: 0.7rem;
    max-width: 120px;
  }

  .BOZ {
    width: 40px;
  }

  .icons-container {
    gap: 6px;
  }

  .user-icon,
  .exit,
  .param,
  .back-icon,
  .chat-icon {
    width: 18px;
    height: 18px;
  }
}

/* Orientation paysage sur petits écrans */
@media (max-height: 500px) and (orientation: landscape) {
  header {
    min-height: 50px;
    padding: 5px 15px;
  }

  .BOZ {
    width: 40px;
  }
}
</style>
