import { computed } from 'vue'

export function useCarColor() {
  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'available':
        return 'text-green-600 bg-green-100 border-green-200'
      case 'reserved':
        return 'text-blue-600 bg-blue-100 border-blue-200'
      case 'rented':
        return 'text-amber-600 bg-amber-100 border-amber-200'
      case 'maintenance':
        return 'text-red-600 bg-red-100 border-red-200'
      case 'out_of_service':
        return 'text-gray-600 bg-gray-100 border-gray-200'
      default:
        return 'text-slate-600 bg-slate-100 border-slate-200'
    }
  }

  const getStatusDotColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'available':
        return 'bg-green-500'
      case 'reserved':
        return 'bg-blue-500'
      case 'rented':
        return 'bg-amber-500'
      case 'maintenance':
        return 'bg-red-500'
      default:
        return 'bg-slate-500'
    }
  }

  return {
    getStatusColor,
    getStatusDotColor
  }
}
