<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { ref } from 'vue';
import { Building2, Users, Briefcase, Plus, Trash2, PieChart } from 'lucide-vue-next';

const props = defineProps({
    departments: Array,
    stats: Object,
    topEmployees: { type: Object, default: () => ({}) }
});

const { t } = useI18n();

const form = useForm({
    name: ''
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (dept) => {
    isEditing.value = true;
    editingId.value = dept.id;
    form.name = dept.name;
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/departments/${editingId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/departments', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteDept = (id) => {
    if (confirm(t('departments.confirmDelete'))) {
        router.delete(`/departments/${id}`);
    }
};
</script>

<template>
    <Head title="Departments" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('menu.departments') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('departments.subtitle') }}</p>
                </div>
                
                <button @click="openAddModal" class="h-9 px-4 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] inline-flex items-center gap-2 rounded-lg font-bold text-xs uppercase tracking-widest shadow-lg shadow-[hsl(var(--primary))]/20 hover:bg-[hsl(var(--primary))]/90">
                    <Plus class="h-4 w-4" /> {{ t('departments.addDepartment') }}
                </button>
            </div>

            <!-- Stats Overview -->
            <div v-if="stats" class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <Building2 class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('departments.totalDepts') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.totalDepts }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('departments.totalEmployees') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.totalEmployees }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                        <Briefcase class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('departments.totalVacancies') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.totalVacancies }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm p-4 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <PieChart class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-extrabold text-[hsl(var(--muted-foreground))]">{{ t('departments.avgFillRate') }}</p>
                        <p class="text-2xl font-bold text-[hsl(var(--foreground))]">{{ stats.avgFillRate }}%</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="dept in departments" :key="dept.id" class="border border-[hsl(var(--border))] shadow-sm hover:border-[hsl(var(--primary))]/50 transition-all group overflow-hidden rounded-xl">
                    <div class="h-1.5 w-full bg-[hsl(var(--primary))]/20 group-hover:bg-[hsl(var(--primary))] transition-colors"></div>
                    <div class="p-6 pb-2 flex flex-row items-start justify-between">
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold uppercase tracking-tight">{{ dept.name }}</h3>
                            <div class="flex items-center gap-2 text-[hsl(var(--muted-foreground))]">
                                <Building2 class="h-3 w-3" />
                                <span class="text-[10px] font-bold uppercase tracking-widest">{{ t('departments.label') }}</span>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openEditModal(dept)" class="h-8 w-8 flex items-center justify-center rounded-lg hover:bg-[hsl(var(--muted))] text-[hsl(var(--muted-foreground))] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit2 h-4 w-4"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>
                            </button>
                            <button @click="deleteDept(dept.id)" class="h-8 w-8 flex items-center justify-center rounded-lg hover:bg-rose-50 text-[hsl(var(--destructive))] transition-colors">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <div class="px-6 pb-6 space-y-4 pt-2">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('departments.employees') }}</p>
                                <div class="flex items-center gap-2">
                                    <Users class="h-4 w-4 text-[hsl(var(--primary))]" />
                                    <span class="text-lg font-bold">{{ dept.employees }}</span>
                                </div>
                            </div>
                            <div class="space-y-1 text-right">
                                <p class="text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase">{{ t('departments.vacancies') }}</p>
                                <div class="flex items-center gap-2 justify-end">
                                    <span class="text-lg font-bold text-amber-600">{{ dept.vacancies }}</span>
                                    <Briefcase class="h-4 w-4 text-amber-500" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <div class="flex justify-between items-center text-[10px] font-bold uppercase">
                                <span class="text-[hsl(var(--muted-foreground))] tracking-tighter">{{ t('dashboard.fillRate') }}</span>
                                <span class="text-[hsl(var(--primary))]">{{ dept.fillRate }}%</span>
                            </div>
                            <div class="h-1.5 w-full bg-[hsl(var(--muted))] rounded-full overflow-hidden">
                                <div class="h-full bg-[hsl(var(--primary))] transition-all rounded-full" :style="{ width: dept.fillRate + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Department Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))]">
                    <h2 class="text-xl font-bold">{{ isEditing ? t('departments.editTitle') : t('departments.newTitle') }}</h2>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] uppercase font-bold tracking-widest mt-1">
                        {{ isEditing ? t('departments.editSubtitle') : t('departments.newSubtitle') }}
                    </p>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="p-6">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('departments.nameLabel') }}</label>
                        <input v-model="form.name" class="h-9 w-full text-xs mt-1 rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" :placeholder="t('departments.namePlaceholder')" />
                    </div>
                    <div class="px-6 pb-6 flex items-center gap-3">
                        <button type="button" @click="isModalOpen = false" class="w-1/2 h-10 hover:bg-[hsl(var(--muted))] text-[hsl(var(--muted-foreground))] rounded-lg text-xs font-bold uppercase tracking-widest transition-colors">{{ t('common.cancel') }}</button>
                        <button type="submit" class="w-1/2 h-10 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg text-xs font-bold uppercase tracking-widest transition-colors">{{ t('common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
