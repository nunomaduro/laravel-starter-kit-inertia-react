---
title: Resort & Villa Booking Platform
status: draft
author: AI-assisted
created: 2026-03-27
updated: 2026-03-27
---

# Resort & Villa Booking Platform — PRD

## 1. Executive Summary

A booking platform for resorts, hotels, and villas where property owners list their properties and guests browse and request to book rooms/units. Unlike Airbnb's peer-to-peer model, this targets **commercial hospitality properties with multiple rooms**. The platform provides a public-facing homepage for discovery, a host dashboard for property management, and an admin panel for platform oversight.

## 2. Problem Statement

- **What:** Independent resorts, hotels, and villa operators lack an affordable, modern booking platform tailored to multi-room properties. Existing solutions are either too expensive (Booking.com commissions) or too peer-to-peer focused (Airbnb).
- **Who:** Property owners/managers with multiple rooms/units, and travelers looking for resort-style stays.
- **Why now:** Growing demand for direct booking platforms that give hosts more control and lower fees.

## 3. Goals & Success Metrics

| Goal | Metric | Target |
| ---- | ------ | ------ |
| Guests can discover and book properties | Booking request completion rate | > 60% of started bookings |
| Hosts can manage listings efficiently | Time to publish a listing | < 10 minutes |
| Platform is usable and fast | Page load time | < 2 seconds |
| Trust through reviews | % of completed stays with a review | > 30% |

## 4. User Personas

### Guest (Traveler)

- **Role:** Someone looking for resort/hotel/villa stays
- **Goals:** Find a property, view details & photos, check availability, request a booking
- **Pain points:** Hard to compare options, unclear availability, slow communication with hosts

### Host (Property Owner/Manager)

- **Role:** Owns or manages a resort, hotel, or villa with multiple rooms
- **Goals:** List property & rooms, manage bookings, communicate with guests, track earnings
- **Pain points:** Managing availability across rooms, responding to booking requests quickly

### Admin (Platform Operator)

- **Role:** Manages the platform
- **Goals:** Approve/reject listings, manage users, moderate reviews, oversee platform health

## 5. User Stories & Acceptance Criteria

### Public Homepage (Guest-facing)

#### US-1: Browse featured properties

> As a guest, I want to see featured properties on the homepage so I can quickly discover popular stays.

- [ ] Homepage displays properties where `is_featured = true`
- [ ] Each property card shows: cover image, name, location, starting price (LYD), rating
- [ ] Clicking a card navigates to `/properties/{slug}`

#### US-2: Search properties

> As a guest, I want to search by location, dates, and number of guests so I can find available properties.

- [ ] Search bar with: location (Google Maps autocomplete), check-in/check-out dates, guest count
- [ ] Results page shows matching properties with filters, paginated (20 per page)
- [ ] Google Maps integration shows property locations on a map
- [ ] Only properties with available rooms for the selected dates/guest count are shown

#### US-3: Filter search results

> As a guest, I want to filter results by price, property type, and amenities.

- [ ] Filters: price range, property type (resort/hotel/villa), amenities (pool, wifi, parking, etc.)
- [ ] Filters update results without full page reload

#### US-4: View property details

> As a guest, I want to see full property details including rooms, photos, amenities, and reviews.

- [ ] Photo gallery (multiple images)
- [ ] Property description, amenities list, location on map
- [ ] List of available room types with prices (per night) and max guests
- [ ] Guest reviews with host responses, and average rating
- [ ] Host info (name, phone, response rate, join date)
- [ ] Cancellation policy displayed clearly
- [ ] SEO-friendly URL: `/properties/{slug}`

#### US-5: Request to book a room

> As a guest, I want to request a booking for specific dates and room type so the host can approve it.

- [ ] Select room type, check-in/check-out dates, number of guests
- [ ] Enforce min/max night constraints per room type
- [ ] See per-night price breakdown (each night shows its rate — weekday/weekend/seasonal/special)
- [ ] See commission and total in LYD
- [ ] Submit booking request (requires login)
- [ ] Receive confirmation that request was sent
- [ ] Simulated payment step (no real charges)

#### US-6: View my bookings

> As a guest, I want to see all my booking requests and their status.

- [ ] List of bookings: pending, approved, declined, completed, cancelled
- [ ] Each booking shows: property, room, dates, status, total price, price breakdown

#### US-6b: Cancel a booking

> As a guest, I want to cancel a booking I've made.

- [ ] Guest can cancel a pending booking at any time (no penalty)
- [ ] Guest can cancel an approved booking — cancellation policy text is shown before confirming
- [ ] Cancelled bookings free up room availability
- [ ] Host is notified of cancellation

