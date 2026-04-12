<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { 
  History, Search, Download, Filter, User, Activity, 
  Calendar, ShieldCheck, ShieldAlert, Loader2, Globe, Database, ExternalLink, X, Trash2
} from 'lucide-vue-next';

const props = defineProps({
    logs: { type: Object, default: () => ({ data: [], links: [] }) },
    stats: { type: Object, default: () => ({ totalActions: 0, todayActions: 0, activeUsersCount: 0 }) },
    filters: { type: Object, default: () => ({ search: '' }) }
});

const { t } = useI18n();
const selectedLog = ref(null);
const search = ref(props.filters.search || '');

let searchTimeout = null;
watch(search, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/audit', 
            { search: value }, 
            { preserveState: true, replace: true }
        );
    }, 300);
});

const getActionClass = (action) => {
    const act = String(action).toLowerCase();
    if (act.includes('create') || act.includes('add') || act.includes('import')) return "bg-emerald-50 text-emerald-700";
    if (act.includes('delete') || act.includes('destroy')) return "bg-rose-50 text-rose-700";
    if (act.includes('update') || act.includes('edit')) return "bg-amber-50 text-amber-700";
    return "bg-blue-50 text-blue-700";
};

const parseMetadata = (metadata) => {
    if (!metadata) return null;
    try {
        return typeof metadata === 'string' ? JSON.parse(metadata) : metadata;
    } catch (e) {
        return null;
    }
};

const formatValue = (val) => {
    if (val === null || val === undefined) return '-';
    if (typeof val === 'boolean') return val ? 'Yes' : 'No';
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
};

const getActionKey = (action) => {
    const act = String(action).toLowerCase();
    if (act.includes('import')) return 'import';
    if (act.includes('export')) return 'export';
    if (act.includes('create') || act.includes('add')) return 'create';
    if (act.includes('update') || act.includes('edit')) return 'update';
    if (act.includes('delete') || act.includes('destroy')) return 'delete';
    return act;
};

const getPageName = (url) => {
    if (!url) return '-';
    const path = url.toLowerCase();
    if (path.includes('/timesheet')) return t('menu.timesheet');
    if (path.includes('/employees')) return t('menu.employees');
    if (path.includes('/admin/users')) return t('menu.users');
    if (path.includes('/departments')) return t('menu.departments');
    if (path.includes('/positions')) return t('menu.positions');
    if (path.includes('/admin/audit')) return t('menu.audit');
    if (path.includes('/dashboard')) return t('menu.dashboard');
    if (path.includes('/calendar')) return t('menu.calendar');
    if (path.includes('/payroll')) return t('menu.payroll');
    if (path.includes('/schedule')) return t('menu.schedule');
    return url.split('/').pop() || '/';
};

const getLocalizedDescription = (log) => {
    const actionKey = getActionKey(log.action);
    const entityKey = String(log.entity_type).toLowerCase();
    const actionText = t(`audit.actions.${actionKey}`) || log.action;
    const entityText = t(`audit.entities.${entityKey}`) || log.entity_type;
    return `${actionText}: ${entityText}`;
};

const formatLocalTime = (timestamp) => {
    if (!timestamp) return '-';
    // Ensure we treat it as UTC if no TZ suffix, then let JS convert to Local
    const date = new Date(timestamp.includes('Z') || timestamp.includes('+') ? timestamp : timestamp + 'Z');
    return {
        date: date.toLocaleDateString(),
        time: date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
    };
};

