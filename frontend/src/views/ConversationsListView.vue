<template>
  <div>
    <HeaderComponent />
    <div class="conversations-container">
      <h1>Mes conversations</h1>

      <div v-if="initialLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Chargement des conversations...</p>
      </div>

      <div v-if="silentLoading" class="refresh-indicator">
        <div class="loading-spinner-small"></div>
      </div>

      <div v-if="error" class="error-container">
        <p class="error-message">{{ error }}</p>
        <button @click="loadConversations" class="retry-button">Réessayer</button>
      </div>

      <div v-if="!initialLoading && conversations.length === 0" class="empty-state">
        <div class="empty-icon">
          <font-awesome-icon :icon="['far', 'comments']" size="3x" />
        </div>
        <p>Vous n'avez pas encore de conversations</p>
      </div>

      <div v-if="conversations.length > 0" class="conversations-list">
        <div
          v-for="conversation in sortedConversations"
          :key="conversation.id"
          class="conversation-card"
          @click="openConversation(conversation)"
          v-memo="[
            conversation.id,
            conversation.lastMessage,
            conversation.last_message_timestamp
          ]"
        >
          <div class="conversation-avatar">
            <font-awesome-icon :icon="['fas', 'user']" size="lg" />
          </div>
          <div class="conversation-details">
            <div class="conversation-header">
              <h3 class="other-user">{{ getOtherUserName(conversation) }}</h3>
              <span class="last-time">{{ formatDate(conversation.last_message_timestamp) }}</span>
            </div>
            <p v-if="conversation.lastMessage" class="last-message">
              {{ truncateMessage(conversation.lastMessage) }}
            </p>
            <p v-else class="no-messages">Pas encore de messages</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore';
import { useConversations } from '@/composables/useConversations';

export default {
  name: 'ConversationsListView',
  components: {
    HeaderComponent
  },
  setup() {
    const router = useRouter();
    const userStore = useUserStore();
    const { conversations, error, fetchConversations, setCurrentConversation } = useConversations();

    const initialLoading = ref(true);
    const silentLoading = ref(false);
    const refreshInterval = ref(null);

    // Trier les conversations par date du dernier message (plus récent d'abord)
    const sortedConversations = computed(() => {
      return [...conversations.value].sort((a, b) => {
        const timestampA = a.last_message_timestamp || 0;
        const timestampB = b.last_message_timestamp || 0;
        return timestampB - timestampA;
      });
    });

    const loadConversations = async (silent = false) => {
      try {
        if (silent) {
          silentLoading.value = true;
        } else {
          initialLoading.value = true;
        }

        await fetchConversations(true); // Toujours inclure le dernier message
        console.log('Conversations loaded:', conversations.value);
      } catch (err) {
        console.error('Erreur lors de la récupération des conversations:', err);
      } finally {
        initialLoading.value = false;
        silentLoading.value = false;
      }
    };

    const startAutoRefresh = () => {
      stopAutoRefresh();
      refreshInterval.value = setInterval(() => {
        loadConversations(true);
      }, 15000); // Actualiser toutes les 15 secondes (plus réactif que 30s)
    };

    const stopAutoRefresh = () => {
      if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
      }
    };

    const getOtherUserName = (conversation) => {
      if (!conversation) return '';
      const currentUserLogin = userStore.user?.email;
      if (!currentUserLogin) return '';


      if (conversation.otherUser) {
        return conversation.otherUser;
      }
      if (conversation.user1Login === currentUserLogin) {
        return conversation.user2Login;
      } else if (conversation.user2Login === currentUserLogin) {
        return conversation.user1Login;
      } else {
        console.warn("L'utilisateur actuel n'est ni user1 ni user2 dans cette conversation:",
          { currentUser: currentUserLogin, user1: conversation.user1Login, user2: conversation.user2Login });
        return '';
      }
    };

    const openConversation = (conversation) => {
      setCurrentConversation(conversation);
      router.push(`/chat/${conversation.id}`);
    };

    const formatDate = (timestamp) => {
      if (!timestamp) return '';

      const date = new Date(timestamp * 1000);
      const now = new Date();
      const diff = now - date;
      const oneDay = 24 * 60 * 60 * 1000;

      if (diff < oneDay && date.getDate() === now.getDate()) {
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
      }


      if (diff < 2 * oneDay && date.getDate() === now.getDate() - 1) {
        return 'Hier';
      }


      if (diff < 7 * oneDay) {
        return date.toLocaleDateString('fr-FR', { weekday: 'long' });
      }


      return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
    };

    const truncateMessage = (message, maxLength = 50) => {
      if (!message) return '';

      const cleanedMessage = message.replace(/\s+/g, ' ').trim();
      if (cleanedMessage.length <= maxLength) return cleanedMessage;
      return cleanedMessage.substring(0, maxLength) + '...';
    };

    const handleWindowFocus = () => {
      console.log("Fenêtre a repris le focus, actualisation des conversations");
      loadConversations(true);
    };

    onMounted(() => {
      console.log('ConversationsListView mounted');
      if (conversations.value.length > 0) {
        initialLoading.value = false;
        loadConversations(true);
      } else {
        loadConversations(false);
      }


      startAutoRefresh();


      window.addEventListener('focus', handleWindowFocus);
    });

    onUnmounted(() => {
      stopAutoRefresh();
      window.removeEventListener('focus', handleWindowFocus);
    });

    return {
      conversations,
      sortedConversations,
      initialLoading,
      silentLoading,
      error,
      openConversation,
      loadConversations,
      formatDate,
      truncateMessage,
      getOtherUserName
    };
  }
};
</script>

