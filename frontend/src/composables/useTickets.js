import { ref, computed } from 'vue'
import { useUserStore } from '@/stores/userStore'
import axios from '@/api/index.js'

export function useTickets() {
  const userStore = useUserStore()
  const activeView = ref('pending')
  const pendingTickets = ref([])
  const myTickets = ref([])
  const loading = ref(true)
  const error = ref(null)
  const isProcessing = ref(false)
  const showModal = ref(false)
  const selectedTicket = ref(null)

  const displayedTickets = computed(() => {
    return activeView.value === 'pending' ? pendingTickets.value : myTickets.value
  })

  const fetchPendingTickets = async () => {
    try {
      const response = await axios.get('/tickets/pending')
      pendingTickets.value = response.data.Tickets
    } catch (err) {
      error.value = "Erreur lors de la récupération des tickets en attente"
      console.error(err)
    }
  }

  const fetchMyTickets = async () => {
    try {
      const response = await axios.get('/tickets/admin')
      myTickets.value = response.data.Tickets
    } catch (err) {
      error.value = "Erreur lors de la récupération de vos tickets"
      console.error(err)
    }
  }

  const takeTicket = async (ticketId) => {
    isProcessing.value = true
    try {
      await axios.patch('/tickets', {
        ticketId: ticketId,
        adminId: userStore.user.id
      })
      await Promise.all([fetchPendingTickets(), fetchMyTickets()])
    } catch (err) {
      error.value = "Erreur lors de la prise en charge du ticket"
      console.error(err)
    } finally {
      isProcessing.value = false
    }
  }

  const closeTicket = async (ticketId) => {
    isProcessing.value = true
    try {
      await axios.patch(`/tickets/close/${ticketId}`)
      await fetchMyTickets()
      alert('Ticket fermé avec succès !')
    } catch (err) {
      error.value = "Erreur lors de la fermeture du ticket"
      console.error(err)
    } finally {
      isProcessing.value = false
    }
  }

  const openGiveCashModal = (ticket) => {
    selectedTicket.value = ticket
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
    selectedTicket.value = null
  }

  const giveCash = async ({ user_login, amount }) => {
    isProcessing.value = true
    try {
      await axios.post('/admin/give', {
        user_login,
        amount
      })
      closeModal()
      alert('Argent envoyé avec succès !')
    } catch (err) {
      error.value = err.response?.data?.error || "Erreur lors de l'envoi d'argent"
      console.error("Erreur complète:", err)
    } finally {
      isProcessing.value = false
    }
  }

  // Charger les données initiales
  const initialize = async () => {
    try {
      await Promise.all([fetchPendingTickets(), fetchMyTickets()])
    } finally {
      loading.value = false
    }
  }

  // Appeler initialize immédiatement
  initialize()

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
