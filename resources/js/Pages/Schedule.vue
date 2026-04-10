<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { ref, computed } from 'vue';
import { CalendarDays, ChevronLeft, ChevronRight, Download, Plus, Clock, X } from 'lucide-vue-next';

const props = defineProps({
    employees: {
        type: Array,
        default: () => []
    },
    shifts: {
        type: Array,
        default: () => []
    }
});

const { t } = useI18n();

import { useForm } from '@inertiajs/vue3';

const isAddShiftOpen = ref(false);
const form = useForm({
    employee_id: '',
    date_key: '0',
    shift_type: 'day'
});

const days = ['Душанбе', 'Сешанбе', 'Чоршанбе', 'Панҷшанбе', 'Ҷумъа', 'Шанбе', 'Якшанбе'];
const activeEmployees = computed(() => props.employees.filter(e => e.status !== 'Retired'));

const getShiftType = (empId, dayIdx) => {
    const shift = props.shifts.find(s => s.employee_id === empId && s.date_key == dayIdx);
    return shift ? shift.shift_type : null;
};

const handleAddShift = () => {
    if (!form.employee_id) return;
    form.post('/schedule', {
        preserveScroll: true,
        onSuccess: () => {
            isAddShiftOpen.value = false;
        }
    });
};

const getShift = (empId, dayIdx) => {
    return props.shifts.find(s => s.employee_id === empId && s.date_key == dayIdx);
};

const handleDeleteShift = (shiftId) => {
    if(confirm('Мехоҳед ин бастро нест кунед?')) {
        router.delete(`/schedule/${shiftId}`, { preserveScroll: true });
    }
};

</script>

<template>
    <Head title="Schedule" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('schedule.title') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('schedule.subtitle') }}</p>
                </div>
                <div class="flex gap-2">
                    <button class="h-9 px-3 inline-flex items-center gap-2 text-[10px] font-bold uppercase border border-[hsl(var(--border))] rounded-lg hover:bg-[hsl(var(--muted))]">
                        <Download class="h-4 w-4" /> {{ t('common.export') }}
                    </button>
                    
                    <button @click="isAddShiftOpen = true" class="h-9 px-3 inline-flex items-center gap-2 text-[10px] font-bold uppercase bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg shadow-sm">
                        <Plus class="h-4 w-4" /> {{ t('schedule.addShift') }}
                    </button>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden rounded-2xl bg-white">
                <div class="flex flex-row items-center justify-between border-b p-4 bg-[hsl(var(--muted))]/5">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 bg-[hsl(var(--muted))]/50 p-1 rounded-lg border border-[hsl(var(--border))]">
                            <button class="h-8 w-8 flex items-center justify-center rounded hover:bg-[hsl(var(--card))]"><ChevronLeft class="h-4 w-4" /></button>
                            <span class="text-[10px] font-bold px-4 uppercase tracking-tighter">Март 2024</span>
                            <button class="h-8 w-8 flex items-center justify-center rounded hover:bg-[hsl(var(--card))]"><ChevronRight class="h-4 w-4" /></button>
                        </div>
                        <span class="text-[10px] font-extrabold bg-[hsl(var(--primary))]/10 text-[hsl(var(--primary))] rounded-md uppercase px-3 py-1">
                            {{ t('dashboard.currentQuarterProgress').split(' ')[0] }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-[9px] text-[hsl(var(--muted-foreground))] font-bold uppercase tracking-widest">
                        <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-emerald-500" /> {{ t('schedule.dayShift') }}</div>
                        <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-indigo-500" /> {{ t('schedule.nightShift') }}</div>
                    </div>
                </div>
                
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/10 border-b border-[hsl(var(--border))]">
                                <th class="p-4 text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase border-r border-[hsl(var(--border))] w-64">{{ t('menu.employees') }}</th>
                                <th v-for="day in days" :key="day" class="p-4 text-center text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase border-r border-[hsl(var(--border))]">
                                    {{ day }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[hsl(var(--border))]">
                            <tr v-for="emp in activeEmployees" :key="emp.id" class="hover:bg-[hsl(var(--primary))]/[0.02] transition-colors">
                                <td class="p-4 border-r border-[hsl(var(--border))]">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-[hsl(var(--primary))]/5 flex items-center justify-center font-bold text-[hsl(var(--primary))] text-[10px] uppercase">
                                            {{ emp.first_name ? emp.first_name[0] : '' }}
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-bold uppercase leading-tight">{{ emp.first_name }} {{ emp.last_name }}</p>
                                            <p class="text-[9px] text-[hsl(var(--muted-foreground))] font-bold tracking-tight">{{ emp.position }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td v-for="idx in 7" :key="idx" class="p-2 border-r border-[hsl(var(--border))] text-center group/shift">
                                    <div v-if="getShift(emp.id, idx - 1)" class="relative">
                                        <div :class="[
                                            'p-2 rounded-lg text-[9px] font-bold uppercase border',
                                            getShift(emp.id, idx - 1).shift_type === 'day' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-indigo-50 text-indigo-700 border-indigo-100'
                                        ]">
                                            {{ getShift(emp.id, idx - 1).shift_type === 'day' ? '09:00 - 18:00' : '18:00 - 02:00' }}
                                        </div>
                                        <button @click="handleDeleteShift(getShift(emp.id, idx - 1).id)" class="absolute -top-1 -right-1 h-4 w-4 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover/shift:opacity-100 transition-opacity">
                                            <X class="h-2 w-2" />
                                        </button>
                                    </div>
                                    <div v-else class="text-[9px] text-[hsl(var(--muted-foreground))]/30 font-bold italic uppercase">{{ t('common.noData') }}</div>
                                </td>
                            </tr>
                            <tr v-if="activeEmployees.length === 0">
                                <td colspan="8" class="p-8 text-center text-[10px] text-[hsl(var(--muted-foreground))] uppercase tracking-widest font-bold">
                                    {{ t('common.noData') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Shift Modal -->
        <div v-if="isAddShiftOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))]">
                    <h2 class="text-xl font-bold">{{ t('schedule.addShift') }}</h2>
                </div>
                <div class="p-6 grid gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('menu.employees') }}</label>
                        <select v-model="form.employee_id" class="h-10 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none">
                            <option value="">Интихоби корманд</option>
                            <option v-for="emp in activeEmployees" :key="emp.id" :value="emp.id">
                                {{ emp.first_name }} {{ emp.last_name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">Рӯзона / Шабона</label>
                            <select v-model="form.shift_type" class="h-10 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none">
                                <option value="day">Рӯзона (Day Shift)</option>
                                <option value="night">Шабона (Night Shift)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('common.all') }}</label>
                            <select v-model="form.date_key" class="h-10 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none">
                                <option v-for="(d, i) in days" :key="i" :value="String(i)">{{ d }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-6 pt-2">
                    <button @click="handleAddShift" class="w-full h-11 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-xl text-xs font-bold uppercase">{{ t('common.save') }}</button>
                </div>
            </div>
        </div>
        <div v-if="isAddShiftOpen" @click="isAddShiftOpen = false" class="fixed inset-0 z-40"></div>
    </AppLayout>
</template>
