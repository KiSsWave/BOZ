<template>
  <div>
    <HeaderComponent />
    <div class="vendor-container">
      <h1>Interface Vendeur</h1>

      <div class="invoice-creation-form">
        <h2>Créer une nouvelle facture</h2>
        <form @submit.prevent="createInvoice" class="form-container">
          <div class="form-group">
            <label for="label">Description :</label>
            <input type="text" id="label" v-model="form.label" required placeholder="Description de la facture" />
          </div>
          <div class="form-group">
            <label for="amount">Montant (€) :</label>
            <input type="number" id="amount" v-model="form.tarif" required min="0.01" step="0.01"
              placeholder="Montant" />
          </div>
          <div class="form-group">
            <label for="buyer">Acheteur (optionnel) :</label>
            <div class="buyer-search">
              <input 
                type="text" 
                id="buyer" 
                v-model="buyerQuery" 
                placeholder="Rechercher un acheteur..." 
                @input="searchBuyers"
              />
              <div v-if="showBuyerResults && searchResults.length > 0" class="search-results">
                <div 
                  v-for="user in searchResults" 
                  :key="user.id" 
                  class="search-result-item"
                  @click="selectBuyer(user)">
                  {{ user.login }}
                </div>
              </div>
            </div>
            <div v-if="selectedBuyer" class="selected-buyer">
              Acheteur sélectionné: <span>{{ selectedBuyer.login }}</span>
              <button type="button" class="clear-buyer" @click="clearSelectedBuyer">×</button>
            </div>
          </div>
          <div class="error-message" v-if="error">
            {{ error }}
          </div>
          <div class="success-message" v-if="success">
            {{ success }}
          </div>
          <button type="submit" class="submit-button" :disabled="isProcessing">
            {{ isProcessing ? 'Création en cours...' : 'Créer la facture' }}
          </button>
        </form>
      </div>
      <!-- Liste des factures -->
      <div class="invoices-list">
        <h2>Mes factures</h2>
        <div v-if="loading" class="loading-message">
          Chargement des factures...
        </div>
        <div v-else-if="factures.length === 0" class="no-invoices">
          Aucune facture créée
        </div>
        <div v-else class="invoices-grid">
          <div v-for="facture in factures" :key="facture.id" class="invoice-card">
            <div class="invoice-header">
              <span class="invoice-status" :class="{ 'status-paid': facture.status === 'payée' }">
                {{ facture.status }}
              </span>
            </div>
            <div class="invoice-body">
              <p class="invoice-description">{{ facture.label }}</p>
              <p class="invoice-amount">{{ facture.amount }}€</p>
              <p v-if="facture.buyer_login" class="invoice-buyer">Acheteur: {{ facture.buyer_login }}</p>
            </div>
            <div class="invoice-qr">
              <img :src="`data:image/png;base64,${facture.qr_code}`" :alt="'QR Code pour ' + facture.label"
                class="qr-code" @click="fullscreen" />
            </div>
          </div>
        </div>
      </div>
      <a @click="ticket">
        <font-awesome-icon :icon="['fas', 'ticket']" /> Créer un ticket pour contacter l'administrateur
      </a>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../api/index.js'
import { ref, onMounted, computed, watch } from 'vue'
import { useUserStore } from '@/stores/userStore'
import debounce from 'lodash/debounce'

export default {
  name: 'VendeurView',
  components: {
    HeaderComponent
  },
  methods: {
    fullscreen(event) {
      event.target.classList.toggle('fullscreen')
    },
    ticket() {
      window.location.href = '/VendeurTicket'
    },
  },

  setup() {
    const userStore = useUserStore()
    const form = ref({
      label: '',
      tarif: ''
    })
    const error = ref(null)
    const success = ref(null)
    const isProcessing = ref(false)
    const loading = ref(true)
    const factures = ref([])
    const buyerQuery = ref('')
    const searchResults = ref([])
    const showBuyerResults = ref(false)
    const selectedBuyer = ref(null)
    
    const fullscreen = (event) => {
      event.target.classList.toggle('fullscreen')
    }

    const searchBuyers = debounce(async () => {
      if (buyerQuery.value.length < 2) {
        searchResults.value = []
        return
      }
      
      try {
        const response = await axios.get('/users/search', {
          params: { query: buyerQuery.value }
        })
        searchResults.value = response.data.users
        showBuyerResults.value = true
      } catch (err) {
        console.error('Erreur lors de la recherche d\'utilisateurs:', err)
        searchResults.value = []
      }
    }, 300)

    const selectBuyer = (user) => {
      selectedBuyer.value = user
      buyerQuery.value = ''
      showBuyerResults.value = false
      searchResults.value = []
    }

    const clearSelectedBuyer = () => {
      selectedBuyer.value = null
    }

    // Fermer les résultats de recherche quand on clique ailleurs
    watch(buyerQuery, (newValue) => {
      if (!newValue) {
        setTimeout(() => {
          showBuyerResults.value = false
        }, 200)
      }
    })

    const fetchFactures = async () => {
      try {
        const response = await axios.get('/factures')
        factures.value = response.data.factures
      } catch (err) {
        console.error('Erreur lors de la récupération des factures:', err)
      } finally {
        loading.value = false
      }
    }

    const createInvoice = async () => {
      if (!form.value.label || !form.value.tarif) {
        error.value = 'Veuillez remplir tous les champs'
        return
      }

      if (parseFloat(form.value.tarif) <= 0) {
        error.value = 'Le montant doit être supérieur à 0'
        return
      }

      isProcessing.value = true
      error.value = null
      success.value = null

      try {
        const invoiceData = {
          label: form.value.label,
          tarif: parseFloat(form.value.tarif)
        }
        
        // Ajouter l'acheteur si sélectionné
        if (selectedBuyer.value) {
          invoiceData.buyer_login = selectedBuyer.value.login
        }

        await axios.post('/facture', invoiceData)

        success.value = 'Facture créée avec succès'
        form.value = {
          label: '',
          tarif: ''
        }
        clearSelectedBuyer()

        // Recharger la liste des factures
        await fetchFactures()
      } catch (err) {
        error.value = err.response?.data?.error || 'Erreur lors de la création de la facture'
        console.error('Erreur:', err)
      } finally {
        isProcessing.value = false
      }
    }

    onMounted(() => {
      fetchFactures()
    })

    return {
      form,
      error,
      success,
      isProcessing,
      loading,
      factures,
      createInvoice,
      buyerQuery,
      searchResults,
      showBuyerResults,
      selectedBuyer,
      searchBuyers,
      selectBuyer,
      clearSelectedBuyer
    }
  }
}
</script>

