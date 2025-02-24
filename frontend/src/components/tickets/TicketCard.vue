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
        @click="$emit('take-ticket', ticket.Id)"
        :disabled="isProcessing"
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
          @click="$emit('close-ticket', ticket.Id)"
          :disabled="isProcessing"
          class="close-button"
        >
          Fermer le ticket
        </button>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TicketCard',
  props: {
    ticket: {
      type: Object,
      required: true
    },
    isProcessing: {
      type: Boolean,
      default: false
    },
    activeView: {
      type: String,
      required: true
    }
  },
  emits: ['take-ticket', 'give-cash', 'close-ticket']
}
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

.take-button, .give-button, .close-button {
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
