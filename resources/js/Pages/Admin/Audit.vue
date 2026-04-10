<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { 
  History, Search, Download, Filter, User, Activity, 
  Calendar, ShieldCheck, ShieldAlert, Loader2
} from 'lucide-vue-next';

// We can accept audit logs from the backend someday, for now we can mock them based on the exact same UI structure.
const props = defineProps({
    auditLogs: { type: Array, default: () => [] }
});

const { t } = useI18n();

const logs = props.auditLogs.length ? props.auditLogs : [
    { id: 1, userId: 'user_123', userName: 'admin', action: 'create', entityType: 'user', description: 'Created user @john', timestamp: new Date().toISOString() },
    { id: 2, userId: 'user_123', userName: 'admin', action: 'update', entityType: 'employee', description: 'Updated salary for emp_456', timestamp: new Date(Date.now() - 3600000).toISOString() }
];

const getActionClass = (action) => {
    if (action === 'create') return "bg-emerald-50 text-emerald-700";
    if (action === 'delete') return "bg-rose-50 text-rose-700";
    return "bg-blue-50 text-blue-700";
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
                <button class="h-9 px-3 border border-[hsl(var(--border))] rounded-lg inline-flex items-center gap-2 uppercase font-bold text-[10px] hover:bg-[hsl(var(--muted))]">
                    <Download class="h-4 w-4" /> {{ t('common.export') }}
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm rounded-xl p-6">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[hsl(var(--muted-foreground))] mb-2">{{ t('common.all').toUpperCase() }} ACTIONS</p>
                    <h3 class="text-2xl font-bold">{{ logs.length }}</h3>
                </div>
                <div class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm rounded-xl p-6">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[hsl(var(--muted-foreground))] mb-2">TODAY</p>
                    <h3 class="text-2xl font-bold">{{ logs.length }}</h3>
                </div>
                <div class="border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm rounded-xl p-6">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[hsl(var(--muted-foreground))] mb-2">ACTIVE USERS</p>
                    <h3 class="text-2xl font-bold">{{ new Set(logs.map(l => l.userId)).size }}</h3>
                </div>
            </div>

            <div class="border border-[hsl(var(--border))] shadow-sm overflow-hidden bg-[hsl(var(--card))] rounded-xl">
                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex items-center justify-between">
                    <div class="relative max-w-sm flex-1">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <input :placeholder="t('common.search')" class="pl-9 h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent focus:outline-none" />
                    </div>
                    <button class="h-9 px-3 border border-[hsl(var(--border))] rounded-lg inline-flex items-center gap-2 text-[10px] font-bold uppercase hover:bg-[hsl(var(--muted))]">
                        <Filter class="h-4 w-4" /> {{ t('common.filter') }}
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-[hsl(var(--muted))]/30 uppercase tracking-widest text-[9px] font-bold text-[hsl(var(--muted-foreground))] border-b border-[hsl(var(--border))]">
                                <th class="px-6 py-4">{{ t('audit.user') }}</th>
                                <th class="px-6 py-4">{{ t('common.action') }}</th>
                                <th class="px-6 py-4">{{ t('audit.entity') }}</th>
                                <th class="px-6 py-4">{{ t('audit.description') }}</th>
                                <th class="px-6 py-4 text-right">{{ t('audit.time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs" :key="log.id" class="text-[11px] group hover:bg-[hsl(var(--primary))]/[0.02] border-b border-[hsl(var(--border))] last:border-0">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-[hsl(var(--primary))]/10 flex items-center justify-center font-bold text-[hsl(var(--primary))] text-[10px]">
                                            {{ log.userName ? log.userName[0].toUpperCase() : 'U' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[hsl(var(--foreground))]/80">{{ log.userName }}</p>
                                            <p class="text-[9px] text-[hsl(var(--muted-foreground))] font-mono">{{ log.userId.substring(0, 8) }}...</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-[8px] uppercase font-extrabold px-2 py-0.5 rounded-full inline-block', getActionClass(log.action)]">
                                        {{ t(`audit.actions.${log.action}`) || log.action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Activity class="h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/60" />
                                        <span class="font-bold text-[hsl(var(--foreground))]/70">{{ t(`audit.entities.${log.entityType}`) || log.entityType }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[hsl(var(--muted-foreground))] italic">{{ log.description }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="font-bold text-[hsl(var(--foreground))]/80">{{ new Date(log.timestamp).toLocaleDateString() }}</span>
                                        <span class="text-[9px] text-[hsl(var(--muted-foreground))] font-mono">{{ new Date(log.timestamp).toLocaleTimeString() }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
