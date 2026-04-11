<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import { computed, ref } from 'vue';
import { 
  ShieldCheck, Shield, UserPlus, Search, Edit2, Trash2, CheckCircle2, ChevronRight, Info,
  ArrowUpDown, ChevronUp, ChevronDown
} from 'lucide-vue-next';

const props = defineProps({
    users: { type: Array, default: () => [] }
});

const { t } = useI18n();



const activeTab = ref('users');
const isUserModalOpen = ref(false);
const isEditingUser = ref(false);
const editingUserId = ref(null);
const isAddRoleOpen = ref(false);

const form = useForm({
    username: '',
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    role: 'hr_mgr'
});

const roles = ref([
    { id: 'admin', name: 'Администратор', description: 'Дастрасӣ пурра ба ҳамаи модулҳо', permissionIds: ['view_all', 'edit_all'] },
    { id: 'hr_mgr', name: 'Мудири HR', description: 'Идораи кормандон ва давомотҳо', permissionIds: ['view_users', 'edit_users'] }
]);

const permissions = ref([
    { id: 'view_all', name: 'Дидани ҳама', category: 'Умумӣ', description: 'Имкони дидани ҳамаи маълумот' },
    { id: 'edit_all', name: 'Таҳрири ҳама', category: 'Умумӣ', description: 'Имкони таҳрири ҳамаи маълумот' }
]);

const searchQuery = ref('');

