<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { History, Search, Calendar, MoreVertical } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    employees: { type: Array, default: () => [] }
});

const retiredEmployees = computed(() => props.employees.filter(e => e.status === 'Retired'));

const searchTerm = ref('');
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
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-1 rounded-lg flex items-center gap-2 text-[10px] font-bold uppercase tracking-tight">
                    <History class="h-3 w-3" />
                    Танҳо барои хондан
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
                            <template v-if="retiredEmployees.length > 0">
                                <tr v-for="(emp, i) in retiredEmployees" :key="emp.id" class="text-[11px] group transition-colors hover:bg-rose-50/30 border-b border-[hsl(var(--border))] last:border-0">
                                    <td class="font-bold text-[hsl(var(--muted-foreground))] px-6 py-4">{{ i + 1 }}</td>
                                    <td class="font-bold px-6 py-4">{{ emp.first_name }} {{ emp.last_name }}</td>
                                    <td class="px-6 py-4">{{ emp.position }}</td>
                                    <td class="text-[hsl(var(--muted-foreground))] uppercase text-[10px] font-bold px-6 py-4">{{ emp.department }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-3 w-3 text-[hsl(var(--muted-foreground))]" />
                                            2019 - 2023
                                        </div>
                                    </td>
                                    <td class="italic text-[hsl(var(--muted-foreground))] px-6 py-4">Нафақа</td>
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
    </AppLayout>
</template>
