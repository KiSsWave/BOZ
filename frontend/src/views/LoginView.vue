<template>
  <div class="login-container">
    <div class="login-header">
      <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
      <font-awesome-icon icon="reply" class="back-icon" @click="index" title="Retour à l'accueil" />
    </div>

    <h1>Content de vous revoir !</h1>

    <form @submit.prevent="envoie" class="login-form">
      <div class="input-group">
        <input type="email" placeholder="Email" v-model="form.email" required />
        <input type="password" placeholder="Mot de passe" v-model="form.password" required
          autocomplete="current-password" />
      </div>

      <button type="submit" class="login-button">
        Se connecter
      </button>
    </form>

    <div class="register-prompt">
      <span>Nouveau sur l'application ?</span>
      <label class="register-link" @click="register">
        Créer un compte
      </label>
    </div>
  </div>
</template>

<script>
import { useUserStore } from '@/stores/userStore';
import axios from '../api/index.js';
export default {
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
    };
  },
  methods: {
    register() {
      this.$router.push('/register');
    },
    index() {
      this.$router.push('/');
    },
    async envoie() {
      try {
        const response = await axios.post("/signin", this.form);

        if (response.data && response.data.token) {
          const userStore = useUserStore();
          userStore.login(response.data.token);

          const role = parseInt(userStore.user.role);

          switch(role) {
            case 1:
              this.$router.push('/');
              break;
            case 2:
              this.$router.push('/vendeur');
              break;
            case 3:
              this.$router.push('/admin');
              break;
            default:
              this.$router.push('/');
              break;
          }
        } else {
          alert('Identifiants incorrects');
        }
      } catch (error) {
        console.error("Erreur de connexion :", error);
        if (error.response?.data?.error) {
          alert(error.response.data.error);
        } else {
          alert('Erreur de connexion. Réessayez plus tard.');
        }
      }
    }
  },
};
</script>

<style scoped>
.login-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #f4f6f7;
  padding: 20px;
}

.login-header {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  max-width: 400px;
  margin-bottom: 30px;
}

.BOZ {
  width: 150px;
  height: auto;
  grid-column: 2;
}

.back-icon {
  cursor: pointer;
  color: #3498db;
  font-size: 24px;
  transition: color 0.3s ease;

}

.back-icon:hover {
  color: #2980b9;
}

h1 {
  font-size: 24px;
  color: #2c3e50;
  margin-bottom: 30px;
}

.login-form {
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 400px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-bottom: 25px;
}

input {
  padding: 12px;
  border: 1px solid #bdc3c7;
  border-radius: 8px;
  font-size: 16px;
  background-color: white;
  transition: border-color 0.3s ease;
}

input:focus {
  outline: none;
  border-color: #3498db;
}

.login-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.login-button:hover {
  background-color: #2980b9;
}

.register-prompt {
  margin-top: 20px;
  text-align: center;
}

.register-link {
  color: #3498db;
  cursor: pointer;
  margin-left: 5px;
  font-weight: bold;
  transition: color 0.3s ease;
}

.register-link:hover {
  color: #2980b9;
  text-decoration: underline;
}
</style>
