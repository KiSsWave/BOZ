<template>
  <HeaderComponent/>
  <div class="modif-container">
    <div class="modif-wrapper">
      <div class="modif-card">
        <h1>Modifier votre profil</h1>
        <p class="form-info">Modifiez uniquement les champs que vous souhaitez mettre à jour</p>
        <form @submit.prevent="envoie" class="modif-form">
          <div class="input-group">
            <label for="user">Login</label>
            <input id="user" type="text" placeholder="Votre login" v-model="form.user" />
          </div>

          <div class="input-group">
            <label for="email">Email</label>
            <input id="email" type="email" placeholder="Votre email" v-model="form.email" autocomplete="email" />
          </div>

          <div class="input-group">
            <label for="oldpassword">Votre mot de passe actuel</label>
            <input id="oldpassword" type="password" placeholder="Mot de passe actuel" v-model="form.oldpassword"
            />
            <small class="field-info">*Requis pour toute modification</small>
          </div>

          <div class="input-group">
            <label for="password">Votre nouveau mot de passe</label>
            <input id="password" type="password" placeholder="Nouveau mot de passe" v-model="form.password"
                   autocomplete="new-password" />
            <small class="field-info">Laissez vide pour conserver votre mot de passe actuel</small>
          </div>

          <div class="password-requirements" v-if="form.password">
            <p>Le mot de passe doit contenir :</p>
            <ul>
              <li :class="{ valid: form.password.length >= 8 }">
                <font-awesome-icon :icon="form.password.length >= 8 ? 'check-square' : 'x'" />
                8 caractères minimum
              </li>
              <li :class="{ valid: /[a-z]/.test(form.password) }">
                <font-awesome-icon :icon="/[a-z]/.test(form.password) ? 'check-square' : 'x'" />
                Une lettre minuscule
              </li>
              <li :class="{ valid: /[A-Z]/.test(form.password) }">
                <font-awesome-icon :icon="/[A-Z]/.test(form.password) ? 'check-square' : 'x'" />
                Une lettre majuscule
              </li>
              <li :class="{ valid: /[0-9]/.test(form.password) }">
                <font-awesome-icon :icon="/[0-9]/.test(form.password) ? 'check-square' : 'x'" />
                Un chiffre
              </li>
              <li :class="{ valid: /[!@#$%^&*(),.?:{}|<>]/.test(form.password) }">
                <font-awesome-icon :icon="/[!@#$%^&*(),.?:{}|<>]/.test(form.password) ? 'check-square' : 'x'" />
                Un caractère spécial
              </li>
            </ul>
          </div>

          <button type="submit" class="modif-button">Modifier</button>
        </form>
      </div>

      <!-- Nouvelle section pour devenir vendeur -->
      <div class="vendeur-card">
        <h2>Devenir Vendeur</h2>
        <div class="vendeur-info">
          <div class="avantages">
            <h3>Avantages</h3>
            <ul>
              <li>
                <font-awesome-icon icon="check" class="icon-check" />
                Vendre vos propres produits
              </li>
              <li>
                <font-awesome-icon icon="check" class="icon-check" />
                Gérer votre propre boutique
              </li>
              <li>
                <font-awesome-icon icon="check" class="icon-check" />
                Accès aux statistiques de vente
              </li>
              <li>
                <font-awesome-icon icon="check" class="icon-check" />
                Possibilité de fidéliser votre clientèle
              </li>
            </ul>
          </div>
          <div class="inconvenients">
            <h3>Inconvénients</h3>
            <ul>
              <li>
                <font-awesome-icon icon="exclamation-triangle" class="icon-warning" />
                Responsabilité envers les clients
              </li>
              <li>
                <font-awesome-icon icon="exclamation-triangle" class="icon-warning" />
                Gestion des stocks requise
              </li>
              <li>
                <font-awesome-icon icon="exclamation-triangle" class="icon-warning" />
                Respect des conditions d'utilisation
              </li>
            </ul>
          </div>
        </div>
        <button @click="devenirVendeur" class="vendeur-button">Devenir Vendeur</button>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue';
