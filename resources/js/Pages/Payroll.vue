<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { 
  Wallet, Clock, CheckCircle2, Download, Filter, 
  Search, Plus, MoreVertical, Edit2, Trash2, FileText,
  ArrowUpDown, ChevronUp, ChevronDown
} from 'lucide-vue-next';

// Use same mocked data approach since it's just visual for now, or pass from backend.
// In this case, we'll accept employees as a prop.
const props = defineProps({
  employees: { type: Array, default: () => [] },
  payroll_records: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({ search: '' }) }
});
const page = usePage();
const { t, language } = useI18n();



const payrollData = computed(() => {
  return props.employees.filter(e => e.status !== 'Retired').map(e => {
    const record = props.payroll_records.find(r => r.employee_id === e.id);
    return {
      ...e,
      salary: record ? record.salary : (e.salary || 0),
      hasRecord: !!record,
      adjustment: 0
    };
  });
});

import { router } from '@inertiajs/vue3';

const searchTerm = ref(props.filters?.search || '');

watch(searchTerm, (value) => {
    router.get('/payroll', { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});
const isAddOpen = ref(false);

const form = useForm({
  employee_name: '',
  role: '',
  department: '',
  salary: '',
  month_year: new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0')
});

watch(() => form.employee_name, (newName) => {
    if (newName) {
        const emp = payrollData.value.find(e => {
            const fName = [e.name, e.last_name].filter(Boolean).join(' ');
            return fName === newName;
        });
        if (emp) {
            form.role = emp.role || '';
            form.department = emp.department || '';
            form.salary = emp.salary || '';
        }
    }
});

const uniqueRoles = computed(() => {
    return [...new Set(props.employees.map(e => e.role).filter(Boolean))];
});
const uniqueDepartments = computed(() => {
    return [...new Set(props.employees.map(e => e.department).filter(Boolean))];
});

const filteredPayroll = computed(() => {
  return payrollData.value.filter(p => {
    const term = searchTerm.value.toLowerCase();
    const fullName = `${p.name} ${p.last_name}`.toLowerCase();
    return fullName.includes(term) || (p.department && p.department.toLowerCase().includes(term));
  });
});

const tableData = computed(() => {
    return sortedPayroll.value.filter(e => e.hasRecord);
});

const totalPayroll = computed(() => 
  tableData.value.reduce((acc, e) => acc + (parseFloat(e.salary) || 0), 0)
);

const sortKey = ref(null);
const sortDir = ref(null);

const handleSort = (key) => {
    if (sortKey.value === key && sortDir.value === 'asc') {
        sortDir.value = 'desc';
    } else if (sortKey.value === key && sortDir.value === 'desc') {
        sortKey.value = null;
        sortDir.value = null;
    } else {
        sortKey.value = key;
        sortDir.value = 'asc';
    }
};

const sortedPayroll = computed(() => {
    let items = [...filteredPayroll.value];
    if (sortKey.value && sortDir.value) {
        items.sort((a, b) => {
            let aVal, bVal;
            if (sortKey.value === 'name') {
                aVal = `${a.name} ${a.last_name}`.toLowerCase();
                bVal = `${b.name} ${b.last_name}`.toLowerCase();
            } else {
                aVal = a[sortKey.value] ?? '';
                bVal = b[sortKey.value] ?? '';
            }
            if (typeof aVal === 'number' && typeof bVal === 'number') {
                return sortDir.value === 'asc' ? aVal - bVal : bVal - aVal;
            }
            const strA = String(aVal).toLowerCase();
            const strB = String(bVal).toLowerCase();
            if (strA < strB) return sortDir.value === 'asc' ? -1 : 1;
            if (strA > strB) return sortDir.value === 'asc' ? 1 : -1;
            return 0;
        });
    }
    return items;
});

const handleAddPayroll = () => {
    if (!form.employee_name || !form.salary) return;

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

const fileInput = ref(null);

const triggerImport = () => {
    fileInput.value.click();
};

const handleImport = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!confirm(t('common.confirm'))) {
        event.target.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    
    router.post('/payroll/import', formData, {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        onSuccess: () => {
            alert(t('common.importSuccess'));
            event.target.value = '';
        },
        onError: () => {
            alert(t('common.error'));
            event.target.value = '';
        }
    });
};

const activeDropdown = ref(null);
const toggleDropdown = (id, event) => {
    event.stopPropagation();
    activeDropdown.value = activeDropdown.value === id ? null : id;
};

const closeDropdown = (e) => {
    if (!e.target.closest('.dropdown-container')) {
        activeDropdown.value = null;
    }
};

onMounted(() => document.addEventListener('click', closeDropdown));
onUnmounted(() => document.removeEventListener('click', closeDropdown));

const handleEdit = (emp) => {
    activeDropdown.value = null;
    form.employee_name = [emp.name, emp.last_name].filter(Boolean).join(' ');
    form.role = emp.role;
    form.department = emp.department;
    form.salary = emp.salary;
    isAddOpen.value = true;
};

const handleDelete = (emp) => {
    activeDropdown.value = null;
    if(confirm(t('common.confirm'))) {
        router.delete(`/payroll/${emp.id}`, {
            preserveScroll: true
        });
    }
};

const canAdd = computed(() => page.props.auth.permissions.includes('add_payroll') || page.props.auth.permissions.includes('all'));
const canEdit = computed(() => page.props.auth.permissions.includes('edit_payroll') || page.props.auth.permissions.includes('all'));
const canDelete = computed(() => page.props.auth.permissions.includes('delete_payroll') || page.props.auth.permissions.includes('all'));
const canExport = computed(() => page.props.auth.permissions.includes('export_payroll') || page.props.auth.permissions.includes('all'));
const canImport = computed(() => page.props.auth.permissions.includes('add_payroll') || page.props.auth.permissions.includes('all'));
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
                    <input type="file" ref="fileInput" accept=".csv" class="hidden" @change="handleImport" />
                    <button v-if="canImport" @click="triggerImport" class="h-9 px-3 border border-[hsl(var(--border))] rounded-lg inline-flex items-center justify-center gap-2 uppercase font-bold text-[10px] tracking-widest hover:bg-[hsl(var(--muted))]">
                        <Download class="h-4 w-4 rotate-180" /> {{ t('common.import') }}
                    </button>
                    <button v-if="canExport" @click="handleExport" class="h-9 px-3 border border-[hsl(var(--border))] rounded-lg inline-flex items-center justify-center gap-2 uppercase font-bold text-[10px] tracking-widest hover:bg-[hsl(var(--muted))]">
                        <Download class="h-4 w-4" /> {{ t('common.export') }}
                    </button>
                    
                    <button v-if="canAdd" @click="isAddOpen = true" class="h-9 px-3 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg inline-flex items-center justify-center gap-2 uppercase font-bold text-[10px] tracking-widest shadow-sm">
                        <Plus class="h-4 w-4" /> {{ t('payroll.paid') }}
                    </button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="border border-[hsl(var(--border))] rounded-xl shadow-sm bg-white overflow-hidden group">
                    <div class="h-1 w-full bg-[hsl(var(--primary))]" />
                    <div class="p-6 pb-2 flex flex-row items-center justify-between">
                        <h3 class="text-[10px] font-bold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">{{ t('payroll.totalFund') }}</h3>
                        <Wallet class="h-4 w-4 text-[hsl(var(--primary))] group-hover:scale-110 transition-transform" />
                    </div>
                    <div class="p-6 pt-0">
                        <div class="text-2xl font-bold">{{ totalPayroll.toLocaleString() }} {{ t('common.currency') }}</div>
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
                        <p class="text-[10px] text-[hsl(var(--muted-foreground))] font-bold mt-1 uppercase tracking-tighter">{{ t('payroll.verifiedToday') }}</p>
                    </div>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-white rounded-xl">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-sm w-full">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input v-model="searchTerm" :placeholder="t('common.search')" class="pl-9 h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--muted))]/20 focus:outline-none" />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/30 uppercase tracking-widest text-[9px] font-bold border-b border-[hsl(var(--border))] text-[hsl(var(--muted-foreground))]">
                                <th class="w-12 px-6 py-4">№</th>
                                <th @click="handleSort('name')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'name' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('menu.employees') }}
                                        <ChevronUp v-if="sortKey === 'name' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'name' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                    </div>
                                </th>
                                <th @click="handleSort('department')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'department' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('common.department') }}
                                        <ChevronUp v-if="sortKey === 'department' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'department' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                    </div>
                                </th>
                                <th @click="handleSort('salary')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'salary' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('payroll.salary') }} (TJS)
                                        <ChevronUp v-if="sortKey === 'salary' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'salary' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                    </div>
                                </th>
                                <th class="px-6 py-4">{{ t('common.status') }}</th>
                                <th class="text-right px-6 py-4">{{ t('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="tableData.length > 0">
                                <tr v-for="(emp, i) in tableData" :key="emp.id" class="text-[11px] group transition-colors hover:bg-[hsl(var(--primary))]/[0.03] border-b border-[hsl(var(--border))] last:border-0">
                                    <td class="font-bold text-[hsl(var(--primary))] px-6 py-4">{{ i + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-[hsl(var(--foreground))]/80">{{ emp.name }} {{ emp.last_name }}</span>
                                            <span class="text-[9px] text-[hsl(var(--muted-foreground))] uppercase">{{ emp.role }}</span>
                                        </div>
                                    </td>
                                    <td class="text-[hsl(var(--muted-foreground))] px-6 py-4 font-medium uppercase">{{ emp.department }}</td>
                                    <td class="px-6 py-4 font-mono font-bold">{{ (emp.salary || 0).toLocaleString() }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block text-[8px] font-bold bg-emerald-50 text-emerald-700 rounded-md px-2 py-0.5 uppercase tracking-tighter">
                                            {{ t('payroll.paid') }}
                                        </span>
                                    </td>
                                    <td class="text-right px-6 py-4 relative">
                                        <div class="inline-flex justify-end relative dropdown-container">
                                            <button @click="(e) => toggleDropdown(emp.id, e)" class="h-7 w-7 inline-flex items-center justify-center rounded hover:bg-[hsl(var(--muted))] object-center transition-colors">
                                                <MoreVertical class="h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                                            </button>
                                            <div v-if="activeDropdown === emp.id" class="absolute right-0 top-8 w-36 bg-white border border-[hsl(var(--border))] rounded-xl shadow-xl z-50 py-1 overflow-hidden">
                                                <button v-if="canEdit" @click="handleEdit(emp)" class="w-full text-left px-4 py-2.5 hover:bg-[hsl(var(--muted))]/50 text-[11px] font-bold text-[hsl(var(--foreground))]/80 flex items-center gap-2 transition-colors uppercase tracking-widest">
                                                    <Edit2 class="h-3.5 w-3.5 text-[hsl(var(--primary))]" /> {{ t('common.edit') }}
                                                </button>
                                                <button v-if="canDelete" @click="handleDelete(emp)" class="w-full text-left px-4 py-2.5 hover:bg-red-50 text-[11px] font-bold text-red-600 flex items-center gap-2 transition-colors uppercase tracking-widest">
                                                    <Trash2 class="h-3.5 w-3.5" /> {{ t('common.delete') }}
                                                </button>
                                            </div>
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
                        {{ t('payroll.modalTitle') }}
                    </h2>
                    <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1">{{ t('payroll.modalSubtitle') }}</p>
                </div>
                <div class="p-6 grid gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('menu.employees') }}</label>
                        <input v-model="form.employee_name" list="payroll-emp-list" :placeholder="t('payroll.employeeNamePlaceholder')" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" autocomplete="off" />
                        <datalist id="payroll-emp-list">
                            <option v-for="emp in payrollData" :key="emp.id" :value="[emp.name, emp.last_name].filter(Boolean).join(' ')"></option>
                        </datalist>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('common.role') }}</label>
                        <input v-model="form.role" list="payroll-role-list" :placeholder="t('payroll.selectPositionPlaceholder')" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" autocomplete="off" />
                        <datalist id="payroll-role-list">
                            <option v-for="role in uniqueRoles" :key="role" :value="role"></option>
                        </datalist>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('common.department') }}</label>
                        <input v-model="form.department" list="payroll-dept-list" :placeholder="t('payroll.selectDeptPlaceholder')" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" autocomplete="off" />
                        <datalist id="payroll-dept-list">
                            <option v-for="dept in uniqueDepartments" :key="dept" :value="dept"></option>
                        </datalist>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('payroll.salary') }}</label>
                        <input v-model="form.salary" type="number" :placeholder="t('payroll.salaryPlaceholder')" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                </div>
                <div class="px-6 pb-6 pt-2 flex items-center gap-3">
                    <button type="button" @click="isAddOpen = false" class="w-1/2 h-10 hover:bg-[hsl(var(--muted))] text-[hsl(var(--muted-foreground))] rounded-lg text-xs font-bold uppercase tracking-widest transition-colors">{{ t('common.cancel') }}</button>
                    <button @click="handleAddPayroll" class="w-1/2 h-10 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg text-xs font-bold uppercase">{{ t('common.save') }}</button>
                </div>
            </div>
        </div>
        <div v-if="isAddOpen" @click="isAddOpen = false" class="fixed inset-0 z-40"></div>
    </AppLayout>
</template>
