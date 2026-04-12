<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { ref, onMounted, computed } from 'vue';
import { 
    FileSpreadsheet, Search, CheckCircle2, Clock, 
    XCircle, AlertCircle, ShieldCheck, Plus, UserCircle, Upload 
} from 'lucide-vue-next';

const props = defineProps({
    employees: { type: Array, default: () => [] },
    attendances: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] }
});
const page = usePage();
const { t } = useI18n();



const daysInMonth = Array.from({ length: 31 }, (_, i) => i + 1);
const isAddOpen = ref(false);
const form = useForm({ employee_name: '', day: '1', status: 'P', date_key: '', department: '' });
const selectedDepartment = ref('');

const activeEmployees = computed(() => {
    let list = props.employees.filter(e => (e.status || '').toLowerCase() !== 'retired');
    if (selectedDepartment.value) {
        list = list.filter(e => e.department === selectedDepartment.value);
    }
    return list;
});

const modalActiveEmployees = computed(() => {
    let list = props.employees.filter(e => (e.status || '').toLowerCase() !== 'retired');
    if (form.department) {
        list = list.filter(e => e.department === form.department);
    }
    return list;
});

const attendanceData = computed(() => {
    const data = {};
    activeEmployees.value.forEach(emp => {
        data[emp.id] = {};
    });
    props.attendances.forEach(att => {
        if(data[att.employee_id]) {
            data[att.employee_id][parseInt(att.date_key)] = att.status;
        }
    });
    return data;
});

const stats = computed(() => {
    let present = 0;
    let late = 0;
    let absent = 0;
    let totalRecords = 0;

    activeEmployees.value.forEach(emp => {
        const data = attendanceData.value[emp.id] || {};
        Object.values(data).forEach(status => {
            if (status === 'P') present++;
            if (status === 'L') late++;
            if (status === 'A') absent++;
            totalRecords++;
        });
    });

    const presentRate = totalRecords > 0 ? Math.round((present / totalRecords) * 100) : 0;
    const avgHours = activeEmployees.value.length > 0 ? ((present * 8) / activeEmployees.value.length).toFixed(1) : 0;

    return {
        presentRate: presentRate + '%',
        late,
        absent,
        averageHours: avgHours
    };
});

const handleAddEntry = () => {
    if (!form.employee_name) return;
    form.date_key = form.day;
    form.post('/timesheet', {
        preserveScroll: true,
        onSuccess: () => {
            isAddOpen.value = false;
            form.reset();
        }
    });
};

const getTotalHours = (empId) => {
    const data = attendanceData.value[empId] || {};
    return Object.values(data).filter(s => s === 'P').length * 8;
};

const handleConfirmTimesheet = () => {
    alert("Давомот тасдиқ ва қулф шуд.");
};

const handleExport = () => {
    window.location.href = '/timesheet/export';
};

const fileInput = ref(null);

const handleImport = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
        router.post('/timesheet/import', formData, {
        preserveScroll: true,
        onError: (err) => {
            alert('Хатогӣ ҳангоми импорт: ' + Object.values(err).join('\n'));
        },
        onSuccess: () => {
            alert(t('common.importSuccess') || 'Импорт бо муваффақият анҷом шуд');
            // reset input
            if (fileInput.value) fileInput.value.value = '';
        }
    });
};

const toggleAttendance = (empId, day) => {
    if (!canEdit.value) return;
    const isWeekend = day % 7 === 0 || day % 7 === 6;
    if (isWeekend) return;
    
    const currentStatus = attendanceData.value[empId]?.[day];
    let nextStatus = 'P';
    if (currentStatus === 'P') nextStatus = 'L';
    else if (currentStatus === 'L') nextStatus = 'A';
    
    router.post('/timesheet', {
        employee_id: empId,
        date_key: String(day),
        status: nextStatus
    }, { preserveScroll: true });
};

const canEdit = computed(() => page.props.auth.permissions.includes('edit_timesheet') || page.props.auth.permissions.includes('all'));
const canImport = computed(() => page.props.auth.permissions.includes('import_timesheet') || page.props.auth.permissions.includes('all'));
const canExport = computed(() => page.props.auth.permissions.includes('export_timesheet') || page.props.auth.permissions.includes('all'));

</script>

