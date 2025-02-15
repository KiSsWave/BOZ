<template>
  <div>
    <HeaderComponent />
    <div class="admin-container">
      <h1>Interface Administrateur</h1>

      <div class="tickets-section">
        <!-- Sélecteur de vue -->
        <div class="view-selector">
          <button
            :class="{ 'active': activeView === 'pending' }"
            @click="activeView = 'pending'"
          >
            Tickets en attente
          </button>
          <button
            :class="{ 'active': activeView === 'myTickets' }"
            @click="activeView = 'myTickets'"
          >
            Mes tickets
          </button>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="loading">
          Chargement des tickets...
        </div>

        <!-- Error state -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <!-- Liste des tickets -->
        <div v-else class="tickets-list">
          <div v-if="displayedTickets.length === 0" class="no-tickets">
            Aucun ticket à afficher
          </div>

          <div v-else class="ticket-grid">
            <div
              v-for="ticket in displayedTickets"
              :key="ticket.Id"
              class="ticket-card"
            >
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
                  @click="takeTicket(ticket.Id)"
                  :disabled="isProcessing"
                  class="take-button"
                >
                  Prendre en charge
                </button>
                <button
                  v-if="activeView === 'myTickets'"
                  @click="openGiveCashModal(ticket)"
                  class="give-button"
                >
                  Donner de l'argent
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal pour donner de l'argent -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <h2>Donner de l'argent</h2>
        <p>Utilisateur: {{ selectedTicket?.['User Login'] }}</p>

        <div class="form-group">
          <label>Montant :</label>
          <input
            type="number"
            v-model="amount"
            min="0"
            step="0.01"
            placeholder="Entrez le montant"
          >
        </div>

        <div class="modal-actions">
          <button @click="giveCash" :disabled="isProcessing" class="confirm-button">
            Confirmer
          </button>
          <button @click="closeModal" class="cancel-button">
            Annuler
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../api/index.js'
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'

export default {
  name: 'AdminView',
  components: {
    HeaderComponent
  },

  setup() {
    const userStore = useUserStore()
    const activeView = ref('pending')
    const pendingTickets = ref([])
    const myTickets = ref([])
    const loading = ref(true)
    const error = ref(null)
    const isProcessing = ref(false)
    const showModal = ref(false)
    const selectedTicket = ref(null)
    const amount = ref('')

    const displayedTickets = computed(() => {
      return activeView.value === 'pending' ? pendingTickets.value : myTickets.value
    })

    const fetchPendingTickets = async () => {
      try {
        const response = await axios.get('/tickets/pending')
        pendingTickets.value = response.data.Tickets
      } catch (err) {
        error.value = "Erreur lors de la récupération des tickets en attente"
        console.error(err)
      }
    }

    const fetchMyTickets = async () => {
      try {
        const response = await axios.get('/tickets/admin')
        myTickets.value = response.data.Tickets
      } catch (err) {
        error.value = "Erreur lors de la récupération de vos tickets"
        console.error(err)
      }
    }

    const takeTicket = async (ticketId) => {
      isProcessing.value = true
      try {
        await axios.patch('/tickets', {
          ticketId: ticketId,
          adminId: userStore.user.id
        })
        await Promise.all([fetchPendingTickets(), fetchMyTickets()])
      } catch (err) {
        error.value = "Erreur lors de la prise en charge du ticket"
        console.error(err)
      } finally {
        isProcessing.value = false
      }
    }

    const openGiveCashModal = (ticket) => {
      selectedTicket.value = ticket
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      selectedTicket.value = null
      amount.value = ''
    }

    const giveCash = async () => {
      if (!amount.value || amount.value <= 0) {
        alert('Veuillez entrer un montant valide')
        return
      }

      isProcessing.value = true
      try {
        // Log pour débugger
        console.log("Selected ticket:", selectedTicket.value)
        console.log("User login:", selectedTicket.value['User Login'])

        await axios.post('/give', {
          user_login: selectedTicket.value['User Login'],
          amount: parseFloat(amount.value)
        })
        closeModal()
        alert('Argent envoyé avec succès !')
      } catch (err) {
        error.value = err.response?.data?.error || "Erreur lors de l'envoi d'argent"
        console.error("Erreur complète:", err)
      } finally {
        isProcessing.value = false
      }
    }

    onMounted(async () => {
      try {
        await Promise.all([fetchPendingTickets(), fetchMyTickets()])
      } finally {
        loading.value = false
      }
    })

    return {
      activeView,
      displayedTickets,
      loading,
      error,
      isProcessing,
      takeTicket,
      showModal,
      selectedTicket,
      amount,
      openGiveCashModal,
      closeModal,
      giveCash
    }
  }
}
</script>

<style scoped>
/* Styles existants */
.admin-container {
  padding: 20px;
  background-color: #f4f7f6;
  min-height: calc(100vh - 70px);
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 30px;
}

.view-selector {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 30px;
}

.view-selector button {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  background-color: #fff;
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: bold;
}

.view-selector button.active {
  background-color: #3498db;
  color: white;
}

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

.take-button {
  background-color: #2ecc71;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.take-button:hover:not(:disabled) {
  background-color: #27ae60;
}

.take-button:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

/* Nouveaux styles pour le don d'argent */
.give-button {
  background-color: #f1c40f;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.give-button:hover {
  background-color: #f39c12;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
}

.form-group {
  margin: 20px 0;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
}

.form-group input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.confirm-button {
  background-color: #2ecc71;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
}

.cancel-button {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .ticket-grid {
    grid-template-columns: 1fr;
    padding: 10px;
  }

  .view-selector {
    flex-direction: column;
    gap: 10px;
  }

  .view-selector button {
    width: 100%;
  }
}
</style>
