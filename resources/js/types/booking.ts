export type PropertyType = 'resort' | 'hotel' | 'villa';
export type PropertyStatus = 'pending' | 'approved' | 'rejected';
export type BookingStatus = 'pending' | 'approved' | 'declined' | 'completed' | 'cancelled';

export type Property = {
    id: string;
    slug: string;
    name: string;
    description: string;
    type: PropertyType;
    address: string;
    city: string;
    country: string;
    latitude: number | null;
    longitude: number | null;
    amenities: string[];
    status: PropertyStatus;
    is_featured: boolean;
    cancellation_policy: string | null;
    cover_image: string | null;
    images: PropertyImage[];
    room_types: RoomType[];
    average_rating: number;
    reviews_count: number;
    min_price: number;
    host: PropertyHost;
    created_at: string;
    updated_at: string;
};

export type PropertySummary = {
    id: string;
    slug: string;
    name: string;
    city: string;
    country: string;
    type: PropertyType;
    status: PropertyStatus;
    cover_image: string | null;
    average_rating: number;
    reviews_count: number;
    min_price: number;
    is_featured: boolean;
};

export type PropertyImage = {
    id: string;
    path: string;
    order: number;
};

export type PropertyHost = {
    id: string;
    name: string;
    avatar: string | null;
    bio: string | null;
    created_at: string;
};

export type RoomType = {
    id: string;
    name: string;
    description: string | null;
    max_guests: number;
    base_price_per_night: number;
    min_nights: number;
    max_nights: number | null;
    total_rooms: number;
};

export type Booking = {
    id: string;
    property: PropertySummary;
    room_type: RoomType;
    check_in: string;
    check_out: string;
    guests_count: number;
    status: BookingStatus;
    cancelled_by: 'guest' | 'host' | null;
    cancellation_reason: string | null;
    total_price: number;
    commission_amount: number;
    host_payout: number;
    price_breakdown: Record<string, number>;
    notes: string | null;
    created_at: string;
};

export type Review = {
    id: string;
    guest: { id: string; name: string; avatar: string | null };
    rating: number;
    comment: string;
    host_response: string | null;
    host_responded_at: string | null;
    created_at: string;
};

export type Conversation = {
    id: string;
    property: PropertySummary;
    other_participant: { id: string; name: string; avatar: string | null };
    last_message: Message | null;
    unread_count: number;
    updated_at: string;
};

export type Message = {
    id: string;
    sender_id: string;
    body: string;
    read_at: string | null;
    created_at: string;
};

export type EarningsSummary = {
    total_earnings: number;
    this_month: number;
    pending_payouts: number;
    completed_bookings: number;
};

export type SearchFilters = {
    location?: string;
    check_in?: string;
    check_out?: string;
    guests?: number;
    min_price?: number;
    max_price?: number;
    type?: PropertyType;
    amenities?: string[];
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
    first_page_url: string | null;
    last_page_url: string | null;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
    links: { url: string | null; label: string; active: boolean }[];
};
