<script setup>
import { computed, ref } from 'vue';
import { useI18n } from '@/lib/i18n';
import { usePage, router, Link } from '@inertiajs/vue3';
import { Bell, Search, Globe, ChevronDown, User, LogOut, Settings, Monitor, Languages, Menu } from 'lucide-vue-next';

const { t, language, setLanguage } = useI18n();
const page = usePage();

const user = computed(() => page.props.auth?.user);
const showUserMenu = ref(false);

const clientIp = computed(() => page.props.clientIp || '127.0.0.1');

const emit = defineEmits(['toggle-sidebar']);

const handleLanguageSelect = (e) => {
    setLanguage(e.target.value);
    window.location.reload();
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <header class="sticky top-0 z-30 flex h-14 w-full items-center justify-between border-b bg-[hsl(var(--card))] px-4 shadow-sm">
        <div class="flex items-center gap-4">
            <!-- Burger Menu Button — visible on all screens -->
            <button @click="emit('toggle-sidebar')" class="h-9 w-9 flex items-center justify-center rounded-lg hover:bg-[hsl(var(--muted))] text-[hsl(var(--foreground))] transition-colors">
                <Menu class="h-5 w-5" />
            </button>
            <div class="h-6 w-px bg-[hsl(var(--border))] mx-1 hidden md:block"></div>
            <nav class="hidden md:flex items-center gap-4 text-xs font-medium text-[hsl(var(--muted-foreground))]">
                <span class="text-[hsl(var(--primary))] font-bold uppercase tracking-tight">SI IZI HR</span>
                <span>/</span>
                <span class="capitalize">{{ t('menu.dashboard') }}</span>
            </nav>
        </div>

        <div class="flex items-center gap-3">
            <!-- System Status -->
            <div class="hidden lg:flex items-center bg-[hsl(var(--muted))]/50 rounded-full px-3 py-1 mr-4 border border-[hsl(var(--border))]">
                <Monitor class="h-3 w-3 text-emerald-500 mr-2" />
                <span class="text-[10px] font-mono text-[hsl(var(--muted-foreground))] uppercase font-bold tracking-tight">{{ clientIp }}</span>
            </div>

            <!-- Language selector -->
            <div class="relative flex items-center">
                <Languages class="h-4 w-4 absolute left-2 top-2 text-[hsl(var(--primary))]" />
                <select :value="language" @change="handleLanguageSelect" class="h-8 w-28 pl-8 pr-2 py-1 bg-transparent border border-[hsl(var(--border))] text-[10px] font-bold uppercase tracking-wider focus:ring-0 appearance-none cursor-pointer hover:bg-[hsl(var(--muted))]/50 rounded-lg outline-none">
                    <option value="tj">Тоҷикӣ</option>
                    <option value="ru">Русский</option>
                    <option value="en">English</option>
                </select>
                <ChevronDown class="h-3 w-3 absolute right-2 top-2.5 text-[hsl(var(--muted-foreground))] pointer-events-none" />
            </div>


            <!-- User Profile Dropdown -->
            <div class="relative">
                <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 p-1 pl-2 h-9 rounded-full hover:bg-[hsl(var(--muted))] transition-all">
                    <div class="flex flex-col items-end text-[11px] leading-tight mr-1 hidden sm:flex">
                        <span class="font-bold">{{ user?.first_name || 'Admin' }} {{ user?.last_name || 'User' }}</span>
                        <span class="text-[hsl(var(--muted-foreground))] uppercase text-[9px]">{{ user?.username || 'admin' }}</span>
                    </div>
                    <div class="h-7 w-7 rounded-full border-2 border-[hsl(var(--primary))]/20 bg-[hsl(var(--primary))] flex items-center justify-center text-[10px] font-bold text-white">
                        {{ (user?.first_name || 'A')[0] }}{{ (user?.last_name || 'U')[0] }}
                    </div>
                </button>
                
                <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-[hsl(var(--card))] border border-[hsl(var(--border))] rounded-xl shadow-xl z-50 py-1 overflow-hidden">
                    <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-[hsl(var(--muted-foreground))]">
                        {{ t('menu.admin') }}
                    </div>
                    <Link href="/profile" class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-[hsl(var(--muted))] transition-colors">
                        <User class="h-4 w-4" /> Профил
                    </Link>

                    <div class="border-t border-[hsl(var(--border))]"></div>
                    <button @click="logout" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-rose-500 font-medium hover:bg-rose-50 transition-colors">
                        <LogOut class="h-4 w-4" /> Баромадан
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Backdrop to close dropdown -->
    <div v-if="showUserMenu" @click="showUserMenu = false" class="fixed inset-0 z-20"></div>
</template>
