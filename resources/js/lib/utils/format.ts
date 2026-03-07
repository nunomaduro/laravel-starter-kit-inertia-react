import { format, formatDistanceToNow, isValid, parseISO } from 'date-fns';

function toDate(date: Date | string): Date {
    if (typeof date === 'string') {
        const parsed = parseISO(date);

        return isValid(parsed) ? parsed : new Date(date);
    }

    return date;
}

/**
 * Format a date in a locale-aware way.
 * @example formatDate(new Date('2024-03-15')) // "Mar 15, 2024"
 */
export function formatDate(date: Date | string, fmt = 'MMM d, yyyy'): string {
    return format(toDate(date), fmt);
}

/**
 * Format a date as relative time.
 * @example formatRelativeTime(subHours(new Date(), 2)) // "2 hours ago"
 */
export function formatRelativeTime(date: Date | string): string {
    return formatDistanceToNow(toDate(date), { addSuffix: true });
}

/**
 * Format a number as currency.
 * @example formatCurrency(1234.5) // "$1,234.50"
 */
export function formatCurrency(amount: number, currency = 'USD', locale?: string): string {
    return new Intl.NumberFormat(locale, { style: 'currency', currency }).format(amount);
}

/**
 * Format a number with compact notation.
 * @example formatNumber(1200) // "1.2K"
 * @example formatNumber(3_400_000) // "3.4M"
 */
export function formatNumber(n: number, options?: Intl.NumberFormatOptions): string {
    return new Intl.NumberFormat(undefined, { notation: 'compact', maximumFractionDigits: 1, ...options }).format(n);
}

/**
 * Format bytes as human-readable file size.
 * @example formatBytes(2_400_000) // "2.4 MB"
 */
export function formatBytes(bytes: number, decimals = 1): string {
    if (bytes === 0) {
        return '0 B';
    }
    const k = 1024;
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${units[i]}`;
}
