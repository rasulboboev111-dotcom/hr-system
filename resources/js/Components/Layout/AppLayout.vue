<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppSidebar from './AppSidebar.vue';
import AppHeader from './AppHeader.vue';

const isMobileOpen = ref(false);
const isDesktopCollapsed = ref(false);

const checkWindowSize = () => {
    if (window.innerWidth >= 768) {
        isMobileOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('resize', checkWindowSize);
    checkWindowSize();
});

onUnmounted(() => {
    window.removeEventListener('resize', checkWindowSize);
});

const toggleSidebar = () => {
    if (window.innerWidth < 768) {
        isMobileOpen.value = !isMobileOpen.value;
    } else {
        isDesktopCollapsed.value = !isDesktopCollapsed.value;
    }
};
</script>

<template>
    <div class="flex h-screen w-full bg-[hsl(var(--background))] overflow-hidden font-body">
        <AppSidebar 
            :isMobileOpen="isMobileOpen" 
            :isDesktopCollapsed="isDesktopCollapsed"
            @close-mobile="isMobileOpen = false" 
        />
        <div class="flex-1 flex flex-col h-full overflow-hidden min-w-0 transition-all duration-300">
            <AppHeader @toggle-sidebar="toggleSidebar" />
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[hsl(var(--background))] p-6 md:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
