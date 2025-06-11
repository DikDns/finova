<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const isOpen = ref(false)
</script>

<template>
  <header class="sticky top-0 z-50 bg-background border-b border-border shadow-sm">
    <div class="container mx-auto px-4 sm:px-6 md:px-10 py-4 flex justify-between items-center">
      <!-- Logo -->
      <Link href="/" class="flex items-center">
        <img src="/finova-logo.svg" alt="Finova Logo" class="w-10 h-10" />
        <span class="ml-2 text-xl font-serif font-bold text-primary">FINOVA</span>
      </Link>

      <!-- Desktop Navigation -->
      <nav class="hidden md:flex items-center space-x-8">
        <Link href="/features" class="text-muted-foreground hover:text-primary font-medium transition-colors">Fitur</Link>
        <Link href="/pricing" class="text-muted-foreground hover:text-primary font-medium transition-colors">Harga</Link>
        <Link href="/login" class="text-muted-foreground hover:text-primary font-medium transition-colors">Masuk</Link>
        <Button as-child class="bg-primary/90 hover:bg-primary text-background rounded-full">
          <Link href="/register">Daftar Sekarang</Link>
        </Button>
      </nav>

      <!-- Simple Hamburger (icon fixed, no toggle icon change) -->
      <div class="md:hidden cursor-pointer" @click="isOpen = !isOpen" aria-label="Toggle menu">
        <!-- Hamburger Icon: tiga garis -->
        <svg class="w-6 h-6 text-muted-foreground hover:text-primary" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </div>
    </div>

    <!-- Mobile Menu -->
    <nav v-show="isOpen" class="md:hidden bg-background border-t border-border shadow-inner">
      <div class="px-4 pt-2 pb-4 space-y-1">
        <Link @click="isOpen = false" href="/features" class="block px-3 py-2 rounded-md text-base font-medium text-muted-foreground hover:text-primary hover:bg-muted transition">Fitur</Link>
        <Link @click="isOpen = false" href="/pricing" class="block px-3 py-2 rounded-md text-base font-medium text-muted-foreground hover:text-primary hover:bg-muted transition">Harga</Link>
        <Link @click="isOpen = false" href="/login" class="block px-3 py-2 rounded-md text-base font-medium text-muted-foreground hover:text-primary hover:bg-muted transition">Masuk</Link>
        <Button as-child class="w-full bg-primary/90 hover:bg-primary text-background rounded-full mt-2">
          <Link @click="isOpen = false" href="/register">Daftar Sekarang</Link>
        </Button>
      </div>
    </nav>
  </header>
</template>