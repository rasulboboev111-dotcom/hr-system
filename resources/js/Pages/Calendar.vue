<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    Clock, Calendar as CalendarIcon, Users, Plus, Filter, 
    ChevronLeft, ChevronRight, Palmtree, Coffee, Trash2
} from 'lucide-vue-next';

const props = defineProps({
    eventsList: {
        type: Array,
        default: () => []
    }
});

const { t } = useI18n();

const HOLIDAYS = {
    '01-01': 'Соли нав',
    '03-08': 'Рӯзи модар',
    '03-21': 'Наврӯз',
    '03-22': 'Наврӯз',
    '03-23': 'Наврӯз',
    '03-24': 'Наврӯз',
    '05-09': 'Рӯзи Ғалаба',
    '06-27': 'Рӯзи Ваҳдати миллӣ',
    '09-09': 'Рӯзи Истиқлолият',
    '11-06': 'Рӯзи Конститутсия',
};

const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());
const selectedDay = ref(today.getDate());

const events = computed(() => {
    // Filter props.eventsList by selected date
    const selected = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(selectedDay.value).padStart(2, '0')}`;
    return props.eventsList.filter(e => e.date === selected).map(e => ({
        id: e.id,
        time: '10:00', // Mock time since we used just 'date' in migration
        title: e.title,
        type: e.type,
        participants: 1
    }));
});

const isAddOpen = ref(false);

const form = useForm({
    title: '',
    time: '10:00',
    type: 'Meeting',
    date: ''
});

const monthNames = ['Январ', 'Феврал', 'Март', 'Апрел', 'Май', 'Июн', 'Июл', 'Август', 'Сентябр', 'Октябр', 'Ноябр', 'Декабр'];
const dayNames = ['Яш', 'Ду', 'Се', 'Чо', 'Пш', 'Ҷу', 'Ша'];

const daysInMonth = computed(() => new Date(currentYear.value, currentMonth.value + 1, 0).getDate());
const firstDayOfMonth = computed(() => new Date(currentYear.value, currentMonth.value, 1).getDay());

const calendarDays = computed(() => {
    const days = [];
    // Empty cells for days before the first
    const offset = firstDayOfMonth.value === 0 ? 6 : firstDayOfMonth.value - 1;
    for (let i = 0; i < offset; i++) days.push(null);
    for (let i = 1; i <= daysInMonth.value; i++) days.push(i);
    return days;
});

const selectedDateStr = computed(() => {
    const m = String(currentMonth.value + 1).padStart(2, '0');
    const d = String(selectedDay.value).padStart(2, '0');
    return `${m}-${d}`;
});

const selectedHoliday = computed(() => HOLIDAYS[selectedDateStr.value] || null);

const isHoliday = (day) => {
    if (!day) return false;
    const m = String(currentMonth.value + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    return !!HOLIDAYS[`${m}-${d}`];
};

const isWeekend = (day) => {
    if (!day) return false;
    const date = new Date(currentYear.value, currentMonth.value, day);
    return date.getDay() === 0 || date.getDay() === 6;
};

const prevMonth = () => {
    if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value--; }
    else currentMonth.value--;
    selectedDay.value = 1;
};

const nextMonth = () => {
    if (currentMonth.value === 11) { currentMonth.value = 0; currentYear.value++; }
    else currentMonth.value++;
    selectedDay.value = 1;
};

const selectDay = (day) => { if (day) selectedDay.value = day; };

const handleSaveEvent = () => {
    if (!form.title) return;
    
    // Set format YYYY-MM-DD
    form.date = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(selectedDay.value).padStart(2, '0')}`;
    
    form.post('/calendar/events', {
        preserveScroll: true,
        onSuccess: () => {
            isAddOpen.value = false;
            form.reset();
        }
    });
};

