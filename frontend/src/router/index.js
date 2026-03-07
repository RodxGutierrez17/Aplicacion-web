import { createRouter, createWebHistory } from 'vue-router'
import { isAuthenticated } from '../auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    redirect: '/ordenes'
  },
  {
    path: '/clientes',
    name: 'clientes',
    component: () => import('../views/customers/CustomersView.vue')
  },
  {
    path: '/vehiculos',
    name: 'vehiculos',
    component: () => import('../views/vehicles/VehiclesView.vue')
  },
  {
    path: '/mecanicos',
    name: 'mecanicos',
    component: () => import('../views/mechanics/MechanicsView.vue')
  },
  {
    path: '/ordenes',
    name: 'ordenes',
    component: () => import('../views/orders/OrdersListView.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth === false) return true
  if (!isAuthenticated()) {
    return { path: '/login' }
  }
  return true
})

export default router
