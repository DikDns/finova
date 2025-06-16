<script setup lang="ts">
import { type SharedData, type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { History, Plus, Send, X } from 'lucide-vue-next';
import { marked } from 'marked';
import { nextTick, ref } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    reply: String,
    budget_id: String,
});

const emit = defineEmits(['close']);
const emitClose = () => emit('close');

const message = ref('');
const messages = ref<Array<{ id: string; text: string; isUser: boolean }>>([]);
const showSuggestions = ref(true);
const chatHistory = ref<Array<{ id: string; title: string; created_at: string }>>([]);
const showHistory = ref(false);
const currentChatId = ref<string | null>(null);
const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const isLoading = ref(false);
const chatContainer = ref<HTMLElement | null>(null);
const isLoadingHistory = ref(false);

const suggestions = [
    'Berikan rekomendasi kategori berdasarkan profil aku',
    'Buatkan target berdasarkan dana yang aku punya sekarang',
    'Perbarui kategori yang boros dan tidak penting pada bulan ini',
    'Perbarui grup kategori dan kategori berdasarkan prinsip money game',
];

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

const handleSuggestionClick = (suggestion: string) => {
    message.value = suggestion;
    // Menghapus pemanggilan handleSendMessage() agar tidak langsung mengirim pesan
};

const handleSendMessage = async () => {
    if (message.value.trim()) {
        showSuggestions.value = false;

        const userMessage = {
            id: Date.now().toString(),
            text: message.value,
            isUser: true,
        };

        messages.value.push(userMessage);
        scrollToBottom();

        const userInput = message.value;
        message.value = '';

        // Menyiapkan riwayat chat untuk dikirim ke server
        const chatMessages = messages.value.map((msg) => ({
            content: msg.text,
            role: msg.isUser ? 'user' : 'assistant',
        }));

        isLoading.value = true;
        try {
            const res = await axios.post('/ai/chat', {
                message: userInput,
                chat_history: chatMessages,
                budget_id: props.budget_id,
                chat_id: currentChatId.value,
            });
            const aiMessage = {
                id: Date.now().toString(),
                text: res.data.reply,
                isUser: false,
            };

            messages.value.push(aiMessage);
            scrollToBottom();

            // Save the chat ID for future messages in this conversation
            currentChatId.value = res.data.chat_id;

            // Refresh chat history after new chat
            fetchChatHistory();
        } catch (err) {
            console.error(err);
            messages.value.push({
                id: Date.now().toString(),
                text: 'Terjadi kesalahan saat menghubungi AI: ' + JSON.stringify(err),
                isUser: false,
            });
            scrollToBottom();
        } finally {
            isLoading.value = false;
        }
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
    if (showHistory.value) {
        fetchChatHistory();
    }
};

const fetchChatHistory = async () => {
    isLoadingHistory.value = true;
    try {
        const response = await axios.get('/ai/chats');
        chatHistory.value = response.data.chats;
    } catch (error) {
        console.error('Error fetching chat history:', error);
    } finally {
        isLoadingHistory.value = false;
    }
};

const loadChatFromHistory = async (chatId: string) => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/ai/chats/${chatId}`);
        const chatMessages = response.data.messages;

        // Format messages for display
        messages.value = chatMessages.map((msg: any) => ({
            id: msg.id,
            text: msg.content,
            isUser: msg.role === 'user',
        }));

        currentChatId.value = chatId;
        showSuggestions.value = false;
        showHistory.value = false;
        scrollToBottom();
    } catch (error) {
        console.error('Error loading chat:', error);
    } finally {
        isLoading.value = false;
    }
};

const deleteChatFromHistory = async (chatId: string) => {
    try {
        await axios.delete(`/ai/chats/${chatId}`);
        chatHistory.value = chatHistory.value.filter((chat) => chat.id !== chatId);
        if (currentChatId.value === chatId) {
            handleReset();
        }
    } catch (error) {
        console.error('Error deleting chat:', error);
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
            <div ref="chatContainer" class="scrollable-content flex-1" v-auto-animate>
                <!-- Chat History -->
                <div v-if="showHistory" class="p-4">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">Chat History</h3>

                    <!-- Loading State -->
                    <div v-if="isLoadingHistory" class="py-8 text-center">
                        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-gray-300 border-t-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-500">Memuat riwayat chat...</p>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="chatHistory.length === 0" class="py-8 text-center text-sm text-gray-500">Belum ada riwayat chat</div>

                    <!-- Chat List -->
                    <div v-else class="space-y-2">
                        <div
                            v-for="chat in chatHistory"
                            :key="chat.id"
                            class="flex flex-col rounded-lg bg-gray-50 p-3 transition-colors hover:bg-gray-100"
                        >
                            <div class="flex items-center justify-between">
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
                            <div class="mt-1 text-xs text-gray-400">
                                {{ new Date(chat.created_at).toLocaleString() }}
                            </div>
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
                    <div class="space-y-4" v-auto-animate>
                        <div v-for="msg in messages" :key="msg.id" :class="['flex', msg.isUser ? 'justify-end' : 'justify-start']">
                            <div
                                :class="[
                                    'max-w-xs rounded-lg px-3 py-2 text-sm lg:max-w-md',
                                    msg.isUser
                                        ? 'rounded-br-none bg-blue-600 text-white'
                                        : 'prose prose-sm prose-p:font-sans prose-headings:font-serif max-w-full rounded-bl-none bg-gray-100 text-gray-800',
                                ]"
                                v-html="msg.isUser ? msg.text : marked.parse(msg.text)"
                            ></div>
                        </div>

                        <!-- Loading Indicator -->
                        <div v-if="isLoading" class="flex justify-start">
                            <div class="max-w-xs rounded-lg rounded-bl-none bg-gray-100 px-3 py-2 text-gray-500">
                                <div class="flex space-x-1">
                                    <div class="h-2 w-2 animate-pulse rounded-full bg-gray-400"></div>
                                    <div class="h-2 w-2 animate-pulse rounded-full bg-gray-400 delay-100"></div>
                                    <div class="h-2 w-2 animate-pulse rounded-full bg-gray-400 delay-200"></div>
                                </div>
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
                        placeholder="Ketik pesan disini..."
                        class="flex-1 border-none bg-transparent px-2 py-1 text-sm outline-none"
                        @keydown.enter="handleSendMessage"
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
