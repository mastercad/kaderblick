// utils/date.ts

/**
 * Converts a date string (ISO or Date object) to 'YYYY-MM-DD' format for HTML input[type="date"].
 * Returns empty string if falsy or invalid.
 */
export function toDateInputValue(date: string | Date | null | undefined): string {
    if (!date) return '';
    let d: Date;
    if (typeof date === 'string') {
        // Accepts 'YYYY-MM-DD', 'YYYY-MM-DDTHH:mm:ss', etc.
        d = new Date(date);
        if (isNaN(d.getTime())) {
            // Try to parse as 'YYYY-MM-DD' (no time zone)
            const match = date.match(/^\d{4}-\d{2}-\d{2}$/);
            if (match) return date;
            return '';
        }
    } else if (date instanceof Date) {
        d = date;
    } else {
        return '';
    }
    // Format as YYYY-MM-DD
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