import { useAppStore } from '@/stores/appStore';
import { useUserStore } from '@/stores/userStore';
export default {
  data() {
    return {
      form: {
        user: '',
        email: '',
        oldpassword: '',
        password: '',
      },
      initialData: {
        user: '',
        email: '',
      },
    };
  },
  setup() {
    const appStore = useAppStore();
    const userStore = useUserStore();
    
    return {
      appStore,
      userStore
    };
  },
  created() {
    // Chargement des données actuelles de l'utilisateur
    this.loadUserData();
  },
  methods: {
    validatePassword(password) {
      const minLength = password.length >= 8;
      const hasLowerCase = /[a-z]/.test(password);
      const hasUpperCase = /[A-Z]/.test(password);
      const hasNumber = /[0-9]/.test(password);
      const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

      const errors = [];
      if (!minLength) errors.push("8 caractères minimum");
      if (!hasLowerCase) errors.push("une lettre minuscule");
      if (!hasUpperCase) errors.push("une lettre majuscule");
      if (!hasNumber) errors.push("un chiffre");
      if (!hasSpecialChar) errors.push("un caractère spécial");

      return {
        isValid: minLength && hasLowerCase && hasUpperCase && hasNumber && hasSpecialChar,
        errors: errors
      };
    },
    loadUserData() {
      try {
        // Utiliser les données utilisateur du userStore
        if (this.userStore.user) {
          // Remplir le formulaire avec les données actuelles
          this.form.user = this.userStore.user.email || '';
          this.form.email = this.userStore.user.login || '';

          // Sauvegarder les données initiales pour détecter les changements
          this.initialData.user = this.form.user;
          this.initialData.email = this.form.email;
        }
      } catch (error) {
        console.error("Erreur lors du chargement des données utilisateur:", error);
      }
    },
    async envoie() {
      // Prépare un objet avec seulement les champs remplis
      const updatedData = {};

      // Vérifie chaque champ et l'ajoute à l'objet uniquement s'il est rempli
      if (this.form.user) updatedData.login = this.form.user;
      if (this.form.email) updatedData.email = this.form.email;

      // Traitement spécial pour le mot de passe
      if (this.form.password) {
        // Vérifie si le mot de passe actuel est fourni
        if (!this.form.oldpassword) {
          alert("Veuillez saisir votre mot de passe actuel pour effectuer des modifications.");
          return;
        }

        // Vérifie si les mots de passe sont identiques
        if (this.form.password === this.form.oldpassword) {
          alert("Le nouveau mot de passe doit être différent de l'ancien !");
          return;
        }

        // Valide le nouveau mot de passe
        const passwordValidation = this.validatePassword(this.form.password);
        if (!passwordValidation.isValid) {
          alert(`Le mot de passe doit contenir : ${passwordValidation.errors.join(', ')}`);
          return;
        }

        updatedData.password = this.form.password;
        updatedData.oldpassword = this.form.oldpassword;
      }

      // Vérifie s'il y a des données à mettre à jour
      if (Object.keys(updatedData).length === 0) {
        alert("Aucune modification détectée. Veuillez modifier au moins un champ.");
        return;
      }

      try {
        // Utiliser une action personnalisée dans appStore
        const result = await this.updateUserProfile(updatedData);
        alert('Modification réussie !');
        
        // Mettre à jour les informations utilisateur dans le store
        await this.userStore.fetchUserInfo();
        
        this.$router.push('/');
      } catch (error) {
        console.error("Erreur lors de la modification du profil :", error);
        alert('Erreur lors de la modification du profil: ' + (error.message || 'Erreur inconnue'));
      }
    },
    
    async updateUserProfile(profileData) {
      // Méthode personnalisée qui pourrait être déplacée vers appStore si nécessaire
      try {
        // Utiliser l'instance axios importée par appStore via son module d'api
        const response = await this.appStore.$axios.patch("/profile", profileData);
        return response.data;
      } catch (error) {
        console.error("Erreur lors de la mise à jour du profil:", error);
        throw error;
      }
    },
    async devenirVendeur() {
      try {
        // Utiliser l'instance axios via appStore 
        const response = await this.appStore.$axios.patch("/role");
        
        if (response.status === 200) {
          alert('Félicitations ! Vous êtes maintenant un vendeur.');
          
          // Mettre à jour les informations utilisateur dans le store
          await this.userStore.fetchUserInfo();
          
          this.$router.push('/vendeur');
        } else {
          alert('Erreur lors du changement de rôle');
        }
      } catch (error) {
        console.error("Erreur lors du changement de rôle :", error);
        alert('Erreur lors du changement de rôle. Veuillez réessayer plus tard.');
      }
    }
  },
  components: {
    HeaderComponent,
  }
};
</script>

<style scoped>
.modif-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f4f6f7;
  padding: 20px;
}

.modif-wrapper {
  display: flex;
  flex-direction: row;
  gap: 30px;
  max-width: 1000px;
  width: 100%;
}

@media (max-width: 768px) {
  .modif-wrapper {
    flex-direction: column;
  }
}

.modif-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  flex: 1;
  max-width: 400px;
  text-align: center;
}

.vendeur-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  flex: 1;
  max-width: 400px;
  display: flex;
  flex-direction: column;
}

h1, h2 {
  font-size: 22px;
  color: #2c3e50;
  margin-bottom: 20px;
  text-align: center;
}

h3 {
  font-size: 18px;
  color: #2c3e50;
  margin-bottom: 10px;
}

.modif-form {
  display: flex;
  flex-direction: column;
}

.form-info {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 20px;
  text-align: center;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 20px;
  text-align: left;
}

label {
  font-size: 14px;
  font-weight: 600;
  color: #34495e;
}

input {
  padding: 12px;
  border: 1px solid #bdc3c7;
  border-radius: 8px;
  font-size: 16px;
  background-color: white;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
}

.modif-button, .vendeur-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.modif-button:hover, .vendeur-button:hover {
  background-color: #2980b9;
}

.vendeur-button {
  background-color: #27ae60;
  margin-top: auto;
}

.vendeur-button:hover {
  background-color: #219955;
}

.password-requirements {
  margin-top: 10px;
  font-size: 14px;
  color: #666;
  text-align: left;
}

.password-requirements ul, .avantages ul, .inconvenients ul {
  list-style: none;
  padding-left: 0;
  margin-top: 5px;
}

.password-requirements li {
  margin: 3px 0;
  color: #e74c3c;
}

.password-requirements li.valid {
  color: #27ae60;
}

.password-requirements i {
  margin-right: 8px;
}

.password-requirements li:not(.valid) i {
  color: #e74c3c;
}

.password-requirements li.valid i {
  color: #27ae60;
}

.field-info {
  font-size: 12px;
  color: #7f8c8d;
  margin-top: 3px;
}

.vendeur-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 20px;
}

.avantages, .inconvenients {
  text-align: left;
}

.avantages ul li, .inconvenients ul li {
  margin: 10px 0;
  display: flex;
  align-items: flex-start;
}

.icon-check {
  color: #27ae60;
  margin-right: 10px;
  font-size: 16px;
}

.icon-warning {
  color: #e67e22;
  margin-right: 10px;
  font-size: 16px;
}
</style>