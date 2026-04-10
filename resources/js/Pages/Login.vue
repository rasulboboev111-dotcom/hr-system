<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';
import { Building2, LogIn, Lock, Mail } from 'lucide-vue-next';

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <Head title="Login" />
    
    <div class="min-h-screen bg-[hsl(var(--background))] flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-[hsl(var(--card))] rounded-2xl shadow-xl border border-[hsl(var(--border))] overflow-hidden flex flex-col">
            
            <div class="p-8 pb-6 flex flex-col items-center">
                <div class="h-14 w-14 rounded-2xl bg-[hsl(var(--primary))] flex items-center justify-center shadow-lg shadow-[hsl(var(--primary))]/30 mb-4">
                    <Building2 class="h-7 w-7 text-[hsl(var(--primary-foreground))]" />
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-[hsl(var(--foreground))]">Siizi HR Platform</h1>
                <p class="text-xs text-[hsl(var(--muted-foreground))] uppercase tracking-widest font-bold mt-1">Ба ҳисоби худ ворид шавед</p>
            </div>

            <div class="p-8 pt-0">
                <form @submit.prevent="submit" class="space-y-4">
                    
                    <div class="space-y-1.5 relative">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Почтаи электронӣ</label>
                        <div class="relative">
                            <Mail class="absolute left-3 top-2.5 h-5 w-5 text-[hsl(var(--muted-foreground))]/60" />
                            <input v-model="form.email" type="email" required autofocus class="pl-10 h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" placeholder="name@siizi.tj" />
                        </div>
                        <p v-if="form.errors.email" class="text-xs text-rose-500 font-medium">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-1.5 relative">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Рамз</label>
                        <div class="relative">
                            <Lock class="absolute left-3 top-2.5 h-5 w-5 text-[hsl(var(--muted-foreground))]/60" />
                            <input v-model="form.password" type="password" required class="pl-10 h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" placeholder="••••••••" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.remember" class="rounded border-[hsl(var(--border))] bg-transparent" />
                            <span class="text-xs font-medium text-[hsl(var(--muted-foreground))]">Маро дар ёд дор</span>
                        </label>
                        <a href="#" class="text-xs font-bold text-[hsl(var(--primary))] hover:underline">Рамзро фаромӯш кардед?</a>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full h-10 flex items-center justify-center gap-2 rounded-xl font-bold uppercase tracking-widest bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow-lg shadow-[hsl(var(--primary))]/20 hover:bg-[hsl(var(--primary))]/90 disabled:opacity-50 mt-4">
                        <span v-if="form.processing">Воридшавӣ...</span>
                        <template v-else>
                            <span>Воридшавӣ</span> <LogIn class="h-4 w-4" />
                        </template>
                    </button>
                    
                </form>
            </div>
            
        </div>
    </div>
</template>
