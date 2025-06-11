<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Wallet, BarChart, PieChart, Bell, DollarSign } from 'lucide-vue-next'
import Navbar from '@/components/NavBar.vue'

const fiturItems = [
  { icon: DollarSign, title: 'Atur Keuangan', color: '#4f46e5' },
  { icon: Bell, title: 'Pengingat Pengeluaran', color: '#4f46e5' },
  { icon: Wallet, title: 'Kelola Tabungan', color: '#4f46e5' },
  { icon: BarChart, title: 'Laporan Pengeluaran', color: '#4f46e5' },
  { icon: PieChart, title: 'Laporan Pemasukan', color: '#4f46e5' }
]
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Head title="Finova - Finansial dalam Genggaman" />

    <!-- Import Navbar Component -->
    <Navbar />

    <!-- Hero Section -->
    <section class="bg-muted py-16 flex-grow">
      <div class="container mx-auto px-4 sm:px-6 md:px-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div class="space-y-5 text-center md:text-left">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold leading-tight">
              <span class="font-serif text-muted-foreground">Finansial dalam</span><br />
              <span class="font-serif text-primary">Genggaman</span>
            </h1>
            <p class="text-muted-foreground">Kelola keuangan dengan Finova</p>
            <div class="pt-6 flex justify-center md:justify-start">
              <Button as-child class="bg-primary hover:bg-primary/90 text-background rounded-full">
                <Link href="/register">Mulai Hidup Elegan</Link>
              </Button>
            </div>
          </div>
          <div class="flex justify-center">
            <img src="/peeps.webp" alt="Hero Image" class="w-full max-w-md h-auto" />
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonial Section -->
    <section class="bg-muted py-16">
      <div class="container mx-auto px-4 sm:px-6 md:px-12 text-center">
        <h2 class="text-2xl font-medium text-muted-foreground mb-8 italic max-w-3xl mx-auto">
          "Finova bukan sekadar aplikasi, tapi kunci keuangan yang lebih teratur dan tenang."
        </h2>
        <p class="text-lg font-medium">
          <span class="text-muted-foreground">Rafi Zamzami</span>,
          <span class="text-muted-foreground/70">Media Social Student Associate</span>
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 items-center mt-16">
          <template v-for="logo in ['/ascendia.webp', '/himarpl.webp', '/manifest.webp', '/explora.webp']" :key="logo">
            <div class="flex justify-center">
              <img
                :src="logo"
                alt="Partner Logo"
                class="h-12 grayscale brightness-0 opacity-25 hover:grayscale-0 hover:brightness-100 hover:opacity-100 transition duration-300"
              />
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- Pengingat Batas Pengeluaran Section -->
    <section class="bg-background py-16">
      <div class="container mx-auto px-4 sm:px-6 md:px-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div class="flex justify-center">
            <img src="/bell.webp" alt="Notification Bell" class="w-full max-w-md h-auto" />
          </div>
          <div class="space-y-6 text-center md:text-left">
            <h1 class="font-serif font-bold text-4xl sm:text-5xl md:text-6xl text-foreground leading-tight">
              Pengingat Batas Pengeluaran
            </h1>
            <p class="text-lg text-muted-foreground">
              Fitur ini membantu mu mengelola keuangan dengan memberikan pengingat saat pengeluaran mencapai batas yang ditetapkan.
            </p>
            <div class="pt-4 flex justify-center md:justify-start">
              <Button as-child class="bg-primary/90 hover:bg-primary text-background rounded-full">
                <Link href="/features/expense-alert">Pelajari Lebih Lanjut</Link>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Carousel Section -->
    <section class="bg-muted py-16">
      <div class="container mx-auto px-4 sm:px-6 md:px-12 text-center space-y-6">
        <h2 class="font-serif text-4xl sm:text-5xl md:text-6xl font-bold text-muted-foreground">
          Bisa ngapain aja sih Finova?
        </h2>
        <p class="text-muted-foreground text-base max-w-xl mx-auto">Finova bisa untuk...</p>

        <div class="overflow-hidden" ref="embla">
          <div class="flex space-x-6 embla__container">
            <div 
              v-for="(item, index) in fiturItems" 
              :key="index"
              class="flex-shrink-0 w-full sm:max-w-xs md:w-1/2 lg:w-1/3 bg-card p-6 rounded-lg shadow-md embla__slide text-center"
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
      <div class="container mx-auto px-4 sm:px-6 md:px-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div class="space-y-6 text-center md:text-left">
            <h1 class="font-serif font-bold text-4xl sm:text-5xl md:text-6xl text-foreground leading-tight">
              Uangmu Kendalimu
            </h1>
            <p class="text-lg text-muted-foreground">
              Kontrol penuh atas keuangan pribadi Anda dengan fitur yang komprehensif
            </p>
            <div class="flex flex-wrap gap-4 pt-4 justify-center md:justify-start">
              <Button as-child class="bg-primary/90 text-background hover:bg-primary rounded-full">
                <Link href="/features/money-control">Mulai Kendalikan Uangmu</Link>
              </Button>
              <Button as-child class="bg-primary/90 text-background hover:bg-primary rounded-full">
                <Link href="/features">Lihat Fitur Lainnya</Link>
              </Button>
            </div>
          </div>
          <div class="flex justify-center">
            <img src="/handMoney.webp" alt="Hand picking gold coins" class="w-full max-w-md h-auto" />
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-transparent py-8">
      <div class="container mx-auto px-4 sm:px-6 md:px-12 text-center">
        <p class="text-muted-foreground">© 2025 Finova. All Rights Reserved.</p>
      </div>
    </footer>
  </div>
</template>