#### US-7: Leave a review

> As a guest, I want to review a property after my stay.

- [ ] Can leave a review only after checkout date has passed
- [ ] Rating (1-5 stars) + text review
- [ ] Review appears on the property detail page

#### US-8: Message the host

> As a guest, I want to message a host to ask questions before or during a booking.

- [ ] Conversations are grouped by `Conversation` entity (see data model)
- [ ] Guest can start a conversation from a property page (pre-booking inquiry) or from an existing booking
- [ ] Real-time or near-real-time messaging
- [ ] Notification when host replies

### Host Dashboard

#### US-9: List a property

> As a host, I want to create a property listing with details, photos, and room types.

- [ ] Property form: name, description, type (resort/hotel/villa), location (Google Maps pin), amenities, cancellation policy
- [ ] Upload multiple photos
- [ ] Add room types: name, description, max guests, base price per night, min nights, max nights, total rooms
- [ ] Listing is submitted for admin approval

#### US-9b: Edit or delete a property

> As a host, I want to update or remove my property listings.

- [ ] Edit all property fields, photos, and room types
- [ ] Edited approved listings go back to pending approval if key fields change (name, location, type)
- [ ] Soft-delete a property (hides from search, preserves booking history)
- [ ] Cannot delete a property with active (pending/approved) bookings

#### US-10: Set room pricing

> As a host, I want to set different prices for different days of the week, seasons, and special dates.

- [ ] Set base price per night (default fallback)
- [ ] Set day-of-week prices (e.g. Friday/Saturday higher than weekdays)
- [ ] Set seasonal prices with start/end date range (e.g. "Summer" Jun 1 - Aug 31)
- [ ] Set special date prices with a label (e.g. "Thanksgiving", "New Year's Eve")
- [ ] Price resolution: special date > seasonal > day-of-week > base price
- [ ] Price preview: host sees a calendar with color-coded pricing

#### US-11: Manage room availability

> As a host, I want to manage room availability using a calendar.

- [ ] Calendar view per room type showing booked/available dates and prices
- [ ] Shows remaining rooms available per date (total_rooms minus concurrent approved bookings)
- [ ] Block dates manually (maintenance, private use)
- [ ] Availability auto-updates when bookings are approved or cancelled

#### US-12: Handle booking requests

> As a host, I want to approve or decline booking requests.

- [ ] List of pending booking requests
- [ ] See guest info (name, phone, join date), dates, room type, and message
- [ ] Approve or decline with optional message
- [ ] On approve: system checks room is still available (concurrent booking guard)
- [ ] Guest is notified of the decision

#### US-12b: Cancel a booking (host-initiated)

> As a host, I want to cancel a previously approved booking if necessary.

- [ ] Host can cancel an approved booking with a required reason
- [ ] Guest is notified with the cancellation reason
- [ ] Cancelled bookings free up room availability

#### US-13: View earnings

> As a host, I want to see my earnings from completed bookings.

- [ ] Earnings summary: total, this month, pending payouts
- [ ] Each booking shows: total price, commission deducted, host payout amount
- [ ] List of completed bookings with amounts (simulated)

#### US-13b: Respond to reviews

> As a host, I want to respond to guest reviews on my properties.

- [ ] Host can add one response per review
- [ ] Response appears below the guest review on the property page

#### US-14: Message guests

> As a host, I want to communicate with guests who have booked or inquired.

- [ ] Same messaging system as guest side
- [ ] Inbox with all conversations

### Admin Panel

#### US-15: Manage users

> As an admin, I want to view, edit, and deactivate user accounts.

- [ ] List all users with role filter (guest/host/admin)
- [ ] View user profile details
- [ ] Set commission rate per host
- [ ] Deactivate/reactivate accounts

#### US-16: Approve property listings

> As an admin, I want to review and approve/reject new property listings.

- [ ] Queue of pending listings
- [ ] View full listing details
- [ ] Approve or reject with reason

#### US-17: Moderate reviews

> As an admin, I want to remove inappropriate reviews.

- [ ] Flag/report system for reviews
- [ ] Admin can hide or delete flagged reviews

## 6. Functional Requirements

