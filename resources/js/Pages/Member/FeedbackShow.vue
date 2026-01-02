<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import MemberLayout from "@/Layouts/MemberLayout.vue"
import { useAuthModal } from '@/Composables/useAuthModal'

const props = defineProps({
    feedback: Object,
    upvoteCount: Number,
    upvoters: Array,
    hasUpvoted: Boolean,
})

const { requireAuth } = useAuthModal()

const mainVoted = ref(props.hasUpvoted)
const currentUpvoteCount = ref(props.upvoteCount)
const newComment = ref('')
const replyContent = ref('')
const replyingToId = ref(null)
const isSubmittingComment = ref(false)
const isUpvoting = ref(false)
const showUpvoteAnimation = ref(false)

const goBack = () => {
    router.visit('/feedback')
}

const toggleMainVote = async () => {
    if (!requireAuth()) return
    if (isUpvoting.value) return
    isUpvoting.value = true

    try {
        const response = await fetch(`/feedback/${props.feedback.id}/upvote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        })

        const data = await response.json()

        if (data.success) {
            mainVoted.value = data.upvoted
            currentUpvoteCount.value = data.upvoteCount

            if (data.upvoted) {
                showUpvoteAnimation.value = true
                setTimeout(() => {
                    showUpvoteAnimation.value = false
                }, 600)
            }
        }
    } catch (error) {
        console.error('Failed to toggle upvote:', error)
    } finally {
        isUpvoting.value = false
    }
}

const totalComments = computed(() => {
    if (!props.feedback.comments) return 0
    let count = props.feedback.comments.length
    props.feedback.comments.forEach(c => {
        if (c.replies) count += c.replies.length
    })
    return count
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getRelativeTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now - date) / 1000)

    if (diffInSeconds < 60) return 'just now'
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`
    return formatDate(dateString)
}


const submitComment = () => {
    if (!requireAuth()) return
    if (!newComment.value.trim() || isSubmittingComment.value) return
    isSubmittingComment.value = true

    router.post(`/feedback/${props.feedback.id}/comment`, {
        content: newComment.value,
    }, {
        onSuccess: () => {
            newComment.value = ''
        },
        onFinish: () => {
            isSubmittingComment.value = false
        }
    })
}

const submitReply = (parentId) => {
    if (!requireAuth()) return
    if (!replyContent.value.trim()) return

    router.post(`/feedback/${props.feedback.id}/comment`, {
        content: replyContent.value,
        parent_id: parentId,
    }, {
        onSuccess: () => {
            replyContent.value = ''
            replyingToId.value = null
        }
    })
}
</script>

<template>
    <Head :title="feedback.title" />

    <MemberLayout>

        <div class="min-h-screen bg-[#FDFDFC]">
            <!-- Header -->
            <main class="max-w-5xl mx-auto px-6 py-4">
                <a @click.prevent="goBack" href="#" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition-colors py-1 pl-0 pr-3 rounded-md group">
                    <div class="p-1 rounded-md group-hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </div>
                    <span>Back to Feedback</span>
                </a>
            </main>

            <!-- Main Content -->
            <div class="max-w-5xl mx-auto px-6 pb-4">
                <div class="flex gap-10">
                    <!-- Left Content -->
                    <div class="flex-1 max-w-2xl px-8 py-4 border border-gray-200 shadow-sm rounded-xl bg-white">
                        <!-- Status & Upvote Row -->
                        <div class="flex items-center justify-between mb-4">
                            <span
                                v-if="feedback.status"
                                :class="['inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-md', $statusClasses[feedback.status?.color] || 'bg-gray-50 text-gray-700']"
                            >
                                <span :class="['w-1.5 h-1.5 rounded-full', $statusDotColors[feedback.status?.color] || 'bg-gray-500']"></span>
                                {{ feedback.status.name }}
                            </span>

                            <!-- Upvote Button -->
                            <button
                                @click="toggleMainVote"
                                :disabled="isUpvoting"
                                :class="[
                                    'inline-flex items-center gap-2 px-4 py-2 text-sm border rounded-lg transition-all duration-200 relative overflow-hidden',
                                    mainVoted
                                        ? 'border-gray-900 bg-gray-900 text-white'
                                        : 'border-gray-300 hover:border-gray-400 text-gray-700',
                                    isUpvoting ? 'opacity-70 cursor-not-allowed' : ''
                                ]"
                            >
                                <svg
                                    :class="[
                                        'w-5 h-5 transition-transform duration-300',
                                        showUpvoteAnimation ? 'animate-upvote-bounce' : ''
                                    ]"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    :fill="mainVoted ? 'currentColor' : 'none'"
                                >
                                    <use href="/images/icons.svg#upvote" />
                                </svg>
                                <span class="font-medium">{{ mainVoted ? 'Upvoted' : 'Upvote' }}</span>
                                <span
                                    :class="[
                                        'transition-all duration-300',
                                        mainVoted ? 'text-gray-400' : 'text-gray-400',
                                        showUpvoteAnimation ? 'scale-125 text-emerald-400' : ''
                                    ]"
                                >{{ currentUpvoteCount }}</span>
                                <span
                                    v-if="showUpvoteAnimation"
                                    class="absolute inset-0 bg-emerald-400/20 animate-upvote-ripple rounded-lg"
                                ></span>
                            </button>
                        </div>

                        <!-- Title -->
                        <h1 class="text-2xl font-bold text-gray-900 leading-tight mb-2">
                            {{ feedback.title }}
                        </h1>

                        <!-- Date -->
                        <p class="text-sm text-gray-400 mb-6">{{ formatDate(feedback.created_at) }}</p>

                        <!-- Description -->
                        <p class="text-gray-600 leading-relaxed mb-8">
                            {{ feedback.description }}
                        </p>

                        <!-- Comment Input -->
                        <div class="mb-8">
                            <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center gap-2">
                                Comment
                            </h3>
                            <textarea
                                v-model="newComment"
                                placeholder="Add to the discussion"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all"
                                rows="3"
                            ></textarea>
                            <div class="flex justify-end mt-3">
                                <button
                                    @click="submitComment"
                                    :disabled="!newComment.trim() || isSubmittingComment"
                                    class="px-4 py-1.5 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ isSubmittingComment ? 'Posting...' : 'Comment' }}
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-black/5 my-10"></div>

                        <!-- Discussion Section -->
                        <div class="mt-10">
                            <div class="flex items-center gap-2 mb-6 text-gray-800">
                                <svg class="w-6 h-6" stroke-width="2" stroke="currentColor" fill="none">
                                    <use href="/images/icons.svg#comment" />
                                </svg>
                                <h2 class="text-xl font-semibold">Discussion</h2>
                                <span class="text-sm text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ totalComments }}</span>
                            </div>

                            <!-- Comments List -->
                            <div v-if="feedback.comments && feedback.comments.length > 0" class="divide-y divide-gray-100">
                                <div v-for="comment in feedback.comments" :key="comment.id" class="py-6 first:pt-0">
                                    <!-- Main Comment -->
                                    <div class="flex gap-3">
                                        <img
                                            v-if="comment.user?.avatar"
                                            :src="comment.user.avatar"
                                            :alt="comment.user.name"
                                            class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                        />
                                        <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-medium text-sm flex-shrink-0">
                                            {{ comment.user?.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-900 text-sm">{{ comment.user?.name || 'Unknown' }}</span>
                                                <span v-if="comment.internal" class="px-1.5 py-0.5 text-[10px] font-semibold text-white bg-blue-500 rounded">TEAM</span>
                                                <span class="text-xs text-gray-400">{{ getRelativeTime(comment.created_at) }}</span>
                                            </div>
                                            <p class="text-gray-700 text-sm leading-relaxed mt-1">{{ comment.content }}</p>
                                            <button
                                                @click="replyingToId = replyingToId === comment.id ? null : comment.id"
                                                class="text-xs text-gray-400 hover:text-gray-600 mt-2"
                                            >
                                                Reply
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Replies with vertical line -->
                                    <div v-if="(comment.replies && comment.replies.length > 0) || replyingToId === comment.id" class="ml-4 mt-3 border-l-2 border-gray-200 pl-7">
                                        <!-- Reply Input -->
                                        <div v-if="replyingToId === comment.id" class="flex gap-2 mb-4">
                                            <input
                                                v-model="replyContent"
                                                type="text"
                                                placeholder="Write a reply..."
                                                @keyup.enter="submitReply(comment.id)"
                                                class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                            />
                                            <button @click="replyingToId = null; replyContent = ''" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                                            <button
                                                @click="submitReply(comment.id)"
                                                :disabled="!replyContent.trim()"
                                                class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 disabled:opacity-50"
                                            >
                                                Reply
                                            </button>
                                        </div>

                                        <!-- Replies -->
                                        <div v-for="(reply, rIndex) in comment.replies" :key="reply.id" :class="rIndex > 0 ? 'mt-4' : ''">
                                            <div class="flex gap-3">
                                                <img
                                                    v-if="reply.user?.avatar"
                                                    :src="reply.user.avatar"
                                                    :alt="reply.user.name"
                                                    class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                                />
                                                <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-medium text-sm flex-shrink-0">
                                                    {{ reply.user?.name?.charAt(0).toUpperCase() || '?' }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-gray-900 text-sm">{{ reply.user?.name || 'Unknown' }}</span>
                                                        <span v-if="reply.internal" class="px-1.5 py-0.5 text-[10px] font-semibold text-white bg-blue-500 rounded">TEAM</span>
                                                        <span class="text-xs text-gray-400">{{ getRelativeTime(reply.created_at) }}</span>
                                                    </div>
                                                    <p class="text-gray-700 text-sm leading-relaxed mt-1">{{ reply.content }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-8 text-gray-400">
                                No comments yet
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div style="width: 280px" class="flex-shrink-0">
                        <div class="sticky top-8 border border-gray-200 rounded-xl shadow-sm bg-gray-50/50 p-4 space-y-4">

                            <!-- Requested By -->
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 block">Requested By</span>
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="feedback.author?.avatar"
                                        :src="feedback.author.avatar"
                                        :alt="feedback.author.name"
                                        class="w-9 h-9 rounded-full object-cover border border-gray-100"
                                    />
                                    <div v-else class="w-9 h-9 rounded-full bg-gray-300 flex items-center justify-center text-white font-semibold border border-gray-100">
                                        {{ feedback.author?.name?.charAt(0).toUpperCase() || '?' }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm leading-tight">{{ feedback.author?.name || 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">{{ getRelativeTime(feedback.created_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200"></div>

                            <!-- Board -->
                            <div v-if="feedback.board">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2 block">Board</span>
                                <div class="flex items-center gap-2 text-sm font-medium px-3 py-2.5 border rounded-lg transition-colors"
                                    :class="$statusClasses[feedback.board.color]">
                                    <span :class="['w-2 h-2 rounded-full', $statusDotColors[feedback.board.color] || 'bg-gray-500']"></span>
                                    <span>{{ feedback.board.name }}</span>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div v-if="feedback.tags && feedback.tags.length > 0">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2 block">Tags</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="tag in feedback.tags"
                                        :key="tag.id"
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded"
                                    >
                                        #{{ tag.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="border-t border-gray-200"></div>

                            <!-- Upvoters -->
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 block">Upvoters ({{ currentUpvoteCount }})</span>
                                <div v-if="upvoters && upvoters.length > 0" class="space-y-2">
                                    <div v-for="voter in upvoters.slice(0, 6)" :key="voter.id" class="flex items-center gap-3">
                                        <img
                                            v-if="voter.avatar"
                                            :src="voter.avatar"
                                            :alt="voter.name"
                                            class="w-8 h-8 rounded-full object-cover border border-gray-100"
                                        />
                                        <div
                                            v-else
                                            class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-white font-semibold text-xs border border-gray-100"
                                        >
                                            {{ voter.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>
                                        <span class="text-sm text-gray-600">{{ voter.name }}</span>
                                    </div>
                                    <p v-if="currentUpvoteCount > 6" class="text-xs text-gray-400 pt-1">
                                        +{{ currentUpvoteCount - 6 }} more
                                    </p>
                                </div>
                                <p v-else class="text-sm text-gray-400">No upvotes yet</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<style scoped>
@keyframes upvote-bounce {
    0%, 100% { transform: translateY(0) scale(1); }
    25% { transform: translateY(-4px) scale(1.2); }
    50% { transform: translateY(-2px) scale(1.1); }
    75% { transform: translateY(-1px) scale(1.05); }
}

@keyframes upvote-ripple {
    0% { transform: scale(0); opacity: 1; }
    100% { transform: scale(2); opacity: 0; }
}

.animate-upvote-bounce {
    animation: upvote-bounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.animate-upvote-ripple {
    animation: upvote-ripple 0.6s ease-out forwards;
}
</style>
