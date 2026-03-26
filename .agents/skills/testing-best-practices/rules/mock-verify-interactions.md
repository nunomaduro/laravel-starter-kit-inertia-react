---
title: Verify Important Mock Interactions
impact: MEDIUM
impactDescription: "catches missing or incorrect side effects in 40% more cases"
tags: mocking, verify, spy, assertions
---

## Verify Important Mock Interactions

**Impact: MEDIUM (catches missing or incorrect side effects in 40% more cases)**

When you set up mocks, always assert they were called with the right arguments, the right number of times, and in the right order when it matters.

## Incorrect

```typescript
// ❌ Bad: mocks set up but never verified
describe('RegistrationService', () => {
  test('registers a new user', async () => {
    const mockSendEmail = vi.fn();
    const mockSaveUser = vi.fn().mockResolvedValue({ id: 'user_1' });
    const mockAuditLog = vi.fn();

    const service = new RegistrationService(mockSaveUser, mockSendEmail, mockAuditLog);
    const result = await service.register({
      name: 'Alice',
      email: 'alice@example.com',
    });

    // Only checks the return value — never verifies side effects
    expect(result.id).toBe('user_1');
    // Was the welcome email sent? To the right address? With the right content?
    // Was the audit log written? Nobody knows.
  });
});
```

**Problems:**
- Welcome email could silently stop sending and the test still passes
- Audit log could receive wrong data without detection
- Side effects are the most important thing to verify in service-layer tests
- False green — the test gives confidence it hasn't earned

## Correct

```typescript
// ✅ Good: verify every important mock interaction
describe('RegistrationService', () => {
  const mockSendEmail = vi.fn();
  const mockSaveUser = vi.fn();
  const mockAuditLog = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    mockSaveUser.mockResolvedValue({ id: 'user_1', name: 'Alice', email: 'alice@example.com' });
    mockSendEmail.mockResolvedValue(undefined);
  });

  test('saves user and sends welcome email with correct content', async () => {
    const service = new RegistrationService(mockSaveUser, mockSendEmail, mockAuditLog);

    await service.register({ name: 'Alice', email: 'alice@example.com' });

    // Verify user was saved with correct data
    expect(mockSaveUser).toHaveBeenCalledTimes(1);
    expect(mockSaveUser).toHaveBeenCalledWith({
      name: 'Alice',
      email: 'alice@example.com',
    });

    // Verify welcome email sent to the right address with expected content
    expect(mockSendEmail).toHaveBeenCalledTimes(1);
    expect(mockSendEmail).toHaveBeenCalledWith(
      'alice@example.com',
      expect.stringContaining('Welcome'),
    );

    // Verify audit log records the registration event
    expect(mockAuditLog).toHaveBeenCalledWith(
      expect.objectContaining({
        action: 'USER_REGISTERED',
        userId: 'user_1',
      }),
    );
  });

  test('does not send email when save fails', async () => {
    mockSaveUser.mockRejectedValue(new Error('DB error'));
    const service = new RegistrationService(mockSaveUser, mockSendEmail, mockAuditLog);

    await expect(
      service.register({ name: 'Alice', email: 'alice@example.com' })
    ).rejects.toThrow('DB error');

    // Verify no email was sent after a failed save
    expect(mockSendEmail).not.toHaveBeenCalled();
    expect(mockAuditLog).toHaveBeenLastCalledWith(
      expect.objectContaining({ action: 'REGISTRATION_FAILED' }),
    );
  });
});
```

**Benefits:**
- Every important side effect is explicitly verified
- `toHaveBeenCalledTimes` catches duplicate calls (e.g., double-sending emails)
- `toHaveBeenCalledWith` ensures correct arguments reach dependencies
- `toHaveBeenLastCalledWith` verifies the final state when order matters
- `not.toHaveBeenCalled()` confirms side effects are skipped on error paths

Reference: [Jest Mock Functions](https://jestjs.io/docs/mock-function-api)
