<script setup lang="ts">
import Navbar from '@/components/common/NavBar.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeSlide = ref(0);

const changeSlide = (index) => {
  activeSlide.value = index;
};

</script>

<template>  
  <!-- Main Content -->
    <div class="flex min-h-screen flex-col">
    <Head title="Finova - Features" />

    <!-- Import Navbar Component -->
    <Navbar />

        <!-- Hero Section -->
        <section class="bg-muted flex-grow py-16">
            <div class="container mx-auto px-4 sm:px-6 md:px-12">
                <div class="flex flex-col-reverse items-center gap-12 md:grid md:grid-cols-2 md:items-center">
                    <div class="space-y-5 text-center md:text-left">
                        <h1 class="text-4xl leading-tight font-bold sm:text-5xl md:text-6xl">
                            <span class="text-muted-foreground font-serif">Atur Keuangan</span><br />
                            <span class="text-primary font-serif">Jadi Lebih Mudah!</span>
                        </h1>
                        <p class="text-muted-foreground">Pantau pemasukan dan pengeluaran, dapatkan rekomendasi keuangan berbasis AI, dan buat laporan otomatis dalam satu platform.</p>
                        <div class="flex justify-center pt-6 md:justify-start">
                            <Button as-child class="w-full md:w-auto bg-primary hover:bg-primary/90 text-background rounded-full">
                                <Link href="/register">Mulai Sekarang</Link>
                            </Button>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <img src="../../../public/atur-keuangan.png" alt="Hero Image" class="h-auto w-full max-w-md" />
                    </div>
                </div>
            </div>
        </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4 text-center">
        <h1 class="mb-4 text-3xl font-bold font-serif">Fitur Andalan Kami</h1>
        <p class="mb-12 text-gray-600">Fitur yang bikin keuanganmu lebih teratur dan terkontrol.</p>
        
        <div class="relative">
          <!-- Features Slider -->
          <div class="overflow-hidden">
            <!-- Slides -->
            <div class="flex transition-transform duration-300" :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
              <!-- Slide 1 -->
              <div class="w-full flex-shrink-0 px-4">
                <div class="rounded-lg bg-white p-6 shadow-md">
                  <img src="../../../public/management.png" alt="Manajemen Keuangan" class="mx-auto mb-4 h-48">
                  <h5 class="text-xl font-bold">Manajemen Keuangan</h5>
                </div>
              </div>
              
              <!-- Slide 2 -->
              <div class="w-full flex-shrink-0 px-4">
                <div class="rounded-lg bg-white p-6 shadow-md">
                  <img src="../../../public/rekomendasi-ai.png" alt="Rekomendasi AI" class="mx-auto mb-4 h-48">
                  <h5 class="text-xl font-bold">Rekomendasi AI</h5>
                </div>
              </div>
              
              <!-- Slide 3 -->
              <div class="w-full flex-shrink-0 px-4">
                <div class="rounded-lg bg-white p-6 shadow-md">
                  <img src="../../../public/alert.png" alt="Alert Batas Bulanan" class="mx-auto mb-4 h-48">
                  <h5 class="text-xl font-bold">Alert Batas Bulanan</h5>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination Dots -->
          <div class="mt-8 flex justify-center gap-2">
            <button 
              v-for="index in 3" 
              :key="index-1"
              @click="changeSlide(index-1)" 
              class="h-3 w-3 rounded-full"
              :class="activeSlide === index-1 ? 'bg-[#284A63]' : 'bg-gray-300'"
              aria-label="Change slide"
            ></button>
          </div>
        </div>
      </div>
    </section>

    <!-- Zero Cost Budgeting Section -->
        <section class="bg-background py-16">
            <div class="container mx-auto px-4 sm:px-6 md:px-12">
                <div class="grid grid-cols-1 items-center gap-12 md:grid-cols-2">
                    <div class="flex justify-center">
                        <img src="../../../public/zero-cost.jpg" alt="Notification Bell" class="h-auto w-full max-w-md" />
                    </div>
                    <div class="space-y-6 text-center md:text-left">
                        <h1 class="text-foreground font-serif text-4xl leading-tight font-bold sm:text-5xl md:text-6xl">
                            <span class="text-muted-foreground font-serif">Apa itu Zero</span><br />
                            <span class="text-primary font-serif">Cost Budgeting Method?</span>
                        </h1>
                        <p class="text-muted-foreground text-lg">
                            Cara ngatur uang biar tiap pemasukan punya tujuan jelas. AI bantu kamu bikin rencana pengeluaran yang pas.
                        </p>
                        <div class="flex justify-center pt-6 md:justify-start">
                            <Button as-child class="w-full md:w-auto bg-primary hover:bg-primary/90 text-background rounded-full">
                                <Link href="/features/expense-alert">Pelajari Lebih Lanjut</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Kelola Uang Section -->
        <section class="bg-background py-16">
            <div class="container mx-auto px-4 sm:px-6 md:px-12">
                <div class="flex flex-col-reverse items-center gap-12 md:grid md:grid-cols-2">
                    <div class="space-y-6 text-center md:text-left">
                        <h1 class="text-foreground font-serif text-4xl leading-tight font-bold sm:text-5xl md:text-6xl">
                          <span class="text-muted-foreground font-serif">Waktunya Kelola Uang</span>
                            <span class="text-primary font-serif"> Lebih Baik!</span></h1>
                        <p class="text-muted-foreground text-lg">Bergabung dengan platform kami untuk mendapatkan perencanaan keuangan berbasis AI, notifikasi batas anggaran, dan laporan keuangan otomatis. Yuk mulai atur keuanganmu sekarang!</p>
                        <div class="flex flex-col items-center gap-4 pt-4 md:flex-row md:justify-start">
                            <Button as-child class="w-full md:w-auto bg-primary/90 text-background hover:bg-primary rounded-full">
                                <Link href="/register">Mulai Gratis</Link>
                            </Button>
                            <Button as-child class="w-full md:w-auto bg-primary/90 text-background hover:bg-primary rounded-full">
                                <Link href="/pricing">Lihat Harga</Link>
                            </Button>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <img src="../../../public/hand-money.png" alt="Hand money" class="h-auto w-full max-w-md" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-transparent py-8">
            <div class="container mx-auto px-4 text-center sm:px-6 md:px-12">
                <p class="text-muted-foreground">© 2025 Finova. All Rights Reserved.</p>
            </div>
        </footer>
    </div>
</template>
