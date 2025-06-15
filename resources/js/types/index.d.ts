import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface AccountType {
    id: string;
    type: string;
    isActive: boolean;
    accounts: {
        id: string;
        name: string;
        url: string;
        balance: number;
    }[];
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: string;
    name: string;
    username: string;
    email: string;
    role: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: string;
    name: string;
    budget_id: string;
    category_group_id: string;
    category_budgets: CategoryBudget[];
}

export interface CategoryBudget {
    id: string;
    assigned: string;
    activity: string;
    available: string;
    category_id: string;
    created_at: string;
    updated_at: string;
    monthly_budget_id: string;
    monthly_budget: MonthlyBudget;
}

export interface CategoryGroup {
    id: string;
    name: string;
    budget_id: string;
    categories: Category[];
}

export interface MonthlyBudget {
    id: string;
    month: string;
    total_balance: string;
    total_assigned: string;
    total_activity: string;
    total_available: string;
    created_at: string;
    updated_at: string;
}

export interface Budget {
    id: string;
    name: string;
    description: string;
    currency_code: string;
    monthly_budgets: MonthlyBudget[];
    category_groups: CategoryGroup[];
    created_at: string;
    updated_at: string;
}

export interface Transaction {
    id: string;
    account_id: string;
    category_id?: string;
    budget_id: string;
    payee: string;
    date: string;
    amount: number;
    memo: string;
    category?: Category;
}

export interface Account {
    id: string;
    budget_id: string;
    name: string;
    type: string;
    balance: number;
    interest: number;
    minimum_payment_monthly: number;
    transactions?: Transaction[];
}

export type BreadcrumbItemType = BreadcrumbItem;
