<template>
  <HeaderComponent />
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
          <input id="email" type="email" placeholder="Votre email" v-model="form.email" required autocomplete="email" />
        </div>

        <div class="input-group">
          <label for="password">Mot de passe</label>
          <input id="password" type="password" placeholder="Votre mot de passe" v-model="form.password" required
            autocomplete="current-password" />
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
        password: '',
      },
    };
  },
  methods: {
    async envoie() {
      try {
        // const response = await axios.post("/modification", this.form);

      } catch (error) {
        console.error("Erreur lors de la modification du profil :", error);
      }
    },
    components: {
      HeaderComponent,
    },
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
</style>
