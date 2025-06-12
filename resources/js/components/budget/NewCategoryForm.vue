<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Check, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    isLoading: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    save: [categoryName: string];
    cancel: [];
}>();

const newCategoryName = ref('');

const handleSave = () => {
    if (newCategoryName.value.trim()) {
        emit('save', newCategoryName.value.trim());
        newCategoryName.value = '';
    }
};

const handleCancel = () => {
    newCategoryName.value = '';
    emit('cancel');
};
</script>

<template>
    <div class="bg-muted/30 border-t px-6 py-3">
        <div class="flex text-sm">
            <div class="flex min-w-0 flex-1 items-center gap-x-4">
                <div class="flex h-4 w-4 items-center justify-center"></div>
                <div class="flex min-w-0 flex-1 items-center gap-2">
                    <Input
                        v-model="newCategoryName"
                        placeholder="Nama kategori baru"
                        class="h-8 text-sm"
                        @keyup.enter="handleSave"
                        @keyup.escape="handleCancel"
                        @click.stop
                        :disabled="isLoading"
                    />
                    <Button
                        size="sm"
                        variant="ghost"
                        @click.stop="handleSave"
                        :disabled="!newCategoryName.trim() || isLoading"
                    >
                        <Check class="h-3 w-3" />
                    </Button>
                    <Button size="sm" variant="ghost" @click.stop="handleCancel" :disabled="isLoading">
                        <X class="h-3 w-3" />
                    </Button>
                </div>
            </div>
            <div class="text-muted-foreground w-32 flex-shrink-0 text-right">-</div>
            <div class="text-muted-foreground w-32 flex-shrink-0 text-right">-</div>
            <div class="text-muted-foreground w-32 flex-shrink-0 text-right">-</div>
        </div>
    </div>
</template>