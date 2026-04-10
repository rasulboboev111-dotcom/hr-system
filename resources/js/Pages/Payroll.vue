<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { ref, computed } from 'vue';
import { 
  Wallet, Clock, CheckCircle2, Download, Filter, 
  Search, Plus, MoreVertical, Edit2, Trash2, FileText 
} from 'lucide-vue-next';

// Use same mocked data approach since it's just visual for now, or pass from backend.
// In this case, we'll accept employees as a prop.
const props = defineProps({
  employees: { type: Array, default: () => [] },
  payroll_records: { type: Array, default: () => [] }
});

const { t, language } = useI18n();

import { useForm } from '@inertiajs/vue3';

const payrollData = computed(() => {
  return props.employees.filter(e => e.status !== 'Retired').map(e => {
    const record = props.payroll_records.find(r => r.employee_id === e.id);
    return {
      ...e,
      bonus: record ? record.bonus : 500,
      salary: record ? record.salary : 8500,
      adjustment: 0
    };
  });
});

const searchTerm = ref('');
const isAddOpen = ref(false);

const form = useForm({
  employee_id: '',
  salary: '',
  bonus: '500',
  month_year: new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0')
});

const filteredPayroll = computed(() => {
  return payrollData.value.filter(p => {
    const term = searchTerm.value.toLowerCase();
    const fullName = `${p.first_name} ${p.last_name}`.toLowerCase();
    return fullName.includes(term) || (p.department && p.department.toLowerCase().includes(term));
  });
});

const totalPayroll = computed(() => 
  filteredPayroll.value.reduce((acc, e) => acc + (parseFloat(e.salary) || 0) + (parseFloat(e.bonus) || 0), 0)
);

const handleAddPayroll = () => {
    if (!form.employee_id || !form.salary) return;

    form.post('/payroll/bonus', {
        preserveScroll: true,
        onSuccess: () => {
            isAddOpen.value = false;
            form.reset();
        }
    });
};

const handleExport = () => {
    window.location.href = '/payroll/export';
};
</script>

