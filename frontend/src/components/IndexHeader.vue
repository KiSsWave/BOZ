<template>
    <header>
        <img class="BOZ" src="../assets/logoBOZ.png" />
        <h1>Boz - Dépensez n'importe où !</h1>
        <img class="user" src="../assets/user.png" @click="login" />
    </header>
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
</style>