<template>
  <div>
    <HeaderComponent />
    <div class="contact-container">
      <h1>Contacter l'administrateur</h1>

      <form @submit.prevent="submitTicket" class="contact-form">
        <div class="form-group">
          <label for="type">Type de demande :</label>
          <select id="type" v-model="form.type" required>
            <option value="bug">Signaler un bug</option>
            <option value="question">Question</option>
            <option value="remboursement">Demande de remboursement</option>
            <option value="partenariat">Demande de partenariat</option>
            <option value="erreur">Erreur de transaction</option>
            <option value="suppression">Suppression du compte</option>
            <option value="autre">Autre</option>
          </select>
        </div>

        <div class="form-group">
          <label for="message">Votre message :</label>
          <textarea id="message" v-model="form.message" required rows="6"
            placeholder="Décrivez votre problème en détail..."></textarea>
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <div v-if="success" class="success-message">
          {{ success }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Envoi en cours...' : 'Envoyer le ticket' }}
        </button>
      </form>
      <div class="FAQ">
        <span>Vous avez des questions ? Elles sont peut être</span>
        <label class="link" @click="FAQ">
          ici
        </label>.
      </div>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../api/index.js'
import { ref } from 'vue'

export default {
  name: 'ContactView',
  components: {
    HeaderComponent
  },

  setup() {
    const form = ref({
      type: 'question',
      message: ''
    })
    const loading = ref(false)
    const error = ref(null)
    const success = ref(null)

    const FAQ = () => {
      window.location.href = '/faq'
    }
    const submitTicket = async () => {
      loading.value = true
      error.value = null
      success.value = null

      try {
        await axios.post('/tickets', {
          type: form.value.type,
          message: form.value.message
        })

        success.value = 'Votre ticket a été envoyé avec succès !'
        form.value.message = ''

        // Retour à l'accueil après 2 secondes
        setTimeout(() => {
          window.location.href = '/'
        }, 2000)
      } catch (err) {
        error.value = err.response?.data?.error || 'Une erreur est survenue lors de l\'envoi du ticket'
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      error,
      success,
      submitTicket,
      FAQ
    }
  }
}
</script>

<style scoped>
.contact-container {
  padding: 20px;
  max-width: 600px;
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

.contact-form {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #2c3e50;
  font-weight: bold;
}

.link {
  color: #3498db;
  cursor: pointer;
  font-weight: bold;
  transition: color 0.3s ease;
  display: inline-block;
  margin-left: 5px;
}

.link:hover {
  color: #2980b9;
  text-decoration: underline;
}

.FAQ {
  text-align: center;
  margin-top: 20px;

}

select,
textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

select:focus,
textarea:focus {
  outline: none;
  border-color: #3498db;
}

textarea {
  resize: vertical;
  min-height: 120px;
}

button {
  width: 100%;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 15px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
  text-transform: uppercase;
  font-weight: bold;
}

button:hover:not(:disabled) {
  background-color: #2980b9;
}

button:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.error-message {
  background-color: #ffebee;
  color: #e74c3c;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 20px;
  text-align: center;
}

.success-message {
  background-color: #e8f5e9;
  color: #2ecc71;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 20px;
  text-align: center;
}


@media (max-width: 480px) {
  .contact-container {
    padding: 10px;
  }

  .contact-form {
    padding: 15px;
  }

  button {
    padding: 12px;
    font-size: 0.9rem;
  }
}
</style>
