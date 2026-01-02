<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const show = ref(false)
const message = ref('')
const type = ref('success') // success, error, warning
const toastKey = ref(0) // Key to restart animation
let timeout = null
let lastShownMessage = null // Track last shown message to prevent re-triggering

// Watch for flash messages from Laravel
watch(() => page.props.flash, (flash) => {
    const msg = flash?.success || flash?.error
    const msgType = flash?.success ? 'success' : 'error'

    // Only trigger if there's a message and it's different from the last one shown
    if (msg && msg !== lastShownMessage) {
        triggerToast(msg, msgType)
    }
}, { deep: true })

const triggerToast = (msg, toastType) => {
    lastShownMessage = msg
    message.value = msg
    type.value = toastType
    toastKey.value++ // Increment key to restart animation
    show.value = true

    // Reset timer if a new message comes in quickly
    if (timeout) clearTimeout(timeout)

    timeout = setTimeout(() => {
        show.value = false
    }, 5000) // 5 seconds
}

// Styles based on type
const styles = {
    success: 'bg-white border-l-4 border-emerald-500 text-emerald-600',
    error:   'bg-white border-l-4 border-red-500 text-red-900',
    warning: 'bg-white border-l-4 border-yellow-500 text-yellow-900',
}

const icons = {
    success: `<svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`,
    error:   `<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`,
}
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-full max-w-sm px-4">
            <div
                :class="['shadow-lg rounded-lg overflow-hidden relative', styles[type]]"
                class="pointer-events-auto ring-1 ring-black ring-opacity-5"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0" v-html="icons[type] || icons.success"></div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium">{{ message }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :key="toastKey" class="absolute bottom-0 left-0 h-1 bg-current opacity-20 w-full origin-left animate-life"></div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes life {
    0% { transform: scaleX(1); }
    100% { transform: scaleX(0); }
}

.animate-life {
    animation: life 5000ms linear forwards;
}
</style>
