<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ref } from 'vue'
import { Wallet, BarChart, PieChart, Bell, DollarSign } from 'lucide-vue-next'

const mobileMenuOpen = ref(false)

const fiturItems = [
  {
    icon: DollarSign, 
    title: 'Atur Keuangan',
    color: '#4f46e5'
  },
  {
    icon: Bell, 
    title: 'Pengingat Pengeluaran',
    color: '#4f46e5'
  },
  { 
    icon: Wallet, 
    title: 'Kelola Tabungan',
    color: '#4f46e5'
  },
  { 
    icon: BarChart, 
    title: 'Laporan Pengeluaran',
    color: '#4f46e5'
  },
  { 
    icon: PieChart, 
    title: 'Laporan Pemasukan',
    color: '#4f46e5'
  }
]
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Head title="Finova - Finansial dalam Genggaman" />

    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 bg-background border-b border-border shadow-sm">
      <div class="container mx-auto px-10 py-4">
        <div class="flex justify-between items-center">
          <!-- Logo -->
          <Link href="/" class="flex items-center">
            <img src="/finova-logo.svg" alt="Finova Logo" class="w-10 h-10" />
            <span class="ml-2 text-xl font-bold text-primary-foreground">FINOVA</span>
          </Link>

          <!-- Mobile menu button -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden text-muted-foreground hover:text-foreground focus:outline-none"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Desktop Navigation -->
          <div class="hidden md:flex items-center space-x-8">
            <Link href="/features" class="text-muted-foreground hover:text-primary-foreground font-medium transition-colors">Fitur</Link>
            <Link href="/pricing" class="text-muted-foreground hover:text-primary-foreground font-medium transition-colors">Harga</Link>
            <Link href="/login" class="text-muted-foreground hover:text-primary-foreground font-medium transition-colors">Masuk</Link>
            <Button as-child class="bg-primary-foreground/90 hover:bg-primary-foreground text-background rounded-full">
              <Link href="/register">Daftar Sekarang</Link>
            </Button>
          </div>
        </div>

        <!-- Mobile Navigation -->
        <div v-if="mobileMenuOpen" class="mt-4 md:hidden space-y-4">
          <Link href="/features" class="block text-muted-foreground hover:text-primary font-medium">Fitur</Link>
          <Link href="/pricing" class="block text-muted-foreground hover:text-primary font-medium">Harga</Link>
          <Link href="/login" class="block text-muted-foreground hover:text-primary font-medium">Masuk</Link>
          <Button as-child class="w-full bg-primary hover:bg-primary/90 text-primary-foreground rounded-full">
            <Link href="/register">Daftar Sekarang</Link>
          </Button>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-muted py-16 flex-grow">
      <div class="container mx-auto px-12">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div class="space-y-5">
            <h1 class="text-6xl font-bold leading-tight">
              <span class="font-serif text-muted-foreground">Finansial dalam</span><br />
              <span class="font-serif text-primary-foreground">Genggaman</span>
            </h1>
            <p class="text-muted-foreground">Kelola keuangan dengan Finova</p>
            <div class="pt-6">
              <Button as-child class="bg-primary-foreground hover:bg-primary-foreground/90 text-background rounded-full">
                <Link href="/register">Mulai Hidup Elegan</Link>
              </Button>
            </div>
          </div>
          <div class="flex justify-center">
            <img src="/peeps.webp" alt="Hero Image" class="w-full max-w-md" />
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonial Section -->
    <section class="bg-muted py-16">
      <div class="container mx-auto px-10 text-center">
        <h2 class="text-2xl font-medium text-muted-foreground mb-8 italic">
          "Finova bukan sekadar aplikasi, tapi kunci keuangan yang lebih teratur dan tenang."
        </h2>
        <p class="text-lg font-medium">
          <span class="text-muted-foreground">Rafi Zamzami</span>,
          <span class="text-muted-foreground/70">Media Social Student Associate</span>
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 items-center mt-16">
          <template v-for="logo in ['/ascendia.webp', '/himarpl.webp', '/manifest.webp', '/explora.webp']" :key="logo">
            <div class="flex justify-center">
              <img :src="logo" alt="Partner Logo" class="h-12 grayscale brightness-0 opacity-25 hover:grayscale-0 hover:brightness-100 hover:opacity-100 transition duration-300" />
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- Pengingat Batas Pengeluaran Section -->
    <section class="bg-background py-16">
      <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div class="flex justify-center">
            <img src="/bell.webp" alt="Notification Bell" class="w-full max-w-md" />
          </div>
          <div class="space-y-6">
            <h1 class="font-serif font-bold text-6xl text-foreground leading-tight">
              Pengingat Batas Pengeluaran
            </h1>
            <p class="text-lg text-muted-foreground">
              Fitur ini membantu mu mengelola keuangan dengan memberikan pengingat saat pengeluaran mencapai batas yang ditetapkan.
            </p>
            <div class="pt-4">
              <Button as-child class="bg-primary-foreground/90 hover:bg-primary-foreground text-background rounded-full">
                <Link href="/features/expense-alert">Pelajari Lebih Lanjut</Link>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Carousel Section -->
    <section class="bg-muted py-16">
      <div class="container mx-auto px-4 text-center space-y-6">
        <h2 class="font-serif text-6xl font-bold text-muted-foreground">Bisa ngapain aja sih Finova?</h2>
        <p class="text-muted-foreground text-base">Finova bisa untuk...</p>

        <div class="overflow-hidden" ref="embla">
          <div class="flex space-x-6 embla__container">
            <div 
              v-for="(item, index) in fiturItems" 
              :key="index"
              class="flex-shrink-0 w-full sm:w-[80%] md:w-[50%] lg:w-[33.33%] bg-card p-6 rounded-lg shadow-md embla__slide text-center"
            >
              <div class="flex justify-center mb-4">
                <component 
                  :is="item.icon" 
                  class="w-10 h-10 mx-auto"
                  :style="{ color: item.color }"
                />
              </div>
              <h3 class="text-xl font-semibold text-card-foreground">{{ item.title }}</h3>
            </div>
          </div>
        </div>

        <div class="flex justify-center gap-3 mt-6" ref="emblaDots">
          <button 
            v-for="n in fiturItems.length" 
            :key="n" 
            class="w-3 h-3 rounded-full bg-muted-foreground/40 embla-dot" 
            :class="{ 'bg-muted-foreground/70': n === 1 }" 
          />
        </div>
      </div>
    </section>


    <!-- Kontrol Uangmu Section -->
    <section class="bg-background py-16">
      <div class="container mx-auto px-12">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div class="space-y-6">
            <h1 class="font-serif font-bold text-6xl text-foreground leading-tight">
              Uangmu Kendalimu
            </h1>
            <p class="text-lg text-muted-foreground">
              Kontrol penuh atas keuangan pribadi Anda dengan fitur yang komprehensif
            </p>
            <div class="flex flex-wrap gap-4 pt-4">
              <Button as-child class="bg-primary-foreground/90 hover:bg-primary-foreground text-background rounded-full">
                <Link href="/features/money-control">Mulai Kendalikan Uangmu</Link>
              </Button>
              <Button as-child class="bg-primary/90 text-primary-foreground hover:bg-primary rounded-full">
                <Link href="/features">Lihat Fitur Lainnya</Link>
              </Button>
            </div>
          </div>
          <div class="flex justify-center">
            <img src="/handMoney.webp" alt="Hand picking gold coins" class="w-full max-w-md" />
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-transparent py-8">
      <div class="container mx-auto px-6 text-center">
        <p class="text-muted-foreground">© 2025 Finova. All Rights Reserved.</p>
      </div>
    </footer>
  </div>
</template>
