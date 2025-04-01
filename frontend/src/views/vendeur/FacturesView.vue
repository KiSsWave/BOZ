<template>
  <HeaderComponent />
  <!-- Liste des factures -->
  <div class="invoices-list">
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
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../../api/index.js'
import { ref, onMounted } from 'vue'

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

    const fullscreen = (event) => {
      event.target.classList.toggle('fullscreen')
    }

    onMounted(() => {
      fetchFactures()
    })

    return {
      factures,
      loading,
      error,
      fullscreen,
    }
  }
}
</script>

<style scoped>
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
</style>
