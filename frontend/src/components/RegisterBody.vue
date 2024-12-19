<template>
    <img class="BOZ" src="../assets/logoBOZ.png" />
    <h1>Inscription</h1>
    <form>
        <input type="text" name="login" placeholder="Nom d'utilisateur" />
        <input type="password" name="password" placeholder="Mot de passe" />
        <input type="password" name="password" placeholder="Conformer le mot de passe" />
        <button type="button">S'inscrire</button>
    </form>
    <div>
        <label> Déjà un compte ?</label>
        <label class="link" @click="login"> Se Connecter</label>
    </div>
</template>

<script>
export default {
    methods: {
        login() {
            this.$router.push('/login')
        },
        index() {
            this.$router.push('/')
        }
    }
}
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