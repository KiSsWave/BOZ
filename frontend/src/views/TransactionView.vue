<template>
  <div>
    <HeaderComponent />
    <div class="transaction-container">
      <h1>Transactions</h1>

      <div v-if="loading" class="loading">
        Chargement des transactions...
      </div>

      <div v-else-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-else-if="transactions.length === 0" class="no-transactions">
        Aucune transaction à afficher
      </div>

      <div v-else class="transactions-list">
        <div v-for="transaction in transactions" :key="transaction.transaction_id" class="transaction-card">
          <div class="transaction-details">
            <div class="transaction-amount" :class="{
              'amount-add': transaction.type === 'add',
              'amount-pay': transaction.type === 'pay'
            }">
              {{ transaction.type === 'pay' ? '-' : '+' }}{{ transaction.amount }}€
            </div>
            <div class="transaction-date">
              {{ formatDate(transaction.timestamp) }}
            </div>
          </div>
          <div class="transaction-id">
            ID: {{ transaction.transaction_id }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue';
import HeaderComponent from '@/components/HeaderComponent.vue';
import { useAppStore } from '@/stores/appStore';

export default {
  name: 'TransactionView',
  components: {
    HeaderComponent
  },
  setup() {
    const appStore = useAppStore();

    const transactions = computed(() => appStore.transactions);
    const loading = computed(() => appStore.loadingStates.transactions);
    const error = computed(() => appStore.errorStates.transactions);

    const formatDate = (timestamp) => {
      const date = new Date(timestamp);
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    onMounted(async () => {
      if (!appStore.isDataLoaded('transactions')) {
        await appStore.fetchTransactions();
      }
    });

    return {
      transactions,
      loading,
      error,
      formatDate
    };
  }
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
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
}

:deep(.BOZ) {
  width: 80px;
  height: auto;
}


.transaction-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

h1 {
  font-size: 1.2rem;
  color: #2c3e50;
  text-align: center;
  margin: 20px 0;
}

.loading,
.error-message,
.no-transactions {
  text-align: center;
  padding: 20px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-top: 20px;
}

.error-message {
  color: #e74c3c;
}

.transactions-list {
  margin-top: 20px;
}

.transaction-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.transaction-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.transaction-amount {
  font-size: 1.2rem;
  font-weight: bold;
}

.amount-add {
  color: #2ecc71;
}

.amount-pay {
  color: #e74c3c;
}

.transaction-date {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.transaction-id {
  font-size: 0.8rem;
  color: #95a5a6;
}
</style>
