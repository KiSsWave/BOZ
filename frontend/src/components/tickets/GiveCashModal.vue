<template>
  <div class="modal">
    <div class="modal-overlay" @click="$emit('close')"></div>
    <div class="modal-content">
      <div class="modal-header">
        <h2>Donner de l'argent</h2>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>

      <div class="modal-body">
        <div class="user-info">
          <span class="user-label">Utilisateur:</span>
          <span class="user-value">{{ ticket?.['User Login'] }}</span>
        </div>

        <div class="form-group">
          <label for="amount-input">Montant :</label>
          <div class="input-wrapper">
            <span class="currency-symbol">€</span>
            <input
              id="amount-input"
              type="number"
              v-model="amount"
              min="0"
              step="0.01"
              placeholder="Entrez le montant"
            >
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button
          @click="$emit('close')"
          class="cancel-button"
        >
          Annuler
        </button>
        <button
          @click="handleSubmit"
          :disabled="isProcessing"
          class="confirm-button"
        >
          <span v-if="isProcessing" class="spinner"></span>
          <span v-else>Confirmer</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'

export default {
  name: 'GiveCashModal',
  props: {
    ticket: {
      type: Object,
      required: true
    },
    isProcessing: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'submit'],
  setup(props, { emit }) {
    const amount = ref('')

    const handleSubmit = () => {
      if (!amount.value || amount.value <= 0) {
        alert('Veuillez entrer un montant valide')
        return
      }
      emit('submit', {
        user_login: props.ticket['User Login'],
        amount: parseFloat(amount.value)
      })
    }

    return {
      amount,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
  animation: fadeIn 0.2s ease;
}

.modal-content {
  position: relative;
  background: white;
  padding: 0;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  z-index: 1001;
  overflow: hidden;
  animation: slideIn 0.3s ease;
}

.modal-header {
  background-color: #f8f9fa;
  padding: 16px 20px;
  border-bottom: 1px solid #eaeaea;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #2c3e50;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #95a5a6;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #e74c3c;
}

.modal-body {
  padding: 20px;
}

.user-info {
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eaeaea;
}

.user-label {
  font-weight: 600;
  color: #7f8c8d;
  margin-right: 8px;
}

.user-value {
  color: #2c3e50;
  font-weight: 500;
}

.form-group {
  margin: 20px 0;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #2c3e50;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-symbol {
  position: absolute;
  left: 12px;
  color: #7f8c8d;
}

input {
  width: 100%;
  padding: 12px 12px 12px 30px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

input:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
}

input:hover {
  border-color: #bdc3c7;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 20px;
  background-color: #f8f9fa;
  border-top: 1px solid #eaeaea;
}

.confirm-button, .cancel-button {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 100px;
}

.confirm-button {
  background-color: #2ecc71;
  color: white;
}

.confirm-button:hover:not(:disabled) {
  background-color: #27ae60;
}

.cancel-button {
  background-color: #f1f2f6;
  color: #2c3e50;
}

.cancel-button:hover {
  background-color: #e6e8ed;
}

button:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
  opacity: 0.8;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 0.8s linear infinite;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 480px) {
  .modal-content {
    width: 95%;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }

  .confirm-button, .cancel-button {
    width: 100%;
  }
}
</style>
