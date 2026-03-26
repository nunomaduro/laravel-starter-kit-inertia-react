---
title: Use Realistic Mock Behavior
impact: MEDIUM
impactDescription: "catches 30% more integration bugs by simulating real API behavior"
tags: mocking, realistic, msw, test-doubles
---

## Use Realistic Mock Behavior

**Impact: MEDIUM (catches 30% more integration bugs by simulating real API behavior)**

Mocks should behave like the real dependency — return realistic data structures, simulate error responses, and respect the contract of the thing they replace.

## Incorrect

```typescript
// ❌ Bad: mocks that always succeed with minimal data
const mockApi = {
  getUser: vi.fn().mockResolvedValue({ name: 'test' }),
  createOrder: vi.fn().mockResolvedValue(true),
  fetchProducts: vi.fn().mockResolvedValue([]),
};

describe('Dashboard', () => {
  test('loads user data', async () => {
    const dashboard = new DashboardService(mockApi);
    const data = await dashboard.load('user_1');

    // Mock returns { name: 'test' } — missing id, email, role, createdAt
    // Real API returns a full user object; code accessing user.role will crash in production
    expect(data.user.name).toBe('test');
  });

  test('creates an order', async () => {
    const dashboard = new DashboardService(mockApi);
    const result = await dashboard.placeOrder({ productId: 'p1', quantity: 2 });

    // Mock returns `true` — real API returns { orderId, status, estimatedDelivery }
    // No error path tested at all
    expect(result).toBe(true);
  });
});
```

**Problems:**
- Incomplete mock responses hide runtime errors when real fields are accessed
- Never testing error paths means error handling code is unverified
- `mockResolvedValue(true)` doesn't match the real API contract
- Tests pass in CI but the feature breaks in production

## Correct

```typescript
// ✅ Good: realistic mocks with MSW for API simulation
import { http, HttpResponse } from 'msw';
import { setupServer } from 'msw/node';

// Realistic response shapes matching the actual API contract
const handlers = [
  http.get('/api/users/:id', ({ params }) => {
    if (params.id === 'not_found') {
      return HttpResponse.json(
        { error: 'User not found', code: 'USER_NOT_FOUND' },
        { status: 404 },
      );
    }

    return HttpResponse.json({
      id: params.id,
      name: 'Alice Johnson',
      email: 'alice@example.com',
      role: 'admin',
      createdAt: '2024-01-15T10:30:00Z',
    });
  }),

  http.post('/api/orders', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;

    if (!body.productId) {
      return HttpResponse.json(
        { error: 'Product ID is required', code: 'VALIDATION_ERROR' },
        { status: 400 },
      );
    }

    return HttpResponse.json(
      {
        orderId: 'order_789',
        status: 'confirmed',
        estimatedDelivery: '2024-02-01',
        items: [{ productId: body.productId, quantity: body.quantity }],
      },
      { status: 201 },
    );
  }),
];

const server = setupServer(...handlers);

beforeAll(() => server.listen());
afterEach(() => server.resetHandlers());
afterAll(() => server.close());

describe('DashboardService', () => {
  const service = new DashboardService();

  test('loads complete user profile', async () => {
    const data = await service.load('user_1');

    expect(data.user).toMatchObject({
      id: 'user_1',
      name: expect.any(String),
      email: expect.stringContaining('@'),
      role: expect.any(String),
    });
  });

  test('handles user not found gracefully', async () => {
    const data = await service.load('not_found');

    expect(data.user).toBeNull();
    expect(data.error).toBe('User not found');
  });

  test('creates order with full response data', async () => {
    const result = await service.placeOrder({ productId: 'p1', quantity: 2 });

    expect(result).toMatchObject({
      orderId: expect.stringMatching(/^order_/),
      status: 'confirmed',
      estimatedDelivery: expect.any(String),
    });
  });

  test('rejects order with missing product ID', async () => {
    await expect(
      service.placeOrder({ productId: '', quantity: 1 })
    ).rejects.toThrow('Product ID is required');
  });
});
```

**Benefits:**
- MSW intercepts real fetch/axios calls — no manual mock wiring needed
- Response shapes match the actual API contract, catching field-access bugs early
- Both success and error paths are tested with realistic status codes
- Tests run against the same HTTP layer used in production

Reference: [Mock Service Worker](https://mswjs.io/docs/getting-started)
