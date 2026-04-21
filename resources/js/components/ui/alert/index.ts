import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Alert } from "./Alert.vue"
export { default as AlertDescription } from "./AlertDescription.vue"
export { default as AlertTitle } from "./AlertTitle.vue"

export const alertVariants = cva(
  "relative w-full rounded-lg border px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current",
  {
    variants: {
      variant: {
        default: "bg-card text-card-foreground shadow-sm shadow-slate-200/50",
        destructive:
          "text-destructive bg-card border-none shadow-xl shadow-rose-200/20 [&>svg]:text-current *:data-[slot=alert-description]:text-destructive/90",
        success:
          "bg-emerald-50 text-emerald-900 border-emerald-100 shadow-xl shadow-emerald-200/20 ring-1 ring-emerald-200/50 [&>svg]:text-emerald-600 *:data-[slot=alert-description]:text-emerald-700/80",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)

export type AlertVariants = VariantProps<typeof alertVariants>
