<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Avatar from "@/Components/Avatar.vue";

const showUserMenu = ref(false);

const logout = () => {
    router.post('/logout');
};

const emit = defineEmits(['open-feedback-modal']);

const page = usePage();

// Get app name from shared props
const appName = computed(() => page.props.app?.name || 'Feedback');

// Get stats and filter state from page props
const sidebarStats = computed(() => page.props.sidebarStats || { totalCount: 0, unreadCount: 0 });
const boards = computed(() => page.props.boards || []);
const sortedBoards = computed(() => [...boards.value].sort((a, b) => a.id - b.id));
const currentFilter = computed(() => page.props.currentFilter);
const currentBoardId = computed(() => page.props.currentBoardId);

// Get current route for roadmap detection
const currentRoute = computed(() => page.props.currentRoute || '');

// Check if a nav item is selected
const isAllFeedbackSelected = computed(() => page.url === '/admin');
const isUnreadSelected = computed(() => page.url === '/admin/unread');
const isBoardSelected = (boardId: number) => currentBoardId.value === boardId && !currentFilter.value;
const isRoadmapSelected = computed(() => page.url.startsWith('/admin/roadmap'));
const isChangelogSelected = computed(() => page.url.startsWith('/admin/changelog'));
const isSettingsSelected = computed(() => page.url.startsWith('/admin/settings'));

// Nav item classes
const navItemClasses = (isSelected: boolean) => [
    'w-full flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-all duration-200',
    isSelected
        ? 'text-gray-900 bg-white border border-gray-200 shadow-sm font-medium'
        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 border border-transparent'
];
</script>

<template>
    <aside class="w-64 flex flex-col h-screen border-r border-gray-200 bg-[#FDFDFC] flex-shrink-0">
        <!-- Logo -->
        <div class="min-h-[100px] px-6 py-6 flex flex-row items-center gap-4 border-b border-gray-100">
            <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center text-white font-bold text-base flex-shrink-0">
                {{ appName.charAt(0).toUpperCase() }}
            </div>
            <div class="flex flex-col justify-center">
                <span class="text-lg font-semibold text-gray-900">{{ appName }}</span>
            </div>
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-4 font-medium items-center overflow-y-auto">
            <!-- New Feedback -->
            <button
                @click="emit('open-feedback-modal')"
                class="w-full flex justify-center items-center text-center mt-4 gap-2 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:scale-105 duration-200"
            >
                <svg class="w-5 h-5" stroke-width="1.5">
                    <use href="/images/icons.svg#plus" />
                </svg>
                Create Request
            </button>

            <!-- Overview -->
            <div class="text-xs text-gray-400 font-medium mt-3 mb-1 p-2">OVERVIEW</div>
            <div class="space-y-2">
                <Link
                    href="/admin"
                    :class="navItemClasses(isAllFeedbackSelected)"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#inbox" />
                    </svg>
                    <span class="flex-1 text-left">All Feedback</span>
                    <span class="text-xs text-gray-400">{{ sidebarStats.totalCount }}</span>
                </Link>
                <Link
                    href="/admin/unread"
                    :class="navItemClasses(isUnreadSelected)"
                >
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8m18 0-8.029-4.46a2 2 0 0 0-1.942 0L3 8m18 0-9 6.5L3 8"/>
                    </svg>
                    <span class="flex-1 text-left">Unread</span>
                    <span
                        v-if="sidebarStats.unreadCount > 0"
                        class="text-xs text-blue-600 font-medium"
                    >{{ sidebarStats.unreadCount }}</span>
                    <span v-else class="text-xs text-gray-400">0</span>
                </Link>
            </div>

            <!-- Boards -->
            <div class="text-xs text-gray-400 font-medium mt-3 mb-1 p-2">BOARDS</div>
            <div class="space-y-2">
                <Link
                    v-for="board in sortedBoards"
                    :key="board.id"
                    :href="`/admin/board/${board.slug}`"
                    :class="[...navItemClasses(isBoardSelected(board.id)), 'group']"
                >
                    <span
                        class="w-2 h-2 rounded-full ring-2 group-hover:ring-4 ring-opacity-20 transition-all"
                        :class="$boardColorClasses[board.color] || 'bg-gray-500 ring-gray-500'"
                    ></span>
                    <span class="flex-1 text-left">{{ board.name }}</span>
                    <span class="text-xs text-gray-400">{{ board.feedback_count }}</span>
                </Link>
            </div>

            <!-- Admin -->
            <div class="text-xs text-gray-400 font-medium mt-3 mb-1 p-2">ADMIN</div>
            <div class="space-y-2">
                <Link
                    href="/admin/roadmap"
                    :class="navItemClasses(isRoadmapSelected)"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#roadmap" />
                    </svg>
                    <span class="flex-1 text-left">Roadmap</span>
                </Link>
                <Link
                    href="/admin/changelog"
                    :class="navItemClasses(isChangelogSelected)"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#changelog" />
                    </svg>
                    <span class="flex-1 text-left">Changelog</span>
                </Link>
                <Link
                    href="/feedback"
                    :class="navItemClasses(false)"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#dashboard" />
                    </svg>
                    <span class="flex-1 text-left">Public Board</span>
                </Link>
                <Link
                    href="/admin/settings"
                    :class="navItemClasses(isSettingsSelected)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="flex-1 text-left">Settings</span>
                </Link>
            </div>
        </nav>

        <!-- Admin Control -->
        <div class="p-2 border-t border-gray-100 relative">
            <button
                @click.stop="showUserMenu = !showUserMenu"
                class="w-full flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-100 transition-colors"
            >
                <Avatar :name="$page.props.auth.user?.name" size="4"/>
                <div class="flex flex-col flex-1 text-left">
                    <span v-if="$page.props.auth.user"
                          class="text-sm text-gray-600 font-medium">{{ $page.props.auth.user.name }}</span>
                    <span v-if="$page.props.auth.user" class="text-xs text-gray-400 font-medium">{{
                            $page.props.auth.user.email
                        }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                <span v-if="!$page.props.auth.user" class="text-sm text-gray-700">NOT SIGNED IN</span>
            </button>

            <!-- User Menu Dropdown -->
            <div
                v-if="showUserMenu"
                class="absolute bottom-full left-2 right-2 mb-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50"
            >
                <button
                    @click="logout"
                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </div>

            <!-- Click outside to close -->
            <div v-if="showUserMenu" class="fixed inset-0 z-40" @click="showUserMenu = false"></div>
        </div>
    </aside>
</template>
