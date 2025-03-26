<template>
  <header>
    <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
    <h1>Boz - Dépensez n'importe où !</h1>
    

    <div v-if="showCountdown" class="countdown-alert">
      Session: {{ secondsRemaining }}s
    </div>
    
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
import { useRoute, useRouter } from 'vue-router';
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { jwtDecode } from 'jwt-decode';

export default {
  name: 'HeaderComponent',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const userStore = useUserStore();
    const isNotHome = computed(() => route.path !== '/');
    const isNotModification = computed(() => route.path !== '/modification');
    

    const secondsRemaining = ref(0);
    const showCountdown = ref(false);
    let tokenExpirationTimeout; 
    let countdownInterval; 
    
   
    const getToken = () => {
      return localStorage.getItem("token");
    };
    

    const isTokenExpired = () => {
      const token = getToken();
      if (!token) return true;
      
      try {
        const decoded = jwtDecode(token);
        return decoded.exp * 1000 < Date.now();
      } catch (error) {
        return true;
      }
    };
    
    
    const getTokenRemainingTimeMs = () => {
      const token = getToken();
      if (!token) return 0;
      
      try {
        const decoded = jwtDecode(token);
      
        return Math.max(0, (decoded.exp * 1000) - Date.now());
      } catch (error) {
        return 0;
      }
    };
    
   
    const setupTokenExpirationHandler = () => {
      if (!userStore.isAuthenticated) return;
      
 
      clearTimeout(tokenExpirationTimeout);
      clearInterval(countdownInterval);
      
     
      const remainingTimeMs = getTokenRemainingTimeMs();
      
      if (remainingTimeMs <= 0) {
       
        handleExpiration();
        return;
      }
      
      if (remainingTimeMs <= 10000) {
      
        startCountdown(Math.ceil(remainingTimeMs / 1000));
      } else {
        const timeUntilCountdown = remainingTimeMs - 10000; 
        tokenExpirationTimeout = setTimeout(() => {
          startCountdown(10); 
        }, timeUntilCountdown);
      }
    };
    

    const startCountdown = (initialSeconds) => {
      showCountdown.value = true;
      secondsRemaining.value = initialSeconds;
      
      countdownInterval = setInterval(() => {
        secondsRemaining.value -= 1;
        
        if (secondsRemaining.value <= 0) {
          clearInterval(countdownInterval);
          handleExpiration();
        }
      }, 1000);
    };
    

    const handleExpiration = () => {
      clearTimeout(tokenExpirationTimeout);
      clearInterval(countdownInterval);
      showCountdown.value = false;
      userStore.logout();
      router.push('/login');
    };
    
    onMounted(() => {
      if (userStore.isAuthenticated) {
        setupTokenExpirationHandler();
      }
    });
    
    watch(
      () => userStore.isAuthenticated,
      (newValue, oldValue) => {
        if (newValue && !oldValue) {
     
          setupTokenExpirationHandler();
        } else if (!newValue && oldValue) {
      
          clearTimeout(tokenExpirationTimeout);
          clearInterval(countdownInterval);
          showCountdown.value = false;
        }
      }
    );
    
    onBeforeUnmount(() => {
      clearTimeout(tokenExpirationTimeout);
      clearInterval(countdownInterval);
    });

    return {
      userStore,
      isNotHome,
      isNotModification,
      secondsRemaining,
      showCountdown
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


.icons-container {
  display: flex;
  align-items: center;
  gap: clamp(8px, 2vw, 15px);
  flex-shrink: 0;
}


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

.countdown-alert {
  background-color: #e74c3c;
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  margin-left: 10px;
  animation: pulse 1s infinite alternate;
  font-weight: bold;
  flex-shrink: 0;
}

@keyframes pulse {
  from {
    opacity: 0.8;
  }
  to {
    opacity: 1;
  }
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
@media (max-width: 600px) {
  h1 {
    font-size: 0.9rem;
  }
  
  .countdown-alert {
    font-size: 0.7rem;
    padding: 2px 6px;
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
  
  .countdown-alert {
    font-size: 0.65rem;
    padding: 2px 4px;
    margin-left: 5px;
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
  
  .countdown-alert {
    font-size: 0.6rem;
    padding: 1px 3px;
  }
}

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