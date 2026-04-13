<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { computed, ref, watch } from 'vue';
import { 
    Briefcase, Plus, Search, Filter, Download, 
    MoreHorizontal, Edit2, Trash2, Users2, ShieldCheck, HelpCircle,
    ArrowUpDown, ChevronUp, ChevronDown
} from 'lucide-vue-next';

const props = defineProps({
    positions: Array,
    filters: Object,
    stats: Object,
    departments: {
        type: Array,
        default: () => []
    }
});
const page = usePage();
const { t } = useI18n();


const searchQuery = ref(props.filters?.search || '');

watch(searchQuery, (value) => {
    router.get('/positions', { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

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

const sortedPositions = computed(() => {
    let items = [...props.positions];
    if (sortKey.value && sortDir.value) {
        items.sort((a, b) => {
            let aVal = a[sortKey.value] ?? '';
            let bVal = b[sortKey.value] ?? '';
            const strA = String(aVal).toLowerCase();
            const strB = String(bVal).toLowerCase();
            if (strA < strB) return sortDir.value === 'asc' ? -1 : 1;
            if (strA > strB) return sortDir.value === 'asc' ? 1 : -1;
            return 0;
        });
    }
    return items;
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    title: '',
    department: 'Engineering',
    status: 'vacant',
    salary: '',
    skills: ''
});

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (pos) => {
    isEditing.value = true;
    editingId.value = pos.id;
    form.title = pos.title;
    form.department = pos.department;
    form.status = pos.status || 'vacant';
    form.salary = pos.salary || '';
    form.skills = (pos.requiredSkills || []).join(', ');
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/positions/${editingId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/positions', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const handleExport = () => {
    window.location.href = '/positions/export';
};

const deletePosition = (id) => {
    if (confirm(t('common.confirmDelete'))) {
        router.delete(`/positions/${id}`);
    }
};

const canAdd = computed(() => page.props.auth.permissions.includes('add_positions') || page.props.auth.permissions.includes('all'));
const canEdit = computed(() => page.props.auth.permissions.includes('edit_positions') || page.props.auth.permissions.includes('all'));
const canDelete = computed(() => page.props.auth.permissions.includes('delete_positions') || page.props.auth.permissions.includes('all'));
const canExport = computed(() => page.props.auth.permissions.includes('export_positions') || page.props.auth.permissions.includes('all'));

</script>

<template>
    <Head title="Positions" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight flex items-center gap-3 text-[hsl(var(--foreground))]">
                        <div class="h-10 w-10 rounded-xl bg-[hsl(var(--primary))]/10 flex items-center justify-center">
                            <Briefcase class="h-6 w-6 text-[hsl(var(--primary))]" />
                        </div>
                        {{ t('positions.title') }}
                    </h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">
                        {{ t('positions.subtitle') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="canExport" @click="handleExport" class="h-10 px-4 inline-flex items-center justify-center rounded-xl font-bold text-xs uppercase tracking-widest border border-[hsl(var(--border))] hover:bg-[hsl(var(--muted))] text-[hsl(var(--foreground))] gap-2">
                        <Download class="h-4 w-4" /> {{ t('common.export') }}
                    </button>
                    <button v-if="canAdd" @click="openAddModal" class="h-10 px-4 inline-flex items-center justify-center rounded-xl bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] font-bold text-[10px] uppercase tracking-widest hover:bg-[hsl(var(--primary))]/90 shadow-lg shadow-[hsl(var(--primary))]/20 gap-2">
                        <Plus class="h-4 w-4" /> {{ t('positions.addPosition') }}
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <Briefcase class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('positions.stats.total') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.total }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <Users2 class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('positions.stats.filled') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.filled }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                        <ShieldCheck class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('positions.stats.vacant') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.vacant }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-600">
                        <HelpCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('positions.stats.onHold') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.on_hold }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex items-center justify-between">
                    <div class="relative max-w-sm flex-1">
                        <Search class="absolute left-3 top-3 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input v-model="searchQuery" :placeholder="t('common.search')" class="pl-10 h-10 w-full text-sm rounded-xl border-none bg-[hsl(var(--muted))]/30 focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[hsl(var(--muted))]/30 text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))] tracking-widest">
                            <tr>
                                <th class="px-6 py-4">{{ t('positions.table.id') }}</th>
                                <th @click="handleSort('title')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'title' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('positions.table.title') }}
                                        <ChevronUp v-if="sortKey === 'title' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'title' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
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
                                <th class="px-6 py-4">{{ t('positions.table.skills') }}</th>
                                <th @click="handleSort('status')" class="px-6 py-4 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'status' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                    <div class="flex items-center">{{ t('common.status') }}
                                        <ChevronUp v-if="sortKey === 'status' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ChevronDown v-else-if="sortKey === 'status' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                        <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-right">{{ t('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pos in sortedPositions" :key="pos.id" class="border-b border-[hsl(var(--border))] last:border-0 hover:bg-[hsl(var(--primary))]/[0.02]">
                                <td class="px-6 py-4 text-[hsl(var(--muted-foreground))] font-mono">POS-{{ String(pos.id).padStart(4, '0') }}</td>
                                <td class="px-6 py-4 font-bold">{{ pos.title }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest bg-[hsl(var(--muted))]">{{ pos.department }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="pos.skills" class="px-2 py-0.5 rounded text-[9px] bg-blue-50 text-blue-600 font-bold uppercase">{{ pos.skills }}</span>
                                    <span v-else class="text-[9px] text-[hsl(var(--muted-foreground))]">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="pos.status === 'vacant'" class="text-[10px] font-extrabold uppercase text-orange-600 bg-orange-50 px-2 py-1 rounded">{{ t('common.vacant') }}</span>
                                    <span v-else-if="pos.status === 'filled'" class="text-[10px] font-extrabold uppercase text-emerald-600 bg-emerald-50 px-2 py-1 rounded">{{ t('common.filled') }}</span>
                                    <span v-else class="text-[10px] font-extrabold uppercase text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ t('common.onHold') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button v-if="canEdit" @click="openEditModal(pos)" class="h-8 w-8 inline-flex items-center justify-center rounded hover:bg-[hsl(var(--muted))] transition-colors">
                                        <Edit2 class="h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                                    </button>
                                    <button v-if="canDelete" @click="deletePosition(pos.id)" class="p-2 hover:bg-rose-50 text-[hsl(var(--destructive))] rounded-lg ml-1">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-[hsl(var(--background))]/80 backdrop-blur-sm p-4">
            <div class="w-full max-w-md bg-[hsl(var(--card))] rounded-2xl shadow-xl overflow-hidden border border-[hsl(var(--border))]">
                <div class="p-6 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/10">
                    <h2 class="text-lg font-bold">{{ isEditing ? t('positions.modal.editTitle') : t('positions.modal.addTitle') }}</h2>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase font-bold tracking-widest">
                        {{ isEditing ? t('positions.modal.editSubtitle') : t('positions.modal.addSubtitle') }}
                    </p>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('positions.table.title') }}</label>
                        <input v-model="form.title" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.department') }}</label>
                        <input v-model="form.department" list="departments-list" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" :placeholder="t('positions.modal.deptPlaceholder')" autocomplete="off" />
                        <datalist id="departments-list">
                            <option v-for="dept in departments" :key="dept.id" :value="dept.name"></option>
                        </datalist>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.status') }}</label>
                        <select v-model="form.status" class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]">
                            <option value="vacant">{{ t('common.vacant') }}</option>
                            <option value="filled">{{ t('common.filled') }}</option>
                            <option value="on_hold">{{ t('common.onHold') }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('positions.modal.skillsLabel') }}</label>
                        <input v-model="form.skills" :placeholder="t('positions.modal.skillsPlaceholder')" class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[hsl(var(--border))] mt-6">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs font-bold text-[hsl(var(--muted-foreground))] tracking-widest uppercase rounded-xl hover:bg-[hsl(var(--muted))] transition-colors">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg text-sm font-bold bg-[hsl(var(--primary))] text-white hover:bg-[hsl(var(--primary))]/90 disabled:opacity-50">
                            {{ t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