| ID | Requirement |
| ---- | ----------- |
| FR-1 | Guest registration and login via email/password |
| FR-2 | Host registration (separate role, can also be a guest) |
| FR-3 | Property CRUD with multi-image upload and soft-delete |
| FR-4 | Room type management per property (name, max guests, base price, min/max nights, total rooms) |
| FR-5 | Day-of-week pricing per room type (e.g. weekends cost more) |
| FR-6 | Seasonal pricing with date ranges (e.g. "Summer" Jun-Aug) |
| FR-7 | Special date pricing with labels (e.g. "Thanksgiving" at higher rate) |
| FR-8 | Price resolution: special date > seasonal > day-of-week > base price |
| FR-9 | Per-night price breakdown shown to guest at booking time |
| FR-10 | Search with Google Maps autocomplete, map view, and pagination |
| FR-11 | Date-based availability: count concurrent approved bookings against total_rooms per room type |
| FR-12 | Request-to-book flow with host approval/decline and concurrent booking guard |
| FR-13 | Simulated payment on booking approval (no real gateway) |
| FR-14 | Booking status lifecycle: pending -> approved/declined -> completed/cancelled |
| FR-15 | Cancellation flow: guest can cancel pending (free) or approved (policy shown); host can cancel with reason |
| FR-16 | Messaging via Conversation threads (pre-booking inquiries and booking-related) |
| FR-17 | Review system (1-5 stars + text, post-checkout only) with host response |
| FR-18 | Admin listing approval queue |
| FR-19 | Admin user management with per-host commission rate |
| FR-20 | Host-defined cancellation policy per property (displayed to guest) |
| FR-21 | Email notifications for: booking requests, approvals, declines, cancellations, messages, reviews |
| FR-22 | Wishlist / save properties for guests |
| FR-23 | All prices displayed in LYD |
| FR-24 | Commission tracked per booking (commission_amount, host_payout on Booking) |
| FR-25 | SEO-friendly property URLs using slugs |

## 7. Non-Functional Requirements

| Category | Requirement |
| -------- | ----------- |
| Performance | Pages load in < 2s on 3G connection |
| Security | CSRF protection, XSS prevention, input validation on all forms |
| Accessibility | WCAG 2.1 AA compliance for public pages |
| SEO | Server-side rendering for property pages, meta tags, structured data, slug-based URLs |
| Responsive | Mobile-first design, fully usable on phone/tablet/desktop |
| Image handling | Images optimized and resized on upload, max 10 per property |

## 8. Technical Specifications

### Stack

- **Backend:** Laravel 13, PHP 8.5
- **Frontend:** React 19, Inertia.js v3, Tailwind CSS v4
- **Auth:** Laravel Fortify (email/password)
- **Maps:** Google Maps JavaScript API (key provided by user)
- **Payments:** Simulated (fake gateway, no real charges)
- **File storage:** Laravel filesystem (local or S3)
- **Testing:** Pest v5

### Data Model (Core Entities)

```text
User
  - id, name, email, phone (nullable), password
  - role (guest|host|admin), avatar, bio
  - commission_rate (nullable, admin-set per host, e.g. 0.10 = 10%)
  - timestamps

Property
  - id, host_id (FK User), name, slug (unique), description
  - type (resort|hotel|villa)
  - address, city, country, latitude, longitude
  - amenities (JSON), status (pending|approved|rejected)
  - is_featured (boolean, default false)
  - cancellation_policy (text, set by host)
  - deleted_at (soft delete)
  - timestamps

PropertyImage
  - id, property_id (FK Property), path, order
  - timestamps

RoomType
  - id, property_id (FK Property), name, description
  - max_guests (max number of people per room)
  - base_price_per_night (default/fallback price in LYD)
  - min_nights (default 1), max_nights (nullable)
  - total_rooms
  - timestamps

RoomTypePrice (day-of-week pricing)
  - id, room_type_id (FK RoomType)
  - day_of_week (0=Sunday .. 6=Saturday)
  - price_per_night
  - unique constraint on (room_type_id, day_of_week)

SeasonalPrice (date range pricing)
  - id, room_type_id (FK RoomType)
  - name (e.g. "Summer", "Winter Peak")
  - start_date, end_date
  - price_per_night
  - timestamps

SpecialDatePrice (holidays, events)
  - id, room_type_id (FK RoomType)
  - date (specific date, e.g. 2026-11-26)
  - price_per_night
  - label (optional, e.g. "Thanksgiving", "New Year's Eve")
  - unique constraint on (room_type_id, date)

  Price resolution order: SpecialDatePrice > SeasonalPrice > RoomTypePrice > base_price_per_night

Booking
  - id, guest_id (FK User), property_id (FK Property), room_type_id (FK RoomType)
  - check_in, check_out, guests_count
  - status (pending|approved|declined|completed|cancelled)
  - cancelled_by (nullable: guest|host), cancellation_reason (nullable)
  - total_price, commission_amount, host_payout
  - price_breakdown (JSON — per-night breakdown)
  - notes
  - timestamps

Review
  - id, booking_id (FK Booking), guest_id (FK User), property_id (FK Property)
  - rating (1-5), comment
  - host_response (nullable text)
  - host_responded_at (nullable)
  - timestamps

Conversation
  - id, property_id (FK Property)
  - booking_id (FK Booking, nullable — null for pre-booking inquiries)
  - guest_id (FK User), host_id (FK User)
  - timestamps

Message
  - id, conversation_id (FK Conversation)
  - sender_id (FK User)
  - body
  - read_at (nullable), timestamps

Wishlist
  - id, user_id (FK User), property_id (FK Property)
  - unique constraint on (user_id, property_id)
  - timestamps

BlockedDate
  - id, room_type_id (FK RoomType)
  - date
  - reason (nullable)
  - unique constraint on (room_type_id, date)
```

