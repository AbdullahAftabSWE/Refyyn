<script setup>
import Navbar from '@/Pages/Member/Navbar.vue'
import AuthModal from '@/Pages/Auth/AuthModal.vue'
import { useAuthModal } from '@/Composables/useAuthModal'

const { showAuthModal, authMode } = useAuthModal()
</script>

<template>
    <div class="h-screen overflow-hidden bg-white flex flex-col">
        <Navbar />

        <main class="flex-1 overflow-y-auto">
            <slot />
        </main>

        <AuthModal
            v-model:show="showAuthModal"
            v-model:mode="authMode"
        />
    </div>
</template>
