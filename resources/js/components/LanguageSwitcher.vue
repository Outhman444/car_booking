<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
import { Languages } from 'lucide-vue-next'

const { currentLocale, changeLanguage } = useLanguage()

const languages = [
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'ar', name: 'العربية', flag: '🇸🇦' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
]

const currentLang = languages.find(l => l.code === currentLocale.value) || languages[0]
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" class="w-9 h-9 rounded-full">
        <Languages class="h-4 w-4" />
        <span class="sr-only">Switch language</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuItem
        v-for="lang in languages"
        :key="lang.code"
        @click="changeLanguage(lang.code)"
        class="cursor-pointer flex items-center gap-2"
        :class="{ 'bg-slate-100 font-medium': lang.code === currentLocale }"
      >
        <span class="text-lg">{{ lang.flag }}</span>
        <span>{{ lang.name }}</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
