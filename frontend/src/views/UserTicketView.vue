<template>
  <HeaderComponent />
  <h1>Consultation de vos tickets</h1>

  <div v-if="loading" class="loading">
    <p>Chargement de vos tickets...</p>
  </div>

  <div v-else-if="error" class="error">
    <p>{{ error }}</p>
  </div>

  <div v-else-if="myTicketsUser && myTicketsUser.length > 0" class="ticket-container">
    <div v-for="ticket in myTicketsUser" :key="ticket.Id" class="ticket" :class="getStatusClass(ticket.status)">
      <div class="ticket-header">
        <span class="ticket-type">{{ ticket.type }}</span>
        <span class="ticket-status">Statut: {{ ticket.status }}</span>
      </div>
      <div class="ticket-body">
        <p>{{ ticket.message }}</p>
      </div>
      <div class="ticket-footer">
        <span v-if="ticket['Id Admin']" class="admin-assigned">Un administrateur s'occupe de votre ticket</span>
        <span v-else class="pending-assignment">En attente d'assignation</span>
      </div>
    </div>
  </div>

  <div v-else class="no-tickets">
    <p>Aucun ticket trouvé.</p>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue';
import { useTicketsUser } from '@/composables/useTicketsUser';
import { onMounted } from 'vue';

export default {
  components: {
    HeaderComponent,
  },
  setup() {
    const { myTicketsUser, fetchMyTicketsUser, loading, error } = useTicketsUser();

    onMounted(async () => {
      await fetchMyTicketsUser();
    });

    const getStatusClass = (status) => {
      switch(status?.toLowerCase()) {
        case 'en cours': return 'status-in-progress';
        case 'en attente': return 'status-waiting';
        default: return '';
      }
    };

    return {
      myTicketsUser,
      getStatusClass,
      loading,
      error
    };
  },
};
</script>

<style scoped>
h1 {
  text-align: center;
  margin: 1.5rem 0;
  color: #333;
  font-size: 1.8rem;
}

.loading, .error, .no-tickets {
  text-align: center;
  margin: 40px auto;
  padding: 20px;
  max-width: 600px;
  border-radius: 8px;
  background-color: #f9f9f9;
}

.error {
  color: #e53935;
  background-color: #ffebee;
  border: 1px solid #ffcdd2;
}

.ticket-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin: 20px auto;
  max-width: 800px;
  padding: 0 20px;
}

.ticket {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  background-color: white;
}

.ticket:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  font-weight: bold;
}

.ticket-type {
  background-color: #2196f3;
  color: white;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
}

.ticket-status {
  color: #000000;
  font-size: 0.9rem;
}

.ticket-body {
  margin-bottom: 20px;
  line-height: 1.5;
  color: #333;
}

.ticket-footer {
  font-size: 0.9rem;
  color: #666;
  border-top: 1px solid #eee;
  padding-top: 15px;
}

.admin-assigned {
  color: #4caf50;
  font-weight: 500;
}

.pending-assignment {
  color: #ff9800;
  font-weight: 500;
}

.status-in-progress {
  border-left: 4px solid #00ff0f;
}

.status-waiting {
  border-left: 4px solid #ff0000;
  opacity: 0.8;
}

@media (max-width: 600px) {
  .ticket-container {
    padding: 0 10px;
  }

  .ticket {
    padding: 15px;
  }

  .ticket-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
</style>
