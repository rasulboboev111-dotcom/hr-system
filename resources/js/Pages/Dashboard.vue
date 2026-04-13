<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { computed } from 'vue';
import { 
    Users, Briefcase, Target, TrendingUp,
    ArrowUpRight, ArrowDownRight, Star, Zap, ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    totalEmployees: Number,
    activeVacancies: Number,
    fillRate: Number,
    turnover: Number,
    topPerformers: Array,
    quickStats: Object,
});

const { t } = useI18n();

const metrics = computed(() => [
    {
        title: t('dashboard.totalEmployees'),
        value: String(props.totalEmployees),
        change: '+4.1%',
        trend: 'up',
        icon: Users,
        color: 'bg-blue-600'
    },
    {
        title: t('dashboard.activeVacancies'),
        value: String(props.activeVacancies),
        change: '+2',
        trend: 'up',
        icon: Briefcase,
        color: 'bg-emerald-600'
    },
    {
        title: t('dashboard.fillRate'),
        value: `${props.fillRate}%`,
        change: '+1.2%',
        trend: 'up',
        icon: Target,
        color: 'bg-amber-600'
    },
    {
        title: t('dashboard.turnover'),
        value: '1.8%',
        change: '-0.5%',
        trend: 'down',
        icon: TrendingUp,
        color: 'bg-rose-600'
    }
]);

const performers = computed(() => props.topPerformers || []);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="space-y-8 max-w-[1600px] mx-auto">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-[hsl(var(--foreground))]/90">
                    {{ t('dashboard.title') }}
                </h1>
                <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-[0.2em] font-bold">
                    {{ t('dashboard.subtitle') }}
                </p>
            </div>

            <!-- Metrics Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="metric in metrics" :key="metric.title"
                     class="border-none shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300 bg-white/50 backdrop-blur-sm rounded-xl">

                    <div class="flex flex-row items-center justify-between space-y-0 pb-2 px-6 pt-6">
                        <h3 class="text-[10px] font-extrabold text-[hsl(var(--muted-foreground))]/60 uppercase tracking-widest">
                            {{ metric.title }}
                        </h3>
                        <div class="h-10 w-10 rounded-xl bg-[hsl(var(--muted))]/30 flex items-center justify-center text-[hsl(var(--foreground))] group-hover:bg-[hsl(var(--primary))] group-hover:text-white transition-all duration-300">
                            <component :is="metric.icon" class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <div class="text-3xl font-bold tracking-tight">{{ metric.value }}</div>
                        <div class="flex items-center mt-3 text-[10px] font-bold">
                            <span v-if="metric.trend === 'up'" class="flex items-center text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full border border-emerald-100">
                                <ArrowUpRight class="h-3 w-3 mr-1" /> {{ metric.change }}
                            </span>
                            <span v-else class="flex items-center text-rose-600 bg-rose-50 px-2 py-1 rounded-full border border-rose-100">
                                <ArrowDownRight class="h-3 w-3 mr-1" /> {{ metric.change }}
                            </span>
                            <span class="text-[hsl(var(--muted-foreground))]/60 ml-3">{{ t('dashboard.vsLastMonth') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid gap-6 md:grid-cols-12">
                <!-- Left Column -->
                <div class="md:col-span-8 space-y-6">
                    <!-- Top Performers -->
                    <div class="border-none shadow-sm rounded-2xl bg-white/70 backdrop-blur-md p-6">
                        <div class="flex items-center justify-between mb-8">
                            <div class="space-y-1">
                                <h3 class="text-lg font-bold flex items-center gap-2">
                                    <Star class="h-5 w-5 text-amber-500 fill-amber-500" />
                                    {{ t('dashboard.topPerformers') }}
                                </h3>
                                <p class="text-xs text-[hsl(var(--muted-foreground))]">{{ t('dashboard.topPerformersSubtitle') }}</p>
                            </div>
                            <span class="text-[10px] uppercase font-bold text-[hsl(var(--primary))] border border-[hsl(var(--border))] rounded-full px-3 py-1">{{ t('dashboard.monthlyRating') }}</span>
                        </div>
                        
                        <div class="grid gap-4">
                            <div v-for="(emp, i) in performers" :key="emp.id" class="flex items-center justify-between p-4 rounded-xl bg-[hsl(var(--muted))]/20 border border-transparent hover:border-[hsl(var(--primary))]/20 transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="h-12 w-12 rounded-full bg-[hsl(var(--primary))]/10 flex items-center justify-center font-bold text-[hsl(var(--primary))]">
                                            {{ emp.name[0] }}
                                        </div>
                                        <div class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-amber-500 border-2 border-white flex items-center justify-center text-[10px] font-bold text-white">
                                            {{ i + 1 }}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm uppercase">{{ emp.name }} {{ emp.last_name }}</h4>
                                        <p class="text-[10px] text-[hsl(var(--muted-foreground))] font-bold tracking-tight">{{ emp.role }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center gap-1 text-emerald-600 font-bold text-sm">
                                        <Zap class="h-3 w-3 fill-emerald-600" />
                                        {{ emp.performance_score || 95 }}%
                                    </div>
                                    <p class="text-[9px] text-[hsl(var(--muted-foreground))] uppercase font-bold">{{ t('dashboard.performance') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="md:col-span-4 space-y-6">
                    <!-- Quick Stats -->
                    <div class="border-none shadow-sm rounded-2xl bg-white/70 backdrop-blur-md p-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[hsl(var(--muted-foreground))] mb-4">{{ t('dashboard.quickStats') }}</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[hsl(var(--muted-foreground))]">{{ t('dashboard.newEmployees') }}</span>
                                <span class="font-bold">{{ props.quickStats?.newEmployees || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[hsl(var(--muted-foreground))]">{{ t('dashboard.pendingRequests') }}</span>
                                <span class="font-bold text-amber-600">{{ props.quickStats?.pendingRequests || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[hsl(var(--muted-foreground))]">{{ t('dashboard.scheduledInterviews') }}</span>
                                <span class="font-bold">{{ props.quickStats?.scheduledInterviews || 0 }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Status -->
                    <div class="border-none shadow-sm rounded-2xl bg-[hsl(var(--primary))]/5 p-6 border border-[hsl(var(--primary))]/10">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[hsl(var(--primary))] mb-4">{{ t('dashboard.systemStatus') }}</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="text-xs font-bold text-[hsl(var(--primary))]/80 uppercase">{{ t('dashboard.dbConnected') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="text-xs font-bold text-[hsl(var(--primary))]/80 uppercase">{{ t('dashboard.authServiceActive') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
