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
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../api/index.js'
import { ref } from 'vue'
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
      } catch (err) {
        error.value = err.response?.data?.error || 'Erreur lors de la création de la facture'
        console.error('Erreur:', err)
      } finally {
        isProcessing.value = false
      }
    }

    return {
      form,
      error,
      success,
      isProcessing,
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
}
</style>
