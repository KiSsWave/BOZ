<template>

    <body>
        <label>VOTRE SOLDE EST DE :</label>
        <div>
         €</div>
    </body>
</template>

<style scoped>
body {
    color: blue;
    flex-direction: column;
    display: flex;
    font-size: 30px;
    text-align: center;
    height: 150px;
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


}
</style>