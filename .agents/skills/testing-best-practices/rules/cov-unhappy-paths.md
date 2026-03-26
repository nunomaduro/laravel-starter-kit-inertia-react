---
title: Test Error Scenarios
impact: MEDIUM
impactDescription: "prevents silent failures and unhandled exceptions in production"
tags: coverage, errors, exceptions, unhappy-path
---

## Test Error Scenarios

**Impact: MEDIUM (prevents silent failures and unhandled exceptions in production)**

Test thrown exceptions, rejected promises, validation failures, network errors, and timeouts. Error handling code is only reliable if it is exercised by tests.

## Incorrect

```typescript
// ❌ Bad: only testing success scenarios
describe('PaymentService', () => {
  test('processes payment successfully', async () => {
    const service = new PaymentService(mockGateway);
    mockGateway.charge.mockResolvedValue({ id: 'txn_1', status: 'paid' });

    const result = await service.processPayment({
      amount: 99.99,
      cardToken: 'tok_valid',
    });

    expect(result.status).toBe('paid');
  });
});

describe('FileParser', () => {
  test('parses valid JSON file', () => {
    const data = parseConfigFile('{"port": 3000}');
    expect(data.port).toBe(3000);
  });
});

// What happens when the card is declined?
// What happens when the network times out?
// What happens when the JSON is malformed?
// Nobody knows — these paths are untested.
```

**Problems:**
- Error handling code never executes during tests, so bugs in it go unnoticed
- Production users hit unhandled exceptions that crash the process
- Silent failures (swallowed errors, missing error messages) slip through
- No confidence that the system degrades gracefully under failure

## Correct

```typescript
// ✅ Good: test every error scenario the code handles
describe('PaymentService', () => {
  const mockGateway = { charge: vi.fn() };
  const service = new PaymentService(mockGateway);

  test('processes payment successfully', async () => {
    mockGateway.charge.mockResolvedValue({ id: 'txn_1', status: 'paid' });

    const result = await service.processPayment({
      amount: 99.99,
      cardToken: 'tok_valid',
    });

    expect(result.status).toBe('paid');
  });

  test('throws PaymentDeclinedError when card is declined', async () => {
    mockGateway.charge.mockRejectedValue(new Error('Card declined'));

    await expect(
      service.processPayment({ amount: 99.99, cardToken: 'tok_declined' })
    ).rejects.toThrow('Card declined');
  });

  test('throws ValidationError for negative amount', () => {
    expect(() =>
      service.validatePayment({ amount: -10, cardToken: 'tok_valid' })
    ).toThrow('Amount must be positive');
  });

  test('throws ValidationError for zero amount', () => {
    expect(() =>
      service.validatePayment({ amount: 0, cardToken: 'tok_valid' })
    ).toThrow('Amount must be positive');
  });

  test('wraps gateway timeout in a user-friendly error', async () => {
    mockGateway.charge.mockRejectedValue(new Error('ETIMEDOUT'));

    await expect(
      service.processPayment({ amount: 50, cardToken: 'tok_valid' })
    ).rejects.toThrow('Payment service is temporarily unavailable');
  });

  test('rejects with specific error type for insufficient funds', async () => {
    mockGateway.charge.mockRejectedValue(
      Object.assign(new Error('Insufficient funds'), { code: 'insufficient_funds' })
    );

    await expect(
      service.processPayment({ amount: 9999, cardToken: 'tok_valid' })
    ).rejects.toBeInstanceOf(InsufficientFundsError);
  });
});

describe('FileParser', () => {
  test('parses valid JSON', () => {
    const data = parseConfigFile('{"port": 3000}');
    expect(data.port).toBe(3000);
  });

  test('throws descriptive error for malformed JSON', () => {
    expect(() => parseConfigFile('{invalid}')).toThrow(
      expect.objectContaining({
        message: expect.stringContaining('Invalid configuration format'),
      }),
    );
  });

  test('throws for empty input', () => {
    expect(() => parseConfigFile('')).toThrow('Configuration cannot be empty');
  });

  test('rejects config missing required fields', () => {
    expect(() => parseConfigFile('{"debug": true}')).toThrow(
      'Missing required field: port',
    );
  });
});
```

**Benefits:**
- `expect().rejects.toThrow()` verifies async error handling without try/catch boilerplate
- `expect(() => fn()).toThrow()` confirms synchronous errors are thrown correctly
- `toBeInstanceOf` ensures the right error type is used for programmatic error handling
- Timeout and network error tests verify the system degrades gracefully
- Each test documents what the user sees when something goes wrong

Reference: [Jest Expect API - Errors](https://jestjs.io/docs/expect#tothrowerror)