const handleClear = () => {
    if (confirm(t('audit.clearConfirm'))) {
        router.delete('/admin/audit/clear', {
            preserveState: false,
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Audit Logs" />

    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <History class="h-6 w-6 text-[hsl(var(--primary))]" />
                        {{ t('audit.title') }}
                    </h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-widest font-bold">{{ t('audit.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="handleClear" class="h-9 px-3 border border-rose-200 rounded-lg inline-flex items-center gap-2 uppercase font-bold text-[10px] text-rose-600 hover:bg-rose-50 transition-colors">
                        <Trash2 class="h-4 w-4" /> {{ t('audit.clear') }}
                    </button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm rounded-xl p-6 hover:shadow-md transition-shadow">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[hsl(var(--muted-foreground))] mb-2">{{ t('audit.stats.allActions') }}</p>
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold">{{ stats.totalActions }}</h3>
                        <Activity class="h-5 w-5 text-blue-500 opacity-20" />
                    </div>
                </div>
                <div class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm rounded-xl p-6 hover:shadow-md transition-shadow">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[hsl(var(--muted-foreground))] mb-2">{{ t('audit.stats.today') }}</p>
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold">{{ stats.todayActions }}</h3>
                        <Calendar class="h-5 w-5 text-green-500 opacity-20" />
                    </div>
                </div>
                <div class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm rounded-xl p-6 hover:shadow-md transition-shadow">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[hsl(var(--muted-foreground))] mb-2">{{ t('audit.stats.activeUsers') }}</p>
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold">{{ stats.activeUsersCount }}</h3>
                        <User class="h-5 w-5 text-purple-500 opacity-20" />
                    </div>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-[hsl(var(--card))] rounded-xl">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex items-center justify-between">
                    <div class="relative max-w-sm flex-1">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input 
                            v-model="search"
                            :placeholder="t('common.search')" 
                            class="pl-9 h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent focus:outline-none" 
                        />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/30 uppercase tracking-widest text-[9px] font-bold text-[hsl(var(--muted-foreground))] border-b border-[hsl(var(--border))]">
                                <th class="px-6 py-4">{{ t('audit.user') }}</th>
                                <th class="px-6 py-4">{{ t('common.action') }}</th>
                                <th class="px-6 py-4">{{ t('audit.entity') }}</th>
                                <th class="px-6 py-4">{{ t('audit.page') }}</th>
                                <th class="px-6 py-4">{{ t('audit.description') }}</th>
                                <th class="px-6 py-4 text-right">{{ t('audit.time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id" 
                                @click="log.metadata ? (selectedLog = log) : null"
                                :class="['text-[11px] group border-b border-[hsl(var(--border))] last:border-0 transition-colors', log.metadata ? 'cursor-pointer hover:bg-[hsl(var(--primary))]/[0.05]' : 'hover:bg-[hsl(var(--primary))]/[0.02]']">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-[hsl(var(--primary))]/10 flex items-center justify-center font-bold text-[hsl(var(--primary))] text-[10px]">
                                            {{ log.user_name ? log.user_name[0].toUpperCase() : 'U' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[hsl(var(--foreground))]/80 text-[11px]">{{ log.user_name }}</p>
                                            <p class="text-[9px] text-[hsl(var(--muted-foreground))] font-mono font-bold">{{ log.ip_address || '0.0.0.0' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-[8px] uppercase font-extrabold px-2 py-0.5 rounded-full inline-block', getActionClass(log.action)]">
                                        {{ t(`audit.actions.${getActionKey(log.action)}`) || log.action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 max-w-[150px] overflow-hidden">
                                        <Activity class="h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/60" />
                                        <span class="font-bold text-[hsl(var(--foreground))]/70 text-[10px]">{{ t(`audit.entities.${String(log.entity_type).toLowerCase()}`) || log.entity_type }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 max-w-[150px] overflow-hidden">
                                        <Globe class="h-3 w-3 text-[hsl(var(--primary))]/70" />
                                        <span class="truncate text-[10px] font-bold text-[hsl(var(--foreground))]/70 uppercase tracking-tighter">{{ getPageName(log.url) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[hsl(var(--muted-foreground))] italic text-[11px]">{{ getLocalizedDescription(log) }}</span>
                                        <span v-if="log.metadata" class="text-[8px] text-[hsl(var(--primary))] font-bold uppercase flex items-center gap-1">
                                            <ExternalLink class="h-2 w-2" /> {{ t('common.details').toUpperCase() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="font-bold text-[hsl(var(--foreground))]/80 text-[11px]">{{ formatLocalTime(log.timestamp).date }}</span>
                                        <span class="text-[9px] text-[hsl(var(--muted-foreground))] font-mono font-bold uppercase">{{ formatLocalTime(log.timestamp).time }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-[hsl(var(--card))] border border-[hsl(var(--border))] shadow-2xl rounded-2xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-[hsl(var(--border))] flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <Database class="h-5 w-5 text-[hsl(var(--primary))]" />
                            {{ t('audit.details') }}
                        </h2>
                        <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1">{{ selectedLog.user_name }} • {{ selectedLog.entity_type }} #{{ selectedLog.entity_id }}</p>
                    </div>
                    <button @click="selectedLog = null" class="h-8 w-8 rounded-full flex items-center justify-center hover:bg-[hsl(var(--muted))]">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <div class="space-y-4">
                        <div v-if="selectedLog.url" class="flex items-center gap-2 text-[10px] bg-[hsl(var(--muted))]/30 p-2 rounded-lg border border-[hsl(var(--border))]">
                            <Globe class="h-3 w-3" />
                            <span class="font-mono">{{ selectedLog.url }}</span>
                        </div>

                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[9px] uppercase font-bold text-[hsl(var(--muted-foreground))] border-b border-[hsl(var(--border))]">
                                    <th class="py-2">{{ t('audit.field') }}</th>
                                    <th class="py-2" v-if="selectedLog.action === 'updated'">{{ t('audit.oldValue') }}</th>
                                    <th class="py-2">{{ selectedLog.action === 'updated' ? t('audit.newValue') : t('common.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs">
                                <tr v-for="(val, key) in parseMetadata(selectedLog.metadata)" :key="key" class="border-b border-[hsl(var(--border))] last:border-0 hover:bg-[hsl(var(--muted))]/20">
                                    <td class="py-3 font-bold text-[hsl(var(--primary))] font-mono">{{ key }}</td>
                                    
                                    <!-- Log is an Update -->
                                    <template v-if="selectedLog.action === 'updated'">
                                        <td class="py-3">
                                            <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 break-all">{{ formatValue(val.old) }}</span>
                                        </td>
                                        <td class="py-3">
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 break-all">{{ formatValue(val.new) }}</span>
                                        </td>
                                    </template>
                                    
                                    <!-- Log is a Create/Delete -->
                                    <td v-else class="py-3">
                                        <span class="text-[hsl(var(--foreground))]/70 break-all">{{ formatValue(val) }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="p-4 bg-[hsl(var(--muted))]/10 border-t border-[hsl(var(--border))] flex justify-end">
                    <button @click="selectedLog = null" class="px-4 py-2 bg-[hsl(var(--primary))] text-white rounded-lg font-bold text-xs hover:opacity-90">
                        {{ t('common.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

