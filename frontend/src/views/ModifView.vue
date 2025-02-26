<template>
  <HeaderComponent/>
  <div class="modif-container">
    <div class="modif-card">
      <h1>Modifier votre profil</h1>
      <form @submit.prevent="envoie" class="modif-form">
        <div class="input-group">
          <label for="user">Login</label>
          <input id="user" type="text" placeholder="Votre login" v-model="form.user" required />
        </div>

        <div class="input-group">
          <label for="email">Email</label>
          <input id="email" type="email" placeholder="Votre email" v-model="form.email"  autocomplete="email" />
        </div>

        <div class="input-group">
          <label for="password">Votre mot de passe actuel</label>
          <input id="Oldpassword" type="password" placeholder="Mot de passe actuel" v-model="form.oldpassword" required
          />
        </div>

        <div class="input-group">
          <label for="password">Votre nouveau mot de passe</label>
          <input id="password" type="password" placeholder="Nouveau mot de passe" v-model="form.password" required
            autocomplete="current-password" />
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
  </div>
</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue';
export default {
  data() {
    return {
      form: {
        user: '',
        email: '',
        oldpassword: '',
        password: '',
      },
    };
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
    async envoie() {
      if (this.form.password === this.form.oldpassword) {
        alert("Les mots de passe sont identiques !");
        return;
      }

      const passwordValidation = this.validatePassword(this.form.password);
      if (!passwordValidation.isValid) {
        alert(`Le mot de passe doit contenir : ${passwordValidation.errors.join(', ')}`);
        return;
      }
      try {
        // const response = await axios.patch("/modification", this.form);

      } catch (error) {
        console.error("Erreur lors de la modification du profil :", error);
      }
    },
  },
  components: {
      HeaderComponent,
    },
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

.modif-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

h1 {
  font-size: 22px;
  color: #2c3e50;
  margin-bottom: 20px;
}

.modif-form {
  display: flex;
  flex-direction: column;
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

.modif-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.modif-button:hover {
  background-color: #2980b9;
}

.password-requirements {
  margin-top: 10px;
  font-size: 14px;
  color: #666;
  text-align: left;
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
