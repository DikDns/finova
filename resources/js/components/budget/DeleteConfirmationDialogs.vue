<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface Props {
    showDeleteGroupDialog: boolean;
    showDeleteCategoryDialog: boolean;
    groupToDelete: { id: string; name: string } | null;
    categoryToDelete: { id: string; name: string } | null;
    isLoading: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    'update:showDeleteGroupDialog': [value: boolean];
    'update:showDeleteCategoryDialog': [value: boolean];
    confirmDeleteGroup: [];
    confirmDeleteCategory: [];
}>();

const handleCloseGroupDialog = () => {
    emit('update:showDeleteGroupDialog', false);
};

const handleCloseCategoryDialog = () => {
    emit('update:showDeleteCategoryDialog', false);
};

const handleConfirmDeleteGroup = () => {
    emit('confirmDeleteGroup');
};

const handleConfirmDeleteCategory = () => {
    emit('confirmDeleteCategory');
};
</script>

<template>
    <!-- Delete Group Confirmation Dialog -->
    <AlertDialog :open="showDeleteGroupDialog" @update:open="emit('update:showDeleteGroupDialog', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Konfirmasi Hapus Grup</AlertDialogTitle>
                <AlertDialogDescription>
                    Apakah Anda yakin ingin menghapus grup "{{ groupToDelete?.name }}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua
                    kategori dalam grup ini.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="handleCloseGroupDialog">Batal</AlertDialogCancel>
                <AlertDialogAction @click="handleConfirmDeleteGroup" :disabled="isLoading" variant="destructive">
                    {{ isLoading ? 'Menghapus...' : 'Hapus' }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <!-- Delete Category Confirmation Dialog -->
    <AlertDialog :open="showDeleteCategoryDialog" @update:open="emit('update:showDeleteCategoryDialog', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Konfirmasi Hapus Kategori</AlertDialogTitle>
                <AlertDialogDescription>
                    Apakah Anda yakin ingin menghapus kategori "{{ categoryToDelete?.name }}"? Tindakan ini tidak dapat dibatalkan.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="handleCloseCategoryDialog">Batal</AlertDialogCancel>
                <AlertDialogAction @click="handleConfirmDeleteCategory" :disabled="isLoading" variant="destructive">
                    {{ isLoading ? 'Menghapus...' : 'Hapus' }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
