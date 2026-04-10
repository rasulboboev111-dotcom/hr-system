<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { ref, watch } from 'vue';
import { 
    Users, Plus, Search, Filter, Download, UploadCloud,
    MoreHorizontal, Edit2, Trash2, Mail
} from 'lucide-vue-next';

const fileInputRef = ref(null);

const handleExport = () => {
    window.location.href = '/employees/export';
};

const handleImport = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    router.post('/employees/import', formData, {
        preserveScroll: true,
        onSuccess: () => {
            alert('Импорт успешно завершен');
            fileInputRef.value.value = '';
        }
    });
};

const props = defineProps({
    employees: Object,
    filters: Object
});

const { t } = useI18n();

const searchQuery = ref(props.filters?.search || '');
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

watch(searchQuery, (value) => {
    router.get('/employees', { search: value }, { preserveState: true, replace: true });
});

const form = useForm({
    name: '',
    last_name: '',
    email: '',
    role: '',
    department: 'Engineering'
});

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (emp) => {
    isEditing.value = true;
    editingId.value = emp.id;
    form.name = emp.name;
    form.last_name = emp.last_name;
    form.email = emp.email;
    form.role = emp.role;
    form.department = emp.department;
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/employees/${editingId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/employees', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteEmployee = (id) => {
    if (confirm('Шумо мутмаин ҳастед, ки ин кормандро нест кардан мехоҳед?')) {
        router.delete(`/employees/${id}`);
    }
};

</script>

<template>
    <Head title="Employees" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight flex items-center gap-3 text-[hsl(var(--foreground))]">
                        <div class="h-10 w-10 rounded-xl bg-[hsl(var(--primary))]/10 flex items-center justify-center">
                            <Users class="h-6 w-6 text-[hsl(var(--primary))]" />
                        </div>
                        {{ t('menu.employees') }}
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <input type="file" ref="fileInputRef" accept=".csv" class="hidden" @change="handleImport" />
                    <button @click="fileInputRef.click()" class="h-10 px-4 inline-flex items-center justify-center rounded-xl font-bold text-xs uppercase tracking-widest border border-[hsl(var(--border))] hover:bg-[hsl(var(--muted))] text-[hsl(var(--foreground))] gap-2">
                        <UploadCloud class="h-4 w-4" /> Импорти CSV
                    </button>
                    <button @click="handleExport" class="h-10 px-4 inline-flex items-center justify-center rounded-xl font-bold text-xs uppercase tracking-widest border border-[hsl(var(--border))] hover:bg-[hsl(var(--muted))] text-[hsl(var(--foreground))] gap-2">
                        <Download class="h-4 w-4" /> {{ t('common.export') }}
                    </button>
                    <button @click="openAddModal" class="h-10 px-4 inline-flex items-center justify-center rounded-xl font-bold text-xs uppercase tracking-widest bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow-lg shadow-[hsl(var(--primary))]/20 gap-2">
                        <Plus class="h-4 w-4" /> {{ t('common.add') }}
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex items-center justify-between">
                    <div class="relative max-w-sm flex-1">
                        <Search class="absolute left-3 top-3 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input v-model="searchQuery" :placeholder="t('common.search')" class="pl-10 h-10 w-full text-sm rounded-xl border-none bg-[hsl(var(--muted))]/30 focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <button class="h-10 px-4 inline-flex items-center justify-center gap-2 text-[10px] font-bold uppercase hover:bg-[hsl(var(--muted))] rounded-xl">
                        <Filter class="h-4 w-4" /> {{ t('common.filter') }}
                    </button>
                </div>
                
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[hsl(var(--muted))]/30 text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))] tracking-widest">
                            <tr>
                                <th class="px-6 py-4">{{ t('common.name') }}</th>
                                <th class="px-6 py-4">{{ t('common.role') }}</th>
                                <th class="px-6 py-4">{{ t('common.department') }}</th>
                                <th class="px-6 py-4">{{ t('common.status') }}</th>
                                <th class="px-6 py-4 text-right">{{ t('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="emp in employees.data" :key="emp.id" class="border-b border-[hsl(var(--border))] last:border-0 hover:bg-[hsl(var(--primary))]/[0.02] transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-[hsl(var(--primary))]/10 flex items-center justify-center text-[hsl(var(--primary))] font-bold shadow-sm">
                                            {{ emp.name.charAt(0) }}{{ emp.last_name ? emp.last_name.charAt(0) : '' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[hsl(var(--foreground))]">{{ emp.name }} {{ emp.last_name }}</p>
                                            <p class="text-[10px] text-[hsl(var(--muted-foreground))] font-mono flex items-center gap-1 mt-0.5">
                                                <Mail class="h-3 w-3" /> {{ emp.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium">{{ emp.role }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest bg-[hsl(var(--muted))] text-[hsl(var(--muted-foreground))]">
                                        {{ emp.department }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="emp.status === 'active'" class="text-[10px] font-extrabold uppercase text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                                        {{ t('common.active') }}
                                    </span>
                                    <span v-else class="text-[10px] font-extrabold uppercase text-amber-600 bg-amber-50 px-2 py-1 rounded">
                                        {{ t('common.onHold') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openEditModal(emp)" class="p-2 hover:bg-[hsl(var(--muted))] rounded-lg">
                        <Edit2 class="h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                    </button>
                                    <button @click="deleteEmployee(emp.id)" class="p-2 hover:bg-rose-50 text-[hsl(var(--destructive))] rounded-lg ml-1">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div v-if="employees.links && employees.links.length > 3" class="p-4 border-t border-[hsl(var(--border))] flex items-center justify-center gap-1">
                    <template v-for="(link, key) in employees.links" :key="key">
                        <button 
                            @click="link.url && router.get(link.url, { search: searchQuery }, { preserveState: true })"
                            :class="[
                                'px-3 py-1 text-xs font-bold rounded-md transition-colors',
                                link.active ? 'bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))]' : 'text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--muted))]',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        ></button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Add/Edit Employee Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))]">
                    <h2 class="text-xl font-bold">{{ isEditing ? 'Таҳрири Корманд' : 'Иловаи Корманд' }}</h2>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] uppercase font-bold tracking-widest mt-1">
                        {{ isEditing ? 'Маълумоти кормандро иваз кунед' : 'Сабти навро ворид кунед' }}
                    </p>
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
                        <input v-model="form.role" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('common.department') }}</label>
                        <select v-model="form.department" class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]">
                            <option>Engineering</option>
                            <option>Human Resources</option>
                            <option>Design</option>
                            <option>Management</option>
                        </select>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[hsl(var(--border))] mt-6">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-lg text-sm font-bold text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--muted))]">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg text-sm font-bold bg-[hsl(var(--primary))] text-white hover:bg-[hsl(var(--primary))]/90 disabled:opacity-50">
                            {{ t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
