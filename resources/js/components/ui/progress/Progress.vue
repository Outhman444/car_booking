<script setup lang="ts">
import { type HTMLAttributes, computed } from "vue"
import {
  ProgressIndicator,
  ProgressRoot,
  type ProgressRootProps,
} from "reka-ui"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<ProgressRootProps & { class?: HTMLAttributes["class"] }>(),
  {
    modelValue: 0,
  },
)

const transformedValue = computed(() => {
  if (typeof props.modelValue === "number") return props.modelValue
  return 0
})
</script>

<template>
  <ProgressRoot
    v-bind="props"
    :class="
      cn(
        'relative h-4 w-full overflow-hidden rounded-full bg-secondary',
        props.class,
      )
    "
  >
    <ProgressIndicator
      class="h-full w-full flex-1 bg-primary transition-all"
      :style="`transform: translateX(-${100 - transformedValue}%)`"
    />
  </ProgressRoot>
</template>
