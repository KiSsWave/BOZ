<template>
  <div class="ticket-card">
    <div class="ticket-header">
      <span class="ticket-type">{{ ticket.type }}</span>
      <span class="ticket-status">{{ ticket.status }}</span>
    </div>

    <div class="ticket-body">
      <p class="ticket-message">{{ ticket.message }}</p>
      <p class="ticket-user">Utilisateur: {{ ticket['User Login'] }}</p>
    </div>

    <div class="ticket-actions">
      <button
        v-if="activeView === 'pending'"
        @click="handleTakeTicket"
        :disabled="appStore.isProcessing"
        class="take-button"
      >
        Prendre en charge
      </button>
      <template v-if="activeView === 'myTickets'">
        <button
          @click="$emit('give-cash', ticket)"
          class="give-button"
        >
          Donner de l'argent
        </button>
        <button
          @click="handleStartConversation"
          class="chat-button"
          :disabled="appStore.isProcessing"
        >
          Discuter
        </button>
        <button
          @click="handleCloseTicket"
          :disabled="appStore.isProcessing"
          class="close-button"
        >
          Fermer le ticket
        </button>
      </template>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router';
import { useAppStore } from '@/stores/appStore';

export default {
  name: 'TicketCard',
  props: {
    ticket: {
      type: Object,
      required: true
    },
    activeView: {
      type: String,
      required: true
    }
  },
  emits: ['give-cash'],
  setup(props) {
    const router = useRouter();
    const appStore = useAppStore();

    const handleTakeTicket = async () => {
      try {
        await appStore.takeTicket(props.ticket.Id);
      } catch (error) {
        alert('Erreur lors de la prise en charge du ticket. Veuillez réessayer.');
      }
    };

    const handleCloseTicket = async () => {
      try {
        const result = await appStore.closeTicket(props.ticket.Id);
        if (result) {
          alert('Ticket fermé avec succès !');
        }
      } catch (error) {
        alert('Erreur lors de la fermeture du ticket. Veuillez réessayer.');
      }
    };

    const handleStartConversation = async () => {
      try {
        const conversation = await appStore.startConversation(props.ticket.Id);
        if (conversation && conversation.id) {
          router.push(`/chat/${conversation.id}`);
        }
      } catch (error) {
        console.error('Erreur lors de la création de la conversation:', error);
        alert('Erreur lors de la création de la conversation. Veuillez réessayer.');
      }
    };

    return {
      appStore,
      handleTakeTicket,
      handleCloseTicket,
      handleStartConversation
    };
  }
};
</script>

<style scoped>
.ticket-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.ticket-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}

.ticket-type {
  background-color: #3498db;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 0.9rem;
}

.ticket-status {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.ticket-body {
  margin-bottom: 15px;
}

.ticket-message {
  margin-bottom: 10px;
  color: #2c3e50;
}

.ticket-user {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.ticket-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.take-button, .give-button, .close-button, .chat-button {
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  color: white;
}

.take-button {
  background-color: #2ecc71;
}

.take-button:hover:not(:disabled) {
  background-color: #27ae60;
}

.give-button {
  background-color: #f1c40f;
}

.give-button:hover {
  background-color: #f39c12;
}

.chat-button {
  background-color: #3498db;
}

.chat-button:hover:not(:disabled) {
  background-color: #2980b9;
}

.close-button {
  background-color: #e74c3c;
}

.close-button:hover:not(:disabled) {
  background-color: #c0392b;
}

button:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}
</style>
