import { ref} from 'vue'
import axios from '@/api/index.js'

export function useTicketsUser() {
  const myTicketsUser = ref([])
  const loading = ref(true)
  const error = ref(null)

  const fetchMyTicketsUser = async () => {
    try {
      const response = await axios.get('/tickets')
      myTicketsUser.value = response.data.Tickets
    } catch (err) {
      error.value = "Erreur lors de la récupération de vos tickets"
      console.error(err)
    }
  }

  // Charger les données initiales
  const initialize = async () => {
    try {
      await Promise.all([fetchMyTicketsUser()])
    } finally {
      loading.value = false
    }
  }

  // Appeler initialize immédiatement
  initialize()

  return {
    loading,
    error,
    myTicketsUser,
    fetchMyTicketsUser
  }
}
