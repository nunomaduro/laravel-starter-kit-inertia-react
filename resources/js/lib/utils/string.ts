/**
 * Truncate a string, appending a suffix if it exceeds the given length.
 * @example truncate("Hello World", 7) // "Hello W…"
 */
export function truncate(str: string, length: number, suffix = '…'): string {
    if (str.length <= length) {
        return str;
    }

    return str.slice(0, length) + suffix;
}

/**
 * Convert a string to a URL-safe slug.
 * @example slugify("Hello World!") // "hello-world"
 */
export function slugify(str: string): string {
    return str
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

/**
 * Extract initials from a name.
 * @example initials("John Doe") // "JD"
 * @example initials("Jane Mary Smith", 2) // "JS"
 */
export function initials(name: string, maxChars = 2): string {
    return name
        .trim()
        .split(/\s+/)
        .map((w) => w.charAt(0).toUpperCase())
        .slice(0, maxChars)
        .join('');
}
