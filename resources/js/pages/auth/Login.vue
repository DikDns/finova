<script setup lang="ts">
import InputError from '@/components/InputError.vue';
// import TextLink from '@/components/TextLink.vue';
// import { Button } from '@/components/ui/button';
// import { Checkbox } from '@/components/ui/checkbox';
// import { Input } from '@/components/ui/input';
// import { Label } from '@/components/ui/label';
// import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <div class="flex flex-col md:flex-row w-screen h-screen bg-[#f0f4f8]">
    <div class="relative w-full md:w-1/2 h-[30vh] md:h-full">
      <img src="womanLaptop.webp" alt="woman-laptop" class="w-full h-full object-cover" />
      <div class="absolute bottom-4 right-4 md:bottom-10 bg-[#ffffff] p-4 md:p-10 rounded-lg md:left-10 md:right-10 sm:left-5 sm:right-5">
        <p class="font-bold mb-1.5 text-[#000000] text-xl md:text-3xl">Coba demo secara gratis</p>
        <p class="text-xs md:text-sm text-[#666]">
          Sebagian besar finover hemat Rp50.000 di dua bulan pertama mereka
          (dan kamu pasti lebih besar dari mereka)
        </p>
      </div>
      <div class="absolute top-4 md:top-10 left-4 md:left-10 flex items-center bg-[#ffffff] rounded-full pt-2 md:pt-2.5 pb-2 md:pb-2.5 pl-4 md:pl-5 pr-4 md:pr-5 shadow-md text-[#000000]">
        <div class="pr-2 md:pr-4 text-base md:text-xl text-[#000000]">
          <i class="fa-solid fa-arrow-left"></i>
        </div>
        <button class="back-btn" onclick="history.back()">Kembali</button>
      </div>
    </div>
    <div class="w-full md:w-1/2 flex justify-center items-center p-4 md:pl-0 md:pr-0 md:pt-50 md:pb-50 bg-[#ffffff]">
      <div class="w-full max-w-lg">
        <h1 class="text-2xl md:text-4xl text-center mb-2.5 text-[#000000]">Lanjut, atur keuanganmu!</h1>
        <p class="mb-4 md:mb-8 text-center text-[#000000]">Masuk menggunakan email dan password yang telah terdaftar</p>
        <form @submit.prevent="submit" class="p-4 md:p-8">
          <div class="mb-5">
            <label class="block mb-1.5 text-[#000000]">Email</label>
            <input v-model="form.email" type="email" class="w-full border-0 border-b-[#ccc] border-b p-2.5 focus:outline-0 focus:border-b-[#007bff] text-[#000000]" required placeholder="Masukan email..." />
            <InputError :message="form.errors.email" />
          </div>
          <div class="mb-5">
            <label class="block mb-1.5 text-[#000000]">Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                class="w-full border-0 border-b-[#ccc] border-b p-2.5 focus:outline-0 focus:border-b-[#007bff] text-[#000000]"
                type="password"
                required
                placeholder="Masukan password..."
              />
              <span class="absolute right-0 top-1/2 -translate-y-1/2 cursor-pointer">👁</span>
              <InputError :message="form.errors.password" />
            </div>
          </div>
          <div class="pt-6 md:pt-8">
            <button type="submit" class="w-full p-3 md:p-4 bg-[#284a63] text-[#ffffff] border-0 rounded-3xl md:rounded-4xl cursor-pointer transition-colors duration-300 hover:bg-[#dff6ff] hover:text-[#284a63] disabled:opacity-75 " :disabled="form.processing">
              <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin inline mr-2" />
              Masuk
            </button>
          </div>
        </form>
        <div class="text-center mt-4 text-sm text-[#000000]">
          <p>Sudah punya akun? <a :href="route('login')" class="text-[#007bff] decoration-0">Masuk disini</a></p>
        </div>
      </div>
    </div>
  </div>
</template>
