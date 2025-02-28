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
      <button @click="viewTickets" v-if="userStore.isAuthenticated">Consulter les tickets</button>
      <button @click="viewTransactions" v-if="userStore.isAuthenticated">Consulter les transactions</button>
      <label v-if="!userStore.isAuthenticated">Pour accéder aux fonctionnalités, veuillez vous connecter.</label>
    </footer>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import HeaderComponent from '@/components/HeaderComponent.vue';
import { useUserStore } from '@/stores/userStore';
import { useAppStore } from '@/stores/appStore';

export default {
  components: {
    HeaderComponent,
  },
  setup() {
    const router = useRouter();
    const userStore = useUserStore();
    const appStore = useAppStore();

    // Utiliser le solde depuis le store global
    const balance = computed(() => appStore.balance);

    onMounted(async () => {
      // Charger le solde si l'utilisateur est authentifié
      if (userStore.isAuthenticated) {
        try {
          await appStore.fetchBalance();
        } catch (error) {
          console.error("Erreur lors de la récupération du solde :", error);
        }
      }
    });

    // Méthodes de navigation
    const contactAdmin = () => {
      router.push('/contact');
    };

    const viewTickets = () => {
      router.push('/userTicket');
    };

    const viewTransactions = () => {
      router.push('/transaction');
    };

    return {
      balance,
      userStore,
      contactAdmin,
      viewTickets,
      viewTransactions
    };
  }
}
</script>
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
</style>
<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.bank-app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f4f7f6;
}

:deep(header) {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 10;
  height: 70px; /* Hauteur fixe pour le header */
}


.balance-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  flex: 0 1 auto; /* Modification pour ne pas étirer */
  margin: 20px 0; /* Ajout de marges */
}

.balance-label {
  font-size: 1.2rem;
  margin-bottom: 20px;
  color: #7f8c8d;
  text-transform: uppercase;
  font-weight: bold;
}

.balance-display {
  font-size: 3rem;
  font-weight: bold;
  color: #2ecc71;
  background-color: #ffffff;
  border: 2px dashed #3498db;
  border-radius: 15px;
  padding: 30px 40px;
  min-width: 300px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 40px; /* Ajout d'une marge en bas */
}

footer {
  margin-top: auto; /* Pousse le footer vers le bas */
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
  padding: 30px;
  background-color: #ffffff;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
}

footer button {
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 20px 15px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  text-transform: uppercase;
  font-weight: bold;
  height: 80px; /* Hauteur fixe pour les boutons */
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
  font-size: 1.1rem;
  padding: 20px;
}

/* Responsive design amélioré */
@media (max-width: 768px) {
  .balance-display {
    font-size: 2.5rem;
    min-width: 250px;
    padding: 25px 30px;
  }

  footer {
    padding: 20px;
    gap: 15px;
  }

  footer button {
    height: 70px;
    padding: 15px 10px;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .balance-container {
    padding: 20px 15px;
  }

  .balance-display {
    font-size: 2rem;
    min-width: 200px;
    padding: 20px 25px;
  }

  footer button {
    height: 60px;
    font-size: 0.8rem;
    padding: 10px 8px;
  }
}
</style>