<style scoped>
a {
  text-align: center;
  cursor: pointer;
  text-decoration: underline;
  font-size: 1.2rem;
  font-weight: bold;
  color: #34495e;
  margin-top: 50px;
  display: block;
}

a:hover {
  color: #3498db;
}

.vendor-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
  min-height: calc(100vh - 70px);
  background-color: #f4f7f6;
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin: 20px 0;
  font-size: 1.5rem;
}

h2 {
  color: #34495e;
  margin-bottom: 20px;
  font-size: 1.2rem;
}

.invoice-creation-form {
  background: white;
  padding: 25px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.form-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

label {
  font-weight: bold;
  color: #2c3e50;
}

input {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

input:focus {
  outline: none;
  border-color: #3498db;
}

.submit-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
  font-weight: bold;
}

.submit-button:hover:not(:disabled) {
  background-color: #2980b9;
}

.submit-button:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.error-message {
  color: #e74c3c;
  background-color: #fdeaea;
  padding: 10px;
  border-radius: 4px;
  text-align: center;
}

.success-message {
  color: #27ae60;
  background-color: #e8f8f5;
  padding: 10px;
  border-radius: 4px;
  text-align: center;
}

/* Styles pour la liste des factures */
.invoices-list {
  margin-top: 30px;
}

.loading-message,
.no-invoices {
  text-align: center;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.invoices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.invoice-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.invoice-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 10px;
}

.invoice-status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9rem;
  background-color: #f39c12;
  color: white;
}

.status-paid {
  background-color: #27ae60;
}

.invoice-body {
  margin-bottom: 15px;
}

.invoice-description {
  font-size: 1.1rem;
  color: #2c3e50;
  margin-bottom: 8px;
}

.invoice-amount {
  font-size: 1.2rem;
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 8px;
}

.invoice-buyer {
  font-size: 0.9rem;
  color: #7f8c8d;
  font-style: italic;
}

.invoice-qr {
  display: flex;
  justify-content: center;
  padding: 10px;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.qr-code {
  width: 150px;
  height: 150px;
  object-fit: contain;
  cursor: pointer;
}

.qr-code.fullscreen {
  position: fixed;
  top: 50%;
  left: 50%;
  width: 70vw;
  height: 70vw;
  max-width: 80vh;
  max-height: 80vh;
  transform: translate(-50%, -50%);
  z-index: 1000;
  background: white;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
}

/* Styles pour la recherche d'acheteurs */
.buyer-search {
  position: relative;
  width: 100%;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 0 0 6px 6px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 10;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.search-result-item {
  padding: 10px 15px;
  cursor: pointer;
  border-bottom: 1px solid #eee;
}

.search-result-item:hover {
  background-color: #f0f7fb;
}

.search-result-item:last-child {
  border-bottom: none;
}

.selected-buyer {
  display: flex;
  align-items: center;
  background-color: #e1f5fe;
  padding: 8px 12px;
  border-radius: 4px;
  margin-top: 8px;
}

.selected-buyer span {
  font-weight: bold;
  margin-left: 5px;
}

.clear-buyer {
  margin-left: auto;
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #e74c3c;
}

@media (max-width: 480px) {
  .vendor-container {
    padding: 10px;
  }

  .invoice-creation-form {
    padding: 15px;
  }

  input {
    padding: 10px;
  }

  .invoices-grid {
    grid-template-columns: 1fr;
  }

  .qr-code {
    width: 120px;
    height: 120px;
  }
}
</style>