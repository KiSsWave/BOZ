<template>
  <header>
      <img class="BOZ" src="../assets/logoBOZ.png" />
      <h1>Boz - Dépensez n'importe où !</h1>
      <img class="user" src="../assets/user.png" @click="login" />
  </header>
  <body>
        <label>VOTRE SOLDE EST DE :</label>
        <div>
         €</div>
    </body>
    <footer>
        <boutton name="Contact">Contacter l'administrateur</boutton>
        <boutton name="Consulter">Consulter les transactions</boutton>
    </footer>
</template>


<script>

export default {
  methods: {
      login() {
          this.$router.push('/login')
      }
  }
}
</script>


<style scoped>
header {
  position: sticky;
  display: grid;
  grid-template-columns: 1fr 2fr 1fr;
  grid-row: auto;
  justify-content: space-between;
  align-items: center;
  top: 0;
  width: 100%;
  height: auto;
  padding: 0;
  margin: 0;

  .user {
      width: 25%;
      padding: 0;
      margin: 0;
      top:0;
      columns:3;
      cursor:pointer;
      justify-content: end;
      border:solid red 5px;

  }

  .BOZ {
      width: 50%;
      padding: 0;
      margin: 0;
      columns:1;
  }
  h1{
      text-align: center;
      padding: 0;
      margin: 0;
  }
}
body {
    color: blue;
    flex-direction: column;
    display: flex;
    font-size: 30px;
    text-align: center;
    height: 15%;
    label{
        margin-bottom:1%;
    }
    div {
        text-overflow: fill;
        align-self: center;
        font-size: 50px;
        color: blue;
        height: 150px;
        width: 20%;
        border: dashed 3px black;
        border-radius: 40px;
        padding: 1%;
        padding-top:3%;
        text-align: end;
    }

    footer {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    column-gap: 5px;
    background-color: blue;
    height: 100%;
    width: 100%;
    bottom: 0px;
align-items: center;
    boutton {
        width: 25%;
        height: 100%;
        text-align: center;

        border-radius: 10px;
        background-color: rgb(221, 205, 205);
        box-shadow: 1px 1px 3px 2px black;
        font-size: 25px;
    }
}
}
</style>
