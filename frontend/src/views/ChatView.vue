<template>
  <div class="chat-page">
    <HeaderComponent />

    <div class="chat-container">
      <div class="chat-header">
        <button class="back-button" @click="goBack">
          <font-awesome-icon :icon="['fas', 'arrow-left']" />
        </button>
        <h2 v-if="currentConversation">{{ getOtherUserName(currentConversation) }}</h2>
        <div class="placeholder" aria-hidden="true"></div>
      </div>

      <div v-if="initialLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Chargement des messages...</p>
      </div>

      <div v-else-if="error" class="error-container">
        <p class="error-message">{{ error }}</p>
        <button @click="loadMessages(false)" class="retry-button">Réessayer</button>
      </div>

      <div v-else class="messages-container" ref="messagesContainer">
        <div v-if="!messages || messages.length === 0" class="empty-chat">
          <p>Aucun message dans cette conversation.</p>
          <p>Commencez à discuter maintenant!</p>
        </div>

        <div v-else class="messages-list">
          <div v-for="(message, index) in messages" :key="message?.id || index" :class="[
            'message',
            (messages[0]?.senderLogin === message?.senderLogin) ? 'sent' : 'received',
            (message?.id === messages[0]?.id) ? 'first-message' : '',
          ]">
            <div class="message-content">
              <p>{{ message?.content || '' }}</p>
              <span class="message-time">{{ formatTime(message?.timestamp) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="message-form">
        <textarea v-model="newMessage" placeholder="Écrivez votre message..." @keyup.enter="handleEnter"
          class="message-input" :disabled="sendingMessage"></textarea>
        <button @click="sendMessageHandler" class="send-button" :disabled="!newMessage.trim() || sendingMessage">
          <font-awesome-icon v-if="!sendingMessage" :icon="['fas', 'paper-plane']" />
          <font-awesome-icon v-else :icon="['fas', 'circle-notch']" spin />
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, nextTick, watch, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import HeaderComponent from '@/components/HeaderComponent.vue';
import { useUserStore } from '@/stores/userStore';
import { useConversations } from '@/composables/useConversations';

export default {
  name: 'ChatView',
  components: {
    HeaderComponent
  },
  setup() {
    console.log("ChatView setup init");
    const route = useRoute();
    const router = useRouter();
    const userStore = useUserStore();
    const {
      messages,
      currentConversation,
      loading,
      error,
      fetchMessages,
      fetchConversations,
      sendMessage,
      addOptimisticMessage,
      setCurrentConversation,
      refreshMessages
    } = useConversations();

    // Références et états locaux
    const messagesContainer = ref(null);
    const conversationId = computed(() => route.params.id);
    const newMessage = ref('');
    const initialLoading = ref(true);
    const silentLoading = ref(false);
    const sendingMessage = ref(false);
    const pollingInterval = ref(null);

    // Valider les messages pour éviter les erreurs
    const validateMessages = () => {
      console.log("Validation des messages:", messages.value);
      if (!Array.isArray(messages.value)) {
        console.warn('Messages n\'est pas un tableau:', messages.value);
        messages.value = []; // Initialiser comme un tableau vide
      }
    };

    // Récupérer les messages
    const loadMessages = async (silent = false) => {
      try {
        if (!conversationId.value) {
          console.error("ID de conversation manquant");
          error.value = "ID de conversation invalide";
          initialLoading.value = false;
          return;
        }

        if (silent) {
          silentLoading.value = true;
        } else {
          initialLoading.value = true;
        }

        console.log(`Chargement des messages pour la conversation ${conversationId.value}`);
        await fetchMessages(conversationId.value);
        validateMessages();

        if (!silent) {
          scrollToBottom();
        }
      } catch (err) {
        console.error('Erreur lors de la récupération des messages:', err);
      } finally {
        initialLoading.value = false;
        silentLoading.value = false;
      }
    };

    // Polling sécurisé pour les nouveaux messages
    const startPolling = () => {
      // Arrêter d'abord tout polling existant
      stopPolling();

      // Créer un nouvel intervalle (toutes les 8 secondes pour éviter les requêtes trop fréquentes)
      pollingInterval.value = setInterval(() => {
        if (conversationId.value) {
          refreshMessages(conversationId.value);
        }
      }, 8000);

      console.log("Polling démarré");
    };

    const stopPolling = () => {
      if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
        console.log("Polling arrêté");
      }
    };

    const getOtherUserName = (conversation) => {
      if (!conversation) return '';
      const currentUserLogin = userStore.user?.login;
      if (!currentUserLogin) return conversation.user1Login || '';

      return conversation.user1Login === currentUserLogin
        ? conversation.user2Login
        : conversation.user1Login;
    };

    const formatTime = (timestamp) => {
      if (!timestamp) return '';

      try {
        const date = new Date(timestamp * 1000);
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
      } catch (e) {
        console.error("Erreur de formatage de l'heure:", e);
        return '';
      }
    };

    const scrollToBottom = () => {
      nextTick(() => {
        if (messagesContainer.value) {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
      });
    };

    const handleEnter = (event) => {
      if (!event.shiftKey) {
        event.preventDefault();
        sendMessageHandler();
      }
    };

    const sendMessageHandler = async () => {
      if (!newMessage.value.trim() || sendingMessage.value) return;
      if (!conversationId.value) {
        console.error("Impossible d'envoyer le message: ID de conversation manquant");
        return;
      }

      // Optimistic update - ajouter immédiatement un message temporaire
      const tempMessage = {
        id: 'temp-' + Date.now(),
        senderLogin: userStore.user?.login,
        receiverLogin: getOtherUserName(currentConversation.value),
        content: newMessage.value,
        timestamp: Math.floor(Date.now() / 1000),
        isTemp: true,
        conversationId: conversationId.value
      };

      // Effacer l'input avant d'envoyer
      const messageToSend = newMessage.value;
      newMessage.value = '';

      try {
        // Ajouter temporairement le message à la liste
        addOptimisticMessage(tempMessage);
        scrollToBottom();

        sendingMessage.value = true;
        await sendMessage(conversationId.value, messageToSend);
      } catch (err) {
        console.error('Erreur lors de l\'envoi du message:', err);
      } finally {
        sendingMessage.value = false;
        scrollToBottom();
      }
    };

    const goBack = () => {
      router.push('/conversations');
    };

    // Observer les changements dans les messages pour auto-scroller
    watch(messages, () => {
      scrollToBottom();
    });

    onMounted(async () => {
      console.log("ChatView mounted");

      try {
        if (!currentConversation.value) {
          // Si on accède directement à la page de chat sans passer par la liste
          console.log("Pas de conversation courante, chargement initial");

          try {
            const conversations = await fetchConversations(false);
            console.log(`${conversations.length} conversations récupérées`);

            const conversation = conversations.find(c => c.id === conversationId.value);
            if (conversation) {
              console.log("Conversation trouvée:", conversation.id);
              setCurrentConversation(conversation);
            } else {
              console.warn("Conversation non trouvée dans la liste:", conversationId.value);
            }
          } catch (err) {
            console.error('Erreur lors de la récupération des conversations:', err);
          }
        } else {
          console.log("Conversation courante déjà définie:", currentConversation.value.id);
        }

        await loadMessages(false);
        startPolling();
      } catch (e) {
        console.error("Erreur lors de l'initialisation:", e);
      }
    });

    onUnmounted(() => {
      console.log("ChatView unmounted");
      stopPolling();
    });

    return {
      conversationId,
      messages,
      newMessage,
      initialLoading,
      silentLoading,
      sendingMessage,
      error,
      messagesContainer,
      currentConversation,
      userStore,
      sendMessageHandler,
      handleEnter,
      formatTime,
      getOtherUserName,
      goBack,
      loadMessages
    };
  }
};
</script>

<style scoped>
.chat-page {
  min-height: 100vh;
  background-color: #f4f7f6;
}

.chat-container {
  max-width: 800px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  height: calc(100vh - 70px);
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 20px;
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.chat-header h2 {
  margin: 0;
  font-size: 1.2rem;
  color: #2c3e50;
}

.back-button {
  background: none;
  border: none;
  cursor: pointer;
  color: #3498db;
  font-size: 1.2rem;
  padding: 5px;
}

.placeholder {
  width: 24px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  background-color: white;
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
  to {
    transform: rotate(360deg);
  }
}

.error-container {
  text-align: center;
  padding: 30px;
  background-color: white;
  flex: 1;
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

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background-color: white;
}

.empty-chat {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #95a5a6;
  text-align: center;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.message {
  display: flex;
  margin-bottom: 10px;
}

.message-content {
  max-width: 70%;
  padding: 10px 15px;
  border-radius: 18px;
  position: relative;
}

/* Correction pour les styles des messages */

/* Aligner les messages à gauche/droite */
/* Style pour le premier message qui sera placé à droite */
.message.first-message {
  justify-content: flex-end;
}

.message.first-message .message-content {
  background-color: #3498db;
  /* Bleu pour l'expéditeur du premier message */
  color: white;
  border-bottom-right-radius: 4px;
}

/* Les autres messages restent à gauche, mais peuvent être personnalisés pour les autres utilisateurs */
.message.received {
  justify-content: flex-start;
}

.message.received .message-content {
  background-color: #f1f2f6;
  color: #2c3e50;
  border-bottom-left-radius: 4px;
}

/* Style du contenu des messages */
.sent .message-content {
  background-color: #3498db;
  color: white;
  border-bottom-right-radius: 4px;
}

.received .message-content {
  background-color: #f1f2f6;
  color: #2c3e50;
  border-bottom-left-radius: 4px;
}

.message-content p {
  margin: 0;
  word-wrap: break-word;
}

.message-time {
  display: block;
  font-size: 0.7rem;
  margin-top: 5px;
  text-align: right;
  opacity: 0.8;
}

.message-form {
  display: flex;
  padding: 15px;
  background-color: white;
  border-top: 1px solid #e5e5e5;
}

.message-input {
  flex: 1;
  border: 1px solid #e5e5e5;
  border-radius: 20px;
  padding: 10px 15px;
  resize: none;
  max-height: 100px;
  font-family: inherit;
}

.message-input:focus {
  outline: none;
  border-color: #3498db;
}

.received {
  background-color: #f1f2f6;
  color: #2c3e50;
  border-bottom-left-radius: 4px;
}

.received-mine {
  background-color: #d1e7dd;
  /* Vert clair pour tes propres messages reçus */
  color: #155724;
  border-bottom-left-radius: 4px;
}

.send-button {
  background-color: #3498db;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-left: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s;
}

.send-button:disabled {
  background-color: #bdc3c7;
  cursor: not-allowed;
}

.send-button:not(:disabled):hover {
  background-color: #2980b9;
}

@media (max-width: 768px) {
  .chat-container {
    height: calc(100vh - 70px);
  }

  .message-content {
    max-width: 85%;
  }
}
</style>
