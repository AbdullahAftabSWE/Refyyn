<script setup>
import {onBeforeUnmount, onMounted, ref} from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import AdminLayout from "@/Layouts/AdminLayout.vue"
import dayjs from 'dayjs'

const props = defineProps({
    changelogs: Array,
    boards: Array,
    sidebarStats: Object,
})

const contextMenu = ref({ show: false, x: 0, y: 0, changelogId: null })

const deleteChangelog = (changelog) => {
    if (confirm('Are you sure you want to delete this changelog?')) {
        router.delete(`/admin/dashboard/changelog/${changelog.id}`, {
            preserveScroll: true,
        })
    }
    contextMenu.value.show = false
}

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

// Strip HTML tags and get plain text excerpt
const getExcerpt = (html, maxLength = 100) => {
    const text = html?.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() || ''
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

const handleRowRightClick = (e, changelog) => {
    e.preventDefault()
    contextMenu.value = {
        show: true,
        x: e.clientX,
        y: e.clientY,
        changelogId: changelog.id,
        changelog: changelog,
    }
}

const closeContextMenu = () => {
    contextMenu.value.show = false
}

onMounted(() => {
    document.addEventListener('click', closeContextMenu)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', closeContextMenu)
    document.removeEventListener('contextmenu', closeContextMenu)
})
</script>

<template>
    <Head title="Changelog" />

    <AdminLayout>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="min-h-[100px] flex items-center justify-between px-8 py-6 border-b border-gray-100 flex-shrink-0">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Changelog</h1>
                </div>

                <Link
                    href="/admin/dashboard/changelog/create"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Changelog
                </Link>
            </header>

            <!-- Changelogs List -->
            <div class="flex-1 overflow-y-auto">
                <div v-if="changelogs.length === 0" class="flex items-center justify-center h-full text-gray-400">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-2" stroke-width="1">
                            <use href="/images/icons.svg#changelog" />
                        </svg>
                        <p class="text-lg font-medium mb-1">No changelogs yet</p>
                        <p class="text-sm">Create your first changelog to share updates with your users</p>
                    </div>
                </div>

                <table v-else class="w-full">
                    <thead class="sticky top-0 z-10 bg-white font-bold">
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="w-[15%] py-3 px-6 text-left text-xs text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="w-[15%] py-3 px-6 text-left text-xs text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="w-[50%] py-3 px-6 text-left text-xs text-gray-500 uppercase tracking-wider">
                                Changelog
                            </th>
                            <th class="w-[20%] py-3 px-6 text-left text-xs text-gray-500 uppercase tracking-wider">
                                Author
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="changelog in changelogs"
                            :key="changelog.id"
                            @click="router.visit(`/admin/dashboard/changelog/${changelog.slug}`)"
                            @contextmenu="handleRowRightClick($event, changelog)"
                            class="border-b border-gray-100 hover:bg-gray-50 transition-colors group cursor-pointer"
                        >
                            <!-- Status -->
                            <td class="py-5 px-6">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border-[0.25px] border-emerald-200 bg-emerald-50 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Published
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="text-xs py-5 px-6">
                                <span class="text-sm text-gray-600">{{ formatDate(changelog.created_at) }}</span>
                            </td>

                            <!-- Title -->
                            <td class="py-5 px-6">
                                <div class="text-base font-medium text-gray-900">{{ changelog.title }}</div>
                                <p class="text-sm text-gray-600 line-clamp-2">{{ getExcerpt(changelog.description) }}</p>
                            </td>

                            <!-- Author -->
                            <td class="py-5 px-6">
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="changelog.author?.avatar"
                                        :src="changelog.author.avatar"
                                        :alt="changelog.author.name"
                                        class="w-8 h-8 rounded-full object-cover"
                                    />
                                    <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                        {{ changelog.author?.name?.charAt(0) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">{{ changelog.author?.name }}</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Context Menu -->
        <div
            v-if="contextMenu.show"
            :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
            class="fixed z-50 bg-white rounded-lg shadow-lg border border-gray-200 py-1 min-w-max"
            @click.stop
            @contextmenu.stop
        >
            <button
                @click="deleteChangelog(contextMenu.changelog)"
                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </div>
    </AdminLayout>
</template>
