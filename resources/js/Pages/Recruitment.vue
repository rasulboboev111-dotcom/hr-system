<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    Sparkles, Plus, Users, Clock, Briefcase, 
    ArrowRight, LayoutDashboard, Wand2, Search 
} from 'lucide-vue-next';

const searchQuery = ref(props.filters?.search || '');

watch(searchQuery, (value) => {
    router.get('/recruitment', { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const props = defineProps({
    vacancies: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '' }) }
});

const { t } = useI18n();

const candidates = ref([
    { name: 'Алишер Раҳимов', role: 'QA Engineer', time: '2 с. пеш' },
    { name: 'Заррина Солеҳова', role: 'Frontend Dev', time: '5 с. пеш' },
    { name: 'Иқбол Назаров', role: 'Data Analyst', time: 'Дирӯз' },
    { name: 'Самир Каримов', role: 'UI Designer', time: '2 рӯз пеш' },
]);

const isAiOpen = ref(false);
const isGenerating = ref(false);
const jobTitle = ref('');
const dept = ref('');

const generateAiDescription = () => {
    isGenerating.value = true;
    axios.post('/recruitment/generate', { title: jobTitle.value, dept: dept.value })
        .then(res => {
            alert('AI Generated Description:\n\n' + res.data.description);
            isAiOpen.value = false;
        })
        .catch(err => alert('Failed to generate description.'))
        .finally(() => isGenerating.value = false);
};
</script>

<template>
    <Head title="Recruitment" />

    <AppLayout>
        <div class="space-y-8 max-w-[1400px] mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-[hsl(var(--primary))]/10 flex items-center justify-center">
                            <Users class="h-7 w-7 text-[hsl(var(--primary))]" />
                        </div>
                        {{ t('recruitment.title') }}
                    </h1>
                    <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-[0.2em] font-bold">
                        {{ t('recruitment.subtitle') }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <button @click="isAiOpen = true" class="h-11 px-4 bg-indigo-600 hover:bg-indigo-700 text-white inline-flex items-center gap-2 font-bold text-xs uppercase tracking-widest shadow-lg shadow-indigo-200 rounded-xl transition-colors">
                        <Sparkles class="h-4 w-4" /> {{ t('recruitment.aiGenerator') }}
                    </button>
                    <button class="h-11 px-4 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] inline-flex items-center gap-2 font-bold text-xs uppercase tracking-widest shadow-lg shadow-[hsl(var(--primary))]/20 rounded-xl">
                        <Plus class="h-4 w-4" /> {{ t('recruitment.newVacancy') }}
                    </button>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Left: Active Vacancies -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold flex items-center gap-2 uppercase tracking-tighter">
                            <LayoutDashboard class="h-5 w-5 text-[hsl(var(--primary))]" />
                            {{ t('recruitment.activeVacancies') }}
                        </h2>
                        <div class="flex items-center gap-4">
                            <div class="relative w-64 hidden md:block">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]" />
                                <input v-model="searchQuery" :placeholder="t('common.search')" class="w-full h-9 pl-9 pr-4 text-[10px] font-bold uppercase tracking-widest bg-white border border-[hsl(var(--border))] rounded-xl focus:outline-none focus:ring-1 focus:ring-[hsl(var(--primary))]/20 transition-all" />
                            </div>
                            <span class="bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))] border-none text-[10px] font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shrink-0">
                                {{ vacancies.length }} {{ t('common.active').toUpperCase() }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid gap-4">
                        <div v-for="pos in vacancies" :key="pos.id" class="border-none shadow-sm group hover:shadow-md hover:scale-[1.005] transition-all duration-300 overflow-hidden bg-white/70 backdrop-blur-sm rounded-3xl">
                            <div class="flex">
                                <div class="w-2 bg-amber-500/80"></div>
                                <div class="flex-1 p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="space-y-1">
                                            <h3 class="font-bold text-xl text-[hsl(var(--foreground))]/90 uppercase">{{ pos.title }}</h3>
                                            <div class="flex items-center gap-2 text-[hsl(var(--muted-foreground))] text-[11px] font-bold uppercase tracking-widest">
                                                <Briefcase class="h-3 w-3" />
                                                {{ pos.department }}
                                            </div>
                                        </div>
                                        <span class="text-[10px] bg-amber-50 text-amber-700 border border-amber-100 font-extrabold px-3 py-1 rounded-full uppercase">
                                            {{ t('common.active').toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-6 text-[11px] text-[hsl(var(--muted-foreground))]/80 font-bold mt-6 pt-4 border-t border-[hsl(var(--muted))]/20">
                                        <div class="flex items-center gap-2">
                                            <Users class="h-4 w-4 text-[hsl(var(--primary))]" />
                                            <span>12 {{ t('menu.recruitment') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Clock class="h-4 w-4 text-[hsl(var(--primary))]" />
                                            <span>14 {{ t('common.loading') }}</span>
                                        </div>
                                        <div class="ml-auto">
                                            <button class="p-0 text-[hsl(var(--primary))] font-extrabold text-[11px] uppercase tracking-widest flex items-center gap-1 hover:gap-2 transition-all">
                                                {{ t('common.details') }} <ArrowRight class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="vacancies.length === 0" class="text-center py-12 text-[hsl(var(--muted-foreground))]">
                            <p class="text-sm font-medium">Вакансияҳои фаъол нест</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Recent Candidates -->
                <div class="space-y-6">
                    <h2 class="text-lg font-bold flex items-center gap-2 uppercase tracking-tighter">
                        <Users class="h-5 w-5 text-[hsl(var(--primary))]" />
                        {{ t('recruitment.recentCandidates') }}
                    </h2>
                    <div class="border-none shadow-sm rounded-3xl bg-white/70 backdrop-blur-md overflow-hidden">
                        <div class="divide-y divide-[hsl(var(--muted))]/20">
                            <div v-for="(cand, i) in candidates" :key="i" class="p-5 hover:bg-[hsl(var(--primary))]/[0.03] transition-colors flex items-center gap-4 group cursor-pointer">
                                <div class="h-10 w-10 rounded-xl bg-[hsl(var(--primary))]/10 flex items-center justify-center font-bold text-[hsl(var(--primary))] text-[11px] border border-[hsl(var(--primary))]/20 shadow-sm group-hover:scale-110 transition-transform">
                                    {{ cand.name[0] }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-[hsl(var(--foreground))]/80 truncate uppercase tracking-tighter">{{ cand.name }}</p>
                                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] font-bold uppercase tracking-widest mt-0.5">{{ cand.role }}</p>
                                </div>
                                <span class="text-[10px] text-[hsl(var(--muted-foreground))]/50 font-medium whitespace-nowrap">{{ cand.time }}</span>
                            </div>
                        </div>
                        <div class="p-4 border-t border-[hsl(var(--muted))]/20 bg-[hsl(var(--muted))]/5">
                            <button class="w-full text-[11px] font-extrabold h-11 text-[hsl(var(--primary))] uppercase tracking-widest hover:bg-[hsl(var(--primary))]/5 rounded-xl transition-colors">
                                {{ t('common.viewAll') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Generator Modal -->
        <div v-if="isAiOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white/95 backdrop-blur-lg border-none shadow-2xl rounded-3xl w-full max-w-2xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))]">
                    <h2 class="flex items-center gap-3 text-xl font-bold">
                        <div class="h-10 w-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                            <Wand2 class="h-6 w-6" />
                        </div>
                        {{ t('recruitment.aiGenerator') }}
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]/70 tracking-widest">{{ t('recruitment.jobTitle') }}</label>
                            <input v-model="jobTitle" placeholder="мас. Senior Backend Developer" class="h-11 w-full rounded-xl border border-[hsl(var(--muted))]/30 px-3 text-sm focus:border-indigo-400 focus:ring-indigo-100 focus:outline-none" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]/70 tracking-widest">{{ t('recruitment.dept') }}</label>
                            <input v-model="dept" placeholder="мас. Engineering" class="h-11 w-full rounded-xl border border-[hsl(var(--muted))]/30 px-3 text-sm focus:border-indigo-400 focus:ring-indigo-100 focus:outline-none" />
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0 flex gap-3">
                    <button @click="isAiOpen = false" class="flex-1 h-12 rounded-xl text-sm font-bold text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--muted))] transition-colors">
                        {{ t('common.cancel') }}
                    </button>
                    <button @click="generateAiDescription" :disabled="isGenerating" class="flex-1 h-12 bg-indigo-600 text-white font-bold uppercase tracking-widest shadow-xl shadow-indigo-100 rounded-xl text-xs disabled:opacity-50">
                        {{ isGenerating ? 'Дар ҳоли эҷод...' : t('recruitment.generate') }}
                    </button>
                </div>
            </div>
        </div>
        <div v-if="isAiOpen" @click="isAiOpen = false" class="fixed inset-0 z-40"></div>
    </AppLayout>
</template>
