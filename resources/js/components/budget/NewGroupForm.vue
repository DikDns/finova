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
    save: [groupName: string];
    cancel: [];
}>();

const newGroupName = ref('');

const handleSave = () => {
    if (newGroupName.value.trim()) {
        emit('save', newGroupName.value.trim());
        newGroupName.value = '';
    }
};

const handleCancel = () => {
    newGroupName.value = '';
    emit('cancel');
};
</script>

<template>
    <div class="bg-muted/30 border-b px-6 py-3">
        <div class="flex w-full">
            <div class="flex min-w-0 flex-1 items-center gap-2">
                <Input
                    v-model="newGroupName"
                    placeholder="Nama grup kategori baru"
                    class="h-8 text-sm"
                    @keyup.enter="handleSave"
                    @keyup.escape="handleCancel"
                    :disabled="isLoading"
                />
                <Button size="sm" variant="ghost" @click="handleSave" :disabled="!newGroupName.trim() || isLoading">
                    <Check class="h-3 w-3" />
                </Button>
                <Button size="sm" variant="ghost" @click="handleCancel" :disabled="isLoading">
                    <X class="h-3 w-3" />
                </Button>
            </div>
            <div class="text-muted-foreground w-32 flex-shrink-0 text-right font-medium">-</div>
            <div class="text-muted-foreground w-32 flex-shrink-0 text-right font-medium">-</div>
            <div class="text-muted-foreground w-32 flex-shrink-0 text-right font-medium">-</div>
        </div>
    </div>
</template>