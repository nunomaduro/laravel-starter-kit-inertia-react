---
id: core-yagni-abstractions
title: YAGNI - Abstractions
category: core-principles
priority: critical
tags: [YAGNI, premature-abstraction, simplicity]
related: [core-yagni-features, core-kiss-simplicity, solid-ocp-abstraction]
---

# YAGNI Principle - Abstractions

Don't create abstractions until you have concrete evidence they're needed. Premature abstraction leads to wrong abstractions that are worse than no abstraction.

## Bad Example

```typescript
// Anti-pattern: Creating abstractions before understanding the problem

// Task: Send an email notification
// Over-abstracted solution based on imagined future needs

// "We might need different notification channels someday"
interface NotificationChannel {
  send(notification: Notification): Promise<void>;
  getCapabilities(): ChannelCapabilities;
  isAvailable(): Promise<boolean>;
}

// "We might need different notification types"
interface Notification {
  id: string;
  type: NotificationType;
  priority: NotificationPriority;
  payload: NotificationPayload;
  metadata: NotificationMetadata;
}

// "We might need complex routing logic"
interface NotificationRouter {
  route(notification: Notification): Promise<NotificationChannel[]>;
  registerChannel(channel: NotificationChannel): void;
  setRoutingRules(rules: RoutingRule[]): void;
}

// "We might need to transform notifications per channel"
interface NotificationTransformer {
  transform(notification: Notification, channel: NotificationChannel): TransformedNotification;
}

// "We might need retry logic"
interface NotificationRetryPolicy {
  shouldRetry(attempt: number, error: Error): boolean;
  getDelay(attempt: number): number;
}

// "We might need to track delivery"
interface NotificationTracker {
  trackSent(notification: Notification, channel: NotificationChannel): Promise<void>;
  trackDelivered(notificationId: string): Promise<void>;
  trackFailed(notificationId: string, error: Error): Promise<void>;
}

// "We might need a notification queue"
interface NotificationQueue {
  enqueue(notification: Notification): Promise<void>;
  process(): Promise<void>;
  getStatus(notificationId: string): Promise<QueueStatus>;
}

// Orchestrator that ties it all together
class NotificationOrchestrator {
  constructor(
    private router: NotificationRouter,
    private transformer: NotificationTransformer,
    private retryPolicy: NotificationRetryPolicy,
    private tracker: NotificationTracker,
    private queue: NotificationQueue
  ) {}

  async notify(notification: Notification): Promise<void> {
    await this.queue.enqueue(notification);
    // 200+ lines of orchestration logic
  }
}

// But all we actually needed was:
// Send an email when a user registers

// Result: 1000+ lines of abstraction, weeks of work, for sending one email
```

## Good Example

```typescript
// Correct approach: Start concrete, abstract when patterns emerge

// Task: Send an email notification
// Simple, direct solution

interface EmailOptions {
  to: string;
  subject: string;
  body: string;
}

class EmailService {
  constructor(private smtpClient: SmtpClient) {}

  async send(options: EmailOptions): Promise<void> {
    await this.smtpClient.send({
      from: 'noreply@example.com',
      to: options.to,
      subject: options.subject,
      html: options.body
    });
  }
}

// Usage
const emailService = new EmailService(smtpClient);
await emailService.send({
  to: user.email,
  subject: 'Welcome!',
  body: '<h1>Welcome to our app!</h1>'
});

// Later, when we actually need SMS (not "might need"):
class SmsService {
  constructor(private twilioClient: TwilioClient) {}

  async send(phone: string, message: string): Promise<void> {
    await this.twilioClient.messages.create({
      to: phone,
      from: process.env.TWILIO_NUMBER,
      body: message
    });
  }
}

// Now we have TWO concrete implementations
// We can see what they have in common

// Abstract AFTER seeing the pattern (Rule of Three)
// After email, SMS, and push notifications exist:

interface NotificationSender {
  send(recipient: string, message: NotificationMessage): Promise<void>;
}

interface NotificationMessage {
  subject?: string;
  body: string;
}

class EmailNotificationSender implements NotificationSender {
  constructor(private emailService: EmailService) {}

  async send(recipient: string, message: NotificationMessage): Promise<void> {
    await this.emailService.send({
      to: recipient,
      subject: message.subject || 'Notification',
      body: message.body
    });
  }
}

class SmsNotificationSender implements NotificationSender {
  constructor(private smsService: SmsService) {}

  async send(recipient: string, message: NotificationMessage): Promise<void> {
    // SMS doesn't support subject, so we just use body
    await this.smsService.send(recipient, message.body);
  }
}

class PushNotificationSender implements NotificationSender {
  constructor(private pushService: PushService) {}

  async send(recipient: string, message: NotificationMessage): Promise<void> {
    await this.pushService.send(recipient, {
      title: message.subject,
      body: message.body
    });
  }
}

// Simple notification service that uses the abstraction
class NotificationService {
  constructor(private senders: Map<string, NotificationSender>) {}

  async notify(
    channel: 'email' | 'sms' | 'push',
    recipient: string,
    message: NotificationMessage
  ): Promise<void> {
    const sender = this.senders.get(channel);
    if (!sender) {
      throw new Error(`Unknown notification channel: ${channel}`);
    }
    await sender.send(recipient, message);
  }
}

// The abstraction fits because it was derived from concrete implementations
// It's minimal - just what's needed, nothing speculative

// If we later need retry logic, we add it when we have concrete requirements:
class RetryingNotificationSender implements NotificationSender {
  constructor(
    private sender: NotificationSender,
    private maxAttempts: number = 3
  ) {}

  async send(recipient: string, message: NotificationMessage): Promise<void> {
    let lastError: Error | undefined;

    for (let attempt = 1; attempt <= this.maxAttempts; attempt++) {
      try {
        await this.sender.send(recipient, message);
        return;
      } catch (error) {
        lastError = error as Error;
        if (attempt < this.maxAttempts) {
          await this.delay(attempt * 1000);
        }
      }
    }

    throw lastError;
  }

  private delay(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}
```

## Why

1. **Wrong Abstractions Are Costly**: Premature abstractions are often wrong because you don't understand the problem yet. Wrong abstractions are harder to change than no abstractions.

2. **Rule of Three**: Wait until you have three concrete examples before abstracting. Two is often coincidence; three reveals the pattern.

3. **Duplication Is Cheaper Than Wrong Abstraction**: It's easier to extract a correct abstraction from duplicated code than to fix a wrong abstraction.

4. **Context Matters**: Abstractions made with real requirements fit better than those made speculatively.

5. **Simplicity**: Concrete code is simpler to understand, debug, and modify.

6. **Evolutionary Design**: Let the design emerge from actual needs rather than imagined ones.

7. **Time Value**: The time spent on speculative abstractions could be spent on real features.
