<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { Users, UserPlus, Target, TrendingUp, Download } from 'lucide-vue-next';
import { computed } from 'vue';
import { Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    deptDataProps: { type: Object, required: true },
    hiringTrendDataProps: { type: Object, required: true }
});

const { t } = useI18n();

const deptData = computed(() => props.deptDataProps);
const hiringTrendData = computed(() => props.hiringTrendDataProps);

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10 } } },
        y: { border: { display: false }, ticks: { font: { size: 10 } } }
    }
};

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } }
    },
    cutout: '60%'
};

const stats = computed(() => [
    { title: "Нигоҳдошти кадрҳо", value: "94.2%", icon: Target, trend: "+2.1%", trendUp: true },
    { title: "Миёнаи собиқа", value: "3.4 сол", icon: Users, trend: "+0.5", trendUp: true },
    { title: "Арзиши ба кор гирифтан", value: "1.2k", icon: TrendingUp, trend: "-12%", trendUp: false },
    { title: "Кормандони нав", value: props.employees.length.toString(), icon: UserPlus, trend: "+4", trendUp: true },
]);
</script>

<template>
    <Head title="Analytics" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('menu.analytics') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">Нишондиҳандаҳои асосии самаранокӣ</p>
                </div>
                <button class="h-9 px-3 border border-[hsl(var(--border))] rounded-lg inline-flex items-center justify-center gap-2 uppercase font-bold text-[10px] hover:bg-[hsl(var(--muted))]">
                    <Download class="h-4 w-4" /> {{ t('common.export') }}
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, i) in stats" :key="i" class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm rounded-xl p-6 group hover:border-[hsl(var(--primary))]/50 transition-colors">
                    <div class="flex flex-row items-center justify-between pb-2">
                        <h3 class="text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-wider">{{ stat.title }}</h3>
                        <component :is="stat.icon" class="h-4 w-4 text-[hsl(var(--muted-foreground))] group-hover:text-[hsl(var(--primary))] transition-colors" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                        <p class="text-[10px] font-bold mt-1" :class="stat.trendUp ? 'text-emerald-600' : 'text-rose-600'">
                            {{ stat.trend }} <span class="text-[hsl(var(--muted-foreground))] font-medium ml-1">нисб. моҳи гузашта</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="border border-[hsl(var(--border))] shadow-sm rounded-xl bg-[hsl(var(--card))]">
                    <div class="p-6 pb-0">
                        <h3 class="text-sm font-bold uppercase tracking-tight">Кормандон аз рӯи шуъба</h3>
                        <p class="text-xs text-[hsl(var(--muted-foreground))]">Тақсимоти воқеӣ аз базаи маълумот</p>
                    </div>
                    <div class="p-6 h-80">
                        <Pie :data="deptData" :options="pieOptions" />
                    </div>
                </div>

                <div class="border border-[hsl(var(--border))] shadow-sm rounded-xl bg-[hsl(var(--card))]">
                    <div class="p-6 pb-0">
                        <h3 class="text-sm font-bold uppercase tracking-tight">Динамикаи истихдом</h3>
                        <p class="text-xs text-[hsl(var(--muted-foreground))]">Кормандони нав дар 6 моҳи охир</p>
                    </div>
                    <div class="p-6 h-80">
                        <Bar :data="hiringTrendData" :options="barOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
