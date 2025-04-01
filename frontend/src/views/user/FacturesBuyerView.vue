<template>
    <div>
      <HeaderComponent />
      <div class="buyer-container">
        <h1>Mes Factures</h1>
        
        <div v-if="loading" class="loading">
          <p>Chargement des factures...</p>
        </div>
        
        <div v-else-if="error" class="error-message">
          <p>{{ error }}</p>
        </div>
        
        <div v-else-if="factures.length === 0" class="no-factures">
          <p>Aucune facture trouvée. Les factures que vous paierez apparaîtront ici.</p>
        </div>
        
        <div v-else class="factures-container">
          <!-- Factures non payées -->
          <div v-if="unpaidFactures.length > 0" class="factures-list">
            <h2>Factures à payer</h2>
            
            <div v-for="facture in unpaidFactures" :key="facture.id" class="facture-card unpaid-card">
              <div class="facture-header">
                <h3>{{ facture.label }}</h3>
                <span class="facture-status unpaid">
                  NON PAYÉE
                </span>
              </div>
              
              <div class="facture-details">
                <div class="facture-info">
                  <p>Montant: <span class="amount">{{ facture.amount }}€</span></p>
                  <p>Vendeur: <span>{{ facture.seller_login }}</span></p>
                  <p>Date: <span>{{ formatDate(facture.created_at) }}</span></p>
                </div>
                
                <div class="facture-actions">
                  <button 
                    @click="payFacture(facture.id)" 
                    class="pay-button"
                    :disabled="isPaying">
                    {{ isPaying && currentFactureId === facture.id ? 'Paiement en cours...' : 'Payer' }}
                  </button>
                  <button 
                    @click="showQRCode(facture)" 
                    class="qr-button">
                    Voir QR Code
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Factures payées -->
          <div v-if="paidFactures.length > 0" class="factures-list">
            <h2>Factures payées</h2>
            
            <div v-for="facture in paidFactures" :key="facture.id" class="facture-card paid-card">
              <div class="facture-header">
                <h3>{{ facture.label }}</h3>
                <span class="facture-status paid">
                  PAYÉE
                </span>
              </div>
              
              <div class="facture-details">
                <div class="facture-info">
                  <p>Montant: <span class="amount">{{ facture.amount }}€</span></p>
                  <p>Vendeur: <span>{{ facture.seller_login }}</span></p>
                  <p>Date: <span>{{ formatDate(facture.created_at) }}</span></p>
                </div>
                
                <div class="facture-actions">
                  <button 
                    @click="showQRCode(facture)" 
                    class="qr-button">
                    Voir QR Code
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    
      <!-- QR Code Modal -->
      <div v-if="showModal" class="qr-modal" @click="closeModal">
        <div class="qr-modal-content" @click.stop>
          <h3>{{ selectedFacture.label }}</h3>
          <p class="modal-amount">{{ selectedFacture.amount }}€</p>
          <img 
            v-if="selectedFacture.qr_code"
            :src="`data:image/png;base64,${selectedFacture.qr_code}`" 
            :alt="'QR Code pour ' + selectedFacture.label"
            class="qr-code-image"
          />
          <button @click="closeModal" class="close-button">Fermer</button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import HeaderComponent from '@/components/HeaderComponent.vue'
  import { ref, computed, onMounted } from 'vue'
  import { useUserStore } from '@/stores/userStore'
  import { useAppStore } from '@/stores/appStore'
  import { useRouter } from 'vue-router'
  
  export default {
    name: 'FacturesBuyerView',
    components: {
      HeaderComponent
    },
    setup() {
      const userStore = useUserStore()
      const appStore = useAppStore()
      const router = useRouter()
      
      const factures = ref([])
      const loading = ref(true)
      const error = ref(null)
      const isPaying = ref(false)
      const currentFactureId = ref(null)
      
      // QR Code modal
      const showModal = ref(false)
      const selectedFacture = ref({})
      
      // Computed properties pour séparer les factures payées et non payées
      const paidFactures = computed(() => {
        return factures.value.filter(facture => facture.status === 'payée')
      })
      
      const unpaidFactures = computed(() => {
        return factures.value.filter(facture => facture.status !== 'payée')
      })
  
      const fetchFactures = async () => {
        loading.value = true
        error.value = null
        
        try {
          const response = await appStore.$axios.get('/buyers/factures')
          factures.value = response.data.factures || []
        } catch (err) {
          console.error('Erreur lors de la récupération des factures:', err)
          error.value = 'Impossible de récupérer vos factures. Veuillez réessayer ultérieurement.'
        } finally {
          loading.value = false
        }
      }
  
      const payFacture = async (factureId) => {
        isPaying.value = true
        currentFactureId.value = factureId
        
        try {
          await appStore.$axios.post('/pay', {
            facture_id: factureId
          })
          
          // Mettre à jour les factures et le solde après le paiement
          await fetchFactures()
          await appStore.fetchBalance(true)
          
          // Message de succès
          alert('Paiement effectué avec succès!')
        } catch (err) {
          console.error('Erreur lors du paiement de la facture:', err)
          alert(err.response?.data?.error || 'Erreur lors du paiement. Veuillez réessayer.')
        } finally {
          isPaying.value = false
          currentFactureId.value = null
        }
      }
  
      const showQRCode = (facture) => {
        selectedFacture.value = facture
        showModal.value = true
      }
  
      const closeModal = () => {
        showModal.value = false
      }
  
      const formatDate = (dateString) => {
        if (!dateString) return ''
        
        const date = new Date(dateString)
        return new Intl.DateTimeFormat('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        }).format(date)
      }
  
      onMounted(async () => {
        try {
          await fetchFactures()
        } catch (error) {
          console.error("Erreur lors du chargement initial :", error)
        }
      })
  
      return {
        factures,
        paidFactures,
        unpaidFactures,
        loading,
        error,
        isPaying,
        currentFactureId,
        showModal,
        selectedFacture,
        payFacture,
        showQRCode,
        closeModal,
        formatDate
      }
    }
  }
  </script>
  
  <style scoped>
  .buyer-container {
    background-color: #f4f7f6;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: calc(100vh - 70px);
  }
  
  h1 {
    text-align: center;
    color: #2c3e50;
    margin: 20px 0;
    font-size: 1.5rem;
  }
  
  h2 {
    color: #34495e;
    margin: 20px 0;
    font-size: 1.2rem;
    text-align: center;
    width: 100%;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
  }
  
  .loading, .no-factures {
    text-align: center;
    margin: 40px 0;
    color: #7f8c8d;
    font-size: 1.1rem;
  }
  
  .error-message {
    color: #e74c3c;
    background-color: #fdeaea;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: center;
    width: 100%;
    max-width: 600px;
  }
  
  .factures-container {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 800px;
    gap: 30px;
  }
  
  .factures-list {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  
  .facture-card {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  
  .facture-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }
  
  .unpaid-card {
    border-left: 4px solid #f39c12;
  }
  
  .paid-card {
    border-left: 4px solid #27ae60;
  }
  
  .facture-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #ecf0f1;
    padding-bottom: 10px;
  }
  
  .facture-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.1rem;
  }
  
  .facture-status {
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    text-transform: uppercase;
  }
  
  .paid {
    background-color: #e8f8f5;
    color: #27ae60;
  }
  
  .unpaid {
    background-color: #fef9e7;
    color: #f39c12;
  }
  
  .facture-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
  }
  
  .facture-info {
    flex: 1;
    min-width: 200px;
  }
  
  .facture-info p {
    margin: 8px 0;
    color: #7f8c8d;
  }
  
  .facture-info span {
    color: #2c3e50;
    font-weight: 500;
  }
  
  .facture-info .amount {
    font-weight: bold;
    color: #3498db;
  }
  
  .facture-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  
  .pay-button, .qr-button {
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
    border: none;
    min-width: 120px;
    font-size: 0.9rem;
  }
  
  .pay-button {
    background-color: #27ae60;
    color: white;
  }
  
  .pay-button:hover:not(:disabled) {
    background-color: #219955;
  }
  
  .pay-button:disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
  }
  
  .qr-button {
    background-color: #3498db;
    color: white;
  }
  
  .qr-button:hover {
    background-color: #2980b9;
  }
  
  /* Modal QR Code */
  .qr-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100;
  }
  
  .qr-modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 90%;
    width: 400px;
  }
  
  .qr-modal-content h3 {
    margin-top: 0;
    color: #2c3e50;
  }
  
  .modal-amount {
    font-size: 1.5rem;
    font-weight: bold;
    color: #3498db;
    margin-bottom: 20px;
  }
  
  .qr-code-image {
    width: 200px;
    height: 200px;
    object-fit: contain;
    margin-bottom: 20px;
    border: 1px solid #ecf0f1;
    padding: 10px;
  }
  
  .close-button {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }
  
  .close-button:hover {
    background-color: #c0392b;
  }
  
  /* Responsive design */
  @media (max-width: 768px) {
    .facture-details {
      flex-direction: column;
      align-items: flex-start;
    }
    
    .facture-actions {
      width: 100%;
      flex-direction: row;
      justify-content: space-between;
    }
    
    .pay-button, .qr-button {
      min-width: 0;
      flex: 1;
    }
  }
  
  @media (max-width: 600px) {
    .buyer-container {
      padding: 15px;
    }
    
    .facture-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }
    
    .facture-status {
      margin-left: 0;
    }
  }
  
  @media (max-width: 480px) {
    .qr-code-image {
      width: 160px;
      height: 160px;
    }
    
    .qr-modal-content {
      padding: 20px;
    }
    
    .facture-actions {
      flex-direction: column;
    }
  }
  </style>