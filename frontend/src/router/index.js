import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/orders/OrdersListView.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
