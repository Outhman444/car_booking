import { computed } from 'vue'

export function useLanguage() {
  const currentLocale = computed(() => 'fr')
  
  const isRtl = computed(() => false)

  const changeLanguage = async (newLocale: string) => {
    // No-op as we only use French
  }

  // A dummy 't' function that just returns the key
  const t = (key: string) => {
    return key
  }

  return {
    currentLocale,
    isRtl,
    changeLanguage,
    t
  }
}
