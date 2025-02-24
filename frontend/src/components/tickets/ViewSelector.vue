<template>
  <div class="view-selector">
    <button
      :class="{ 'active': modelValue === 'pending' }"
      @click="$emit('change-view', 'pending')"
    >
      Tickets en attente
    </button>
    <button
      :class="{ 'active': modelValue === 'myTickets' }"
      @click="$emit('change-view', 'myTickets')"
    >
      Mes tickets
    </button>
  </div>
</template>

<script>
export default {
  name: 'ViewSelector',
  props: {
    modelValue: {
      type: String,
      required: true
    }
  },
  emits: ['change-view']
}
</script>

<style scoped>
.view-selector {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 30px;
}

.view-selector button {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  background-color: #fff;
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: bold;
}

.view-selector button.active {
  background-color: #3498db;
  color: white;
}

@media (max-width: 768px) {
  .view-selector {
    flex-direction: column;
    gap: 10px;
  }

  .view-selector button {
    width: 100%;
  }
}
</style>
