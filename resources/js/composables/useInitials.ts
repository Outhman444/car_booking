export function getInitials(fullName?: string): string {
    if (!fullName) return '??';

    const names = fullName.trim().split(/\s+/);

    if (names.length === 0) return '??';
    
    // If only one name, take the first two letters for more "professionality" as requested
    if (names.length === 1) {
        return names[0].substring(0, 2).toUpperCase();
    }

    // If multiple names, take the first letter of the first and last name
    const firstInitial = names[0].charAt(0);
    const lastInitial = names[names.length - 1].charAt(0);
    
    return `${firstInitial}${lastInitial}`.toUpperCase();
}

export function useInitials() {
    return { getInitials };
}
