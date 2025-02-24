<template>
  <div class="tickets-list">
    <!-- Loading state -->
    <div v-if="loading" class="loading">
      Chargement des tickets...
    </div>

    <!-- Error state -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Liste des tickets -->
    <div v-else>
      <div v-if="tickets.length === 0" class="no-tickets">
        Aucun ticket à afficher
      </div>

      <div v-else class="ticket-grid">
        <TicketCard
          v-for="ticket in tickets"
          :key="ticket.Id"
          :ticket="ticket"
          :isProcessing="isProcessing"
          :activeView="activeView"
          @take-ticket="$emit('take-ticket', $event)"
          @give-cash="$emit('give-cash', $event)"
          @close-ticket="$emit('close-ticket', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script>
import TicketCard from './TicketCard.vue'

export default {
  name: 'TicketList',
  components: {
    TicketCard
  },
  props: {
    tickets: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: null
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
.loading, .error-message, .no-tickets {
  text-align: center;
  padding: 20px;
  margin: 20px 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.error-message {
  color: #e74c3c;
  background-color: #ffebee;
}

.ticket-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  padding: 20px;
}

@media (max-width: 768px) {
  .ticket-grid {
    grid-template-columns: 1fr;
    padding: 10px;
  }
}
</style>
