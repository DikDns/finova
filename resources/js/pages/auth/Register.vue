<script setup lang="ts">
import InputError from '@/components/common/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle, ArrowLeft, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    username: '' // This will be auto-generated
});

// Add state for password visibility
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};

const submit = () => {
    // Generate username from name before submitting
    form.username = form.name.toLowerCase().replace(/\s+/g, '');
    
    form.post(route('register'), {
        onSuccess: () => {
            // Show success message
            alert('Registration successful! Redirecting to budgets page...');
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="flex h-screen w-screen flex-col bg-[#f0f4f8] md:flex-row">
        <div class="relative h-[50vh] w-full sm:h-1/2 md:h-full md:w-1/2">
            <img src="womanLaptop.webp" alt="woman-laptop" class="h-full w-full object-cover" />
            <div class="absolute left-4 right-4 bottom-4 rounded-[10px] bg-[#ffffff] p-4 sm:right-5 sm:left-5 sm:rounded-[10px] sm:bottom-5 md:right-10 md:bottom-10 md:left-10 md:p-10">
                <p class="mb-2 text-lg font-bold text-[#000000] sm:text-[20px] md:text-3xl">Coba demo secara gratis</p>
                <p class="text-xs text-[#666] md:text-sm sm:text-xs">
                    Sebagian besar finover hemat Rp50.000 di dua bulan pertama mereka (dan kamu pasti bisa lebih besar dari mereka)
                </p>
            </div>
            <div class="">
                <button class="absolute text-sm top-4 left-4 flex items-center rounded-full pt-2 pr-4 pb-2 pl-4 text-[#000000] shadow-md sm:top-5 sm:left-5 md:top-10 md:left-10 md:pt-2.5 md:pr-5 md:pb-2.5 md:pl-5 bg-white cursor-pointer" onclick="history.back()">
                    <ArrowLeft class="pr-2" />
                    Kembali
                </button>
            </div>
        </div>
        <div class="flex w-full items-center justify-center bg-[#ffffff] p-4 md:w-1/2 md:pt-50 md:pr-0 md:pb-50 md:pl-0">
            <div class="w-full max-w-lg">
                <h1 class="py-5 text-center text-2xl text-[#000000] md:text-4xl md:px-5">Atur keuanganmu sekarang!</h1>
                <p class="text-center text-[#000000] md:mb-4 md:px-5">Tanpa kartu kredit!</p>
                <form @submit.prevent="submit" class="p-2 px-10 md:p-4 md:px-14">
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[#000000]">Nama</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full border-0 border-b border-b-[#ccc] p-2.5 text-[#000000] focus:border-b-[#007bff] focus:outline-0"
                            required
                            placeholder="Masukan nama..."
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[#000000]">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full border-0 border-b border-b-[#ccc] p-2.5 text-[#000000] focus:border-b-[#007bff] focus:outline-0"
                            required
                            placeholder="Masukan email..."
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[#000000]">Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                class="w-full border-0 border-b border-b-[#ccc] p-2.5 text-[#000000] focus:border-b-[#007bff] focus:outline-0"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Masukan password..."
                            />
                            <span 
                                class="absolute top-1/2 right-0 -translate-y-1/2 cursor-pointer"
                                @click="togglePasswordVisibility"
                            >
                                <EyeOff v-if="!showPassword"/>
                                <Eye v-else/>
                            </span>
                            <InputError :message="form.errors.password" />
                        </div>
                    </div>
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[#000000]">Konfirmasi Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                class="w-full border-0 border-b border-b-[#ccc] p-2.5 text-[#000000] focus:border-b-[#007bff] focus:outline-0"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                required
                                placeholder="Konfirmasi password..."
                            />
                            <span 
                                class="absolute top-1/2 right-0 -translate-y-1/2 cursor-pointer"
                                @click="togglePasswordConfirmationVisibility"
                            >
                                <EyeOff v-if="!showPasswordConfirmation"/>
                                <Eye v-else/>
                            </span>
                            <InputError :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                    <div class="pt-5">
                        <button
                            type="submit"
                            class="w-full cursor-pointer rounded-3xl border-0 bg-[#284a63] p-3 text-[#ffffff] transition-colors duration-300 hover:bg-[#dff6ff] hover:text-[#284a63] disabled:opacity-75 md:rounded-4xl md:p-4"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="mr-2 inline h-4 w-4 animate-spin" />
                            Mulai Demo
                        </button>
                    </div>
                </form>
                <div class="text-center text-sm text-[#000000]">
                    <p>Sudah punya akun? <a :href="route('login')" class="text-[#007bff] decoration-0">Masuk disini</a></p>
                </div>
            </div>
        </div>
    </div>
</template>