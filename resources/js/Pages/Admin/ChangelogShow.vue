<script setup>
import {router, Link, Head} from '@inertiajs/vue3'
import AdminLayout from "@/Layouts/AdminLayout.vue"
import dayjs from 'dayjs'

const props = defineProps({
    changelog: Object,
    boards: Array,
    sidebarStats: Object,
})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const deleteChangelog = () => {
    if (confirm('Are you sure you want to delete this changelog?')) {
        router.delete(`/admin/dashboard/changelog/${props.changelog.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.visit('/admin/dashboard/changelog')
            },
        })
    }
}

const goBack = () => {
    router.visit('/admin/dashboard/changelog')
}
</script>

<template>
    <AdminLayout>
        <Head :title="changelog.title"/>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="min-h-[90px] flex items-center justify-between px-8 py-6 border-b border-gray-100 flex-shrink-0">
                <div>
                    <button
                        @click="goBack"
                        class="text-sm text-gray-600 hover:text-gray-900 mb-2 inline-flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Changelogs
                    </button>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ changelog.title }}</h1>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="`/admin/dashboard/changelog/${changelog.slug}/edit`"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Edit
                    </Link>
                    <button
                        @click="deleteChangelog"
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                    >
                        Delete
                    </button>
                </div>
            </header>

            <!-- Content area -->
            <div class="flex-1 overflow-y-auto px-8 py-6">
                <article class="max-w-4xl mx-auto">
                    <!-- Published date & author -->
                    <div class="flex items-center gap-3 mb-8">
                        <img
                            v-if="changelog.author?.avatar"
                            :src="changelog.author.avatar"
                            :alt="changelog.author.name"
                            class="w-10 h-10 rounded-full object-cover"
                        />
                        <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                            {{ changelog.author?.name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ changelog.author?.name }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(changelog.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Hero image -->
                    <div v-if="changelog.image" class="mb-8 rounded-lg overflow-hidden">
                        <img :src="`/storage/${changelog.image}`" :alt="changelog.title" class="w-full h-auto" />
                    </div>

                    <!-- Rich text content -->
                    <div class="prose prose-gray max-w-none" v-html="changelog.description"></div>
                </article>
            </div>
        </div>
    </AdminLayout>
</template>
