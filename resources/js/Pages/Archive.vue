<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { History, Search, Calendar, MoreVertical, Plus, ArrowUpDown, ChevronUp, ChevronDown, Trash2, Edit2 } from 'lucide-vue-next';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();
const page = usePage();

const props = defineProps({
    employees: { type: Array, default: () => [] },
    filters: Object,
    departments: { type: Array, default: () => [] },
    positions: { type: Array, default: () => [] }
});

const searchTerm = ref(props.filters?.search || '');

watch(searchTerm, (value) => {
    router.get('/archive', { search: value }, { preserveState: true, replace: true });
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    last_name: '',
    email: '',
    role: '',
    department: 'Engineering'
});

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/archive/${editingId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
                isEditing.value = false;
                editingId.value = null;
            }
        });
    } else {
        form.post('/archive', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
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
    isEditing.value = true;
    editingId.value = emp.id;
    form.name = emp.name;
    form.last_name = emp.last_name;
    form.email = emp.email;
    form.role = emp.role;
    form.department = emp.department;
    isModalOpen.value = true;
    activeDropdown.value = null;
};

const handleDelete = (emp) => {
    if(confirm(t('archive.confirmDelete'))) {
        router.delete(`/archive/${emp.id}`, { preserveScroll: true });
        activeDropdown.value = null;
    }
};

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

const sortedEmployees = computed(() => {
    let items = [...props.employees];
    if (sortKey.value && sortDir.value) {
        items.sort((a, b) => {
            let aVal, bVal;
            if (sortKey.value === 'fullname') {
                aVal = `${a.name} ${a.last_name}`.toLowerCase();
                bVal = `${b.name} ${b.last_name}`.toLowerCase();
            } else {
                aVal = a[sortKey.value] ?? '';
                bVal = b[sortKey.value] ?? '';
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
const canAdd = computed(() => page.props.auth.permissions.includes('add_archive') || page.props.auth.permissions.includes('all'));
const canEdit = computed(() => page.props.auth.permissions.includes('edit_archive') || page.props.auth.permissions.includes('all'));
const canDelete = computed(() => page.props.auth.permissions.includes('delete_archive') || page.props.auth.permissions.includes('all'));
</script>

<template>
    <Head title="Archive" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('archive.title') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('archive.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded-lg flex items-center gap-2 text-[10px] font-bold uppercase tracking-tight">
                        <History class="h-3 w-3" /> {{ t('archive.readOnly') }}
                    </div>
                    <button v-if="canAdd" @click="isModalOpen = true" class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] hover:bg-[hsl(var(--primary))]/90 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                        <Plus class="h-4 w-4" /> {{ t('archive.addToArchive') }}
                    </button>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-[hsl(var(--card))] rounded-xl">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/10">
                    <div class="relative max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input v-model="searchTerm" :placeholder="t('archive.search')" class="pl-9 h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent focus:outline-none" />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/30 uppercase tracking-widest text-[9px] font-bold text-[hsl(var(--muted-foreground))] border-b border-[hsl(var(--border))]">
                                <th class="w-12 px-6 py-4">№</th>
                                <th @click="handleSort('fullname')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'fullname' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('archive.table.fullname') }}
                                        <ChevronUp v-if="sortKey === 'fullname' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'fullname' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                    </div>
                                </th>
                                <th @click="handleSort('role')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'role' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('archive.table.lastRole') }}
                                        <ChevronUp v-if="sortKey === 'role' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'role' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                    </div>
                                </th>
                                <th @click="handleSort('department')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'department' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('archive.table.department') }}
                                        <ChevronUp v-if="sortKey === 'department' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'department' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                    </div>
                                </th>
                                <th class="px-6 py-4">{{ t('archive.table.workPeriod') }}</th>
                                <th class="px-6 py-4">{{ t('archive.table.leaveReason') }}</th>
                                <th class="text-right px-6 py-4">{{ t('archive.table.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="pb-24 block min-w-full table-row-group">
                            <template v-if="sortedEmployees.length > 0">
                                <tr v-for="(emp, i) in sortedEmployees" :key="emp.id" class="text-[11px] group transition-colors hover:bg-rose-50/30 border-b border-[hsl(var(--border))] last:border-0">
                                    <td class="font-bold text-[hsl(var(--muted-foreground))] px-6 py-4">{{ i + 1 }}</td>
                                    <td class="font-bold px-6 py-4">{{ emp.name }} {{ emp.last_name }}</td>
                                    <td class="px-6 py-4">{{ emp.role }}</td>
                                    <td class="text-[hsl(var(--muted-foreground))] uppercase text-[10px] font-bold px-6 py-4">{{ emp.department }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-3 w-3 text-[hsl(var(--muted-foreground))]" />
                                            2023 - {{ new Date(emp.deleted_at).getFullYear() }}
                                        </div>
                                    </td>
                                    <td class="italic text-[hsl(var(--muted-foreground))] px-6 py-4">{{ t('archive.laidOff') }}</td>
                                    <td class="text-right px-6 py-4 relative">
                                        <div class="inline-flex justify-end relative dropdown-container">
                                            <button @click="(e) => toggleDropdown(emp.id, e)" class="h-8 w-8 inline-flex items-center justify-center rounded hover:bg-[hsl(var(--muted))]">
                                                <MoreVertical class="h-4 w-4" />
                                            </button>
                                            <div v-if="activeDropdown === emp.id" class="absolute right-0 top-8 w-44 bg-white border border-[hsl(var(--border))] rounded-xl shadow-xl z-50 py-1 overflow-hidden">
                                                <button v-if="canEdit" @click="handleEdit(emp)" class="w-full text-left px-4 py-2 hover:bg-[hsl(var(--muted))]/50 text-xs font-bold text-[hsl(var(--foreground))]/80 flex items-center gap-2 transition-colors uppercase tracking-widest">
                                                    <Edit2 class="h-3.5 w-3.5 text-[hsl(var(--primary))]" /> {{ t('archive.actions.edit') }}
                                                </button>
                                                <button v-if="canDelete" @click="handleDelete(emp)" class="w-full text-left px-4 py-2 hover:bg-red-50 text-xs font-bold text-red-600 flex items-center gap-2 transition-colors uppercase tracking-widest">
                                                    <Trash2 class="h-3.5 w-3.5" /> {{ t('archive.actions.fullDelete') }}
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="7" class="h-32 text-center text-[hsl(var(--muted-foreground))] text-xs border-b border-[hsl(var(--border))]">
                                    {{ t('archive.empty') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add to Archive Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))] flex justify-between items-center bg-orange-50/30">
                    <div>
                        <h2 class="text-xl font-bold">{{ t('archive.modal.title') }}</h2>
                        <p class="text-[10px] text-[hsl(var(--muted-foreground))] uppercase font-bold tracking-widest mt-1">
                            {{ t('archive.modal.subtitle') }}
                        </p>
                    </div>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.name') }}</label>
                            <input v-model="form.name" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.lastName') }}</label>
                            <input v-model="form.last_name" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.email') }}</label>
                        <input type="email" v-model="form.email" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.role') }}</label>
                        <input v-model="form.role" list="archive-roles-list" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" autocomplete="off" />
                        <datalist id="archive-roles-list">
                            <option v-for="pos in positions" :key="pos.id" :value="pos.title"></option>
                        </datalist>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.department') }}</label>
                        <input v-model="form.department" list="archive-departments-list" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" autocomplete="off" />
                        <datalist id="archive-departments-list">
                            <option v-for="dept in departments" :key="dept.id" :value="dept.name"></option>
                        </datalist>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[hsl(var(--border))] mt-6">
                        <button type="button" @click="() => { isModalOpen = false; isEditing = false; editingId = null; form.reset(); }" class="px-4 py-2 text-xs font-bold text-[hsl(var(--muted-foreground))] tracking-widest uppercase rounded-xl hover:bg-[hsl(var(--muted))] transition-colors">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg text-sm font-bold bg-[hsl(var(--primary))] text-white hover:bg-[hsl(var(--primary))]/90 disabled:opacity-50">
                            {{ isEditing ? t('archive.actions.editSubmit') : t('archive.modal.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
