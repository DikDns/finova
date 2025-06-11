import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from './pages/Dashboard.vue';
import BudgetDetail from './pages/users/BudgetDetail.vue';
import Budgets from './pages/users/Budgets.vue';
import Transactions from './pages/users/Transactions.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
    },
    {
        path: '/transactions',
        name: 'transactions',
        component: Transactions,
    },
    {
        path: '/budgets',
        name: 'budgets',
        component: Budgets,
    },
    {
        path: '/budgets/:id',
        name: 'budget-detail',
        component: BudgetDetail,
        props: true,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
