/**
 * Shared CRM types used across contact and deal pages.
 *
 * ContactSummary — minimal contact reference (used in deal associations).
 * Contact        — full contact record (list & edit views).
 * Deal           — deal record with optional contact reference.
 */

/** Minimal contact reference used when embedding in related entities (e.g. deals). */
export interface ContactSummary {
    id: number;
    first_name: string;
    last_name: string;
}

/** Full contact record for list and detail views. */
export interface Contact extends ContactSummary {
    email: string;
    phone: string | null;
    company: string | null;
    position: string | null;
    status: string;
}

/** Extended contact with optional fields visible on the edit form. */
export interface ContactEditable extends Contact {
    source: string | null;
    notes: string | null;
}

export interface Deal {
    id: number;
    title: string;
    value: string;
    currency: string | null;
    stage: string;
    probability: number | null;
    expected_close_date: string | null;
    status: string;
    contact: ContactSummary | null;
}
