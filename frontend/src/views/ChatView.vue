<template>
  <div class="chat-page">
    <HeaderComponent />

    <div class="chat-container">
      <div class="chat-header">
        <button class="back-button" @click="goBack">
          <font-awesome-icon :icon="['fas', 'arrow-left']" />
        </button>
        <h2 v-if="currentConversation">{{ getOtherUserName(currentConversation) }}</h2>
        <div class="polling-indicator" v-if="pollingActive">
          <font-awesome-icon :icon="['fas', 'sync']" class="polling-icon" :class="{ active: isPolling }" />
        </div>
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
          <div
            v-for="(message, index) in messages"
            :key="message?.id || index"
            class="message"
            :class="[isMyMessage(message) ? 'my-message' : 'other-message']"
          >
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
      refreshMessages,
      pollingActive
    } = useConversations();

    // Références et états locaux
    const messagesContainer = ref(null);
    const conversationId = computed(() => route.params.id);
    const newMessage = ref('');
    const initialLoading = ref(true);
    const silentLoading = ref(false);
    const sendingMessage = ref(false);
    const pollingInterval = ref(null);
    const isPolling = ref(false);


    const isMyMessage = (message) => {

      if (message?.isTemp) {
        return true;
      }


      if (message?.isMine !== undefined) {
        return message.isMine;
      }


      return message?.senderLogin === userStore.user?.email;
    };


    const validateMessages = () => {

      if (!Array.isArray(messages.value)) {
        console.warn('Messages n\'est pas un tableau:', messages.value);
        messages.value = [];
      }
    };


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


    const startPolling = () => {

      stopPolling();
      pollingActive.value = true;

      pollingInterval.value = setInterval(async () => {
        if (conversationId.value) {
          try {
            isPolling.value = true;
            const hasNewMessages = await refreshMessages(conversationId.value);

            if (hasNewMessages) {
              scrollToBottom();
            }

            setTimeout(() => {
              isPolling.value = false;
            }, 1000);
          } catch (error) {
            isPolling.value = false;
          }
        }
      }, 5000);
    };

    const stopPolling = () => {
      if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
        pollingActive.value = false;
        isPolling.value = false;
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

      const otherUserLogin = getOtherUserName(currentConversation.value);
      const tempMessage = {
        id: 'temp-' + Date.now(),
        senderLogin: userStore.user?.email,
        receiverLogin: otherUserLogin,
        content: newMessage.value,
        timestamp: Math.floor(Date.now() / 1000),
        isTemp: true,
        conversationId: conversationId.value,
        isMine: true
      };


      const messageToSend = newMessage.value;
      newMessage.value = '';

      try {
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


    watch(messages, () => {
      scrollToBottom();
    });


    let forceRefreshInterval = null;
    const startForceRefresh = () => {
      forceRefreshInterval = setInterval(async () => {
        if (conversationId.value) {
          await loadMessages(true);
        }
      }, 60000); // Toutes les minutes
    };

    const stopForceRefresh = () => {
      if (forceRefreshInterval) {
        clearInterval(forceRefreshInterval);
        forceRefreshInterval = null;
      }
    };


    const handleWindowFocus = async () => {
      if (conversationId.value) {
        await loadMessages(true);
      }
    };

    onMounted(async () => {
      try {
        if (!currentConversation.value || currentConversation.value.id !== conversationId.value) {
          try {
            const conversations = await fetchConversations(false);

            const conversation = conversations.find(c => c.id === conversationId.value);
            if (conversation) {
              setCurrentConversation(conversation);
            } else {
              console.warn("Conversation non trouvée dans la liste:", conversationId.value);
              error.value = "Conversation introuvable";
            }
          } catch (err) {
            console.error('Erreur lors de la récupération des conversations:', err);
            error.value = "Erreur lors du chargement des conversations";
          }
        }
        await loadMessages(false);
        startPolling();
        startForceRefresh();

        window.addEventListener('focus', handleWindowFocus);
      } catch (e) {
        error.value = "Erreur d'initialisation";
      }
    });

    onUnmounted(() => {
      stopPolling();
      stopForceRefresh();
      window.removeEventListener('focus', handleWindowFocus);
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
      isMyMessage,
      goBack,
      loadMessages,
      pollingActive,
      isPolling
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

.polling-indicator {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.polling-icon {
  color: #bdc3c7;
  font-size: 1rem;
}

.polling-icon.active {
  color: #3498db;
  animation: spin 1s linear infinite;
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
  to { transform: rotate(360deg); }
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

/* Message envoyé par moi */
.my-message {
  justify-content: flex-end;
}

.my-message .message-content {
  background-color: #3498db;
  color: white;
  border-radius: 18px;
  border-bottom-right-radius: 4px;
}

/* Message envoyé par l'autre personne */
.other-message {
  justify-content: flex-start;
}

.other-message .message-content {
  background-color: #f1f2f6;
  color: #2c3e50;
  border-radius: 18px;
  border-bottom-left-radius: 4px;
}

.message-content {
  max-width: 70%;
  padding: 10px 15px;
  position: relative;
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

@media (max-width: 480px) {
  .chat-header h2 {
    font-size: 1rem;
  }

  .messages-container {
    padding: 15px 10px;
  }

  .message-form {
    padding: 10px;
  }

  .send-button {
    width: 36px;
    height: 36px;
  }
}
</style>
