/**
 * Shared organization types used across organization and user pages.
 *
 * NOTE: OrganizationSummary (id, name, slug) is already defined in index.d.ts
 * and is used by the Auth interface. Import it from '@/types' when you only need
 * the summary shape.
 *
 * The types below extend or complement that base type for pages that need
 * richer organization data.
 */

/** Organization with owner info, used in org detail/settings views. */
export interface OrganizationDetail {
    id: number;
    name: string;
    slug: string;
    owner?: { id: number; name: string; email: string } | null;
}

export interface OrgMember {
    id: number;
    name: string;
    email: string;
    is_owner: boolean;
    role: string;
    joined_at: string | null;
}

export interface PendingInvitation {
    id: number;
    email: string;
    role: string;
    expires_at: string;
}
