<template>
  <img class="BOZ" src="../assets/logoBOZ.png" />
  <h1>Inscription</h1>
  <form @submit.prevent="envoie">
      <input
          type="text"
          name="login"
          v-model="form.login"
          placeholder="Login"
          required
      />
      <input
          type="email"
          name="email"
          v-model="form.email"
          placeholder="Email"
          required
      />
      <input
          type="password"
          name="password"
          v-model="form.password"
          placeholder="Mot de passe"
          required
      />
      <input
          type="password"
          name="confirmPassword"
          v-model="form.confirmPassword"
          placeholder="Confirmer le mot de passe"
          required
      />
      <button type="submit">S'inscrire</button>
  </form>
  <div>
      <label>Déjà un compte ?</label>
      <label class="link" @click="connexion"> Se Connecter</label>
  </div>
</template>

<script>
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
          console.log("Formulaire soumis avec les données :", this.form);
          try {
              const response = await fetch("http://localhost:44050/register", {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({
                      login: this.form.login,
                      email: this.form.email,
                      password: this.form.password,
                  }),
              });
              console.log("Requête envoyée, en attente de réponse...");
              if (response.ok) {
                  const data = await response.json();
                  console.log("la Réponse du serveur :", data);
              } else {
                  console.error('Réponse du serveur :', response.status, response.statusText);
              }
          } catch (error) {
              console.error("Erreur lors de l'envoi :", error);
          }
      },
  },
};
</script>


<style scoped>
body {

  display: grid;
  grid-template-columns: 2fr 1fr 2fr;
  text-align: center;
  align-items: center;
  justify-content: center;

  div{
      text-align: center;
  }
  .link{
      color: blue;
      cursor: pointer;
  }
  .link:hover{
          color: red;
      }
  form {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      margin: 5%;
      columns: 2;
  }

  img {
      columns: 2;
      width: 15%;
      padding: 0;
      margin: 0;
      align-self: middle;
      text-align: middle;
      justify-content: middle;

  }

  .back{
      cursor: pointer;
      width: 5%;
      top:0;
      border:solid red 5px;
  }

  h1 {
      font-size: 30px;
      columns: 2;
      color: black;
  }

  input {
      background-color: antiquewhite;
      columns: 2;
      color: gray;
      margin: 5%;
  }

  button {
      background-color: black;
      columns: 2;
      color: white;
      width: 20%;
      border-radius: 10px;
  }
}
</style>