### Availability Logic

When checking if a room type is available for given dates:

1. For each date in the range, count approved bookings that overlap that date
2. If `approved_bookings_count >= total_rooms` for any date, the room type is unavailable
3. Also check BlockedDate — if any date in the range is blocked and would push count to total_rooms, unavailable
4. When approving a booking, re-check availability inside a DB transaction to prevent race conditions

### Key Routes Structure

```text
Public:
  GET  /                              → Homepage (featured properties)
  GET  /search                        → Search results + map (paginated)
  GET  /properties/{property:slug}    → Property detail page

Guest (auth):
  GET  /bookings                      → My bookings
  POST /bookings                      → Create booking request
  PATCH /bookings/{booking}/cancel    → Cancel booking
  POST /properties/{property}/reviews → Leave review
  POST /properties/{property}/wishlist → Toggle wishlist
  GET  /messages                      → Message inbox
  GET  /messages/{conversation}       → Conversation thread
  POST /messages/{conversation}       → Send message
  POST /properties/{property}/inquire → Start pre-booking conversation

Host (auth):
  GET  /host/dashboard                → Host dashboard
  GET  /host/properties               → My properties
  POST /host/properties               → Create property
  PUT  /host/properties/{property}    → Update property
  DELETE /host/properties/{property}  → Soft-delete property
  GET  /host/properties/{property}/pricing → Pricing management
  PUT  /host/properties/{property}/pricing → Update pricing rules
  GET  /host/bookings                 → Booking requests
  PATCH /host/bookings/{booking}      → Approve/decline
  PATCH /host/bookings/{booking}/cancel → Host-cancel booking
  GET  /host/earnings                 → Earnings overview
  GET  /host/calendar                 → Room availability calendar
  POST /reviews/{review}/respond      → Respond to review
  GET  /host/messages                 → Host message inbox

Admin (auth):
  GET  /admin/users                   → User management
  PATCH /admin/users/{user}           → Edit user (commission rate, status)
  GET  /admin/listings                → Listing approval queue
  PATCH /admin/listings/{property}    → Approve/reject listing
  GET  /admin/reviews                 → Review moderation
  DELETE /admin/reviews/{review}      → Remove review
```

## 9. Out of Scope

| Item | Reason |
| ---- | ------ |
| Multi-currency support | LYD only, no currency conversion needed |
| Analytics dashboard | Not needed for MVP |
| Dispute resolution system | Handle manually for now |
| Social login (Google/Facebook) | Planned for later phase |
| Real payment processing | Simulated for now, Stripe integration later |
| Mobile native app | Web-only, responsive design covers mobile |
| Host identity verification | Manual process for MVP |
| Instant booking | Request-to-book only for MVP |
| Notification preferences | All notifications enabled by default for MVP |
| Guest public profile page | Hosts see guest info in booking details only |

## 10. Non-Goals

- We are **not** building a peer-to-peer home-sharing platform. Properties are commercial (resorts, hotels, villas).
- We are **not** optimizing for massive scale. Target is small-to-medium platform.
- We are **not** building a channel manager or PMS integration.

## 11. Dependencies & Risks

| Dependency/Risk | Owner | Mitigation |
| --------------- | ----- | ---------- |
| Google Maps API key | User | User will provide key |
| Image storage costs at scale | User | Start with local storage, migrate to S3 later |
| Email delivery for notifications | Dev | Use Laravel's mail system with Mailtrap for dev |
| Booking conflicts (double booking) | Dev | Count concurrent bookings against total_rooms in DB transaction with row locking |
| Seasonal price date range overlaps | Dev | Validate no overlapping seasonal ranges per room type; if overlap, latest-created wins |

## 12. Resolved Questions

| Question | Answer |
| -------- | ------ |
| What currency to display prices in? | LYD (Libyan Dinar) only |
| Should hosts set their own cancellation policies? | Yes — each host defines their own policy per property |
| Commission model? | Per-host — admin sets commission rate individually per host |
| Maximum number of room types per property? | No limit |
