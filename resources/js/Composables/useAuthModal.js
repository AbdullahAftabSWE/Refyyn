import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const showAuthModal = ref(false)
const authMode = ref('login')

export function useAuthModal() {
    const page = usePage()
    const isAuthenticated = computed(() => !!page.props.auth?.user)

    const requireAuth = () => {
        if (!isAuthenticated.value) {
            showAuthModal.value = true
            return false
        }
        return true
    }

    return {
        showAuthModal,
        authMode,
        isAuthenticated,
        requireAuth,
    }
}
