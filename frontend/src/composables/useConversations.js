import { ref, reactive, computed } from 'vue';
import axios from '@/api/index.js';

export function useConversations() {
  // État des conversations
  const conversations = ref([]);
  const currentConversation = ref(null);
  const messages = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const lastFetch = ref(null);

  // Récupérer les conversations de l'utilisateur
  const fetchConversations = async (withLastMessage = false, forceRefresh = false) => {
    // Éviter les requêtes multiples en parallèle
    if (loading.value) return conversations.value;

    // Utiliser le cache si la dernière requête est récente (moins de 30 secondes)
    const now = Date.now();
    const cacheExpiration = 30 * 1000; // 30 secondes

    if (!forceRefresh &&
      lastFetch.value &&
      now - lastFetch.value < cacheExpiration &&
      conversations.value.length > 0) {
      return conversations.value;
    }

    loading.value = true;
    error.value = null;

    try {
      // Utiliser un paramètre pour demander les derniers messages directement
      const url = withLastMessage ? '/conversations?include_last_message=true' : '/conversations';
      const response = await axios.get(url);

      // Vérifier la structure de la réponse et adapter si nécessaire
      let conversationsData = response.data.conversations || [];

      // Format check and conversion if needed (in case backend structure doesn't change)
      conversationsData = conversationsData.map(conv => {
        // If the API returns otherUser instead of user1Login and user2Login
        if (conv.otherUser && !conv.user1Login && !conv.user2Login) {
          const currentUserLogin = localStorage.getItem('userLogin') || '';
          return {
            ...conv,
            user1Login: currentUserLogin,
            user2Login: conv.otherUser
          };
        }
        return conv;
      });

      conversations.value = conversationsData;
      lastFetch.value = now;

      return conversations.value;
    } catch (err) {
      console.error('Erreur lors de la récupération des conversations:', err);
      error.value = 'Impossible de charger vos conversations. Veuillez réessayer plus tard.';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // Récupérer les messages d'une conversation
  const fetchMessages = async (conversationId) => {
    if (!conversationId) {
      error.value = 'ID de conversation manquant';
      return [];
    }

    // Si déjà en chargement, éviter les requêtes parallèles
    if (loading.value) return messages.value;

    loading.value = true;
    error.value = null;

    try {
      console.log(`Récupération des messages pour la conversation ${conversationId}`);
      const response = await axios.get(`/conversations/${conversationId}/messages`);
      console.log('Réponse reçue:', response.data);

      messages.value = response.data.messages || [];
      return messages.value;
    } catch (err) {
      console.error(`Erreur lors de la récupération des messages:`, err);
      error.value = 'Impossible de charger les messages. Veuillez réessayer plus tard.';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // Le reste du code reste inchangé...

  // Ajouter un message temporaire (optimistic update)
  const addOptimisticMessage = (tempMessage) => {
    // Ajouter à la liste des messages
    messages.value.push(tempMessage);

    // Trier les messages par timestamp
    messages.value.sort((a, b) => a.timestamp - b.timestamp);

    // Mettre à jour également le dernier message dans la conversation
    const conversationIndex = conversations.value.findIndex(conv => conv.id === tempMessage.conversationId);
    if (conversationIndex !== -1) {
      conversations.value[conversationIndex].lastMessage = tempMessage.content;
      conversations.value[conversationIndex].last_message_timestamp = tempMessage.timestamp;
    }
  };

  // Envoyer un message
  const sendMessage = async (conversationId, content) => {
    try {
      const response = await axios.post(`/conversations/${conversationId}/messages`, { content });
      const newMessage = response.data.message;

      // Remplacer le message temporaire s'il y en a un
      const tempIndex = messages.value.findIndex(m => m.isTemp);
      if (tempIndex !== -1) {
        messages.value.splice(tempIndex, 1, newMessage);
      } else {
        messages.value.push(newMessage);
      }

      // Mettre à jour le dernier message dans la liste des conversations
      const conversationIndex = conversations.value.findIndex(conv => conv.id === conversationId);
      if (conversationIndex !== -1) {
        conversations.value[conversationIndex].lastMessage = content;
        conversations.value[conversationIndex].last_message_timestamp = Math.floor(Date.now() / 1000);
      }

      return newMessage;
    } catch (err) {
      console.error('Erreur lors de l\'envoi du message:', err);

      // En cas d'erreur, supprimer le message temporaire
      const tempIndex = messages.value.findIndex(m => m.isTemp);
      if (tempIndex !== -1) {
        messages.value.splice(tempIndex, 1);
      }

      throw err;
    }
  };

  // Définir la conversation courante
  const setCurrentConversation = (conversation) => {
    console.log('Setting current conversation:', conversation);
    currentConversation.value = conversation;

    // Vider les messages lors du changement de conversation
    messages.value = [];
  };

  // Rafraîchir les messages en arrière-plan
  const refreshMessages = async (conversationId) => {
    if (!conversationId) return false;

    try {
      const response = await axios.get(`/conversations/${conversationId}/messages`);
      const newMessages = response.data.messages || [];

      // Vérifier s'il y a de nouveaux messages à ajouter
      if (newMessages.length > messages.value.length) {
        messages.value = newMessages;
        return true; // Indique qu'il y a eu des nouveaux messages
      }

      return false; // Pas de nouveaux messages
    } catch (err) {
      console.error(`Erreur lors de l'actualisation des messages:`, err);
      return false;
    }
  };

  return {
    conversations,
    currentConversation,
    messages,
    loading,
    error,
    fetchConversations,
    fetchMessages,
    addOptimisticMessage,
    sendMessage,
    setCurrentConversation,
    refreshMessages
  };
}
