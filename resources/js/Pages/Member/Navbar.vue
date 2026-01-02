<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthModal from '@/Pages/Auth/AuthModal.vue'
import Avatar from '@/Components/Avatar.vue'

const page = usePage()
const showAuthModal = ref(false)
const authMode = ref('login')
const contextMenu = ref({ show: false, x: 0, y: 0 })

const isActive = (route) => page.url.startsWith(route)

const navItemClasses = (active) => [
    'flex items-center gap-2 text-sm font-medium transition-colors',
    active ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900'
]

const handleUserClick = (event) => {
    const rect = event.currentTarget.getBoundingClientRect()
    contextMenu.value.x = rect.left
    contextMenu.value.y = rect.bottom + 4 // 4px gap below the element
    contextMenu.value.show = !contextMenu.value.show
}

const closeContextMenu = () => {
    contextMenu.value.show = false
}

const logout = () => {
    router.post('/logout')
    closeContextMenu()
}

onMounted(() => {
    document.addEventListener('click', closeContextMenu)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', closeContextMenu)
})
</script>

<template>
    <nav class="border-b border-gray-200 px-6 py-4 flex-shrink-0">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center text-white font-bold text-base">
                    {{ $page.props.app.name?.charAt(0).toUpperCase() || 'R'}}
                </div>
                <span class="text-xl font-semibold text-gray-900">{{ $page.props.app.name || 'Organization' }}</span>
            </div>

            <!-- Nav Links -->
            <div class="flex items-center gap-8">
                <Link
                    href="/feedback"
                    :class="navItemClasses(isActive('/feedback'))"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#inbox" />
                    </svg>
                    Feedback
                </Link>
                <Link
                    href="/roadmap"
                    :class="navItemClasses(isActive('/roadmap'))"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#roadmap" />
                    </svg>
                    Roadmap
                </Link>
                <Link
                    href="/changelog"
                    :class="navItemClasses(isActive('/changelog'))"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#changelog" />
                    </svg>
                    Changelog
                </Link>
            </div>

            <!-- Auth Section -->
            <div v-if="!$page.props.auth.user" class="flex items-center gap-3">
                <button
                    @click="showAuthModal = true; authMode = 'login'"
                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors"
                >
                    Log In
                </button>
                <button
                    @click="showAuthModal = true; authMode = 'signup'"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                    Sign Up
                </button>
            </div>
            <div v-else class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user?.is_admin"
                    href="/admin/dashboard"
                    class="flex items-center border border-gray-300 p-2 rounded-md hover:shadow-sm gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors"
                >
                    <svg class="w-5 h-5" stroke-width="1.5">
                        <use href="/images/icons.svg#admin-view" />
                    </svg>
                    Dashboard
                </Link>
                <div
                    @click.stop="handleUserClick"
                    class="flex items-center gap-3 px-2 cursor-pointer rounded-lg transition-colors py-1"
                >
                    <Avatar :name="$page.props.auth.user?.name" size="5" />
                </div>
            </div>
        </div>
    </nav>

    <!-- Context Menu for Logout -->
    <div
        v-if="contextMenu.show"
        :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
        class="fixed z-50 bg-white rounded-lg shadow-lg border border-gray-200 py-1 min-w-max"
        @click.stop
        @contextmenu.stop
    >
        <button
            @click="logout"
            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Logout
        </button>
    </div>

    <!-- Auth Modal -->
    <AuthModal
        :show="showAuthModal"
        :mode="authMode"
        @update:show="showAuthModal = $event"
        @update:mode="authMode = $event"
    />
</template>
