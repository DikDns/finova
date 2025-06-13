<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const isOpen = ref(false);

const { props } = usePage<SharedData>();
const user = props.auth.user;

console.log(user);
</script>

<template>
    <header class="bg-background border-border sticky top-0 z-50 border-b shadow-sm">
        <div class="container mx-auto flex items-center justify-between px-4 py-4 sm:px-6 md:px-10">
            <!-- Logo -->
            <Link href="/" class="flex items-center">
                <img src="/finova-logo.svg" alt="Finova Logo" class="h-10 w-10" />
                <span class="text-primary ml-2 font-serif text-xl font-bold">FINOVA</span>
            </Link>

            <!-- Desktop Navigation -->
            <nav class="hidden items-center space-x-8 md:flex">
                <Link href="/features" class="text-muted-foreground hover:text-primary font-medium transition-colors">Fitur</Link>
                <Link href="/pricing" class="text-muted-foreground hover:text-primary font-medium transition-colors">Harga</Link>
                <template v-if="user">
                    <Button as-child class="bg-primary/90 hover:bg-primary text-background rounded-full">
                        <Link href="/budgets/recent">Kelola Budget</Link>
                    </Button>
                </template>
                <template v-else>
                    <Link href="/login" class="text-muted-foreground hover:text-primary font-medium transition-colors">Masuk</Link>
                    <Button as-child class="bg-primary/90 hover:bg-primary text-background rounded-full">
                        <Link href="/register">Daftar Sekarang</Link>
                    </Button>
                </template>
            </nav>

            <!-- Simple Hamburger (icon fixed, no toggle icon change) -->
            <div class="cursor-pointer md:hidden" @click="isOpen = !isOpen" aria-label="Toggle menu">
                <!-- Hamburger Icon: tiga garis -->
                <svg
                    class="text-muted-foreground hover:text-primary h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    viewBox="0 0 24 24"
                >
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </div>
        </div>

        <!-- Mobile Menu -->
        <nav v-show="isOpen" class="bg-background border-border border-t shadow-inner md:hidden">
            <div class="space-y-1 px-4 pt-2 pb-4">
                <Link
                    @click="isOpen = false"
                    href="/features"
                    class="text-muted-foreground hover:text-primary hover:bg-muted block rounded-md px-3 py-2 text-base font-medium transition"
                    >Fitur</Link
                >
                <Link
                    @click="isOpen = false"
                    href="/pricing"
                    class="text-muted-foreground hover:text-primary hover:bg-muted block rounded-md px-3 py-2 text-base font-medium transition"
                    >Harga</Link
                >
                <template v-if="user">
                    <Button as-child class="bg-primary/90 hover:bg-primary text-background mt-2 w-full rounded-full">
                        <Link @click="isOpen = false" href="/budgets/recent">Dashboard</Link>
                    </Button>
                </template>
                <template v-else>
                    <Link
                        @click="isOpen = false"
                        href="/login"
                        class="text-muted-foreground hover:text-primary hover:bg-muted block rounded-md px-3 py-2 text-base font-medium transition"
                        >Masuk</Link
                    >
                    <Button as-child class="bg-primary/90 hover:bg-primary text-background mt-2 w-full rounded-full">
                        <Link @click="isOpen = false" href="/register">Daftar Sekarang</Link>
                    </Button>
                </template>
            </div>
        </nav>
    </header>
</template>
