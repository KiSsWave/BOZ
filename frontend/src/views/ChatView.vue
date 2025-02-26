<template>
  <div class="chat-page">
    <HeaderComponent />

    <div class="chat-container">
      <!-- En-tête du chat -->
      <div class="chat-header">
        <button class="back-button" @click="goBack">
          <font-awesome-icon :icon="['fas', 'arrow-left']" />
        </button>
        <div v-if="otherUser" class="chat-user">
          <div class="user-avatar">
            <font-awesome-icon :icon="['fas', 'user']" />
          </div>
          <div class="user-info">
            <h2>{{ otherUser }}</h2>
          </div>
        </div>
        <div v-else class="placeholder-user"></div>
      </div>

      <!-- Corps du chat -->
      <div v-if="loading" class="chat-loading">
        <div class="loading-spinner"></div>
        <p>Chargement des messages...</p>
      </div>

      <div v-else-if="error" class="chat-error">
        <p>{{ error }}</p>
        <button @click="fetchMessages" class="retry-button">Réessayer</button>
      </div>

      <div v-else class="chat-messages" ref="messagesContainer">
        <div v-if="messages.length === 0" class="no-messages">
          <p>Aucun message. Commencez la conversation !</p>
        </div>

        <template v-else>
          <div
            v-for="(message, index) in messages"
            :key="message.id"
            :class="['message-wrapper', message.isMine ? 'my-message' : 'their-message']"
          >
            <!-- Afficher la date si c'est un nouveau jour ou le premier message -->
            <div
              v-if="shouldShowDate(message, index)"
              class="date-separator"
            >
              {{ formatDayDate(message.timestamp) }}
            </div>

            <div class="message">
              <div class="message-content">{{ message.content }}</div>
              <div class="message-time">{{ formatTime(message.timestamp) }}</div>
              <div v-if="message.isMine" class="message-status">
                <font-awesome-icon
                  :icon="['fas', message.read ? 'check-double' : 'check']"
                  :class="{ 'read': message.read }"
                />
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Formulaire d'envoi de messages -->
      <div class="chat-input">
        <form @submit.prevent="sendMessage">
          <div class="input-container">
            <textarea
              v-model="newMessage"
              placeholder="Écrivez votre message..."
              @keydown.enter.prevent="sendMessage"
              ref="messageInput"
              :disabled="sending"
            ></textarea>
            <button type="submit" class="send-button" :disabled="!newMessage.trim() || sending">
              <font-awesome-icon v-if="sending" :icon="['fas', 'spinner']" spin />
              <font-awesome-icon v-else :icon="['fas', 'paper-plane']" />
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, nextTick, watch, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import HeaderComponent from '@/components/HeaderComponent.vue';
import { useUserStore } from '@/stores/userStore';
import { useAppStore } from '@/stores/appStore';

export default {
  name: 'ChatView',
  components: {
    HeaderComponent
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const userStore = useUserStore();
    const appStore = useAppStore();

    // Refs locaux
    const conversationId = computed(() => route.params.id);
    const newMessage = ref('');
    const sending = ref(false);
    const messagesContainer = ref(null);
    const messageInput = ref(null);
    const otherUser = ref('');
    const loading = ref(true);
    const error = ref(null);

    // Messages actuels de cette conversation
    const messages = computed(() => {
      return appStore.getMessagesByConversationId(conversationId.value) || [];
    });

    // Définir le polling pour rafraîchir les messages
    let pollingInterval = null;

    const fetchMessages = async () => {
      if (!conversationId.value) return;

      try {
        loading.value = true;
        error.value = null;

        await appStore.fetchMessages(conversationId.value);

        // Déterminer l'autre utilisateur
        if (messages.value.length > 0) {
          const firstMessage = messages.value[0];
          otherUser.value = firstMessage.senderLogin === userStore.user.login
            ? firstMessage.receiverLogin
            : firstMessage.senderLogin;
        } else {
          const conversation = appStore.getConversationById(conversationId.value);
          if (conversation) {
            otherUser.value = conversation.user1Login === userStore.user.login
              ? conversation.user2Login
              : conversation.user1Login;
          }
        }

        // Défiler vers le bas après le chargement des messages
        await nextTick();
        scrollToBottom();
      } catch (err) {
        console.error('Erreur lors de la récupération des messages:', err);
        error.value = 'Impossible de charger les messages. Veuillez réessayer plus tard.';
      } finally {
        loading.value = false;
      }
    };

    const sendMessage = async () => {
      if (!newMessage.value.trim() || sending.value) return;

      try {
        sending.value = true;

        await appStore.sendMessage(conversationId.value, newMessage.value);

        // Vider le champ de message
        newMessage.value = '';

        // Focus sur le champ d'entrée
        messageInput.value.focus();

        // Défiler vers le bas
        await nextTick();
        scrollToBottom();
      } catch (err) {
        console.error('Erreur lors de l\'envoi du message:', err);
        alert('Erreur lors de l\'envoi du message. Veuillez réessayer.');
      } finally {
        sending.value = false;
      }
    };

    const scrollToBottom = () => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      }
    };

    const formatTime = (timestamp) => {
      if (!timestamp) return '';
      const date = new Date(timestamp * 1000);
      return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    };

    const formatDayDate = (timestamp) => {
      if (!timestamp) return '';

      const date = new Date(timestamp * 1000);
      const today = new Date();
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);

      // Si c'est aujourd'hui
      if (date.toDateString() === today.toDateString()) {
        return 'Aujourd\'hui';
      }

      // Si c'est hier
      if (date.toDateString() === yesterday.toDateString()) {
        return 'Hier';
      }

      // Sinon, afficher la date complète
      return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
    };

    const shouldShowDate = (message, index) => {
      if (index === 0) return true;

      const currentDate = new Date(message.timestamp * 1000).toDateString();
      const previousDate = new Date(messages.value[index - 1].timestamp * 1000).toDateString();

      return currentDate !== previousDate;
    };

    const goBack = () => {
      router.push('/conversations');
    };

    // Configurer le polling pour rafraîchir les messages
    const setupPolling = () => {
      // Nettoyer tout intervalle existant
      if (pollingInterval) {
        clearInterval(pollingInterval);
      }

      // Configurer un nouveau polling toutes les 10 secondes
      pollingInterval = setInterval(async () => {
        if (!loading.value && !error.value) {
          await appStore.fetchMessages(conversationId.value, false);
        }
      }, 10000); // 10 secondes
    };

    // Surveiller les changements d'ID de conversation
    watch(conversationId, () => {
      fetchMessages();
    });

    onMounted(() => {
      fetchMessages();
      setupPolling();

      // Focus sur le champ d'entrée
      nextTick(() => {
        if (messageInput.value) {
          messageInput.value.focus();
        }
      });
    });

    // Nettoyer le polling lors de la destruction du composant
    onBeforeUnmount(() => {
      if (pollingInterval) {
        clearInterval(pollingInterval);
      }
    });

    return {
      conversationId,
      messages,
      loading,
      error,
      newMessage,
      sending,
      messagesContainer,
      messageInput,
      otherUser,
      fetchMessages,
      sendMessage,
      formatTime,
      formatDayDate,
      shouldShowDate,
      goBack
    };
  }
};
</script>

