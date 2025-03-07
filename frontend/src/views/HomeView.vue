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
  padding: clamp(20px, 5vw, 40px);
  flex: 1;
  margin: clamp(10px, 2vw, 20px) 0;
}

.balance-label {
  font-size: clamp(1rem, 3vw, 1.2rem);
  margin-bottom: clamp(10px, 4vw, 20px);
  color: #7f8c8d;
  text-transform: uppercase;
  font-weight: bold;
  text-align: center;
}

.balance-display {
  font-size: clamp(1.8rem, 5vw, 3rem);
  font-weight: bold;
  color: #2ecc71;
  background-color: #ffffff;
  border: 2px dashed #3498db;
  border-radius: 15px;
  padding: clamp(15px, 4vw, 30px);
  width: clamp(200px, 80vw, 300px);
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: clamp(20px, 5vw, 40px);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

footer {
  margin-top: auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: clamp(10px, 2vw, 20px);
  padding: clamp(15px, 3vw, 30px);
  background-color: #ffffff;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
}

footer button {
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 10px;
  padding: clamp(10px, 2vw, 20px) clamp(8px, 1.5vw, 15px);
  font-size: clamp(0.8rem, 2vw, 1rem);
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  text-transform: uppercase;
  font-weight: bold;
  min-height: 60px;
  height: auto;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

footer button:hover {
  background-color: #2980b9;
  transform: translateY(-3px);
}

footer label {
  grid-column: 1 / -1; /* Span all columns */
  text-align: center;
  color: #000000;
  font-family: 'Courier New', Courier, monospace;
  font-size: clamp(0.9rem, 2.5vw, 1.1rem);
  padding: clamp(10px, 3vw, 20px);
}

/* Responsive design amélioré avec breakpoints plus précis */
@media (max-width: 1024px) {
  footer {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

@media (max-width: 768px) {
  footer {
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  }

  footer button {
    padding: 12px 10px;
    font-size: 0.9rem;
  }
}

@media (max-width: 600px) {
  footer {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  footer button {
    height: auto;
    min-height: 50px;
    padding: 12px;
  }
}

@media (max-width: 480px) {
  .balance-label {
    font-size: 0.9rem;
  }

  .balance-display {
    font-size: 1.8rem;
    padding: 15px 20px;
  }

  footer button {
    font-size: 0.8rem;
    min-height: 45px;
    padding: 10px 8px;
  }
}

@media (max-width: 320px) {
  .balance-display {
    font-size: 1.5rem;
    padding: 12px 15px;
    width: 90%;
  }

  footer {
    padding: 12px;
  }

  footer button {
    min-height: 40px;
    font-size: 0.75rem;
  }
}
</style>
