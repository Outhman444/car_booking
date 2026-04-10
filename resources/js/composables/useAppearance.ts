import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') return;

    document.documentElement.classList.remove('light', 'dark');

    if (value === 'system') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.add('light');
        }
    } else {
        document.documentElement.classList.add(value);
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') return;

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const appearance = ref<Appearance>('system');

export function initializeTheme() {
    if (typeof window === 'undefined') return;

    const savedAppearance = (localStorage.getItem('appearance') as Appearance) || 'system';
    appearance.value = savedAppearance;
    setCookie('appearance', savedAppearance);
    updateTheme(savedAppearance);
}

export function useAppearance() {
    onMounted(() => {
        const savedAppearance = (localStorage.getItem('appearance') as Appearance) || 'system';
        appearance.value = savedAppearance;
        updateTheme(savedAppearance);

        // Optional listener for system theme changes:
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handleSystemChange = () => {
            if (appearance.value === 'system') {
                updateTheme('system');
            }
        };
        mediaQuery.addEventListener('change', handleSystemChange);
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem('appearance', value);
        setCookie('appearance', value);
        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
