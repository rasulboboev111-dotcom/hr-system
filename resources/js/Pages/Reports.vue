<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { 
  FileText, Download, Search, Filter, 
  FileSpreadsheet, FilePieChart, History 
} from 'lucide-vue-next';

const reportTypes = [
  { id: 1, name: "Ҳисоботи ҳармоҳаи маош", date: "01.02.2024", type: "Payroll", size: "2.4 MB" },
  { id: 2, name: "Таҳлили умумии кадрҳо", date: "28.01.2024", type: "Analytics", size: "1.8 MB" },
  { id: 3, name: "Рӯйхати вакансияҳои фаъол", date: "15.01.2024", type: "Recruitment", size: "0.5 MB" },
  { id: 4, name: "Ҷадвали рухсатиҳо - 2024", date: "10.01.2024", type: "HR", size: "1.1 MB" },
];

const stats = [
  { label: "Умумӣ", value: "128", icon: FileText, color: "text-blue-500" },
  { label: "Моҳи ҷорӣ", value: "12", icon: History, color: "text-emerald-500" },
  { label: "Excel", value: "45", icon: FileSpreadsheet, color: "text-green-600" },
  { label: "PDF", value: "83", icon: FileText, color: "text-rose-500" },
];
</script>

<template>
    <Head title="Reports" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Ҳисоботҳо</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">Бойгонии ҳуҷҷатҳои системавӣ</p>
                </div>
                <button class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] h-9 px-4 rounded-lg inline-flex items-center gap-2 text-sm font-medium">
                    <FilePieChart class="h-4 w-4" /> Генерацияи нав
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <div v-for="(stat, i) in stats" :key="i" class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 hover:border-[hsl(var(--primary))]/30 transition-colors group rounded-xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ stat.label }}</p>
                            <p class="text-xl font-bold mt-1">{{ stat.value }}</p>
                        </div>
                        <component :is="stat.icon" class="h-8 w-8 opacity-20 group-hover:opacity-100 transition-opacity" :class="stat.color" />
                    </div>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-[hsl(var(--card))] rounded-xl">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input placeholder="Номи ҳисоботро ҷустуҷӯ кунед..." class="pl-9 h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent focus:outline-none" />
                    </div>
                </div>
                <div class="divide-y divide-[hsl(var(--border))]">
                    <div v-for="report in reportTypes" :key="report.id" class="p-4 flex items-center justify-between hover:bg-[hsl(var(--muted))]/10 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-lg bg-[hsl(var(--primary))]/5 flex items-center justify-center text-[hsl(var(--primary))] group-hover:bg-[hsl(var(--primary))] group-hover:text-[hsl(var(--primary-foreground))] transition-all">
                                <FileText class="h-5 w-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold">{{ report.name }}</h4>
                                <div class="flex items-center gap-3 mt-1 text-[10px] text-[hsl(var(--muted-foreground))] font-bold uppercase tracking-tight">
                                    <span>{{ report.date }}</span>
                                    <span class="h-1 w-1 rounded-full bg-[hsl(var(--muted-foreground))]/30" />
                                    <span>{{ report.size }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block text-[9px] font-bold bg-[hsl(var(--muted))]/50 rounded px-2 py-0.5">
                                {{ report.type.toUpperCase() }}
                            </span>
                            <a :href="'/reports/download/' + report.type" target="_blank" class="h-8 w-8 inline-flex items-center justify-center text-[hsl(var(--primary))] opacity-0 group-hover:opacity-100 transition-opacity rounded hover:bg-[hsl(var(--muted))]">
                                <Download class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
