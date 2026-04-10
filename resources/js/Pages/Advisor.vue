<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { 
  BrainCircuit, Sparkles, RefreshCcw, Target, TrendingUp, AlertCircle, Lightbulb 
} from 'lucide-vue-next';
import { ref } from 'vue';

const loading = ref(false);
const recommendations = ref(null);

const TEAMS = [
  { id: 't1', name: 'Frontend Guild' },
  { id: 't2', name: 'Backend Core' }
];

const mockResult = {
  placementRecommendations: [
    { employeeId: 'e1', projectId: 'p1', confidenceScore: 92, rationale: 'Excellent fit for React and Typescript requirements.' },
    { employeeId: 'e2', teamId: 't1', confidenceScore: 88, rationale: 'Can mentor junior developers in Vue 3.' }
  ],
  skillGapAnalysis: [
    { teamId: 't2', missingSkills: ['GraphQL', 'Redis'], impact: 'High latency in data delivery', suggestedTraining: ['Advanced Redis Caching', 'GraphQL API Design'] }
  ]
};

const handleGenerate = () => {
    loading.value = true;
    axios.post('/advisor/generate')
        .then(res => {
            recommendations.value = res.data;
        })
        .catch(err => alert('Failed to generate.'))
        .finally(() => loading.value = false);
};
</script>

<template>
    <Head title="Smart Staffing Advisor" />

    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[hsl(var(--primary))]/10 text-[hsl(var(--primary))] text-xs font-bold uppercase tracking-wider">
                        <Sparkles class="h-3 w-3" />
                        AI Мушовир
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Мушовири зиракии кадрҳо</h1>
                    <p class="text-[hsl(var(--muted-foreground))]">Зеҳни сунъӣ малакаҳои кормандон ва лоиҳаҳои ҷорӣ барои оптимизатсияи тақсимоти захираҳоро таҳлил мекунад.</p>
                </div>
                <button 
                    @click="handleGenerate" 
                    :disabled="loading" 
                    class="h-12 px-8 bg-[hsl(var(--primary))] hover:bg-[hsl(var(--primary))]/90 text-[hsl(var(--primary-foreground))] rounded-xl font-bold uppercase tracking-widest text-[10px] shadow-lg transition-all active:scale-95 inline-flex items-center justify-center disabled:opacity-50"
                >
                    <template v-if="loading">
                        <RefreshCcw class="mr-2 h-4 w-4 animate-spin" />
                        Таҳлили маълумот...
                    </template>
                    <template v-else>
                        <BrainCircuit class="mr-2 h-5 w-5" />
                        Оғоз кардани таҳлил
                    </template>
                </button>
            </div>

            <div v-if="!recommendations && !loading" class="py-20 flex flex-col items-center justify-center text-center space-y-6">
                <div class="h-20 w-20 rounded-full bg-[hsl(var(--secondary))]/50 flex items-center justify-center">
                    <BrainCircuit class="h-10 w-10 text-[hsl(var(--muted-foreground))]" />
                </div>
                <div class="space-y-2 max-w-sm">
                    <h3 class="text-xl font-semibold">Барои таҳлил омода</h3>
                    <p class="text-sm text-[hsl(var(--muted-foreground))]">Тугмаи болоро пахш кунед, то AI конфигуратсияҳои лоиҳаҳо ва кормандонро аз ҷиҳати самаранокӣ таҳлил кунад.</p>
                </div>
            </div>

            <div v-if="loading" class="grid gap-6 md:grid-cols-2">
                <div v-for="i in 4" :key="i" class="animate-pulse shadow-sm h-48 bg-[hsl(var(--muted))]/50 rounded-xl" />
            </div>

            <div v-if="recommendations" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <section>
                    <div class="flex items-center gap-2 mb-4">
                        <Target class="h-5 w-5 text-[hsl(var(--primary))]" />
                        <h2 class="text-xl font-bold">Тавсияҳо оид ба ҷойгиршавӣ</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div v-for="(rec, idx) in recommendations.placementRecommendations" :key="idx" class="border border-[hsl(var(--border))] rounded-xl bg-[hsl(var(--card))] shadow-sm hover:shadow-md transition-all p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] uppercase font-bold text-[hsl(var(--primary))] border border-[hsl(var(--primary))]/20 bg-[hsl(var(--primary))]/5 px-2 py-1 rounded-md">
                                    Боварӣ {{ rec.confidenceScore }}%
                                </span>
                                <div class="w-24 h-2 bg-[hsl(var(--muted))]/50 rounded-full overflow-hidden">
                                    <div class="h-full bg-[hsl(var(--primary))]" :style="{ width: rec.confidenceScore + '%' }"></div>
                                </div>
                            </div>
                            <h3 class="text-lg font-bold">Корманд {{ rec.employeeId }}</h3>
                            <p class="text-xs text-[hsl(var(--muted-foreground))] uppercase font-bold mt-1">Тавсия: {{ rec.projectId ? 'Лоиҳа ' + rec.projectId : 'Гурӯҳ ' + rec.teamId }}</p>
                            <p class="text-sm text-[hsl(var(--muted-foreground))] leading-relaxed mt-4 bg-[hsl(var(--muted))]/10 p-3 rounded-lg">
                                {{ rec.rationale }}
                            </p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="flex items-center gap-2 mb-4">
                        <AlertCircle class="h-5 w-5 text-rose-500" />
                        <h2 class="text-xl font-bold">Таҳлили камбудиҳои малакаҳо</h2>
                    </div>
                    <div class="grid gap-4">
                        <div v-for="(gap, idx) in recommendations.skillGapAnalysis" :key="idx" class="border border-[hsl(var(--border))] rounded-xl bg-[hsl(var(--card))] shadow-sm flex flex-col md:flex-row overflow-hidden">
                            <div class="md:w-1/3 bg-[hsl(var(--muted))]/10 p-6 flex flex-col justify-between border-r border-[hsl(var(--border))]">
                                <div>
                                    <h3 class="font-bold text-lg mb-1">{{ TEAMS.find(t => t.id === gap.teamId)?.name || gap.teamId }}</h3>
                                    <p class="text-xs text-[hsl(var(--muted-foreground))] uppercase tracking-widest font-bold">Камбудии муҳим</p>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span v-for="skill in gap.missingSkills" :key="skill" class="bg-rose-500/10 text-rose-600 px-3 py-1 rounded-md text-xs font-bold">
                                        {{ skill }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 p-6 space-y-6">
                                <div class="flex items-start gap-4">
                                    <div class="h-8 w-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                                        <TrendingUp class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold">Таъсир ба бизнес</h4>
                                        <p class="text-sm text-[hsl(var(--muted-foreground))] mt-1">{{ gap.impact }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                        <Lightbulb class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold">Омӯзиши тавсияшуда</h4>
                                        <ul class="text-sm text-[hsl(var(--muted-foreground))] mt-2 space-y-1">
                                            <li v-for="t in gap.suggestedTraining" :key="t" class="flex items-center gap-2">
                                                <div class="h-1.5 w-1.5 bg-emerald-500 rounded-full" /> {{ t }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
