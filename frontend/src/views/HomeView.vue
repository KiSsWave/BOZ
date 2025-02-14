<template>
  <div class="bank-app">
    <HeaderComponent />

    <main class="balance-container">
      <label class="balance-label">VOTRE SOLDE EST DE :</label>
      <div class="balance-display">
        {{ balance }}€
      </div>
    </main>

    <footer>
      <button @click="contactAdmin" v-if="userStore.isAuthenticated">Contacter l'administrateur</button>
      <button @click="viewFactures" v-if="userStore.isAuthenticated">Consulter les factures</button>
      <button @click="viewTransactions" v-if="userStore.isAuthenticated">Consulter les transactions</button>
      <label v-if="!userStore.isAuthenticated">Pour accéder aux fonctionnalités, veuillez vous connecter.</label>
    </footer>
  </div>
</template>

<script>
import { useUserStore } from '@/stores/userStore';
import HeaderComponent from '@/components/HeaderComponent.vue';
import axios from '../api/index.js';
import { onMounted } from 'vue';
import { ref } from 'vue';
import { getToken } from '@/services/authProvider.js';

export default {
  setup() {
    const balance = ref(0);
    const userStore = useUserStore();
    console.log(getToken());
    onMounted(async () => {
      if (userStore.isAuthenticated) {
        try {
          const response = await axios.get("/balance");
          if (response.data) {
            balance.value = response.data.balance || 0;
          }
        } catch (error) {
          console.error("Erreur lors de la récupération du solde :", error);
        }
      }
    });

    return {
      balance,
      userStore
    };
  },
  components: {
    HeaderComponent,
  },


  methods: {
    login() {
      this.$router.push('/login')
    },
    logout() {
      this.userStore.logout()
      this.$router.push('/')
    },
    contactAdmin() {
      this.$router.push('/contact')
    },
    viewTransactions() {
      this.$router.push('/transaction')
    },
    viewFactures() {
      this.$router.push('/facture')
    },
  },
}
</script>


<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Arial', sans-serif;
  background-color: #f4f7f6;
  color: #333;
  max-width: 500px;
  margin: 0 auto;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.balance-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  padding: 20px;
}

.balance-label {
  font-size: 1rem;
  margin-bottom: 10px;
  color: #7f8c8d;
}

.balance-display {
  font-size: 2.5rem;
  font-weight: bold;
  color: #2ecc71;
  background-color: #ffffff;
  border: 2px dashed #3498db;
  border-radius: 15px;
  padding: 20px;
  min-width: 250px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

footer {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 15px;
  padding: 20px;
  background-color: #ffffff;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
}

footer button {
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 15px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  text-transform: uppercase;
  font-weight: bold;
}

footer button:hover {
  background-color: #2980b9;
  transform: translateY(-3px);
}

footer label {
  grid-column: span 3;
  text-align: center;
  color: #000000;
  font-family: 'Courier New', Courier, monospace;
}


@media (max-width: 375px) {
  header h1 {
    font-size: 1rem;
  }

  .balance-display {
    font-size: 2rem;
  }
}
</style>
