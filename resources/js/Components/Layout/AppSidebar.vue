<script setup>
import { computed, ref, onMounted, onUnmounted, provide } from 'vue';
import { useI18n } from '@/lib/i18n';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Users, UserSquare2, Building2, UserPlus, 
    Clock, Calendar as CalendarIcon, CalendarDays, CalendarClock, 
    Banknote, FileText, Settings, ShieldCheck, History, X, ChevronRight
} from 'lucide-vue-next';

const canViewAdmin = true;
const { t } = useI18n();

const props = defineProps({
    isMobileOpen: { type: Boolean, default: false },
    isDesktopCollapsed: { type: Boolean, default: false }
});

const emit = defineEmits(['close-mobile']);

const navigation = computed(() => {
    const defaultNav = [
        {
            title: t('dashboard.title'),
            icon: LayoutDashboard,
            href: '/',
        },
        {
            title: t('menu.company'),
            icon: Building2,
            items: [
                { title: t('menu.positions'), href: '/positions', icon: UserSquare2 },
                { title: t('menu.departments'), href: '/departments', icon: Building2 },
            ],
        },
        {
            title: t('menu.employees'),
            icon: Users,
            items: [
                { title: t('menu.employeeList'), href: '/employees', icon: Users },
                { title: t('menu.archive'), href: '/archive', icon: History },
            ],
        },
        {
            title: t('menu.timeTracking'),
            icon: Clock,
            items: [
                { title: t('menu.calendar'), href: '/calendar', icon: CalendarIcon },
                { title: t('menu.schedule'), href: '/schedule', icon: CalendarDays },
                { title: t('menu.timesheet'), href: '/timesheet', icon: CalendarClock },
            ],
        },
        {
            title: t('menu.payroll'),
            icon: Banknote,
            href: '/payroll',
        },
        {
            title: t('menu.analytics'),
            icon: History,
            href: '/analytics',
        }
    ];

    if (canViewAdmin) {
        defaultNav.push({
            title: t('menu.admin'),
            icon: ShieldCheck,
            items: [
                { title: t('menu.users'), href: '/admin/users', icon: Users },
                { title: t('menu.audit'), href: '/admin/audit', icon: History },
            ],
        });
    }

    return defaultNav;
});

const isCurrentRoute = (href) => {
    return window.location.pathname === href;
};

const isRouteActive = (item) => {
    if (item.href && isCurrentRoute(item.href)) return true;
    if (item.items) return item.items.some(subItem => isCurrentRoute(subItem.href));
    return false;
};
</script>

<template>
    <!-- Mobile Backdrop -->
    <div v-if="isMobileOpen" @click="emit('close-mobile')" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm transition-opacity md:hidden"></div>

    <!-- Sidebar Container -->
    <div :class="[
        'fixed inset-y-0 left-0 z-50 bg-[hsl(var(--sidebar-background))] text-[hsl(var(--sidebar-foreground))] overflow-y-auto border-r border-[hsl(var(--sidebar-border))] transition-all duration-300 ease-in-out md:translate-x-0 md:static flex flex-col shrink-0',
        isMobileOpen ? 'translate-x-0 w-64' : '-translate-x-full w-64',
        isDesktopCollapsed ? 'md:w-[4.5rem]' : 'md:w-64'
    ]">
        <!-- Header -->
        <div class="flex h-16 shrink-0 items-center border-b border-[hsl(var(--sidebar-border))] overflow-hidden gap-3 cursor-default transition-all" :class="isDesktopCollapsed ? 'px-0 justify-center' : 'pl-6 pr-4 justify-between'">
            <div class="flex items-center gap-3 w-full" :class="isDesktopCollapsed ? 'justify-center' : ''">
                <div class="h-8 w-8 shrink-0 rounded-lg bg-[hsl(var(--sidebar-primary))] flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold">S</span>
                </div>
                <div v-if="!isDesktopCollapsed" class="flex flex-col md:whitespace-nowrap transition-opacity">
                    <span class="font-bold text-xl tracking-tight leading-none">Siizi HR</span>
                </div>
            </div>
            
            <button v-if="!isDesktopCollapsed" @click="emit('close-mobile')" class="h-8 w-8 shrink-0 flex items-center justify-center rounded-lg hover:bg-[hsl(var(--sidebar-accent))] transition-colors md:hidden">
                <X class="h-4 w-4" />
            </button>
        </div>

        <!-- Navigation -->
        <div class="p-2 space-y-4 flex-1 overflow-x-hidden" :class="isDesktopCollapsed ? 'px-2' : 'px-4'">
            <template v-for="item in navigation" :key="item.title">
                
                <!-- Single Link -->
                <div v-if="!item.items">
                    <Link :href="item.href"
                          @click="emit('close-mobile')"
                          :title="isDesktopCollapsed ? item.title : ''"
                          :class="[
                              isCurrentRoute(item.href) ? 'bg-[hsl(var(--sidebar-accent))] text-[hsl(var(--sidebar-accent-foreground))] font-medium' : 'text-[hsl(var(--sidebar-foreground))/80] hover:bg-[hsl(var(--sidebar-accent))] hover:text-[hsl(var(--sidebar-accent-foreground))]',
                              'flex items-center gap-3 py-2 text-sm rounded-lg transition-colors group',
                              isDesktopCollapsed ? 'justify-center px-0' : 'px-3'
                          ]">
                        <component :is="item.icon" :class="[
                            isCurrentRoute(item.href) ? 'text-[hsl(var(--sidebar-primary))]' : 'text-current opacity-70 group-hover:opacity-100',
                            'h-4 w-4 shrink-0 transition-colors'
                        ]" />
                        <span v-if="!isDesktopCollapsed" class="whitespace-nowrap">{{ item.title }}</span>
                    </Link>
                </div>

                <!-- Group -->
                <div v-else class="space-y-1">
                    <div v-if="!isDesktopCollapsed" class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-widest text-[hsl(var(--sidebar-foreground))/40] whitespace-nowrap">
                        {{ item.title }}
                    </div>
                    <div v-else class="h-px bg-[hsl(var(--sidebar-border))] mx-2 my-2"></div>

                    <div class="space-y-0.5">
                        <Link v-for="subItem in item.items" :key="subItem.href" :href="subItem.href"
                              @click="emit('close-mobile')"
                              :title="isDesktopCollapsed ? subItem.title : ''"
                              :class="[
                                  isCurrentRoute(subItem.href) ? 'bg-[hsl(var(--sidebar-accent))] text-[hsl(var(--sidebar-accent-foreground))] font-medium' : 'text-[hsl(var(--sidebar-foreground))/80] hover:bg-[hsl(var(--sidebar-accent))] hover:text-[hsl(var(--sidebar-accent-foreground))]',
                                  'flex items-center gap-3 py-2 text-sm rounded-lg transition-colors group',
                                  isDesktopCollapsed ? 'justify-center px-0' : 'px-3 pl-3 pr-3'
                              ]">
                            <component :is="subItem.icon" :class="[
                                isCurrentRoute(subItem.href) ? 'text-[hsl(var(--sidebar-primary))]' : 'text-current opacity-70 group-hover:opacity-100',
                                'h-4 w-4 shrink-0 transition-colors'
                            ]" />
                            <span v-if="!isDesktopCollapsed" class="whitespace-nowrap">{{ subItem.title }}</span>
                        </Link>
                    </div>
                </div>

            </template>
        </div>
    </div>
</template>
