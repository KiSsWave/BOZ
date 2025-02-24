import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import AdminView from '@/views/AdminView.vue'
import VendeurView from '@/views/VendeurView.vue'
import ContactView from '@/views/ContactView.vue'
import UserTicketView from '@/views/UserTicketView.vue'
import TransactionView from '@/views/TransactionView.vue'
import ModifView from '@/views/ModifView.vue'
import { useUserStore } from '@/stores/userStore'
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminView,
      meta: { requiresAuth: true ,role: "3"}
    },
    {
      path: '/vendeur',
      name: 'vendeur',
      component: VendeurView,
      meta: { requiresAuth: true ,role: "2"}
    },
    {
      path: '/contact',
      name: 'contact',
      component: ContactView,
      meta: { requiresAuth: true }
    },
    {
      path: '/userTicket',
      name: 'userTicket',
      component: UserTicketView,
      meta: { requiresAuth: true }
    },
    {
      path: '/transaction',
      name: 'transaction',
      component: TransactionView,
      meta: { requiresAuth: true }
    },
    {
      path: '/modification',
      name: 'modification',
      component: ModifView,
      meta: { requiresAuth: true }
    }


  ],
})
router.beforeEach((to, from, next) => {
  const userStore = useUserStore();

  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    next("/login");
  } else if (to.meta.role && String(userStore.user?.role) !== String(to.meta.role)) {

    next("/");
  } else {
    next();
  }
});

export default router
