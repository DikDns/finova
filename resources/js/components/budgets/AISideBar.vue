<script setup lang="ts">
import { type SharedData, type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { History, Plus, Send, X } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(['close']);
const emitClose = () => emit('close');

const message = ref('');
const messages = ref<Array<{ id: number; text: string; isUser: boolean }>>([]);
const showSuggestions = ref(true);
const chatHistory = ref<Array<{ id: number; title: string; messages: Array<{ id: number; text: string; isUser: boolean }> }>>([]);
const showHistory = ref(false);
const currentChatId = ref<number | null>(null);
const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const suggestions = [
    'Berikan rekomendasi kategori berdasarkan profil aku',
    'Buatkan target berdasarkan dana yang aku punya sekarang',
    'Perbarui target berdasarkan transaksi rekening bulan lalu',
    'Perbarui grup kategori dan kategori berdasarkan prinsip money game',
];

const handleSuggestionClick = (suggestion: string) => {
    message.value = suggestion;
    handleSendMessage();
};

const handleSendMessage = () => {
    if (message.value.trim()) {
        // Hide suggestions after first message
        showSuggestions.value = false;

        if (!currentChatId.value) {
            const newChatId = Date.now();
            currentChatId.value = newChatId;
            chatHistory.value.unshift({
                id: newChatId,
                title: message.value.substring(0, 30) + (message.value.length > 30 ? '...' : ''),
                messages: [],
            });
        }
        const userMessage = {
            id: Date.now(),
            text: message.value,
            isUser: true,
        };

        messages.value.push(userMessage);

        // Add to current chat history
        const currentChat = chatHistory.value.find((chat) => chat.id === currentChatId.value);
        if (currentChat) {
            currentChat.messages.push(userMessage);
        }

        message.value = '';

        // Simulate AI response with delay
        setTimeout(() => {
            const aiMessage = {
                id: Date.now() + 1,
                text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                isUser: false,
            };

            messages.value.push(aiMessage);
            // Add AI response to history
            if (currentChat) {
                currentChat.messages.push(aiMessage);
            }
        }, 1000);
    }
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        handleSendMessage();
    }
};

const handleReset = () => {
    messages.value = [];
    showSuggestions.value = true;
    message.value = '';
    currentChatId.value = null;
    showHistory.value = false;
};

const handleHistory = () => {
    showHistory.value = !showHistory.value;
};

const loadChatFromHistory = (chatId: number) => {
    const chat = chatHistory.value.find((c) => c.id === chatId);
    if (chat) {
        messages.value = [...chat.messages];
        currentChatId.value = chatId;
        showSuggestions.value = false;
        showHistory.value = false;
    }
};

const deleteChatFromHistory = (chatId: number) => {
    chatHistory.value = chatHistory.value.filter((chat) => chat.id !== chatId);
    if (currentChatId.value === chatId) {
        handleReset();
    }
};
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter-from {
    transform: translateX(100%);
}
.slide-leave-to {
    transform: translateX(100%);
}

.backdrop-enter-active,
.backdrop-leave-active {
    transition: opacity 0.3s ease;
}
.backdrop-enter-from,
.backdrop-leave-to {
    opacity: 0;
}

.scrollable-content {
    height: calc(100vh - 140px); /* Adjust based on header height */
    overflow-y: auto;
}

.scrollable-content::-webkit-scrollbar {
    width: 4px;
}

.scrollable-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.scrollable-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.scrollable-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<template>
    <!-- Backdrop -->
    <transition name="backdrop">
        <div v-if="isOpen" class="bg-opacity-20 fixed inset-0 z-40" @click="emitClose" />
    </transition>

    <!-- Sidebar -->
    <transition name="slide">
        <div v-if="isOpen" class="fixed top-0 right-0 z-50 flex h-full w-96 flex-col bg-white shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b bg-white p-4">
                <h2 class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-xl font-semibold text-transparent">AI Assistant</h2>
                <div class="flex items-center gap-2">
                    <button @click="handleHistory" class="rounded-full p-2 transition-colors hover:bg-gray-100" title="Chat history">
                        <History class="h-4 w-4 text-gray-600" />
                    </button>
                    <button @click="handleReset" class="rounded-full p-2 transition-colors hover:bg-gray-100" title="New chat">
                        <Plus class="h-4 w-4 text-gray-600" />
                    </button>
                    <button @click="emitClose" class="rounded-full p-2 transition-colors hover:bg-gray-100">
                        <X class="h-4 w-4 text-gray-600" />
                    </button>
                </div>
            </div>

            <!-- Scrollable Content Area -->
            <div class="scrollable-content flex-1">
                <!-- Chat History -->
                <div v-if="showHistory" class="p-4">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">Chat History</h3>
                    <div v-if="chatHistory.length === 0" class="py-8 text-center text-sm text-gray-500">Belum ada riwayat chat</div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="chat in chatHistory"
                            :key="chat.id"
                            class="flex items-center justify-between rounded-lg bg-gray-50 p-3 transition-colors hover:bg-gray-100"
                        >
                            <button @click="loadChatFromHistory(chat.id)" class="flex-1 truncate text-left text-sm text-gray-700">
                                {{ chat.title }}
                            </button>
                            <button
                                @click="deleteChatFromHistory(chat.id)"
                                class="ml-2 p-1 text-red-500 transition-colors hover:text-red-700"
                                title="Delete chat"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Welcome Message & Suggestions -->
                <div v-else-if="showSuggestions" class="p-6">
                    <div class="mb-6 rounded-2xl bg-gradient-to-br from-blue-50 to-purple-50 p-4">
                        <h3 class="mb-2 text-lg font-semibold text-gray-800">Selamat pagi, {{ user.name }}!</h3>
                        <p class="text-gray-600">Aku siap bantu ngatur keuanganmu hari ini 😊</p>
                    </div>

                    <!-- Suggestions -->
                    <div class="space-y-3">
                        <button
                            v-for="(suggestion, index) in suggestions"
                            :key="index"
                            @click="handleSuggestionClick(suggestion)"
                            class="w-full rounded-lg bg-gray-50 p-3 text-left text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100"
                        >
                            {{ suggestion }}
                        </button>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div v-else class="p-4">
                    <div class="space-y-4">
                        <div v-for="msg in messages" :key="msg.id" :class="['flex', msg.isUser ? 'justify-end' : 'justify-start']">
                            <div
                                :class="[
                                    'max-w-xs rounded-lg px-3 py-2 text-sm lg:max-w-md',
                                    msg.isUser ? 'rounded-br-none bg-blue-600 text-white' : 'rounded-bl-none bg-gray-100 text-gray-800',
                                ]"
                            >
                                {{ msg.text }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fixed Input Area at Bottom -->
            <div class="border-t bg-white p-4">
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white p-2">
                    <input
                        v-model="message"
                        type="text"
                        placeholder="Ketik / untuk memberikan konteks"
                        class="flex-1 border-none bg-transparent px-2 py-1 text-sm outline-none"
                        @keypress="handleKeyPress"
                    />
                    <button
                        @click="handleSendMessage"
                        :disabled="!message.trim()"
                        class="p-2 text-gray-400 transition-colors hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Send class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>
