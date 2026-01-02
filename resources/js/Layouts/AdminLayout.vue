<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from "@/Pages/Admin/Sidebar.vue";
import Toast from "@/Components/Toast.vue";
import FeedbackModal from "@/Pages/Member/FeedbackModal.vue";

const page = usePage();

const showFeedbackModal = ref(false);

const boards = computed(() => page.props.boards || []);
const statuses = computed(() => page.props.statuses || []);
const feedbacks = computed(() => page.props.feedbacks || []);

const openFeedbackModal = () => {
    showFeedbackModal.value = true;
};
</script>

<template>
    <div class="flex min-h-screen max-h-screen bg-white font-sans overflow-hidden relative">
        <Toast />
        <!-- Sidebar -->
        <Sidebar @open-feedback-modal="openFeedbackModal"/>

        <!-- Main Content -->
        <main class="flex-1 flex overflow-hidden min-w-0">
            <slot />
        </main>

        <!-- Feedback Modal -->
        <FeedbackModal
            :show="showFeedbackModal"
            @update:show="showFeedbackModal = $event"
            :boards="boards"
            :statuses="statuses"
            :feedbacks="feedbacks"
            :is-admin="true"
        />
    </div>
</template>

