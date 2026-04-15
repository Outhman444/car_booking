import { computed } from 'vue'

export function useReservationHelpers() {
  const calculateTotalDays = (startDate: string | Date, endDate: string | Date): number => {
    const start = new Date(startDate)
    const end = new Date(endDate)
    const diffTime = Math.abs(end.getTime() - start.getTime())
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays > 0 ? diffDays : 1
  }

  const calculateTotalPrice = (dailyRate: number, totalDays: number, discount: number = 0): number => {
    const total = dailyRate * totalDays
    const discountAmount = total * (discount / 100)
    return total - discountAmount
  }

  const formatCurrency = (amount: number, currency: string = 'USD', locale: string = 'en-US'): string => {
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: currency,
    }).format(amount)
  }

  const isExpired = (endDate: string | Date): boolean => {
    return new Date(endDate).getTime() < new Date().getTime()
  }

  const getStatusBadge = (status: string) => {
    const statusMap: Record<string, { label: string, variant: string }> = {
      'pending': { label: 'Pending', variant: 'warning' },
      'confirmed': { label: 'Confirmed', variant: 'success' },
      'active': { label: 'Active', variant: 'info' },
      'completed': { label: 'Completed', variant: 'primary' },
      'cancelled': { label: 'Cancelled', variant: 'destructive' },
    }
    return statusMap[status.toLowerCase()] || { label: 'Unknown', variant: 'outline' }
  }

  return {
    calculateTotalDays,
    calculateTotalPrice,
    formatCurrency,
    isExpired,
    getStatusBadge
  }
}