const usersList = computed(() => {
    const defaultList = props.users.length ? props.users : [
        { id: 1, username: 'admin', first_name: 'Super', last_name: 'Admin', email: 'admin@siizi.ru', roleIds: ['admin'] }
    ];
    
    if (!searchQuery.value) return defaultList;
    const lower = searchQuery.value.toLowerCase();
    
    return defaultList.filter(u => 
        ((u.first_name || '') + ' ' + (u.last_name || '')).toLowerCase().includes(lower) ||
        (u.username || '').toLowerCase().includes(lower) ||
        (u.email || '').toLowerCase().includes(lower)
    );
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

const sortedUsers = computed(() => {
    let items = [...usersList.value];
    if (sortKey.value && sortDir.value) {
        items.sort((a, b) => {
            let aVal, bVal;
            if (sortKey.value === 'fullname') {
                aVal = `${a.first_name || ''} ${a.last_name || ''}`.toLowerCase();
                bVal = `${b.first_name || ''} ${b.last_name || ''}`.toLowerCase();
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

const openAddUserModal = () => {
    isEditingUser.value = false;
    editingUserId.value = null;
    form.reset();
    isUserModalOpen.value = true;
};

const openEditUserModal = (user) => {
    isEditingUser.value = true;
    editingUserId.value = user.id;
    form.username = user.username;
    form.first_name = user.first_name;
    form.last_name = user.last_name;
    form.email = user.email;
    form.role = user.role_ids && user.role_ids.length ? user.role_ids[0] : 'hr_mgr';
    form.password = ''; // Don't show password
    isUserModalOpen.value = true;
};

const submitUserForm = () => {
    if (isEditingUser.value) {
        form.put(`/admin/users/${editingUserId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                isUserModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/admin/users', {
            preserveScroll: true,
            onSuccess: () => {
                isUserModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const handleDeleteUser = (id) => {
    if(confirm('Шумо мутмаин ҳастед, ки ин истифодабарандаро нест кардан мехоҳед?')) {
        router.delete(`/admin/users/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Admin Users" />

    <AppLayout>
        <div class="space-y-6 max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-[hsl(var(--primary))]/10 flex items-center justify-center">
                            <ShieldCheck class="h-6 w-6 text-[hsl(var(--primary))]" />
                        </div>
                        {{ t('admin.title') }}
                    </h1>
                    <p class="text-[10px] text-[hsl(var(--muted-foreground))] mt-1 uppercase tracking-[0.2em] font-bold">{{ t('admin.subtitle') }}</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <button @click="isAddRoleOpen = true" class="h-10 px-4 border border-[hsl(var(--border))] rounded-xl font-bold text-xs uppercase tracking-widest inline-flex items-center gap-2 hover:bg-[hsl(var(--muted))]">
                        <Shield class="h-4 w-4" /> {{ t('admin.addRole') }}
                    </button>
                    <button @click="openAddUserModal" class="h-10 px-4 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-xl font-bold text-xs uppercase tracking-widest inline-flex items-center gap-2 shadow-lg shadow-[hsl(var(--primary))]/20">
                        <UserPlus class="h-4 w-4" /> {{ t('admin.addUser') }}
                    </button>
                </div>
            </div>

            <div class="w-full">
                <!-- Custom Tabs implementation -->
                <div class="bg-[hsl(var(--muted))]/30 p-1.5 h-12 border border-[hsl(var(--border))] rounded-2xl mb-8 w-fit inline-flex">
                    <button 
                        @click="activeTab = 'users'" 
                        class="text-[10px] font-extrabold uppercase px-10 rounded-xl transition-all"
                        :class="activeTab === 'users' ? 'bg-white shadow-sm text-black' : 'text-[hsl(var(--muted-foreground))] hover:text-black'"
                    >
                        {{ t('admin.users') }}
                    </button>
                    <button 
                        @click="activeTab = 'roles'" 
                        class="text-[10px] font-extrabold uppercase px-10 rounded-xl transition-all"
                        :class="activeTab === 'roles' ? 'bg-white shadow-sm text-black' : 'text-[hsl(var(--muted-foreground))] hover:text-black'"
                    >
                        {{ t('admin.roles') }}
                    </button>
                    <button 
                        @click="activeTab = 'permissions'" 
                        class="text-[10px] font-extrabold uppercase px-10 rounded-xl transition-all"
                        :class="activeTab === 'permissions' ? 'bg-white shadow-sm text-black' : 'text-[hsl(var(--muted-foreground))] hover:text-black'"
                    >
                        {{ t('admin.permissions') }}
                    </button>
                </div>

                <!-- Users Tab -->
                <div v-show="activeTab === 'users'">
                    <div class="border border-[hsl(var(--border))] shadow-sm rounded-3xl overflow-hidden bg-[hsl(var(--card))]">
                        <div class="p-6 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/5 flex items-center justify-between">
                            <div class="relative max-w-sm flex-1">
                                <Search class="absolute left-3.5 top-3 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                                <input v-model="searchQuery" :placeholder="t('common.search')" class="pl-11 h-10 w-full text-xs rounded-xl border-none bg-[hsl(var(--muted))]/20 focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]" />
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[9px] font-bold border border-[hsl(var(--primary))]/20 text-[hsl(var(--primary))] bg-[hsl(var(--primary))]/5 px-4 py-1.5 rounded-full uppercase tracking-tighter">
                                    {{ usersList.length }} {{ t('common.active') }}
                                </span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-max">
                                <thead>
                                    <tr class="bg-[hsl(var(--muted))]/30 uppercase tracking-[0.1em] text-[10px] font-extrabold text-[hsl(var(--muted-foreground))] border-b border-[hsl(var(--border))]">
                                        <th class="w-16 px-8 py-5 text-center">№</th>
                                        <th @click="handleSort('username')" class="px-6 py-5 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'username' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                            <div class="flex items-center">{{ t('admin.username') }}
                                                <ChevronUp v-if="sortKey === 'username' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                                <ChevronDown v-else-if="sortKey === 'username' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                                <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                            </div>
                                        </th>
                                        <th @click="handleSort('fullname')" class="px-6 py-5 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'fullname' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                            <div class="flex items-center">{{ t('common.name') }}
                                                <ChevronUp v-if="sortKey === 'fullname' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                                <ChevronDown v-else-if="sortKey === 'fullname' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                                <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                            </div>
                                        </th>
                                        <th @click="handleSort('email')" class="px-6 py-5 cursor-pointer transition-all group whitespace-nowrap" :class="sortKey === 'email' ? 'bg-[hsl(var(--primary))]/5 text-[hsl(var(--primary))]' : 'hover:bg-[hsl(var(--muted))]/50'">
                                            <div class="flex items-center">{{ t('common.email') }}
                                                <ChevronUp v-if="sortKey === 'email' && sortDir === 'asc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                                <ChevronDown v-else-if="sortKey === 'email' && sortDir === 'desc'" class="ml-2 h-3.5 w-3.5 text-[hsl(var(--primary))]" />
                                                <ArrowUpDown v-else class="ml-2 h-3.5 w-3.5 text-[hsl(var(--muted-foreground))]/30 group-hover:text-[hsl(var(--muted-foreground))]/60" />
                                            </div>
                                        </th>
                                        <th class="px-6 py-5">{{ t('admin.roles') }}</th>
                                        <th class="px-6 py-5">{{ t('common.status') }}</th>
                                        <th class="text-right px-8 py-5">{{ t('common.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(u, i) in sortedUsers" :key="u.id" class="text-[12px] group transition-all hover:bg-[hsl(var(--primary))]/[0.04] border-b border-[hsl(var(--border))] last:border-0">
                                        <td class="font-extrabold text-[hsl(var(--primary))] px-8 py-6 text-center">{{ i + 1 }}</td>
                                        <td class="font-mono font-bold text-[hsl(var(--foreground))] px-6 py-6">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded bg-[hsl(var(--muted))]/40 flex items-center justify-center text-[10px] text-[hsl(var(--muted-foreground))]">@</div>
                                                {{ u.username || u.name }}
                                            </div>
                                        </td>
                                        <td class="font-bold uppercase text-[hsl(var(--foreground))]/80 px-6 py-6">{{ u.first_name }} {{ u.last_name }}</td>
                                        <td class="text-[hsl(var(--muted-foreground))] px-6 py-6 font-medium">{{ u.email }}</td>
                                        <td class="px-6 py-6">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span v-for="rid in (u.roleIds || ['admin'])" :key="rid" class="inline-block rounded-md text-[8px] bg-[hsl(var(--primary))]/10 text-[hsl(var(--primary))] px-3 py-1 uppercase font-extrabold">
                                                    {{ roles.find(r => r.id === rid)?.name || rid }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-6">
                                            <div class="flex items-center gap-2 text-emerald-600 font-extrabold text-[10px] uppercase">
                                                <CheckCircle2 class="h-3 w-3" />
                                                {{ t('common.active') }}
                                            </div>
                                        </td>
                                        <td class="text-right px-8 py-6">
                                            <div class="inline-flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="openEditUserModal(u)" class="h-9 w-9 rounded-xl hover:bg-[hsl(var(--muted))] inline-flex items-center justify-center"><Edit2 class="h-4 w-4 text-[hsl(var(--muted-foreground))]" /></button>
                                                <button @click="handleDeleteUser(u.id)" class="h-9 w-9 rounded-xl hover:bg-rose-50 hover:text-rose-600 inline-flex items-center justify-center"><Trash2 class="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Roles Tab -->
                <div v-show="activeTab === 'roles'">
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="role in roles" :key="role.id" class="border border-[hsl(var(--border))] shadow-sm hover:shadow-xl transition-all duration-300 rounded-3xl bg-[hsl(var(--card))] overflow-hidden group">
                            <div class="h-2 w-full bg-[hsl(var(--primary))]/20 group-hover:bg-[hsl(var(--primary))] transition-colors" />
                            <div class="pb-4 p-8">
                                <div class="flex justify-between items-start">
                                    <div class="space-y-2">
                                        <h3 class="text-base font-extrabold uppercase tracking-widest text-[hsl(var(--primary))]">{{ role.name }}</h3>
                                        <p class="text-xs leading-relaxed font-medium line-clamp-2 text-[hsl(var(--muted-foreground))]">{{ role.description }}</p>
                                    </div>
                                    <div class="h-12 w-12 rounded-2xl bg-[hsl(var(--primary))]/5 flex items-center justify-center shadow-inner shrink-0">
                                        <Shield class="h-6 w-6 text-[hsl(var(--primary))]" />
                                    </div>
                                </div>
                            </div>
                            <div class="px-8 pb-8 space-y-6">
                                <div class="space-y-4">
                                    <p class="text-[10px] font-extrabold text-[hsl(var(--muted-foreground))] uppercase tracking-widest">
                                        {{ t('admin.allowedActions') }} ({{ role.permissionIds?.length || 0 }}):
                                    </p>
                                    <div class="grid gap-2">
                                        <div v-for="pid in role.permissionIds" :key="pid" class="flex items-center gap-3 p-2.5 rounded-xl bg-[hsl(var(--muted))]/30 group/item hover:bg-[hsl(var(--muted))]/50 transition-colors">
                                            <div class="h-5 w-5 rounded-full bg-emerald-100 flex items-center justify-center">
                                                <CheckCircle2 class="h-3 w-3 text-emerald-600" />
                                            </div>
                                            <span class="text-[11px] font-bold text-[hsl(var(--foreground))]/80 uppercase tracking-tighter">
                                                {{ pid.replace('_', ' ') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Tab -->
                <div v-show="activeTab === 'permissions'">
                    <div class="border border-[hsl(var(--border))] shadow-sm rounded-3xl overflow-hidden bg-[hsl(var(--card))]">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-max">
                                <thead>
                                    <tr class="bg-[hsl(var(--muted))]/30 uppercase text-[10px] font-extrabold text-[hsl(var(--muted-foreground))] tracking-widest border-b border-[hsl(var(--border))]">
                                        <th class="px-8 py-5">{{ t('admin.permissions') }}</th>
                                        <th class="px-6 py-5">{{ t('common.department') }}</th>
                                        <th class="px-6 py-5">{{ t('admin.permissionDescription') }}</th>
                                        <th class="text-right px-8 py-5">{{ t('common.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="perm in permissions" :key="perm.id" class="text-[12px] group transition-all hover:bg-emerald-50/30 border-b border-[hsl(var(--border))] last:border-0">
                                        <td class="px-8 py-6">
                                            <div class="flex flex-col gap-1">
                                                <span class="font-mono font-bold text-[hsl(var(--primary))]">{{ perm.id }}</span>
                                                <span class="text-[11px] font-extrabold text-[hsl(var(--foreground))] uppercase tracking-tight">{{ perm.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-6">
                                            <span class="inline-block text-[9px] uppercase font-extrabold text-[hsl(var(--muted-foreground))] border border-[hsl(var(--border))] bg-[hsl(var(--muted))]/10 px-3 py-1 rounded-full">
                                                {{ perm.category }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-6">
                                            <div class="flex items-start gap-2 max-w-md">
                                                <Info class="h-3.5 w-3.5 text-[hsl(var(--primary))]/40 mt-0.5 shrink-0" />
                                                <span class="italic text-[hsl(var(--muted-foreground))] font-medium">{{ perm.description }}</span>
                                            </div>
                                        </td>
                                        <td class="text-right px-8 py-6">
                                            <div class="h-8 w-8 rounded-full bg-emerald-50 text-emerald-600 inline-flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                                <CheckCircle2 class="h-4 w-4" />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit User Modal -->
        <div v-if="isUserModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-[hsl(var(--card))] w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-[hsl(var(--border))]">
                <div class="p-6 border-b border-[hsl(var(--border))] bg-[hsl(var(--muted))]/10 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-[hsl(var(--primary))]/10 flex items-center justify-center">
                        <UserPlus class="h-6 w-6 text-[hsl(var(--primary))]" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ isEditingUser ? 'Таҳрири истифодабаранда' : t('admin.addUser') }}</h2>
                        <p class="text-[10px] text-[hsl(var(--muted-foreground))] uppercase font-bold tracking-widest mt-1">
                            {{ isEditingUser ? 'Маълумотро иваз кунед' : 'Маълумоти истифодабарандаи навро ворид кунед' }}
                        </p>
                    </div>
                </div>
                <form @submit.prevent="submitUserForm" class="p-6 space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('admin.username') }}</label>
                        <input v-model="form.username" type="text" class="h-11 w-full text-sm rounded-xl border border-[hsl(var(--border))] bg-transparent px-4 focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('common.name') }}</label>
                            <input v-model="form.first_name" type="text" class="h-11 w-full text-sm rounded-xl border border-[hsl(var(--border))] bg-transparent px-4 focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('common.lastName') }}</label>
                            <input v-model="form.last_name" type="text" class="h-11 w-full text-sm rounded-xl border border-[hsl(var(--border))] bg-transparent px-4 focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('common.email') }}</label>
                        <input v-model="form.email" type="email" class="h-11 w-full text-sm rounded-xl border border-[hsl(var(--border))] bg-transparent px-4 focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))] tracking-widest">{{ t('common.password') }} <span v-if="isEditingUser">(аз нав нависед ё холӣ гузоред)</span></label>
                        <input type="password" v-model="form.password" :required="!isEditingUser" class="flex h-11 w-full rounded-xl border border-[hsl(var(--border))] bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] uppercase font-bold text-[hsl(var(--muted-foreground))]">{{ t('admin.roles') }}</label>
                        <select v-model="form.role" class="h-9 w-full text-xs rounded-lg border border-[hsl(var(--border))] bg-transparent px-3 focus:outline-none focus:ring-1 focus:ring-[hsl(var(--ring))]">
                            <option value="hr_mgr">Мудири HR</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                    
                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="isUserModalOpen = false" class="flex-1 h-10 rounded-lg text-xs font-bold text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--muted))] uppercase tracking-widest transition-colors">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 h-10 rounded-lg text-xs font-bold bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] hover:bg-[hsl(var(--primary))]/90 disabled:opacity-50 uppercase tracking-widest shadow-lg shadow-[hsl(var(--primary))]/20 transition-all">
                            {{ t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div v-if="isUserModalOpen" @click="isUserModalOpen = false" class="fixed inset-0 z-40"></div>
    </AppLayout>
</template>
