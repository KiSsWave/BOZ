<template>
  <img class="BOZ" src="../assets/logoBOZ.png" />
  <img class="back" src="../assets/back.png" @click="index" />
  <h1>Content de vous revoir !</h1>
  <form>
      <input type="text" name="email" placeholder="Email" v-model="form.email" />
      <input type="password" name="password" placeholder="Mot de passe" v-model="form.password" />
      <button type="submit" @click.prevent="envoie">Se connecter</button>
  </form>
  <div>
      <label> Nouveau sur l'application ?</label>
      <label class="link" @click="register"> Créer un compte</label>
  </div>
</template>

<script>
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
          console.log("Méthode `envoie` appelée avec :", this.form);

          try {
              const response = await fetch("http://localhost:44050/signin", {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({
                      email: this.form.email,
                      password: this.form.password,
                  }),
              });

              console.log("Requête envoyée, en attente de réponse...");

              if (response.ok) {
                  const data = await response.json();
                  console.log("Réponse du serveur :", data);
                  localStorage.setItem('token', data.token);
              } else {
                  console.error('Réponse du serveur :', response.status);
              }
          } catch (error) {
              console.error("Erreur dans le bloc `try-catch` :", error);
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

  div {
      text-align: center;
  }

  .link {
      color: blue;
      cursor: pointer;
  }

  .link:hover {
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

  .back {
      cursor: pointer;
      width: 5%;
      top: 0;
      border: solid red 5px;
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
