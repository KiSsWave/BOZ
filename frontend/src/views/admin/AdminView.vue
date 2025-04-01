<template>
  <div>
    <HeaderComponent />
    <div class="admin-container">
      <h1>Interface Administrateur</h1>
      <ViewSelector
        :activeView="activeView"
        @change-view="activeView = $event"
      />
      <TicketList
        :tickets="displayedTickets"
        :loading="loading"
        :error="error"
        :activeView="activeView"
        @give-cash="openGiveCashModal"
      />
    </div>
    <GiveCashModal
      v-if="showModal"
      :ticket="selectedTicket"
      :isProcessing="appStore.isProcessing"
      @close="closeModal"
      @submit="giveCash"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import HeaderComponent from '@/components/HeaderComponent.vue';
import ViewSelector from '@/components/tickets/ViewSelector.vue';
import TicketList from '@/components/tickets/TicketList.vue';
import GiveCashModal from '@/components/tickets/GiveCashModal.vue';
import { useAppStore } from '@/stores/appStore';

export default {
  name: 'AdminView',
  components: {
    HeaderComponent,
    ViewSelector,
    TicketList,
    GiveCashModal
  },
  setup() {
    const appStore = useAppStore();
    const activeView = ref('pending');
    const showModal = ref(false);
    const selectedTicket = ref(null);

    // Computed properties pour obtenir les données depuis le store
    const displayedTickets = computed(() => {
      return activeView.value === 'pending'
        ? appStore.pendingTickets
        : appStore.myTickets;
    });

    const loading = computed(() => {
      return activeView.value === 'pending'
        ? appStore.loadingStates.pendingTickets
        : appStore.loadingStates.myTickets;
    });

    const error = computed(() => {
      return activeView.value === 'pending'
        ? appStore.errorStates.pendingTickets
        : appStore.errorStates.myTickets;
    });

    // Méthodes pour interagir avec les tickets
    const openGiveCashModal = (ticket) => {
      selectedTicket.value = ticket;
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
      selectedTicket.value = null;
    };

    const giveCash = async (params) => {
      try {
        const result = await appStore.giveCash(params);
        if (result) {
          closeModal();
          alert('Argent envoyé avec succès !');
        }
      } catch (error) {
        alert('Erreur lors de l\'envoi d\'argent. Veuillez réessayer.');
      }
    };

    // Charger les données au montage et lors des changements de vue
    onMounted(async () => {
      if (activeView.value === 'pending') {
        await appStore.fetchPendingTickets();
      } else {
        await appStore.fetchMyTickets();
      }
    });

    watch(activeView, async (newValue) => {
      if (newValue === 'pending') {
        await appStore.fetchPendingTickets();
      } else {
        await appStore.fetchMyTickets();
      }
    });

    return {
      appStore,
      activeView,
      displayedTickets,
      loading,
      error,
      showModal,
      selectedTicket,
      openGiveCashModal,
      closeModal,
      giveCash
    };
  }
};
</script>

<style scoped>
.admin-container {
  padding: 20px;
  background-color: #f4f7f6;
  min-height: calc(100vh - 70px);
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 30px;
}
</style>
