<template>
  <div>
    <HeaderComponent />
    <div class="vendor-container">
      <h1>Interface Vendeur</h1>

      <!-- Formulaire de création de facture -->
      <div class="invoice-creation-form">
        <h2>Créer une nouvelle facture</h2>

        <form @submit.prevent="createInvoice" class="form-container">
          <div class="form-group">
            <label for="label">Description :</label>
            <input
              type="text"
              id="label"
              v-model="form.label"
              required
              placeholder="Description de la facture"
            />
          </div>

          <div class="form-group">
            <label for="amount">Montant (€) :</label>
            <input
              type="number"
              id="amount"
              v-model="form.tarif"
              required
              min="0.01"
              step="0.01"
              placeholder="Montant"
            />
          </div>

          <div class="error-message" v-if="error">
            {{ error }}
          </div>

          <div class="success-message" v-if="success">
            {{ success }}
          </div>

          <button
            type="submit"
            class="submit-button"
            :disabled="isProcessing"
          >
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
              <span class="invoice-status" :class="{'status-paid': facture.status === 'payée'}">
                {{ facture.status }}
              </span>
            </div>

            <div class="invoice-body">
              <p class="invoice-description">{{ facture.label }}</p>
              <p class="invoice-amount">{{ facture.amount }}€</p>
            </div>

            <div class="invoice-qr">
              <img
                :src="`data:image/png;base64,${facture.qr_code}`"
                :alt="'QR Code pour ' + facture.label"
                class="qr-code"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../api/index.js'
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'

export default {
  name: 'VendeurView',
  components: {
    HeaderComponent
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
        await axios.post('/facture', {
          label: form.value.label,
          tarif: parseFloat(form.value.tarif)
        })

        success.value = 'Facture créée avec succès'
        form.value = {
          label: '',
          tarif: ''
        }

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
      createInvoice
    }
  }
}
</script>

<style scoped>
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

.loading-message, .no-invoices {
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
