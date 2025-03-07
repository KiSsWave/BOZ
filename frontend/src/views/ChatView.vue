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

    // Vérifier si c'est mon message
    const isMyMessage = (message) => {
      // Si c'est un message temporaire, il est forcément de moi
      if (message?.isTemp) {
        return true;
      }

      // Si l'API a ajouté une propriété isMine, on l'utilise
      if (message?.isMine !== undefined) {
        return message.isMine;
      }

      // Sinon on compare l'expéditeur avec mon login
      return message?.senderLogin === userStore.user?.login;
    };

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

        // Débogage
        if (messages.value && messages.value.length > 0) {
          console.log(`${messages.value.length} messages chargés`);
        }

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

    // Polling amélioré pour les nouveaux messages
    const startPolling = () => {
      // Arrêter d'abord tout polling existant
      stopPolling();

      pollingActive.value = true;

      // Créer un nouvel intervalle avec une fonction asynchrone
      pollingInterval.value = setInterval(async () => {
        if (conversationId.value) {
          try {
            isPolling.value = true;
            console.log("Polling: Vérification des nouveaux messages...");
            const hasNewMessages = await refreshMessages(conversationId.value);

            if (hasNewMessages) {
              console.log("Polling: Nouveaux messages détectés !");
              scrollToBottom();
            }

            setTimeout(() => {
              isPolling.value = false;
            }, 1000);
          } catch (error) {
            console.error("Erreur lors du polling des messages:", error);
            isPolling.value = false;
          }
        }
      }, 5000); // 5 secondes pour une bonne réactivité

      console.log("Polling démarré avec intervalle de 5 secondes");
    };

    const stopPolling = () => {
      if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
        pollingActive.value = false;
        isPolling.value = false;
        console.log("Polling arrêté");
      }
    };

    const getOtherUserName = (conversation) => {
      if (!conversation) return '';

      // Récupérer le login de l'utilisateur actuel
      const currentUserLogin = userStore.user?.email;

      // Si pas de login utilisateur, retourner une valeur par défaut
      if (!currentUserLogin) return '';

      // Si la propriété otherUser existe déjà (format API simplifiée)
      if (conversation.otherUser) {
        return conversation.otherUser;
      }

      // Vérifier explicitement si l'utilisateur actuel est user1 ou user2
      if (conversation.user1Login === currentUserLogin) {
        return conversation.user2Login; // Si je suis user1, afficher user2
      } else if (conversation.user2Login === currentUserLogin) {
        return conversation.user1Login; // Si je suis user2, afficher user1
      } else {
        console.warn("L'utilisateur actuel n'est ni user1 ni user2 dans cette conversation:",
          { currentUser: currentUserLogin, user1: conversation.user1Login, user2: conversation.user2Login });
        return ''; // Cas anormal
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

      // Obtenir le destinataire
      const otherUserLogin = getOtherUserName(currentConversation.value);

      // Optimistic update - ajouter immédiatement un message temporaire
      const tempMessage = {
        id: 'temp-' + Date.now(),
        senderLogin: userStore.user?.login,
        receiverLogin: otherUserLogin,
        content: newMessage.value,
        timestamp: Math.floor(Date.now() / 1000),
        isTemp: true,
        conversationId: conversationId.value,
        isMine: true
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

    // Forcer une actualisation toutes les minutes pour éviter les problèmes de session
    let forceRefreshInterval = null;
    const startForceRefresh = () => {
      forceRefreshInterval = setInterval(async () => {
        if (conversationId.value) {
          console.log("Actualisation forcée des messages");
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

    // Quand l'utilisateur revient sur l'onglet, on actualise immédiatement
    const handleWindowFocus = async () => {
      console.log("Fenêtre a repris le focus, actualisation des messages");
      if (conversationId.value) {
        await loadMessages(true);
      }
    };

    onMounted(async () => {
      console.log("ChatView mounted");
      console.log("User actuel:", userStore.user?.login);

      try {
        if (!currentConversation.value || currentConversation.value.id !== conversationId.value) {
          // Si on accède directement à la page de chat sans passer par la liste
          console.log("Pas de conversation courante ou ID différent, chargement initial");

          try {
            const conversations = await fetchConversations(false);
            console.log(`${conversations.length} conversations récupérées`);

            const conversation = conversations.find(c => c.id === conversationId.value);
            if (conversation) {
              console.log("Conversation trouvée:", conversation.id);
              console.log("Détails conversation:", {
                user1: conversation.user1Login,
                user2: conversation.user2Login,
                currentUser: userStore.user?.login
              });
              setCurrentConversation(conversation);
              console.log("Nom de l'autre utilisateur:", getOtherUserName(conversation));
            } else {
              console.warn("Conversation non trouvée dans la liste:", conversationId.value);
              error.value = "Conversation introuvable";
            }
          } catch (err) {
            console.error('Erreur lors de la récupération des conversations:', err);
            error.value = "Erreur lors du chargement des conversations";
          }
        } else {
          console.log("Conversation courante déjà définie:", currentConversation.value.id);
          console.log("Détails conversation:", {
            user1: currentConversation.value.user1Login,
            user2: currentConversation.value.user2Login,
            currentUser: userStore.user?.login
          });
          console.log("Nom de l'autre utilisateur:", getOtherUserName(currentConversation.value));
        }

        await loadMessages(false);
        startPolling();
        startForceRefresh();

        // Ajouter un gestionnaire d'événements pour le focus de la fenêtre
        window.addEventListener('focus', handleWindowFocus);
      } catch (e) {
        console.error("Erreur lors de l'initialisation:", e);
        error.value = "Erreur d'initialisation";
      }
    });

    onUnmounted(() => {
      console.log("ChatView unmounted");
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