<style scoped>
.conversations-container {
  max-width: 800px;
  margin: 0 auto;
  padding: clamp(10px, 3vw, 20px);
  min-height: calc(100vh - 70px);
  background-color: #f4f7f6;
  position: relative;
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: clamp(15px, 5vw, 30px);
  font-size: clamp(1.3rem, 4vw, 1.8rem);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: clamp(20px, 5vw, 40px) 0;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top-color: #3498db;
  animation: spin 1s ease-in-out infinite;
  margin-bottom: 15px;
}

.loading-spinner-small {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top-color: #3498db;
  animation: spin 1s ease-in-out infinite;
}

.refresh-indicator {
  position: absolute;
  top: 20px;
  right: 20px;
  background-color: white;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  text-align: center;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-top: 20px;
}

.error-message {
  color: #e74c3c;
  margin-bottom: 15px;
}

.retry-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.retry-button:hover {
  background-color: #2980b9;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: clamp(30px, 10vh, 60px) 0;
  text-align: center;
  color: #7f8c8d;
}

.empty-icon {
  color: #bdc3c7;
  margin-bottom: 20px;
}

.conversations-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.conversation-card {
  display: flex;
  align-items: center;
  padding: 15px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.conversation-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.conversation-avatar {
  width: clamp(40px, 10vw, 50px);
  height: clamp(40px, 10vw, 50px);
  background-color: #f1f2f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  color: #3498db;
  flex-shrink: 0;
}

.conversation-details {
  flex: 1;
  min-width: 0; /* Pour que text-overflow fonctionne */
}

.conversation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.other-user {
  font-size: clamp(0.9rem, 3vw, 1rem);
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 70%;
}

.last-time {
  font-size: clamp(0.7rem, 2vw, 0.8rem);
  color: #7f8c8d;
  white-space: nowrap;
}

.last-message, .no-messages {
  margin: 0;
  font-size: clamp(0.8rem, 2.5vw, 0.9rem);
  color: #7f8c8d;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.no-messages {
  font-style: italic;
  color: #95a5a6;
}

@media (max-width: 768px) {
  .conversations-container {
    padding: 15px;
  }

  .conversation-card {
    padding: 12px;
  }

  .conversation-avatar {
    margin-right: 10px;
  }
}

@media (max-width: 480px) {
  .conversation-card {
    padding: 10px;
  }

  .conversation-avatar {
    width: 36px;
    height: 36px;
  }

  .other-user {
    font-size: 0.85rem;
  }

  .last-message, .no-messages {
    font-size: 0.8rem;
  }
}

@media (max-width: 320px) {
  .conversation-avatar {
    width: 32px;
    height: 32px;
  }

  .other-user {
    max-width: 60%;
  }
}
</style>
