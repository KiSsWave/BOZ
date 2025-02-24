<template>
  <div>
    <HeaderComponent />
    <div class="chat-layout">
      <!-- Section de recherche -->
      <div class="search-section">
        <div class="search-container">
          <input
            type="text"
            v-model="searchQuery"
            @input="searchUsers"
            placeholder="Rechercher un utilisateur..."
            class="search-input"
          />

          <!-- Résultats de recherche -->
          <div v-if="searchResults.length > 0" class="search-results">
            <div
              v-for="user in searchResults"
              :key="user.login"
              class="search-result-item"
            >
              <span>{{ user.login }}</span>
              <span>{{ user.email }}</span>
            </div>
          </div>

          <!-- Message d'erreur -->
          <div v-if="error" class="error-message">
            {{ error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import axios from '../api/index.js'
import { ref } from 'vue'
import debounce from 'lodash/debounce'

export default {
  name: 'ChatView',
  components: {
    HeaderComponent
  },

  setup() {
    const searchQuery = ref('')
    const searchResults = ref([])
    const error = ref(null)

    const searchUsers = debounce(async () => {
      if (searchQuery.value.length < 2) {
        searchResults.value = []
        return
      }

      try {
        const response = await axios.get(`/users/search?query=${searchQuery.value}`)
        searchResults.value = response.data.users
        error.value = null
      } catch (err) {
        console.error('Erreur de recherche:', err)
        error.value = 'Erreur lors de la recherche des utilisateurs'
        searchResults.value = []
      }
    }, 300)

    return {
      searchQuery,
      searchResults,
      error,
      searchUsers
    }
  }
}
</script>

<style scoped>
.chat-layout {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
  min-height: calc(100vh - 70px);
  background-color: #f4f7f6;
}

.search-container {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  margin-bottom: 10px;
}

.search-input:focus {
  outline: none;
  border-color: #3498db;
}

.search-results {
  margin-top: 20px;
}

.search-result-item {
  padding: 15px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-result-item:last-child {
  border-bottom: none;
}

.error-message {
  color: #e74c3c;
  background-color: #fdeaea;
  padding: 10px;
  border-radius: 4px;
  margin-top: 10px;
  text-align: center;
}
</style>
