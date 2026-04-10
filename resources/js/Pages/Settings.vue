<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { Settings, Save, Shield, Bell, Palette } from 'lucide-vue-next';

const form = useForm({
    companyName: 'SI IZI HR Platform',
    theme: 'system',
    notifications: true
});
</script>

<template>
    <Head title="Settings" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Танзимоти система</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">Танзим кардани афзалиятҳои барнома</p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-4">
                <div class="md:col-span-1 space-y-2">
                    <button class="w-full text-left px-4 py-2 bg-[hsl(var(--muted))] text-[hsl(var(--foreground))] rounded-lg font-bold text-xs uppercase flex items-center gap-2"><Settings class="w-4 h-4" /> Умумӣ</button>
                    <button class="w-full text-left px-4 py-2 hover:bg-[hsl(var(--muted))]/50 text-[hsl(var(--muted-foreground))] rounded-lg font-bold text-xs uppercase flex items-center gap-2"><Shield class="w-4 h-4" /> Амният</button>
                    <button class="w-full text-left px-4 py-2 hover:bg-[hsl(var(--muted))]/50 text-[hsl(var(--muted-foreground))] rounded-lg font-bold text-xs uppercase flex items-center gap-2"><Bell class="w-4 h-4" /> Огоҳиномаҳо</button>
                    <button class="w-full text-left px-4 py-2 hover:bg-[hsl(var(--muted))]/50 text-[hsl(var(--muted-foreground))] rounded-lg font-bold text-xs uppercase flex items-center gap-2"><Palette class="w-4 h-4" /> Намуди зоҳирӣ</button>
                </div>

                <div class="md:col-span-3">
                    <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-6">
                        <h2 class="text-lg font-bold mb-6">Афзалиятҳои умумӣ</h2>
                        <form @submit.prevent="form.post('/settings')" class="space-y-4 max-w-md">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Номи ширкат</label>
                                <input v-model="form.companyName" class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                            </div>
                            
                            <div class="space-y-1.5 pt-2">
                                <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Мавзуъ</label>
                                <select v-model="form.theme" class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]">
                                    <option value="system">Пешфарзи система</option>
                                    <option value="light">Рӯшан</option>
                                    <option value="dark">Торик</option>
                                </select>
                            </div>

                            <div class="pt-4 flex items-center gap-3 border-t border-[hsl(var(--border))] mt-6">
                                <button type="submit" class="px-4 py-2 flex items-center gap-2 rounded-lg text-sm font-bold bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] hover:bg-[hsl(var(--primary))]/90">
                                    <Save class="w-4 h-4" /> Сабт кардан
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
