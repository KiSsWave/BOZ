<template>
  <div class="register-container">
    <div class="register-header">
      <img class="BOZ" src="../assets/logoBOZ.png" alt="BOZ Logo" />
    </div>

    <h1>Inscription</h1>

    <form @submit.prevent="envoie" class="register-form">
      <div class="input-group">
        <input type="text" v-model="form.login" placeholder="Login" required minlength="4" />
        <input type="email" v-model="form.email" placeholder="Email" required autocomplete="email" />
        <input type="password" v-model="form.password" placeholder="Mot de passe" required
          pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?:{}|<>]).{8,}"
          title="Le mot de passe doit contenir au moins 8 caractères, une minuscule, une majuscule, un chiffre et un caractère spécial" />
        <input type="password" v-model="form.confirmPassword" placeholder="Confirmer le mot de passe" required />
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
    async envoie() {
      if (this.form.password !== this.form.confirmPassword) {
        alert("Les mots de passe ne correspondent pas !");
        return;
      }

      const passwordValidation = this.validatePassword(this.form.password);
      if (!passwordValidation.isValid) {
        alert(`Le mot de passe doit contenir : ${passwordValidation.errors.join(', ')}`);
        return;
      }

      try {
        const response = await axios.post("/register", {
          login: this.form.login,
          email: this.form.email,
          password: this.form.password,
        });
        if (response.status === 200) {
          alert('Inscription réussie !');
          this.$router.push('/login');
        } else {
          alert('Erreur lors de l\'inscription de l\'utilisateur');
        }
      } catch (error) {
        console.error("Erreur lors de l'inscription :", error);
        if (error.response.data.message) {
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

.password-requirements {
  margin-top: 10px;
  font-size: 14px;
  color: #666;
}

.password-requirements ul {
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
</style>
