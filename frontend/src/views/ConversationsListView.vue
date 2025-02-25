<template>
  <div>
    <HeaderComponent />
    <div class="conversations-container">
      <h1>Mes conversations</h1>

      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Chargement des conversations...</p>
      </div>

      <div v-else-if="error" class="error-container">
        <p class="error-message">{{ error }}</p>
        <button @click="fetchConversations" class="retry-button">Réessayer</button>
      </div>

      <div v-else-if="conversations.length === 0" class="empty-state">
        <div class="empty-icon">
          <font-awesome-icon :icon="['far', 'comments']" size="3x" />
        </div>
        <p>Vous n'avez pas encore de conversations</p>
      </div>

      <div v-else class="conversations-list">
        <div
          v-for="conversation in conversations"
          :key="conversation.id"
          class="conversation-card"
          @click="openConversation(conversation.id)"
        >
          <div class="conversation-avatar">
            <font-awesome-icon :icon="['fas', 'user']" size="lg" />
          </div>
          <div class="conversation-details">
            <div class="conversation-header">
              <h3 class="other-user">{{ conversation.otherUser }}</h3>
              <span class="last-time">{{ formatDate(conversation.lastMessageTimestamp) }}</span>
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
import axios from '@/api/index.js';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'ConversationsListView',
  components: {
    HeaderComponent
  },
  setup() {
    const conversations = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const router = useRouter();

    const fetchConversations = async () => {
      loading.value = true;
      error.value = null;

      try {
        const response = await axios.get('/conversations');
        conversations.value = response.data.conversations || [];

        // Récupérer le dernier message pour chaque conversation
        for (const conversation of conversations.value) {
          try {
            const messagesResponse = await axios.get(`/conversations/${conversation.id}/messages`);
            const messages = messagesResponse.data.messages || [];
            if (messages.length > 0) {
              conversation.lastMessage = messages[messages.length - 1].content;
            }
          } catch (err) {
            console.error(`Erreur lors de la récupération des messages pour la conversation ${conversation.id}:`, err);
          }
        }
      } catch (err) {
        console.error('Erreur lors de la récupération des conversations:', err);
        error.value = 'Impossible de charger vos conversations. Veuillez réessayer plus tard.';
      } finally {
        loading.value = false;
      }
    };

    const openConversation = (conversationId) => {
      router.push(`/chat/${conversationId}`);
    };

    const formatDate = (timestamp) => {
      if (!timestamp) return '';

      const date = new Date(timestamp * 1000);
      const now = new Date();
      const diff = now - date;
      const oneDay = 24 * 60 * 60 * 1000;

      // Si c'est aujourd'hui, afficher l'heure
      if (diff < oneDay && date.getDate() === now.getDate()) {
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
      }

      // Si c'est cette semaine, afficher le jour
      if (diff < 7 * oneDay) {
        return date.toLocaleDateString('fr-FR', { weekday: 'long' });
      }

      // Sinon afficher la date complète
      return date.toLocaleDateString('fr-FR');
    };

    const truncateMessage = (message, maxLength = 50) => {
      if (!message) return '';
      if (message.length <= maxLength) return message;
      return message.substring(0, maxLength) + '...';
    };

    onMounted(fetchConversations);

    return {
      conversations,
      loading,
      error,
      openConversation,
      fetchConversations,
      formatDate,
      truncateMessage
    };
  }
};
</script>

<style scoped>
.conversations-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  min-height: calc(100vh - 70px);
  background-color: #f4f7f6;
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 30px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
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

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  text-align: center;
  padding: 30px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
  padding: 60px 0;
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
  width: 50px;
  height: 50px;
  background-color: #f1f2f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  color: #3498db;
}

.conversation-details {
  flex: 1;
}

.conversation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.other-user {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.last-time {
  font-size: 0.8rem;
  color: #7f8c8d;
}

.last-message, .no-messages {
  margin: 0;
  font-size: 0.9rem;
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
    width: 40px;
    height: 40px;
  }

  .other-user {
    font-size: 0.9rem;
  }
}
</style>
