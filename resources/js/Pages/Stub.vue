<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
</script>

<template>
    <Head title="Stub Page" />
    <AppLayout>
        <div class="h-full flex items-center justify-center p-20 text-center">
            <div class="space-y-4 max-w-sm">
                <div class="h-20 w-20 bg-[hsl(var(--primary))]/10 text-[hsl(var(--primary))] rounded-2xl mx-auto flex items-center justify-center">
                    <svg class="h-10 w-10 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                </div>
                <h1 class="text-2xl font-bold">Under Construction</h1>
                <p class="text-[hsl(var(--muted-foreground))]">This module is currently being ported to the new Laravel/Vue architecture.</p>
            </div>
        </div>
    </AppLayout>
</template>
