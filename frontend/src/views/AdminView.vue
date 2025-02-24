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
        :isProcessing="isProcessing"
        :activeView="activeView"
        @take-ticket="takeTicket"
        @give-cash="openGiveCashModal"
        @close-ticket="closeTicket"
      />
    </div>
    <GiveCashModal
      v-if="showModal"
      :ticket="selectedTicket"
      :isProcessing="isProcessing"
      @close="closeModal"
      @submit="giveCash"
    />
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
import ViewSelector from '@/components/tickets/ViewSelector.vue'
import TicketList from '@/components/tickets/TicketList.vue'
import GiveCashModal from '@/components/tickets/GiveCashModal.vue'
import { useTickets } from '@/composables/useTickets'

export default {
  name: 'AdminView',
  components: {
    HeaderComponent,
    ViewSelector,
    TicketList,
    GiveCashModal
  },
  setup() {
    const {
      activeView,
      displayedTickets,
      loading,
      error,
      isProcessing,
      showModal,
      selectedTicket,
      takeTicket,
      closeTicket,
      openGiveCashModal,
      closeModal,
      giveCash
    } = useTickets()

    return {
      activeView,
      displayedTickets,
      loading,
      error,
      isProcessing,
      showModal,
      selectedTicket,
      takeTicket,
      closeTicket,
      openGiveCashModal,
      closeModal,
      giveCash
    }
  }
}
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