const handleDeleteEvent = (id) => {
    if(confirm('Мехоҳед ин чорабиниро нест кунед?')) {
        router.delete(`/calendar/events/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Calendar" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('calendar.title') }}</h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('calendar.subtitle') }}</p>
                </div>
                <div class="flex gap-2">
                    <button @click="isAddOpen = true" class="h-9 px-3 inline-flex items-center gap-2 text-[11px] font-bold uppercase bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg shadow-sm">
                        <Plus class="h-4 w-4" /> {{ t('calendar.newEvent') }}
                    </button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-[320px_1fr]">
                <!-- Calendar Widget -->
                <div class="space-y-6">
                    <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-white rounded-xl">
                        <div class="p-3">
                            <div class="flex items-center justify-between mb-4 px-1">
                                <button @click="prevMonth" class="h-8 w-8 flex items-center justify-center rounded hover:bg-[hsl(var(--muted))]">
                                    <ChevronLeft class="h-4 w-4" />
                                </button>
                                <span class="text-sm font-bold">{{ monthNames[currentMonth] }} {{ currentYear }}</span>
                                <button @click="nextMonth" class="h-8 w-8 flex items-center justify-center rounded hover:bg-[hsl(var(--muted))]">
                                    <ChevronRight class="h-4 w-4" />
                                </button>
                            </div>
                            <div class="grid grid-cols-7 gap-0">
                                <div v-for="name in dayNames" :key="name" class="text-center text-[9px] font-bold text-[hsl(var(--muted-foreground))] uppercase py-2">
                                    {{ name }}
                                </div>
                                <div v-for="(day, i) in calendarDays" :key="i"
                                    @click="selectDay(day)"
                                    class="text-center py-1.5 text-xs cursor-pointer rounded-md transition-colors"
                                    :class="{
                                        'hover:bg-[hsl(var(--muted))]': day && !isHoliday(day),
                                        'bg-[hsl(var(--primary))] text-white font-bold': day === selectedDay,
                                        'text-rose-600 font-bold bg-rose-50 hover:bg-rose-100': day && isHoliday(day) && day !== selectedDay,
                                        'text-[hsl(var(--muted-foreground))]/50': day && isWeekend(day) && !isHoliday(day) && day !== selectedDay,
                                        '': !day
                                    }">
                                    {{ day || '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Events for selected day -->
                <div class="space-y-4">
                    <!-- Holiday banner -->
                    <div v-if="selectedHoliday" class="bg-rose-50 border border-rose-100 rounded-xl p-6 flex items-center gap-5">
                        <div class="h-12 w-12 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shadow-inner">
                            <Palmtree class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-rose-900 leading-none">{{ selectedHoliday }}</h3>
                            <p class="text-xs text-rose-700 mt-1 uppercase tracking-widest font-bold">{{ t('calendar.holidayNotice') }}</p>
                        </div>
                    </div>

                    <div class="border border-[hsl(var(--border))] shadow-sm flex flex-col bg-white rounded-xl overflow-hidden">
                        <div class="border-b px-6 py-4 flex items-center justify-between bg-[hsl(var(--muted))]/5">
                            <h3 class="text-[13px] font-bold flex items-center gap-2">
                                <CalendarIcon class="h-4 w-4 text-[hsl(var(--primary))]" />
                                {{ t('calendar.scheduleFor') }} {{ selectedDay }}/{{ currentMonth + 1 }}/{{ currentYear }}
                            </h3>
                        </div>
                        <div v-if="!selectedHoliday">
                            <div class="divide-y border-t">
                                <div v-for="event in events" :key="event.id" class="px-6 py-5 hover:bg-[hsl(var(--primary))]/[0.02] transition-colors group">
                                    <div class="flex items-start justify-between">
                                        <div class="flex gap-8">
                                            <div class="flex flex-col items-center justify-center min-w-[70px] py-1 border-r pr-8 border-[hsl(var(--muted))]">
                                                <span class="text-sm font-bold text-[hsl(var(--primary))]">{{ event.time }}</span>
                                            </div>
                                            <div class="space-y-2">
                                                <h4 class="text-[14px] font-bold text-[hsl(var(--foreground))] group-hover:text-[hsl(var(--primary))] transition-colors leading-none">
                                                    {{ event.title }}
                                                </h4>
                                                <div class="flex items-center gap-6 text-[10px] text-[hsl(var(--muted-foreground))]/80 font-bold tracking-tight uppercase">
                                                    <span class="flex items-center gap-1.5 bg-[hsl(var(--muted))]/50 px-2 py-1 rounded">
                                                        <Users class="h-3 w-3" /> {{ event.participants }} {{ t('menu.employees') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[9px] font-extrabold border-none px-3 py-1 uppercase bg-[hsl(var(--muted))] rounded-md">
                                                {{ event.type }}
                                            </span>
                                            <button @click="handleDeleteEvent(event.id)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-md transition-colors opacity-0 group-hover:opacity-100">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-64 flex flex-col items-center justify-center text-center p-8 bg-[hsl(var(--muted))]/5">
                            <Coffee class="h-10 w-10 text-[hsl(var(--muted-foreground))]/30 mb-4" />
                            <h4 class="font-bold text-[hsl(var(--muted-foreground))] uppercase text-[10px] tracking-widest">{{ t('calendar.holidayNotice') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Event Modal -->
        <div v-if="isAddOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-[hsl(var(--border))]">
                    <h2 class="text-lg font-bold">{{ t('calendar.newEvent') }}</h2>
                </div>
                <div class="p-6 grid gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase font-bold">{{ t('calendar.eventTitle') }}</label>
                        <input v-model="form.title" :placeholder="t('calendar.eventTitle')" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-bold">{{ t('calendar.eventTime') }}</label>
                            <input v-model="form.time" type="time" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-bold">{{ t('calendar.eventType') }}</label>
                            <select v-model="form.type" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none">
                                <option value="Meeting">Маҷлис</option>
                                <option value="Interview">Мусоҳиба</option>
                                <option value="Training">Омӯзиш</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0 flex gap-3">
                    <button @click="isAddOpen = false" class="flex-1 h-10 rounded-lg text-xs font-bold text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--muted))]">{{ t('common.cancel') }}</button>
                    <button @click="handleSaveEvent" class="flex-1 h-10 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-lg text-xs font-bold uppercase">{{ t('common.save') }}</button>
                </div>
            </div>
        </div>
        <div v-if="isAddOpen" @click="isAddOpen = false" class="fixed inset-0 z-40"></div>
    </AppLayout>
</template>