<template>
    <Head title="Timesheet" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('timesheet.title') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('timesheet.subtitle') }}</p>
                </div>
                <div class="flex gap-2 items-center">
                    <select v-model="selectedDepartment" class="min-w-[150px] h-9 px-3 bg-[hsl(var(--card))] border border-[hsl(var(--border))] rounded-lg text-[10px] font-bold uppercase focus:outline-none text-[hsl(var(--muted-foreground))]">
                        <option value="">{{ t('common.all') || 'Все отделы' }}</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option>
                    </select>
                    <button v-if="canEdit" @click="isAddOpen = true" class="h-9 px-3 inline-flex items-center gap-2 border border-[hsl(var(--primary))]/20 text-[hsl(var(--primary))] hover:bg-[hsl(var(--primary))]/5 uppercase font-bold text-[10px] rounded-lg">
                        <Plus class="h-4 w-4" /> {{ t('common.add') }}
                    </button>
                    <button v-if="canImport" @click="fileInput.click()" class="h-9 px-3 inline-flex items-center gap-2 text-cyan-700 bg-cyan-50 hover:bg-cyan-100 border border-cyan-200 uppercase font-bold text-[10px] rounded-lg">
                        <Upload class="h-4 w-4" /> {{ t('common.import') }}
                    </button>
                    <input v-if="canImport" type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleImport" />
                    <button v-if="canExport" @click="handleExport" class="h-9 px-3 inline-flex items-center gap-2 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 uppercase font-bold text-[10px] rounded-lg">
                        <FileSpreadsheet class="h-4 w-4" /> {{ t('common.export') }}
                    </button>
                    <button v-if="canEdit" @click="handleConfirmTimesheet" class="h-9 px-3 inline-flex items-center gap-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow-sm uppercase font-bold text-[10px] rounded-lg">
                        <ShieldCheck class="h-4 w-4" /> {{ t('timesheet.confirm') }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 border border-[hsl(var(--border))] shadow-sm flex items-center gap-4 group hover:border-[hsl(var(--primary))]/50 transition-all rounded-xl bg-[hsl(var(--card))]">
                    <div class="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">{{ t('timesheet.present') }}</p>
                        <p class="text-xl font-bold">{{ stats.presentRate }}</p>
                    </div>
                </div>
                <div class="p-4 border border-[hsl(var(--border))] shadow-sm flex items-center gap-4 group hover:border-[hsl(var(--primary))]/50 transition-all rounded-xl bg-[hsl(var(--card))]">
                    <div class="h-10 w-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 shadow-inner">
                        <Clock class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">{{ t('timesheet.late') }}</p>
                        <p class="text-xl font-bold">{{ stats.late }}</p>
                    </div>
                </div>
                <div class="p-4 border border-[hsl(var(--border))] shadow-sm flex items-center gap-4 group hover:border-[hsl(var(--primary))]/50 transition-all rounded-xl bg-[hsl(var(--card))]">
                    <div class="h-10 w-10 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 shadow-inner">
                        <XCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">{{ t('timesheet.absent') }}</p>
                        <p class="text-xl font-bold">{{ stats.absent }}</p>
                    </div>
                </div>
                <div class="p-4 border border-[hsl(var(--border))] shadow-sm flex items-center gap-4 group hover:border-[hsl(var(--primary))]/50 transition-all rounded-xl bg-[hsl(var(--card))]">
                    <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner">
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">Миёнаи соатҳо</p>
                        <p class="text-xl font-bold">{{ stats.averageHours }}</p>
                    </div>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden rounded-2xl bg-white/50 backdrop-blur-sm">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input :placeholder="t('common.search')" class="pl-9 h-9 w-full text-[11px] rounded-lg border-none bg-[hsl(var(--muted))]/20 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[9px] font-bold border border-emerald-500/20 text-emerald-700 bg-emerald-50 px-3 py-1 rounded-md uppercase cursor-default">P - {{ t('timesheet.present') }}</span>
                        <span class="text-[9px] font-bold border border-amber-500/20 text-amber-700 bg-amber-50 px-3 py-1 rounded-md uppercase cursor-default">L - {{ t('timesheet.late') }}</span>
                        <span class="text-[9px] font-bold border border-rose-500/20 text-rose-700 bg-rose-50 px-3 py-1 rounded-md uppercase cursor-default">A - {{ t('timesheet.absent') }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/10 border-b border-[hsl(var(--border))]">
                                <th class="p-3 text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase border-r border-[hsl(var(--border))] sticky left-0 bg-white z-10 w-48">{{ t('menu.employees') }}</th>
                                <th v-for="day in daysInMonth" :key="day" class="p-1 text-center text-[9px] font-bold text-[hsl(var(--muted-foreground))] border-r border-[hsl(var(--border))] min-w-[30px]">
                                    {{ day }}
                                </th>
                                <th class="p-3 text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase border-l border-[hsl(var(--border))] sticky right-0 bg-white z-10 w-24 text-right">{{ t('timesheet.totalHours') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[hsl(var(--border))]">
                            <tr v-for="emp in activeEmployees" :key="emp.id" class="hover:bg-[hsl(var(--primary))]/[0.02] transition-colors">
                                <td class="p-3 border-r border-[hsl(var(--border))] sticky left-0 bg-white z-10">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold uppercase truncate">{{ emp.name }} {{ emp.last_name }}</span>
                                        <span class="text-[9px] text-[hsl(var(--muted-foreground))] font-medium uppercase tracking-tighter">{{ emp.department?.name || emp.department }}</span>
                                    </div>
                                </td>
                                <td v-for="day in daysInMonth" :key="day" 
                                    @click="toggleAttendance(emp.id, day)"
                                    class="p-0 border-r border-[hsl(var(--border))] text-center cursor-pointer hover:bg-[hsl(var(--primary))]/10 transition-colors"
                                    :class="{'bg-[hsl(var(--muted))]/10': day % 7 === 0 || day % 7 === 6}">
                                    <span v-if="attendanceData[emp.id]?.[day] && !(day % 7 === 0 || day % 7 === 6)" 
                                        class="text-[10px] font-bold block w-full py-2.5"
                                        :class="{
                                            'text-emerald-600': attendanceData[emp.id][day] === 'P',
                                            'text-amber-600': attendanceData[emp.id][day] === 'L',
                                            'text-rose-600': attendanceData[emp.id][day] === 'A',
                                        }">
                                        {{ attendanceData[emp.id][day] }}
                                    </span>
                                </td>
                                <td class="p-3 border-l border-[hsl(var(--border))] sticky right-0 bg-white z-10 text-right font-mono font-bold text-[11px] text-[hsl(var(--primary))]">
                                    {{ getTotalHours(emp.id) }}
                                </td>
                            </tr>
                            <tr v-if="activeEmployees.length === 0">
                                <td :colspan="daysInMonth.length + 2" class="p-8 text-center text-[10px] text-[hsl(var(--muted-foreground))] uppercase tracking-widest font-bold">
                                    {{ t('common.noData') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Entry Modal -->
        <div v-if="isAddOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))]">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <UserCircle class="h-6 w-6 text-[hsl(var(--primary))]" />
                        {{ t('timesheet.addEntry') || 'Иловаи сабти давомот' }}
                    </h2>
                </div>
                <div class="p-6 grid gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('menu.departments') || 'Шуъба' }}</label>
                        <select v-model="form.department" class="h-10 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none text-[hsl(var(--muted-foreground))]">
                            <option value="">{{ t('common.all') || 'Ҳама' }}</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('menu.employees') }}</label>
                        <input v-model="form.employee_name" list="timesheet-employee-list" placeholder="Интихоби корманд ё ворид кардани нав" class="h-10 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none" autocomplete="off" />
                        <datalist id="timesheet-employee-list">
                            <option v-for="emp in modalActiveEmployees" :key="emp.id" :value="emp.name + ' ' + (emp.last_name || '')"></option>
                        </datalist>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('timesheet.dayOfMonth') || 'Рӯзи моҳ' }}</label>
                            <select v-model="form.day" class="h-10 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none">
                                <option v-for="d in daysInMonth" :key="d" :value="String(d)">{{ d }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('common.status') || 'Статус' }}</label>
                            <select v-model="form.status" class="h-10 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none">
                                <option value="P">P - Ҳозир</option>
                                <option value="L">L - Дермонда</option>
                                <option value="A">A - Ғоиб</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-6 pt-2 flex items-center gap-3">
                    <button type="button" @click="isAddOpen = false" class="w-1/2 h-11 hover:bg-[hsl(var(--muted))] text-[hsl(var(--muted-foreground))] rounded-xl text-xs font-bold uppercase tracking-widest transition-colors">{{ t('common.cancel') }}</button>
                    <button @click="handleAddEntry" class="w-1/2 h-11 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-xl text-xs font-bold uppercase shadow-sm">{{ t('common.save') }}</button>
                </div>
            </div>
        </div>
        <div v-if="isAddOpen" @click="isAddOpen = false" class="fixed inset-0 z-40"></div>
    </AppLayout>
</template>
