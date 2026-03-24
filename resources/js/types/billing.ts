/**
 * Shared billing types used across billing and pricing pages.
 */

export interface Plan {
    id: number;
    name: string;
    description: string;
    price: number;
    currency: string;
    interval: string;
}

export interface Invoice {
    id: number;
    number: string;
    status: string;
    total: number;
    currency: string;
    created_at: string;
}

export interface CreditPack {
    id: number;
    name: string;
    credits: number;
    bonus_credits: number;
    price: number;
    currency: string;
}

export interface CreditTransaction {
    id: number;
    amount: number;
    type: string;
    description: string | null;
    created_at: string;
}
