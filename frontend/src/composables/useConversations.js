// Modification complète de la fonction useConversations pour améliorer le polling

import { ref, computed } from 'vue';
import axios from '@/api/index.js';

export function useConversations() {
  // État des conversations
  const conversations = ref([]);
  const currentConversation = ref(null);
  const messages = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const lastFetch = ref(null);
  const pollingActive = ref(false);


  const fetchConversations = async (withLastMessage = true, forceRefresh = false) => {
    // Éviter les requêtes multiples en parallèle
    if (loading.value) return conversations.value;


    const now = Date.now();
    const cacheExpiration = 15 * 1000;

    if (!forceRefresh &&
      lastFetch.value &&
      now - lastFetch.value < cacheExpiration &&
      conversations.value.length > 0) {
      return conversations.value;
    }

    loading.value = true;
    error.value = null;

    try {

      const url = withLastMessage ? '/conversations?include_last_message=true' : '/conversations';
      const response = await axios.get(url);


      let conversationsData = response.data.conversations || [];


      const deduplicateConversations = (convArray) => {
        const uniqueConvs = [];
        const seen = new Set();


        const sortedConvs = [...convArray].sort((a, b) => {
          const timestampA = a.last_message_timestamp || 0;
          const timestampB = b.last_message_timestamp || 0;
          return timestampB - timestampA; // Du plus récent au plus ancien
        });

        for (const conv of sortedConvs) {
          const participants = [conv.user1Login, conv.user2Login].sort().join('_');

          if (!seen.has(participants)) {
            seen.add(participants);
            uniqueConvs.push(conv);
          }
        }

        return uniqueConvs;
      };

      conversations.value = deduplicateConversations(conversationsData);
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


  const fetchMessages = async (conversationId) => {
    if (!conversationId) {
      error.value = 'ID de conversation manquant';
      return [];
    }

    if (loading.value) return messages.value;

    loading.value = true;
    error.value = null;

    try {

      const response = await axios.get(`/conversations/${conversationId}/messages`);
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


  const addOptimisticMessage = (tempMessage) => {

    if (!tempMessage || !tempMessage.content) {
      console.warn('Message invalide:', tempMessage);
      return;
    }

    messages.value.push(tempMessage);
    const conversationIndex = conversations.value.findIndex(conv => conv.id === tempMessage.conversationId);
    if (conversationIndex !== -1) {
      conversations.value[conversationIndex].lastMessage = tempMessage.content;
      conversations.value[conversationIndex].last_message_timestamp = tempMessage.timestamp;

      if (currentConversation.value && currentConversation.value.id === tempMessage.conversationId) {
        currentConversation.value.lastMessage = tempMessage.content;
        currentConversation.value.last_message_timestamp = tempMessage.timestamp;
      }
    }
  };

  const sendMessage = async (conversationId, content) => {
    try {
      const response = await axios.post(`/conversations/${conversationId}/messages`, { content });
      const newMessage = response.data.message || response.data.data;


      const tempMessage = messages.value.find(m => m.isTemp);


      if (newMessage) {

        newMessage.isMine = true;


        newMessage.replacesTempId = tempMessage ? tempMessage.id : null;

        if (!messages.value.some(m => m.id === newMessage.id)) {
          messages.value.push(newMessage);
          messages.value.sort((a, b) => a.timestamp - b.timestamp);
        }
      }


      const conversationIndex = conversations.value.findIndex(conv => conv.id === conversationId);
      if (conversationIndex !== -1) {
        conversations.value[conversationIndex].lastMessage = content;
        conversations.value[conversationIndex].last_message_timestamp = Math.floor(Date.now() / 1000);


        if (currentConversation.value && currentConversation.value.id === conversationId) {
          currentConversation.value.lastMessage = content;
          currentConversation.value.last_message_timestamp = Math.floor(Date.now() / 1000);
        }
      }

      setTimeout(() => {
        const tempIndex = messages.value.findIndex(m => m.isTemp);
        if (tempIndex !== -1) {
          messages.value.splice(tempIndex, 1);
        }
      }, 1000);

      return newMessage;
    } catch (err) {
      console.error('Erreur lors de l\'envoi du message:', err);

      const tempIndex = messages.value.findIndex(m => m.isTemp);
      if (tempIndex !== -1) {
        messages.value.splice(tempIndex, 1);
      }

      throw err;
    }
  };


  const setCurrentConversation = (conversation) => {
    currentConversation.value = conversation;


    messages.value = [];
  };


  const refreshMessages = async (conversationId) => {
    if (!conversationId) return false;

    try {

      if (loading.value) {

        return false;
      }

      const response = await axios.get(`/conversations/${conversationId}/messages`);
      const newMessages = response.data.messages || [];


      const tempMessages = messages.value.filter(m => m.isTemp);

      if (messages.value.length === 0 ||
        (newMessages.length > 0 && newMessages.length !== messages.value.filter(m => !m.isTemp).length)) {


        const combinedMessages = [...newMessages, ...tempMessages];


        combinedMessages.sort((a, b) => a.timestamp - b.timestamp);


        messages.value = combinedMessages;
        return true;
      }

      if (newMessages.length > 0 && messages.value.length > 0) {
        const nonTempMessages = messages.value.filter(m => !m.isTemp);

        if (nonTempMessages.length > 0) {
          const lastNewMessage = newMessages[newMessages.length - 1];
          const lastCurrentMessage = nonTempMessages[nonTempMessages.length - 1];


          if (lastNewMessage.id !== lastCurrentMessage.id) {

            const combinedMessages = [...newMessages, ...tempMessages];


            combinedMessages.sort((a, b) => a.timestamp - b.timestamp);

            messages.value = combinedMessages;
            return true;
          }
        }
      }

      return false;
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
    pollingActive,
    fetchConversations,
    fetchMessages,
    addOptimisticMessage,
    sendMessage,
    setCurrentConversation,
    refreshMessages
  };
}
