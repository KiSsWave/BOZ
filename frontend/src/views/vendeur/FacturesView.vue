<template>
  <HeaderComponent />
  <!-- Liste des factures -->
  <div class="invoices-container">
    <h2>Mes factures</h2>
    
    <div v-if="loading" class="loading-message">
      Chargement des factures...
    </div>
    
    <div v-else-if="factures.length === 0" class="no-invoices">
      Aucune facture créée
    </div>
    
    <div v-else class="invoices-sections">
      <!-- Factures non payées -->
      <div v-if="unpaidFactures.length > 0" class="invoices-section">
        <h3>Factures en attente de paiement</h3>
        <div class="invoices-grid">
          <div v-for="facture in unpaidFactures" :key="facture.id" class="invoice-card unpaid-card">
            <div class="invoice-header">
              <span class="invoice-status">
                Non payée
              </span>
            </div>
            <div class="invoice-body">
              <p class="invoice-description">{{ facture.label }}</p>
              <p class="invoice-amount">{{ facture.amount }}€</p>
              <p v-if="facture.buyer_login" class="invoice-buyer">Acheteur: {{ facture.buyer_login }}</p>
              <p v-else class="invoice-buyer">Acheteur: Non assigné</p>
              <p class="invoice-date">Créée le: {{ formatDate(facture.created_at) }}</p>
            </div>
            <div class="invoice-actions">
              <button @click="showQRCode(facture)" class="qr-button">
                Voir QR Code
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Factures payées -->
      <div v-if="paidFactures.length > 0" class="invoices-section">
        <h3>Factures payées</h3>
        <div class="invoices-grid">
          <div v-for="facture in paidFactures" :key="facture.id" class="invoice-card paid-card">
            <div class="invoice-header">
              <span class="invoice-status status-paid">
                Payée
              </span>
            </div>
            <div class="invoice-body">
              <p class="invoice-description">{{ facture.label }}</p>
              <p class="invoice-amount">{{ facture.amount }}€</p>
              <p class="invoice-buyer">Acheteur: {{ facture.buyer_login }}</p>
              <p class="invoice-date">Créée le: {{ formatDate(facture.created_at) }}</p>
            </div>
            <div class="invoice-actions">
              <button @click="showQRCode(facture)" class="qr-button">
                Voir QR Code
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- QR Code Modal -->
  <div v-if="showModal" class="qr-modal" @click="closeModal">
    <div class="qr-modal-content" @click.stop>
      <h3>{{ selectedFacture.label }}</h3>
      <p class="modal-amount">{{ selectedFacture.amount }}€</p>
      <img 
        v-if="selectedFacture.qr_code"
        :src="`data:image/png;base64,${selectedFacture.qr_code}`" 
        :alt="'QR Code pour ' + selectedFacture.label"
        class="qr-code-image"
      />
      <button @click="closeModal" class="close-button">Fermer</button>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../../api/index.js'
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'FacturesView',
  components: {
    HeaderComponent
  },
  setup() {
    const error = ref(null)
    const success = ref(null)
    const isProcessing = ref(false)
    const loading = ref(true)
    const factures = ref([])
    
    // QR Code modal
    const showModal = ref(false)
    const selectedFacture = ref({})

    // Computed properties pour séparer les factures payées et non payées
    const paidFactures = computed(() => {
      return factures.value.filter(facture => facture.status === 'payée')
    })
    
    const unpaidFactures = computed(() => {
      return factures.value.filter(facture => facture.status !== 'payée')
    })

    const fetchFactures = async () => {
      try {
        isProcessing.value = true
        const response = await axios.get('/factures')
        factures.value = response.data.factures
      } catch (err) {
        console.error('Erreur lors de la récupération des factures:', err)
        error.value = 'Impossible de récupérer les factures'
      } finally {
        loading.value = false
        isProcessing.value = false
      }
    }

    const showQRCode = (facture) => {
      selectedFacture.value = facture
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    }

    onMounted(() => {
      fetchFactures()
    })

    return {
      factures,
      paidFactures,
      unpaidFactures,
      loading,
      error,
      showModal,
      selectedFacture,
      showQRCode,
      closeModal,
      formatDate
    }
  }
}
</script>

<style scoped>
.invoices-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

h2 {
  text-align: center;
  color: #2c3e50;
  margin: 20px 0;
  font-size: 1.8rem;
}

h3 {
  color: #34495e;
  margin: 30px 0 15px;
  font-size: 1.4rem;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 10px;
}

.loading-message,
.no-invoices {
  text-align: center;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin: 20px 0;
}

.invoices-sections {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.invoices-section {
  margin-bottom: 20px;
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
  display: flex;
  flex-direction: column;
  height: 100%;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.invoice-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.unpaid-card {
  border-top: 4px solid #f39c12;
}

.paid-card {
  border-top: 4px solid #27ae60;
}

.invoice-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 15px;
}

.invoice-status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: bold;
  background-color: #f39c12;
  color: white;
  text-transform: uppercase;
}

.status-paid {
  background-color: #27ae60;
}

.invoice-body {
  margin-bottom: 15px;
  flex-grow: 1;
}

.invoice-description {
  font-size: 1.1rem;
  color: #2c3e50;
  margin-bottom: 8px;
  font-weight: bold;
}

.invoice-amount {
  font-size: 1.2rem;
  font-weight: bold;
  color: #3498db;
  margin-bottom: 8px;
}

.invoice-buyer {
  color: #7f8c8d;
  font-size: 0.9rem;
  margin-bottom: 5px;
}

.invoice-date {
  color: #7f8c8d;
  font-size: 0.85rem;
  font-style: italic;
}

.invoice-actions {
  display: flex;
  justify-content: center;
  margin-top: 15px;
}

.qr-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease;
  font-size: 0.9rem;
  width: 100%;
}

.qr-button:hover {
  background-color: #2980b9;
}

/* QR Code Modal */
.qr-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
}

.qr-modal-content {
  background-color: white;
  padding: 30px;
  border-radius: 10px;
  text-align: center;
  max-width: 90%;
  width: 400px;
}

.qr-modal-content h3 {
  margin-top: 0;
  color: #2c3e50;
  border-bottom: none;
  text-align: center;
}

.modal-amount {
  font-size: 1.5rem;
  font-weight: bold;
  color: #3498db;
  margin-bottom: 20px;
}

.qr-code-image {
  width: 200px;
  height: 200px;
  object-fit: contain;
  margin-bottom: 20px;
  border: 1px solid #ecf0f1;
  padding: 10px;
}

.close-button {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.close-button:hover {
  background-color: #c0392b;
}

@media (max-width: 768px) {
  .invoices-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  }
}

@media (max-width: 480px) {
  .invoices-grid {
    grid-template-columns: 1fr;
  }
  
  .invoice-card {
    max-width: 100%;
  }
  
  h2 {
    font-size: 1.5rem;
  }
  
  h3 {
    font-size: 1.2rem;
  }
  
  .qr-modal-content {
    padding: 20px;
  }
  
  .qr-code-image {
    width: 180px;
    height: 180px;
  }
}
</style>