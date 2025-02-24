import { defineStore } from 'pinia'
import axios from '../api/index.js'

export const useChatStore = defineStore('chat', {
  state: () => ({
    conversations: [],
    activeConversation: null,
    messages: [],
    isLoading: false,
    error: null
  }),

  actions: {
    async loadConversations() {
      this.isLoading = true
      try {
        const response = await axios.get('/conversations')
        this.conversations = response.data.conversations
      } catch (error) {
        this.error = "Erreur lors du chargement des conversations"
      } finally {
        this.isLoading = false
      }
    },

    async loadMessages(conversationId) {
      try {
        const response = await axios.get(`/conversations/${conversationId}/messages`)
        this.messages = response.data.messages
      } catch (error) {
        this.error = "Erreur lors du chargement des messages"
      }
    },

    setActiveConversation(conversation) {
      this.activeConversation = conversation
      if (conversation) {
        this.loadMessages(conversation.id)
      }
    }
  }
})
