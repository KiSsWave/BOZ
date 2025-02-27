<template>
  <HeaderComponent/>
  <div class="faq-container">
    <h1 class="faq-title">Foire Aux Questions</h1>
    <p class="faq-subtitle">Tout ce que vous devez savoir sur BOZ</p>

    <div class="faq-list">
      <div v-for="(item, index) in faqItems" :key="index" class="faq-item">
        <div
          class="faq-question"
          @click="toggleReponse(index)"
          :class="{ 'active': openedIndex === index }"
        >
          <span>{{ item.question }}</span>
          <span class="faq-arrow" :class="{ 'rotated': openedIndex === index }">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </span>
        </div>
        <div class="faq-reponse" :class="{ 'open': openedIndex === index }">
          <p>{{ item.reponse }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue'
export default {
  name: 'FaqView',
  data() {
    return {
      openedIndex: null,
      faqItems: [
        {
          question: "Je souhaite devenir vendeur, comment je fais ?",
          reponse: "Faites un ticket en indiquant que vous voulez devenir vendeur, et on s'en occupe ! Nous vous configurerons un compte vendeur avec accès au tableau de bord des ventes et au système de QR code."
        },
        {
          question: "Je souhaite créditer mon compte, comment cela se passe ?",
          reponse: "Faites un ticket en indiquant le montant que vous voulez créditer et un administrateur rechargera votre compte. Le crédit sera disponible dans les 24 heures suivant la validation de votre demande."
        },
        {
          question: "Qui êtes vous ?",
          reponse: "Nous sommes une équipe de 4 développeurs passionnés, spécialisés dans les solutions de paiement innovantes. BOZ est notre solution pour permettre les transactions hors connexion lors d'événements et pour les commerçants."
        },
        {
          question: "Comment fonctionne BOZ sans connexion internet ?",
          reponse: "BOZ utilise un système de stockage local pour enregistrer les transactions effectuées hors ligne. Une fois la connexion rétablie, les transactions sont automatiquement synchronisées avec notre serveur sécurisé, garantissant ainsi l'intégrité des données."
        },
        {
          question: "Est-ce que BOZ est sécurisé ?",
          reponse: "Absolument ! Nous utilisons un chiffrement de bout en bout pour toutes les transactions. Même hors ligne, vos données sont protégées et ne peuvent être consultées que par les parties autorisées. Nos systèmes sont régulièrement audités pour garantir les plus hauts standards de sécurité."
        },
        {
          question: "Puis-je utiliser BOZ pour mon festival ou événement ?",
          reponse: "Oui, BOZ est parfaitement adapté aux festivals et événements. Nous proposons des solutions personnalisées incluant des terminaux de paiement, des QR codes et une interface d'administration dédiée. Contactez-nous via un ticket pour obtenir une offre adaptée à votre événement."
        },
        {
          question: "Comment les clients peuvent-ils recharger leur compte pendant un événement ?",
          reponse: "Nous mettons en place des points de rechargement sur les lieux de l'événement où les clients peuvent créditer leur compte BOZ en espèces ou par carte bancaire. Nous fournissons le matériel et le personnel nécessaire pour assurer un service rapide et efficace."
        },
        {
          question: "Quels sont les frais associés à l'utilisation de BOZ ?",
          reponse: "Notre structure tarifaire est transparente avec un petit pourcentage sur chaque transaction. Nous proposons des forfaits adaptés au volume de transactions prévues. Pour obtenir un devis personnalisé, veuillez créer un ticket avec les détails de votre activité."
        }
      ]
    };
  },
  methods: {
    toggleReponse(index) {
      if (this.openedIndex === index) {
        this.openedIndex = null;
      } else {
        this.openedIndex = index;
      }
    }
  },
  components: {
    HeaderComponent
  }
}
</script>

<style scoped>
.faq-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.faq-title {
  text-align: center;
  color: #2196f3;
  margin-bottom: 0.5rem;
  font-size: 2.5rem;
  font-weight: 700;
}

.faq-subtitle {
  text-align: center;
  color: #666;
  margin-bottom: 3rem;
  font-size: 1.1rem;
}

.faq-list {
  border-radius: 10px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.faq-item {
  border-bottom: 1px solid #eaeaea;
  background-color: white;
}

.faq-item:last-child {
  border-bottom: none;
}

.faq-question {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem 1.5rem;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.faq-question:hover {
  background-color: #f9f9f9;
}

.faq-question.active {
  background-color: #e3f2fd;
  color: #1976d2;
}

.faq-arrow {
  transition: transform 0.3s ease;
  color: #757575;
}

.faq-arrow.rotated {
  transform: rotate(180deg);
  color: #1976d2;
}

.faq-reponse {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s ease, padding 0.35s ease;
  padding: 0 1.5rem;
  background-color: #f5f5f5;
}

.faq-reponse.open {
  max-height: 500px;
  padding: 1.2rem 1.5rem;
}

.faq-reponse p {
  margin: 0;
  line-height: 1.6;
  color: #444;
}

@media (max-width: 768px) {
  .faq-container {
    padding: 1rem;
  }

  .faq-title {
    font-size: 2rem;
  }

  .faq-subtitle {
    margin-bottom: 2rem;
  }

  .faq-question {
    padding: 1rem;
    font-size: 0.95rem;
  }

  .faq-reponse.open {
    padding: 1rem;
  }
}
</style>
