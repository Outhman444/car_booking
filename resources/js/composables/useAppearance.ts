import { onMounted, ref } from 'vue';

type Appearance = 'light';

export function updateTheme() {
    if (typeof window === 'undefined') return;

    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('light');
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') return;

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const appearance = ref<Appearance>('light');

export function initializeTheme() {
    if (typeof window === 'undefined') return;

    appearance.value = 'light';
    setCookie('appearance', 'light');
    updateTheme();
}

export function useAppearance() {
    onMounted(() => {
        appearance.value = 'light';
        updateTheme();
    });

    function updateAppearance() {
        appearance.value = 'light';
        localStorage.setItem('appearance', 'light');
        setCookie('appearance', 'light');
        updateTheme();
    }

    return {
        appearance,
        updateAppearance,
    };
}
