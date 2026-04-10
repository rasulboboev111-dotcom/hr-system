<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { User, Mail, ShieldCheck } from 'lucide-vue-next';

const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Профили Админ</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">Идоракунии маълумоти ҳисоб</p>
                </div>
            </div>

            <div class="max-w-2xl">
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm overflow-hidden">
                    <div class="h-32 bg-[hsl(var(--primary))]/10 border-b border-[hsl(var(--border))] relative">
                        <div class="absolute -bottom-10 left-6 h-20 w-20 bg-[hsl(var(--primary))] text-white flex items-center justify-center rounded-xl text-3xl font-extrabold shadow-lg border-4 border-[hsl(var(--card))]">
                            {{ user.first_name?.[0] }}{{ user.last_name?.[0] }}
                        </div>
                    </div>
                    
                    <div class="pt-14 p-6 space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold">{{ user.first_name }} {{ user.last_name }}</h2>
                            <p class="text-sm text-[hsl(var(--muted-foreground))]">{{ user.username }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-[hsl(var(--border))]">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-xs font-bold uppercase text-[hsl(var(--muted-foreground))]">
                                    <Mail class="h-3 w-3" /> Почтаи электронӣ
                                </div>
                                <p class="text-sm font-medium">{{ user.email }}</p>
                            </div>
                            
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-xs font-bold uppercase text-[hsl(var(--muted-foreground))]">
                                    <ShieldCheck class="h-3 w-3" /> Нақш
                                </div>
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded font-bold text-xs inline-flex uppercase">Администратор</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
