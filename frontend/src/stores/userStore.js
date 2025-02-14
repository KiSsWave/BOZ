import { defineStore } from 'pinia';
import { setToken, removeToken, getUser } from '@/services/authProvider';

export const useUserStore = defineStore('user', {
  state: () => ({
    user: getUser(),
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    login(token) {
      setToken(token);
      this.user = getUser();
    },
    logout() {
      removeToken();
      this.user = null;
    },
  },
});
