<script setup>
import { Head, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { User, Mail, ShieldCheck, Save, Loader2 } from 'lucide-vue-next';

const user = usePage().props.auth.user;

const form = useForm({
    email: user.email,
    username: user.username,
    password: '',
});

const submit = () => {
    form.put('/profile', {
        preserveScroll: true,
        onSuccess: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('profile.adminProfile') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('profile.manageAccount') }}</p>
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
                        
                        <form @submit.prevent="submit" class="space-y-4 pt-4 border-t border-[hsl(var(--border))]">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase text-[hsl(var(--muted-foreground))] flex items-center gap-2">
                                        <Mail class="h-3 w-3" /> {{ t('profile.email') }}
                                    </label>
                                    <input v-model="form.email" type="email" class="w-full h-10 px-3 rounded-lg border border-[hsl(var(--border))] bg-transparent text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--primary))]" />
                                    <div v-if="form.errors.email" class="text-xs text-rose-500">{{ form.errors.email }}</div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase text-[hsl(var(--muted-foreground))] flex items-center gap-2">
                                        <User class="h-3 w-3" /> {{ t('profile.username') }}
                                    </label>
                                    <input v-model="form.username" type="text" class="w-full h-10 px-3 rounded-lg border border-[hsl(var(--border))] bg-transparent text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--primary))]" />
                                    <div v-if="form.errors.username" class="text-xs text-rose-500">{{ form.errors.username }}</div>
                                </div>

                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-xs font-bold uppercase text-[hsl(var(--muted-foreground))] flex items-center gap-2">
                                        <ShieldCheck class="h-3 w-3" /> {{ t('profile.newPassword') }} ({{ t('profile.passwordNotice') }})
                                    </label>
                                    <input v-model="form.password" type="password" class="w-full h-10 px-3 rounded-lg border border-[hsl(var(--border))] bg-transparent text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--primary))]" />
                                    <div v-if="form.errors.password" class="text-xs text-rose-500">{{ form.errors.password }}</div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="form.processing" class="h-10 px-6 rounded-lg bg-[hsl(var(--primary))] text-primary-foreground font-bold text-sm tracking-wide flex text-white items-center justify-center gap-2 hover:opacity-90 disabled:opacity-50">
                                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                                    <Save v-else class="h-4 w-4" />
                                    {{ t('common.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
