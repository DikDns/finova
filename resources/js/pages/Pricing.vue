<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
//import 

interface MoneyIcon {
  image: string
  title: string
}

interface PricingPlan {
  id: string
  name: string
  price: string
  discount?: string
}

const selectedPlan = ref('bulanan')

const moneyIcons: MoneyIcon[] = [
  {
    image: '/money_1.webp',
    title: 'Langganan Perhari, Hidup Jadi Simple!'
  },
  {
    image: '/money_2.webp',
    title: 'Gak Perlu Ribet Ngatur Keuangan Lagi!'
  },
  {
    image: '/money_3.webp',
    title: 'Stop Tebak-Tebak Pengeluaran Bulanan!'
  }
]

const pricingPlans: PricingPlan[] = [
  {
    id: 'bulanan',
    name: 'Bulanan',
    price: 'Rp2.500/hari',
    discount: 'Hemat 17%'
  },
  {
    id: 'harian',
    name: 'Harian',
    price: 'Rp3.000/hari'
  }
]
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <AppNavbar />
    <Separator />

    <main class="py-16">
      <section class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-12">
          Upgrade Finansialmu, <br />
          <span class="text-primary">Gak Pakai Ribet!</span>
        </h1>

        <div class="grid md:grid-cols-3 gap-8 mb-16">
          <div
            v-for="(icon, index) in moneyIcons"
            :key="index"
            class="flex flex-col items-center"
          >
            <img
              :src="icon.image"
              :alt="icon.title"
              class="w-24 h-24 mb-4"
              loading="lazy"
            />
            <h6 class="font-medium text-center text-foreground">{{ icon.title }}</h6>
          </div>
        </div>

        <Card class="max-w-2xl mx-auto bg-card text-card-foreground">
          <CardHeader>
            <CardTitle class="text-2xl font-bold">Pilih Paket Sesuai Kebutuhan!</CardTitle>
            <CardDescription>
              <div class="flex flex-col items-center text-muted-foreground">
                <p>
                  Coba demo secara gratis. Suka? Mulai dari Rp2.500/hari.
                </p>
                <p>
                  Untuk akses fitur mantap lainnya!
                </p>
              </div>
            </CardDescription>
          </CardHeader>

          <CardContent>
            <RadioGroup v-model="selectedPlan" class="space-y-4">
              <div
                v-for="plan in pricingPlans"
                :key="plan.id"
                class="rounded-lg p-4 border-2 border-muted hover:border-primary transition-all"
              >
                <RadioGroupItem
                  :value="plan.id"
                  :id="plan.id"
                  class="peer sr-only"
                />
                <label
                  :for="plan.id"
                  class="flex items-center justify-between cursor-pointer"
                >
                  <span class="font-medium">{{ plan.name }}</span>
                  <div class="flex items-center space-x-4">
                    <span
                      v-if="plan.discount"
                      class="bg-green-100 text-green-700 text-accent-foreground text-xs font-bold px-2 py-1 rounded-full"
                    >
                      {{ plan.discount }}
                    </span>
                    <span class="font-bold">{{ plan.price }}</span>
                  </div>
                </label>
              </div>
            </RadioGroup>
            <Button as-child class="w-full mt-12 bg-primary hover:bg-primary/90 text-primary-foreground rounded-full font-bold">
              <Link :href="route('register')">Coba Sekarang!</Link>
            </Button>
          </CardContent>
        </Card>
      </section>

      <Separator class="my-16" />

      <section class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-12">
          <div class="md:w-1/2">
            <h2 class="text-3xl font-bold mb-4">
              Upgrade &<br />
              Nikmati Fitur Lengkap!
            </h2>
            <p class="text-muted-foreground mb-8">
              Cuan lancar, finansial stabil<br />
              Upgrade sekarang biar duit tetap aman dan terkontrol!
            </p>
            <div class="flex flex-col md:flex-row gap-4">
              <Button as-child class="bg-primary hover:bg-primary/90 text-primary-foreground rounded-full">
                <Link :href="route('register')">Mulai Atur Keuanganmu</Link>
              </Button>
              <Button as-child class="bg-primary-foreground/90 hover:bg-primary-foreground text-background rounded-full">
                <Link :href="route('features')">Eksplor Fitur</Link>
              </Button>
            </div>
          </div>
          <div class="md:w-1/2">
            <img src="/handMoney.png" alt="Hand with Money" class="max-w-md mx-auto" />
          </div>
        </div>
      </section>
    </main>

    <Separator />

    <footer class="py-8 border-t border-border">
      <div class="container mx-auto px-4 text-center text-muted-foreground">
        <p>&copy; 2025 Finova. All Rights Reserved.</p>
      </div>
    </footer>
  </div>
</template>