<style scoped>
.chat-page {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.chat-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  max-width: 800px;
  margin: 0 auto;
  width: 100%;
  background-color: #f4f7f6;
  position: relative;
}

.chat-header {
  display: flex;
  align-items: center;
  padding: 15px;
  background-color: white;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-button {
  background: none;
  border: none;
  color: #3498db;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 5px;
  margin-right: 15px;
}

.chat-user {
  display: flex;
  align-items: center;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #f1f2f6;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 10px;
  color: #3498db;
}

.user-info h2 {
  margin: 0;
  font-size: 1.1rem;
  color: #2c3e50;
}

.placeholder-user {
  height: 40px;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.chat-loading, .chat-error, .no-messages {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px 20px;
  text-align: center;
  color: #7f8c8d;
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

.retry-button {
  margin-top: 15px;
  background-color: #3498db;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.message-wrapper {
  display: flex;
  flex-direction: column;
  max-width: 80%;
}

.my-message {
  align-self: flex-end;
}

.their-message {
  align-self: flex-start;
}

.date-separator {
  align-self: center;
  margin: 10px 0;
  padding: 5px 10px;
  background-color: rgba(0, 0, 0, 0.05);
  border-radius: 15px;
  font-size: 0.8rem;
  color: #7f8c8d;
}

.message {
  position: relative;
  padding: 10px 15px;
  border-radius: 18px;
  margin-bottom: 2px;
  word-break: break-word;
}

.my-message .message {
  background-color: #3498db;
  color: white;
  border-bottom-right-radius: 4px;
}

.their-message .message {
  background-color: white;
  color: #2c3e50;
  border-bottom-left-radius: 4px;
}

.message-content {
  margin-bottom: 15px;
}

.message-time {
  position: absolute;
  bottom: 5px;
  right: 10px;
  font-size: 0.7rem;
  opacity: 0.8;
}

.my-message .message-time {
  color: rgba(255, 255, 255, 0.8);
}

.their-message .message-time {
  color: #7f8c8d;
}

.message-status {
  position: absolute;
  bottom: 5px;
  left: 10px;
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.8);
}

.message-status .read {
  color: #2ecc71;
}

.chat-input {
  padding: 15px;
  background-color: white;
  border-top: 1px solid #eaeaea;
}

.input-container {
  display: flex;
  align-items: center;
  background-color: #f9f9f9;
  border-radius: 20px;
  padding: 0 15px;
}

textarea {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  padding: 12px 0;
  resize: none;
  max-height: 100px;
  font-family: inherit;
  font-size: 1rem;
  line-height: 1.5;
}

.send-button {
  background: none;
  border: none;
  color: #3498db;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.send-button:hover:not(:disabled) {
  color: #2980b9;
}

.send-button:disabled {
  color: #95a5a6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .chat-container {
    max-width: 100%;
  }

  .message-wrapper {
    max-width: 90%;
  }
}
</style>
