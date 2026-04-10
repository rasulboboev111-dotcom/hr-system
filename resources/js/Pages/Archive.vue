<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { History, Search, Calendar, MoreVertical, Plus } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps({
    employees: { type: Array, default: () => [] },
    filters: Object
});

const searchTerm = ref(props.filters?.search || '');

watch(searchTerm, (value) => {
    router.get('/archive', { search: value }, { preserveState: true, replace: true });
});

const isModalOpen = ref(false);
const form = useForm({
    name: '',
    last_name: '',
    email: '',
    role: '',
    department: 'Engineering'
});

const submitForm = () => {
    form.post('/archive', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Archive" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Байгонӣ</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">Рӯйхати кормандони собиқ</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded-lg flex items-center gap-2 text-[10px] font-bold uppercase tracking-tight">
                        <History class="h-3 w-3" /> Танҳо барои хондан
                    </div>
                    <button @click="isModalOpen = true" class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] hover:bg-[hsl(var(--primary))]/90 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                        <Plus class="h-4 w-4" /> Илова ба байгонӣ
                    </button>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-[hsl(var(--card))] rounded-xl">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/10">
                    <div class="relative max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input v-model="searchTerm" placeholder="Ҷустуҷӯ дар байгонӣ..." class="pl-9 h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent focus:outline-none" />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/30 uppercase tracking-widest text-[9px] font-bold text-[hsl(var(--muted-foreground))] border-b border-[hsl(var(--border))]">
                                <th class="w-12 px-6 py-4">№</th>
                                <th class="px-6 py-4">Ном ва Насаб</th>
                                <th class="px-6 py-4">Мансаби охирин</th>
                                <th class="px-6 py-4">Шуъба</th>
                                <th class="px-6 py-4">Мӯҳлати кор</th>
                                <th class="px-6 py-4">Сабаби хориҷшавӣ</th>
                                <th class="text-right px-6 py-4">Амал</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="employees.length > 0">
                                <tr v-for="(emp, i) in employees" :key="emp.id" class="text-[11px] group transition-colors hover:bg-rose-50/30 border-b border-[hsl(var(--border))] last:border-0">
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
                                    <td class="italic text-[hsl(var(--muted-foreground))] px-6 py-4">Ихтисор шуд</td>
                                    <td class="text-right px-6 py-4">
                                        <button class="h-8 w-8 inline-flex items-center justify-center rounded hover:bg-[hsl(var(--muted))]"><MoreVertical class="h-4 w-4" /></button>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="7" class="h-32 text-center text-[hsl(var(--muted-foreground))] text-xs border-b border-[hsl(var(--border))]">
                                    Байгонӣ холӣ аст
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
                        <h2 class="text-xl font-bold">Илова ба Байгонӣ</h2>
                        <p class="text-[10px] text-[hsl(var(--muted-foreground))] uppercase font-bold tracking-widest mt-1">
                            Корманди собиқро сабт кунед
                        </p>
                    </div>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Ном</label>
                            <input v-model="form.name" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Насаб</label>
                            <input v-model="form.last_name" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Почта (Email)</label>
                        <input type="email" v-model="form.email" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Мансаб</label>
                        <input v-model="form.role" required class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[hsl(var(--muted-foreground))] uppercase">Шуъба</label>
                        <select v-model="form.department" class="flex h-10 w-full rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]">
                            <option>Engineering</option>
                            <option>Human Resources</option>
                            <option>Design</option>
                            <option>Management</option>
                        </select>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[hsl(var(--border))] mt-6">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-lg text-sm font-bold text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--muted))]">Бекор кардан</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg text-sm font-bold bg-[hsl(var(--primary))] text-white hover:bg-[hsl(var(--primary))]/90 disabled:opacity-50">Сабт ба Байгонӣ</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
