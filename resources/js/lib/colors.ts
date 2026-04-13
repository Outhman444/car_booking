export const colorHexMap: Record<string, string> = {
    white: '#FFFFFF',
    black: '#111827',
    silver: '#D1D5DB',
    gray: '#6B7280',
    red: '#DC2626',
    blue: '#2563EB',
    green: '#16A34A',
    yellow: '#EAB308',
    orange: '#EA580C',
    brown: '#92400E',
};

export const getCarColorHex = (colorName: string | undefined | null) => {
    if (!colorName) return '#ccc';
    return colorHexMap[colorName.toLowerCase()] || '#ccc';
};
