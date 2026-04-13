<script setup lang="ts">
import { HelpCircle, Sparkles } from 'lucide-vue-next';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';

interface Props {
  content: string;
  title?: string;
  side?: 'top' | 'right' | 'bottom' | 'left';
}

withDefaults(defineProps<Props>(), {
  title: 'Expert Guidance',
  side: 'top',
});
</script>

<template>
  <TooltipProvider :delay-duration="0">
    <Tooltip>
      <TooltipTrigger as-child>
        <button 
          type="button" 
          class="p-1 rounded-full hover:bg-slate-100 text-slate-300 hover:text-primary transition-all focus:outline-none focus:ring-0 select-none group/help"
          aria-label="Expert Guidance"
        >
          <HelpCircle class="size-3.5 group-hover/help:scale-110 transition-transform" />
        </button>
      </TooltipTrigger>
      <TooltipContent 
        :side="side"
        :side-offset="8"
        class="z-50 max-w-[calc(100vw-40px)] sm:max-w-[320px] p-0 bg-white rounded-[1.75rem] border border-slate-100 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] animate-in fade-in-0 zoom-in-95"
      >
        <div class="relative overflow-hidden p-5 sm:p-6">
          <!-- Premium Subtle Background Gradient -->
          <div class="absolute inset-0 bg-gradient-to-br from-primary/[0.02] to-transparent pointer-events-none"></div>
          
          <div class="relative space-y-3.5">
            <div class="flex items-center gap-2.5">
              <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                <Sparkles class="size-3" />
              </div>
              <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-900">{{ title }}</span>
            </div>
            
            <p class="text-[11px] sm:text-xs font-bold leading-relaxed text-slate-500/90 tracking-tight">
              {{ content }}
            </p>
          </div>
        </div>
        
        <!-- Bottom Decorative Bar -->
        <div class="h-1.5 w-full bg-slate-50 border-t border-slate-100/50 rounded-b-[1.75rem]"></div>
      </TooltipContent>
    </Tooltip>
  </TooltipProvider>
</template>
