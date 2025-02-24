<template>
  <HeaderComponent />
  <h1>Consulter les tickets</h1>
  <h2>Affiche les tickets de l'user</h2>
  <!-- <div class="ticket-container">
    <div class="ticket-card">
      <div class="ticket-details">
        <div class="ticket-header">
          <span class="ticket-type">{{ ticket.type }}</span>
        </div>
        <div class="ticket-body">
          <p class="ticket-message">{{ ticket.message }}</p>
          <p clas="ticket-date">{{ ticket.date }}</p>
        </div>
      </div>

    </div>
  </div> -->

</template>

<script>
import HeaderComponent from '@/components/HeaderComponent.vue';
import { useUserStore } from '@/stores/userStore';
import axios from '../api/index.js';


export default {
  data() {
    return {
      data: {
        ticket: {
          type: 'Autre',
          message: 'Le message du ticket',
          date: '01/01/2021 12:00',
        },
      },
    };
  },
  components: {
    HeaderComponent,
  },
  methods: {

  },

};

</script>

<style scoped></style>
