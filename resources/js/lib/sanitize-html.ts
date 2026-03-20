import DOMPurify from 'dompurify';

/**
 * Sanitize HTML to prevent XSS attacks.
 * Uses DOMPurify for robust sanitization.
 */
export function sanitizeHtml(html: string): string {
    return DOMPurify.sanitize(html);
}