<template>
    <Head title="Payroll" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('payroll.title') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('payroll.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="handleExport" class="h-9 px-3 border border-[hsl(var(--border))] rounded-lg inline-flex items-center justify-center gap-2 uppercase font-bold text-[10px] tracking-widest hover:bg-[hsl(var(--muted))]">
                        <Download class="h-4 w-4" /> {{ t('common.export') }}
                    </button>
                    
                    <button @click="isAddOpen = true" class="h-9 px-3 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg inline-flex items-center justify-center gap-2 uppercase font-bold text-[10px] tracking-widest shadow-sm">
                        <Plus class="h-4 w-4" /> {{ t('payroll.paid') }}
                    </button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="border border-[hsl(var(--border))] rounded-xl shadow-sm bg-white overflow-hidden group">
                    <div class="h-1 w-full bg-[hsl(var(--primary))]" />
                    <div class="p-6 pb-2 flex flex-row items-center justify-between">
                        <h3 class="text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">{{ t('payroll.totalFund') }}</h3>
                        <Wallet class="h-4 w-4 text-[hsl(var(--primary))] group-hover:scale-110 transition-transform" />
                    </div>
                    <div class="p-6 pt-0">
                        <div class="text-2xl font-bold">{{ totalPayroll.toLocaleString() }} TJS</div>
                        <p class="text-[10px] text-emerald-600 font-bold mt-1">+2.4% <span class="text-[hsl(var(--muted-foreground))] font-normal">{{ t('dashboard.vsLastMonth') }}</span></p>
                    </div>
                </div>
                <div class="border border-[hsl(var(--border))] rounded-xl shadow-sm bg-white overflow-hidden group">
                    <div class="h-1 w-full bg-amber-500" />
                    <div class="p-6 pb-2 flex flex-row items-center justify-between">
                        <h3 class="text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">{{ t('payroll.upcomingPayments') }}</h3>
                        <Clock class="h-4 w-4 text-amber-500 group-hover:scale-110 transition-transform" />
                    </div>
                    <div class="p-6 pt-0">
                        <div class="text-2xl font-bold">05.04.2024</div>
                        <p class="text-[10px] text-[hsl(var(--muted-foreground))] font-bold mt-1 uppercase tracking-tighter">Оғози давраи навбатӣ</p>
                    </div>
                </div>
                <div class="border border-[hsl(var(--border))] rounded-xl shadow-sm bg-white overflow-hidden group">
                    <div class="h-1 w-full bg-emerald-500" />
                    <div class="p-6 pb-2 flex flex-row items-center justify-between">
                        <h3 class="text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">{{ t('payroll.lastStatus') }}</h3>
                        <CheckCircle2 class="h-4 w-4 text-emerald-500 group-hover:scale-110 transition-transform" />
                    </div>
                    <div class="p-6 pt-0">
                        <div class="text-2xl font-bold text-emerald-600">{{ t('common.success').toUpperCase() }}</div>
                        <p class="text-[10px] text-[hsl(var(--muted-foreground))] font-bold mt-1 uppercase tracking-tighter">Тасдиқшуда 2 соат пеш</p>
                    </div>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-white rounded-xl">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-sm w-full">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input v-model="searchTerm" :placeholder="t('common.search')" class="pl-9 h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--muted))]/20 focus:outline-none" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="h-9 px-3 border border-[hsl(var(--border))] rounded-lg inline-flex items-center gap-2 text-[10px] font-bold uppercase hover:bg-[hsl(var(--muted))]">
                            <Filter class="h-4 w-4" /> {{ t('common.filter') }}
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/30 uppercase tracking-widest text-[9px] font-bold border-b border-[hsl(var(--border))] text-[hsl(var(--muted-foreground))]">
                                <th class="w-12 px-6 py-4">№</th>
                                <th class="px-6 py-4">{{ t('menu.employees') }}</th>
                                <th class="px-6 py-4">{{ t('common.department') }}</th>
                                <th class="px-6 py-4">{{ t('payroll.salary') }} (TJS)</th>
                                <th class="px-6 py-4">{{ t('payroll.bonus') }}</th>
                                <th class="px-6 py-4">{{ t('payroll.total') }}</th>
                                <th class="px-6 py-4">{{ t('common.status') }}</th>
                                <th class="text-right px-6 py-4">{{ t('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="filteredPayroll.length > 0">
                                <tr v-for="(emp, i) in filteredPayroll" :key="emp.id" class="text-[11px] group transition-colors hover:bg-[hsl(var(--primary))]/[0.03] border-b border-[hsl(var(--border))] last:border-0">
                                    <td class="font-bold text-[hsl(var(--primary))] px-6 py-4">{{ i + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-[hsl(var(--foreground))]/80">{{ emp.first_name }} {{ emp.last_name }}</span>
                                            <span class="text-[9px] text-[hsl(var(--muted-foreground))] uppercase">{{ emp.position }}</span>
                                        </div>
                                    </td>
                                    <td class="text-[hsl(var(--muted-foreground))] px-6 py-4 font-medium uppercase">{{ emp.department }}</td>
                                    <td class="px-6 py-4 font-mono font-bold">{{ (emp.salary || 0).toLocaleString() }}</td>
                                    <td class="px-6 py-4 font-mono text-emerald-600 font-bold">+{{ (emp.bonus || 0).toLocaleString() }}</td>
                                    <td class="font-bold text-[hsl(var(--primary))] px-6 py-4 font-mono">
                                        {{ ((parseFloat(emp.salary) || 0) + (parseFloat(emp.bonus) || 0)).toLocaleString() }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block text-[8px] font-bold bg-emerald-50 text-emerald-700 rounded-md px-2 py-0.5 uppercase tracking-tighter">
                                            {{ t('payroll.paid') }}
                                        </span>
                                    </td>
                                    <td class="text-right px-6 py-4">
                                        <div class="inline-flex justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="h-7 w-7 inline-flex items-center justify-center rounded hover:bg-[hsl(var(--muted))] object-center"><MoreVertical class="h-4 w-4 text-[hsl(var(--muted-foreground))]" /></button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="8" class="h-32 text-center text-[hsl(var(--muted-foreground))] text-xs italic border-b border-[hsl(var(--border))]">
                                    {{ t('common.noData') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Payroll Modal -->
        <div v-if="isAddOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))]">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <Wallet class="h-5 w-5 text-[hsl(var(--primary))]" />
                        {{ t('payroll.title') }}
                    </h2>
                    <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1">Маълумоти маошро барои корманди интихобшуда навсозӣ кунед.</p>
                </div>
                <div class="p-6 grid gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('menu.employees') }}</label>
                        <select v-model="form.employee_id" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]">
                            <option value="">Интихоби корманд</option>
                            <option v-for="emp in payrollData" :key="emp.id" :value="emp.id">{{ emp.first_name }} {{ emp.last_name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('payroll.salary') }}</label>
                            <input v-model="form.salary" type="number" placeholder="мас. 15000" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('payroll.bonus') }}</label>
                            <input v-model="form.bonus" type="number" placeholder="мас. 500" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-6 pt-2">
                    <button @click="handleAddPayroll" class="w-full h-10 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg text-xs font-bold uppercase">{{ t('common.save') }}</button>
                </div>
            </div>
        </div>
        <div v-if="isAddOpen" @click="isAddOpen = false" class="fixed inset-0 z-40"></div>
    </AppLayout>
</template>
