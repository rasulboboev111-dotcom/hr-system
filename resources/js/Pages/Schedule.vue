<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { Clock, CalendarDays, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    shifts: Array
});

const { t } = useI18n();

const isAddShiftOpen = ref(false);
const form = useForm({
    employee_id: 0,
    date_key: 'weekdays',
    shift_type: ''
});

const handleAddShift = () => {
    if (!form.shift_type) return;
    form.post('/schedule', {
        preserveScroll: true,
        onSuccess: () => {
            isAddShiftOpen.value = false;
            form.reset('shift_type');
        }
    });
};

const handleDeleteShift = (shiftId) => {
    if(confirm(t('common.deleteConfirm') || 'Шумо мутмаин ҳастед?')) {
        router.delete(`/schedule/${shiftId}`, { preserveScroll: true });
    }
};

const handleExport = () => {
    window.location.href = '/schedule/export';
};
</script>

<template>
    <Head :title="t('schedule.title')" />

    <AppLayout>
        <div class="space-y-6 max-w-3xl mx-auto">
            <div class="flex items-center justify-between mt-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('schedule.title') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('schedule.subtitle') }}</p>
                </div>
                <div>
                    <button @click="isAddShiftOpen = true" class="h-9 px-3 inline-flex items-center gap-2 text-[10px] font-bold uppercase bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg shadow-sm hover:opacity-90 transition-opacity">
                        <Plus class="h-4 w-4" /> {{ t('common.add') }}
                    </button>
                </div>
            </div>

            <div class="bg-white border border-[hsl(var(--border))] rounded-2xl overflow-hidden shadow-sm">
                <div class="p-6 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/10 flex items-center gap-5">
                    <div class="h-14 w-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center border border-emerald-100 shadow-sm shrink-0">
                        <CalendarDays class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight">{{ t('schedule.generalSchedule') }}</h2>
                        <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1 uppercase font-bold tracking-widest">{{ t('schedule.weekdays') }}</p>
                    </div>
                </div>
                <div class="p-0">
                    <div v-for="shift in shifts" :key="shift.id" 
                         class="flex items-center justify-between p-5 px-8 border-b border-[hsl(var(--border))] last:border-0 hover:bg-[hsl(var(--muted))]/10 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="h-2.5 w-2.5 rounded-full shadow-sm bg-emerald-500"></div>
                            <span class="text-[13px] font-black uppercase tracking-widest text-[hsl(var(--foreground))]/80">{{ t('schedule.weekdays') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-xs font-black tracking-widest px-4 py-2 rounded-xl border bg-emerald-50 border-emerald-100 text-emerald-700">
                                <Clock class="h-4 w-4 opacity-50" />
                                {{ shift.shift_type }}
                            </div>
                            <button @click="handleDeleteShift(shift.id)" class="opacity-0 group-hover:opacity-100 shrink-0 h-8 w-8 flex items-center justify-center rounded-xl bg-white hover:bg-rose-50 text-slate-300 hover:text-rose-500 transition-all shadow-sm border border-[hsl(var(--border))]">
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                    <div v-if="shifts.length === 0" class="p-12 text-center text-[hsl(var(--muted-foreground))] text-sm font-medium">
                        {{ t('schedule.noShifts') }}
                    </div>
                </div>
            </div>

            <div class="bg-white border border-[hsl(var(--border))] rounded-2xl overflow-hidden shadow-sm">
                <div class="p-6 bg-slate-50/50 flex items-center gap-5">
                    <div class="h-14 w-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center border border-rose-100 shadow-sm shrink-0">
                        <CalendarDays class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight">{{ t('schedule.weekendTitle') }}</h2>
                        <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1 uppercase font-bold tracking-widest">{{ t('schedule.weekendDays') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Shift Modal -->
        <div v-if="isAddShiftOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-5 border-b border-[hsl(var(--border))]">
                    <h2 class="text-lg font-bold tracking-tight">{{ t('schedule.addWorkingTime') }}</h2>
                </div>
                <div class="p-5 grid gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('schedule.timeInput') }}</label>
                        <input v-model="form.shift_type" type="text" placeholder="08:00 - 17:00" class="h-10 w-full text-xs rounded-xl border border-[hsl(var(--border))] bg-transparent px-3 font-medium focus:outline-none focus:ring-2 focus:ring-[hsl(var(--primary))]/20 focus:border-[hsl(var(--primary))] transition-all" @keyup.enter="handleAddShift" />
                    </div>
                </div>
                <div class="p-4 bg-[hsl(var(--muted))]/30 flex items-center justify-end gap-2 border-t border-[hsl(var(--border))]">
                    <button @click="isAddShiftOpen = false" class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--muted))] rounded-xl transition-colors">{{ t('common.cancel') }}</button>
                    <button @click="handleAddShift" class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-xl shadow-sm hover:opacity-90 transition-opacity">{{ t('common.add') }}</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
