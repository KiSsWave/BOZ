<template>
    <div class="footer">
        <btn name="Contact">Contacter l'administrateur</btn>
        <btn name="Consulter">Consulter les transactions</btn>
    </div>
</template>

<style scoped>
body {
    height: auto;
    background-color: blue;
    .footer {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        column-gap: 5px;
        background-color: blue;
        height: 10%;
        width: 100%;
        bottom: 100px;

        btn {
            height: 80px;
            text-align: center;
            align-self: center;
            border-radius: 10px;
            background-color: rgb(221, 205, 205);
            box-shadow: 1px 1px 3px 2px black;
            font-size: 25px;
            vertical-align: middle;
            justify-content: center;
        }
    }
}
</style>