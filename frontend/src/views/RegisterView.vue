<template>
  <div class="register-container">
    <div class="register-header">
      <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
    </div>

    <h1>Inscription</h1>

    <form @submit.prevent="envoie" class="register-form">
      <div class="input-group">
        <input type="text" v-model="form.login" placeholder="Login" required />
        <input type="email" v-model="form.email" placeholder="Email" required autocomplete="email" />
        <input type="password" v-model="form.password" placeholder="Mot de passe" required minlength="8" />
        <input type="password" v-model="form.confirmPassword" placeholder="Confirmer le mot de passe" required
          minlength="8" />
      </div>

      <button type="submit" class="register-button">
        S'inscrire
      </button>
    </form>

    <div class="login-prompt">
      <span>Déjà un compte ?</span>
      <label class="login-link" @click="connexion">
        Se Connecter
      </label>
    </div>
  </div>
</template>

<script>
import axios from '../api/index.js';
export default {
  data() {
    return {
      form: {
        login: '',
        email: '',
        password: '',
        confirmPassword: '',
      },
    };
  },
  methods: {
    connexion() {
      this.$router.push('/login');
    },
    async envoie() {
      if (this.form.password !== this.form.confirmPassword) {
        alert("Les mots de passe ne correspondent pas !");
        return;
      }

      try {
        const response = await axios.post("register", {
          login: this.form.login,
          email: this.form.email,
          password: this.form.password,
        });

        if (response.data) {
          alert('Inscription réussie !');
          this.$router.push('/login');
        } else {
          alert('Erreur lors de l\'inscription');
        }
      } catch (error) {
        console.error("Erreur lors de l'inscription :", error);
        if (error.response && error.response.data && error.response.data.message) {
          alert(error.response.data.message);
        } else {
          alert("Erreur lors de l'inscription. Veuillez réessayer.");
        }
      }
    },
  },
};
</script>

<style scoped>
.register-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #f4f6f7;
  padding: 20px;
}

.register-header {
  margin-bottom: 30px;
}

.BOZ {
  width: 150px;
  height: auto;
}

h1 {
  font-size: 24px;
  color: #2c3e50;
  margin-bottom: 30px;
}

.register-form {
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

.register-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.register-button:hover {
  background-color: #2980b9;
}

.login-prompt {
  margin-top: 20px;
  text-align: center;
}

.login-link {
  color: #3498db;
  cursor: pointer;
  margin-left: 5px;
  font-weight: bold;
  transition: color 0.3s ease;
}

.login-link:hover {
  color: #2980b9;
  text-decoration: underline;
}
</style>
