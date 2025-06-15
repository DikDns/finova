<script setup lang="ts">
import { ref } from 'vue';
import { X, Plus, Send, History } from 'lucide-vue-next';
import { type SharedData, type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { marked } from 'marked';

const props = defineProps({
  isOpen: Boolean,
  reply: String
});

const emit = defineEmits(['close']);
const emitClose = () => emit('close');

const message = ref('');
const messages = ref<Array<{id: number, text: string, isUser: boolean}>>([]);
const showSuggestions = ref(true);
const chatHistory = ref<Array<{id: number, title: string, messages: Array<{id: number, text: string, isUser: boolean}>}>>([]);
const showHistory = ref(false);
const currentChatId = ref<number | null>(null);
const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const suggestions = [
  "Berikan rekomendasi /kategori berdasarkan profil aku",
  "Buatkan /target berdasarkan dana yang aku punya sekarang", 
  "Perbarui /target berdasarkan transaksi rekening bulan lalu",
  "Perbarui /grup-kategori dan /kategori berdasarkan prinsip money game"
];

const handleSuggestionClick = (suggestion: string) => {
  message.value = suggestion;
  handleSendMessage();
};

const handleSendMessage = async () => {
  if (message.value.trim()) {
    showSuggestions.value = false;

    if (!currentChatId.value) {
      const newChatId = Date.now();
      currentChatId.value = newChatId;
      chatHistory.value.unshift({
        id: newChatId,
        title: message.value.substring(0, 30) + (message.value.length > 30 ? '...' : ''),
        messages: []
      });
    }

    const userMessage = {
      id: Date.now(),
      text: message.value,
      isUser: true
    };

    messages.value.push(userMessage);

    const currentChat = chatHistory.value.find(chat => chat.id === currentChatId.value);
    if (currentChat) currentChat.messages.push(userMessage);

    const userInput = message.value;
    message.value = '';

    try {
      const res = await axios.post('/ai/chat', { message: userInput });
      const aiMessage = {
        id: Date.now() + 1,
        text: res.data.reply,
        isUser: false
      };

      messages.value.push(aiMessage);
      if (currentChat) currentChat.messages.push(aiMessage);
    } catch (err) {
      console.error(err);
      messages.value.push({
        id: Date.now() + 1,
        text: 'Terjadi kesalahan saat menghubungi AI.',
        isUser: false
      });
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
};

const loadChatFromHistory = (chatId: number) => {
  const chat = chatHistory.value.find(c => c.id === chatId);
  if (chat) {
    messages.value = [...chat.messages];
    currentChatId.value = chatId;
    showSuggestions.value = false;
    showHistory.value = false
     }
};

const deleteChatFromHistory = (chatId: number) => {
  chatHistory.value = chatHistory.value.filter(chat => chat.id !== chatId);
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
    <div 
      v-if="isOpen" 
      class="fixed inset-0 bg-opacity-20 z-40"
      @click="emitClose"
    />
  </transition>

  <!-- Sidebar -->
  <transition name="slide">
    <div v-if="isOpen" class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl z-50 flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b bg-white">
        <h2 class="text-xl font-semibold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
          AI Assistant
        </h2>
        <div class="flex items-center gap-2">
          <button 
            @click="handleHistory"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
            title="Chat history"
          >
            <History class="w-4 h-4 text-gray-600" />
          </button>
          <button 
            @click="handleReset"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
            title="New chat"
          >
            <Plus class="w-4 h-4 text-gray-600" />
          </button>
          <button 
            @click="emitClose"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
          >
            <X class="w-4 h-4 text-gray-600" />
          </button>
        </div>
      </div>

      <!-- Scrollable Content Area -->
      <div class="flex-1 scrollable-content">
        <!-- Chat History -->
        <div v-if="showHistory" class="p-4">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Chat History</h3>
          <div v-if="chatHistory.length === 0" class="text-sm text-gray-500 text-center py-8">
            Belum ada riwayat chat
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="chat in chatHistory"
              :key="chat.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <button
                @click="loadChatFromHistory(chat.id)"
                class="flex-1 text-left text-sm text-gray-700 truncate"
              >
                {{ chat.title }}
              </button>
              <button
                @click="deleteChatFromHistory(chat.id)"
                class="p-1 text-red-500 hover:text-red-700 transition-colors ml-2"
                title="Delete chat"
              >
                <X class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>

        <!-- Welcome Message & Suggestions -->
        <div v-else-if="showSuggestions" class="p-6">
          <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">
              Selamat pagi, {{ user.name }}!
            </h3>
            <p class="text-gray-600">
              Aku siap bantu ngatur keuanganmu hari ini 😊
            </p>
          </div>

          <!-- Suggestions -->
          <div class="space-y-3">
            <button
              v-for="(suggestion, index) in suggestions"
              :key="index"
              @click="handleSuggestionClick(suggestion)"
              class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm text-gray-700 transition-colors duration-150"
            >
              {{ suggestion }}
            </button>
          </div>
        </div>

        <!-- Chat Messages -->
        <div v-else class="p-4">
          <div class="space-y-4">
            <div
              v-for="msg in messages"
              :key="msg.id"
              :class="[
                'flex',
                msg.isUser ? 'justify-end' : 'justify-start'
              ]"
            >
              <div
                :class="[
                  'max-w-xs lg:max-w-md px-3 py-2 rounded-lg text-sm',
                  msg.isUser 
                    ? 'bg-blue-600 text-white rounded-br-none' 
                    : 'bg-gray-100 text-gray-800 rounded-bl-none prose max-w-full'
                ]"
                v-html="msg.isUser ? msg.text : marked.parse(msg.text)"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Fixed Input Area at Bottom -->
      <div class="p-4 border-t bg-white">
        <div class="flex items-center gap-2 bg-white rounded-lg border border-gray-200 p-2">
          <input
            v-model="message"
            type="text"
            placeholder="Ketik / untuk memberikan konteks"
            class="flex-1 px-2 py-1 text-sm border-none outline-none bg-transparent"
            @keydown.enter="handleSendMessage"
          />
          <button 
            @click="handleSendMessage"
            :disabled="!message.trim()"
            class="p-2 text-gray-400 hover:text-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Send class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>
