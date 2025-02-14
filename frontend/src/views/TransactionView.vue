<template>
  <div>
    <HeaderComponent />
    <h1>Transactions</h1>
_
    <h1>TODO FAIRE LA LISTE DES TRANSACTIONS ET UN ELEMENT=LE COMPOSANT</h1>
  </div>
</template>

<script>
import HeaderComponent from '../components/HeaderComponent.vue'

export default {
  name: 'TransactionView',
  components: {
    HeaderComponent,
  },
}
</script>

<style >
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 10;
}

.BOZ {
  width: 80px;
  height: auto;
}

header h1 {
  font-size: 1.2rem;
  color: #2c3e50;
  text-align: center;
  flex-grow: 1;
}

.user-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.user-icon:hover {
  transform: scale(1.2);
  color: #2980b9
}

.exit {
  margin-left: 10px;
  margin-right:10px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.exit:hover {
  transform: scale(1.2);
  color: red;
}



</style>
