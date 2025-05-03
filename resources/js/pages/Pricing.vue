<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <h1 class="font-serif text-2xl font-bold">Pricing</h1>

    <!-- Template Navigation -->
    <div class="mt-8 flex gap-x-2">
        <Link
            :href="route('home')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >Home</Link
        >
        <Link
            :href="route('features')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >Features</Link
        >
        <Link
            :href="route('pricing')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >Pricing</Link
        >
    </div>
</template>
