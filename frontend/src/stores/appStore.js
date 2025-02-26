// src/stores/appStore.js
import { defineStore } from 'pinia';
import axios from '@/api/index.js';
import { useUserStore } from '@/stores/userStore';

export const useAppStore = defineStore('app', {
  state: () => ({
    // États partagés entre les composants
    balance: 0,
    transactions: [],
    pendingTickets: [],
    myTickets: [],
    userTickets: [],
    conversations: [],
    messages: {},  // Object de messages, organisé par ID de conversation

    // États de chargement et erreurs
    loadingStates: {
      balance: false,
      transactions: false,
      pendingTickets: false,
      myTickets: false,
      userTickets: false,
      conversations: false
    },
    errorStates: {
      balance: null,
      transactions: null,
      pendingTickets: null,
      myTickets: null,
      userTickets: null,
      conversations: null
    },

    // Registre des données chargées
    dataLoaded: {
      balance: false,
      transactions: false,
      pendingTickets: false,
      myTickets: false,
      userTickets: false,
      conversations: false
    },

    // État des opérations en cours
    isProcessing: false
  }),

  getters: {
    // Getter pour vérifier si une donnée a déjà été chargée
    isDataLoaded: (state) => (dataType) => {
      return state.dataLoaded[dataType];
    },

    // Getter pour obtenir les messages d'une conversation spécifique
    getMessagesByConversationId: (state) => (conversationId) => {
      return state.messages[conversationId] || [];
    },

    // Getter pour obtenir une conversation par son ID
    getConversationById: (state) => (conversationId) => {
      return state.conversations.find(conv => conv.id === conversationId);
    }
  },

  actions: {
    // === ACTIONS LIÉES AU SOLDE ET TRANSACTIONS ===

    async fetchBalance(forceRefresh = false) {
      if (this.dataLoaded.balance && !forceRefresh) return this.balance;

      const userStore = useUserStore();
      if (!userStore.isAuthenticated) return 0;

      this.loadingStates.balance = true;
      this.errorStates.balance = null;

      try {
        const response = await axios.get('/balance');
        this.balance = response.data.balance || 0;
        this.dataLoaded.balance = true;
        return this.balance;
      } catch (error) {
        console.error('Erreur lors de la récupération du solde:', error);
        this.errorStates.balance = "Impossible de récupérer votre solde";
        throw error;
      } finally {
        this.loadingStates.balance = false;
      }
    },

    async fetchTransactions(forceRefresh = false) {
      if (this.dataLoaded.transactions && !forceRefresh) return this.transactions;

      this.loadingStates.transactions = true;
      this.errorStates.transactions = null;

      try {
        const response = await axios.get('/history');
        this.transactions = response.data.history || [];
        this.dataLoaded.transactions = true;
        return this.transactions;
      } catch (error) {
        console.error('Erreur lors de la récupération des transactions:', error);
        this.errorStates.transactions = "Impossible de récupérer votre historique de transactions";
        throw error;
      } finally {
        this.loadingStates.transactions = false;
      }
    },

    // === ACTIONS LIÉES AUX TICKETS (ADMIN) ===

    async fetchPendingTickets(forceRefresh = false) {
      if (this.dataLoaded.pendingTickets && !forceRefresh) return this.pendingTickets;

      this.loadingStates.pendingTickets = true;
      this.errorStates.pendingTickets = null;

      try {
        const response = await axios.get('/admin/tickets/pending');
        this.pendingTickets = response.data.Tickets || [];
        this.dataLoaded.pendingTickets = true;
        return this.pendingTickets;
      } catch (error) {
        console.error('Erreur lors de la récupération des tickets en attente:', error);
        this.errorStates.pendingTickets = "Impossible de récupérer les tickets en attente";
        throw error;
      } finally {
        this.loadingStates.pendingTickets = false;
      }
    },

    async fetchMyTickets(forceRefresh = false) {
      if (this.dataLoaded.myTickets && !forceRefresh) return this.myTickets;

      this.loadingStates.myTickets = true;
      this.errorStates.myTickets = null;

      try {
        const response = await axios.get('/admin/tickets/admin');
        this.myTickets = response.data.Tickets || [];
        this.dataLoaded.myTickets = true;
        return this.myTickets;
      } catch (error) {
        console.error('Erreur lors de la récupération de vos tickets:', error);
        this.errorStates.myTickets = "Impossible de récupérer vos tickets";
        throw error;
      } finally {
        this.loadingStates.myTickets = false;
      }
    },

    async takeTicket(ticketId) {
      const userStore = useUserStore();
      this.isProcessing = true;

      try {
        await axios.patch('/admin/tickets', {
          ticketId: ticketId,
          adminId: userStore.user.id
        });

        // Mettre à jour les listes de tickets
        await Promise.all([
          this.fetchPendingTickets(true),
          this.fetchMyTickets(true)
        ]);

        return true;
      } catch (error) {
        console.error('Erreur lors de la prise en charge du ticket:', error);
        throw error;
      } finally {
        this.isProcessing = false;
      }
    },

    async closeTicket(ticketId) {
      this.isProcessing = true;

      try {
        await axios.patch(`/admin/tickets/close/${ticketId}`);
        await this.fetchMyTickets(true);
        return true;
      } catch (error) {
        console.error('Erreur lors de la fermeture du ticket:', error);
        throw error;
      } finally {
        this.isProcessing = false;
      }
    },

    async giveCash({ user_login, amount }) {
      this.isProcessing = true;

      try {
        await axios.post('/admin/give', {
          user_login,
          amount
        });

        // Mettre à jour le solde
        await this.fetchBalance(true);

        return true;
      } catch (error) {
        console.error('Erreur lors de l\'envoi d\'argent:', error);
        throw error;
      } finally {
        this.isProcessing = false;
      }
    },

    // === ACTIONS LIÉES AUX TICKETS (UTILISATEUR) ===

    async fetchUserTickets(forceRefresh = false) {
      if (this.dataLoaded.userTickets && !forceRefresh) return this.userTickets;

      this.loadingStates.userTickets = true;
      this.errorStates.userTickets = null;

      try {
        const response = await axios.get('/tickets');
        this.userTickets = response.data.Tickets || [];
        this.dataLoaded.userTickets = true;
        return this.userTickets;
      } catch (error) {
        console.error('Erreur lors de la récupération de vos tickets:', error);
        this.errorStates.userTickets = "Impossible de récupérer vos tickets";
        throw error;
      } finally {
        this.loadingStates.userTickets = false;
      }
    },

    async submitTicket(type, message) {
      this.isProcessing = true;

      try {
        await axios.post('/tickets', {
          type,
          message
        });

        // Mettre à jour la liste des tickets utilisateur
        await this.fetchUserTickets(true);

        return true;
      } catch (error) {
        console.error('Erreur lors de la création du ticket:', error);
        throw error;
      } finally {
        this.isProcessing = false;
      }
    },

    // === ACTIONS LIÉES AUX CONVERSATIONS ===

    async fetchConversations(forceRefresh = false) {
      if (this.dataLoaded.conversations && !forceRefresh) return this.conversations;

      this.loadingStates.conversations = true;
      this.errorStates.conversations = null;

      try {
        const response = await axios.get('/conversations');
        this.conversations = response.data.conversations || [];

        // Charger le dernier message pour chaque conversation
        for (const conversation of this.conversations) {
          try {
            // Si nous n'avons pas encore les messages pour cette conversation ou forceRefresh
            if (!this.messages[conversation.id] || forceRefresh) {
              await this.fetchMessages(conversation.id, false);

              // Ajouter le dernier message comme propriété de la conversation
              const conversationMessages = this.messages[conversation.id] || [];
              if (conversationMessages.length > 0) {
                conversation.lastMessage = conversationMessages[conversationMessages.length - 1].content;
              }
            }
          } catch (err) {
            console.error(`Erreur lors de la récupération des messages pour la conversation ${conversation.id}:`, err);
          }
        }

        this.dataLoaded.conversations = true;
        return this.conversations;
      } catch (error) {
        console.error('Erreur lors de la récupération des conversations:', error);
        this.errorStates.conversations = "Impossible de récupérer vos conversations";
        throw error;
      } finally {
        this.loadingStates.conversations = false;
      }
    },

    async fetchMessages(conversationId, updateLastMessage = true) {
      try {
        const response = await axios.get(`/conversations/${conversationId}/messages`);

        // Stocker les messages dans l'état
        this.messages[conversationId] = response.data.messages || [];

        // Mettre à jour les détails de la conversation si nécessaire
        if (updateLastMessage && response.data.conversation) {
          const conversationIndex = this.conversations.findIndex(c => c.id === conversationId);
          if (conversationIndex !== -1) {
            this.conversations[conversationIndex] = {
              ...this.conversations[conversationIndex],
              ...response.data.conversation
            };
          }
        }

        return this.messages[conversationId];
      } catch (error) {
        console.error('Erreur lors de la récupération des messages:', error);
        throw error;
      }
    },

    async sendMessage(conversationId, content) {
      this.isProcessing = true;

      try {
        await axios.post(`/conversations/${conversationId}/messages`, { content });

        // Rafraîchir les messages
        await this.fetchMessages(conversationId);

        return true;
      } catch (error) {
        console.error('Erreur lors de l\'envoi du message:', error);
        throw error;
      } finally {
        this.isProcessing = false;
      }
    },

    async startConversation(ticketId) {
      this.isProcessing = true;

      try {
        const response = await axios.post('/admin/tickets/start-conversation', {
          ticketId
        });

        // Rafraîchir la liste des conversations
        await this.fetchConversations(true);

        return response.data.conversation;
      } catch (error) {
        console.error('Erreur lors de la création de la conversation:', error);
        throw error;
      } finally {
        this.isProcessing = false;
      }
    },

    // === ACTION POUR CHARGER TOUTES LES DONNÉES INITIALES ===

    async loadInitialData() {
      const userStore = useUserStore();
      if (!userStore.isAuthenticated) return;

      // Déterminer quelles données charger en fonction du rôle
      const role = userStore.user.role;

      const promises = [];

      // Données communes
      promises.push(this.fetchBalance());
      promises.push(this.fetchTransactions());
      promises.push(this.fetchConversations());

      // Données spécifiques à l'administrateur
      if (role === '3') {
        promises.push(this.fetchPendingTickets());
        promises.push(this.fetchMyTickets());
      }

      // Données spécifiques à l'utilisateur
      if (role === '1') {
        promises.push(this.fetchUserTickets());
      }

      // Exécuter toutes les requêtes en parallèle
      try {
        await Promise.allSettled(promises);
      } catch (error) {
        console.error('Erreur lors du chargement des données initiales:', error);
      }
    },

    // Méthode pour réinitialiser le store
    resetStore() {
      this.$reset();
    }
  }
});
