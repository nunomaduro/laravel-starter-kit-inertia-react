---
id: core-yagni-features
title: YAGNI - Features
category: core-principles
priority: critical
tags: [YAGNI, speculative-features, lean-development]
related: [core-yagni-abstractions, core-kiss-simplicity]
---

# YAGNI Principle - Features

You Aren't Gonna Need It. Don't implement features until they are actually required. Building speculative features wastes time and adds unnecessary complexity.

## Bad Example

```typescript
// Anti-pattern: Building features "just in case"

// Task: Create a simple user registration
// Over-built solution with features nobody asked for

interface User {
  id: string;
  email: string;
  password: string;
  // Speculative fields - nobody asked for these
  phone?: string;
  address?: Address;
  preferences?: UserPreferences;
  socialProfiles?: SocialProfile[];
  twoFactorEnabled?: boolean;
  twoFactorSecret?: string;
  backupCodes?: string[];
  apiKeys?: ApiKey[];
  loginHistory?: LoginAttempt[];
  sessions?: Session[];
  subscriptionTier?: 'free' | 'basic' | 'premium' | 'enterprise';
  billingInfo?: BillingInfo;
  teamMemberships?: TeamMembership[];
  referralCode?: string;
  referredBy?: string;
  loyaltyPoints?: number;
}

class UserService {
  // Basic registration - actually needed
  async register(email: string, password: string): Promise<User> {
    // ...
  }

  // These were built "just in case" - never used

  async enableTwoFactor(userId: string): Promise<TwoFactorSetup> {
    // 200 lines of 2FA implementation
    // Product never asked for this feature
  }

  async generateApiKey(userId: string, scopes: string[]): Promise<ApiKey> {
    // 150 lines of API key management
    // No API exists for external developers
  }

  async trackLoginAttempt(userId: string, success: boolean, ip: string): Promise<void> {
    // 100 lines of login tracking
    // No dashboard to view this data
  }

  async manageSessions(userId: string): Promise<Session[]> {
    // 180 lines of session management
    // Users can't actually see or manage sessions
  }

  async upgradeSubscription(userId: string, tier: string): Promise<void> {
    // 300 lines of subscription logic
    // App is free, no paid tiers planned
  }

  async processReferral(referrerCode: string, newUserId: string): Promise<void> {
    // 120 lines of referral logic
    // Marketing hasn't planned a referral program
  }

  async awardLoyaltyPoints(userId: string, action: string): Promise<void> {
    // 100 lines of loyalty point logic
    // No loyalty program exists
  }

  // 1500+ lines of code for features that may never be used
}

// Result:
// - 3 weeks spent building speculative features
// - 1500+ lines to maintain
// - Bugs introduced in code that's never executed
// - Actual requirements delayed
```

## Good Example

```typescript
// Correct approach: Build only what's needed now

// Task: Create a simple user registration
// Focused solution that meets actual requirements

interface User {
  id: string;
  email: string;
  passwordHash: string;
  createdAt: Date;
  updatedAt: Date;
}

interface CreateUserData {
  email: string;
  password: string;
}

class UserService {
  constructor(
    private userRepository: UserRepository,
    private passwordHasher: PasswordHasher,
    private emailService: EmailService
  ) {}

  async register(data: CreateUserData): Promise<User> {
    // Validate email
    const existingUser = await this.userRepository.findByEmail(data.email);
    if (existingUser) {
      throw new UserAlreadyExistsError(data.email);
    }

    // Create user
    const passwordHash = await this.passwordHasher.hash(data.password);
    const user = await this.userRepository.create({
      email: data.email,
      passwordHash,
      createdAt: new Date(),
      updatedAt: new Date()
    });

    // Send welcome email
    await this.emailService.sendWelcome(user.email);

    return user;
  }

  async findById(id: string): Promise<User | null> {
    return this.userRepository.findById(id);
  }

  async findByEmail(email: string): Promise<User | null> {
    return this.userRepository.findByEmail(email);
  }
}

// Result:
// - Built in 2 days
// - ~100 lines of focused code
// - Easy to understand and maintain
// - Shipped to users quickly
// - Can add features when actually needed

// When 2FA is actually requested (6 months later):
// Add it then with full context of actual requirements

interface TwoFactorService {
  enable(userId: string): Promise<TwoFactorSetup>;
  verify(userId: string, code: string): Promise<boolean>;
  disable(userId: string): Promise<void>;
}

// When subscription tiers are planned (1 year later):
// Add with actual business requirements, pricing, and features defined

interface SubscriptionService {
  getCurrentPlan(userId: string): Promise<Plan>;
  upgrade(userId: string, planId: string): Promise<void>;
  downgrade(userId: string, planId: string): Promise<void>;
  cancel(userId: string): Promise<void>;
}

// Benefits of waiting:
// 1. Requirements are clearer
// 2. You know the actual use cases
// 3. Technology may have improved
// 4. You didn't maintain unused code for months
// 5. You might not need it at all (50%+ of speculative features are never used)
```

## Why

1. **Waste Prevention**: Speculative features consume development time that could be spent on actual needs.

2. **Maintenance Burden**: Every line of code must be maintained, tested, and understood - even unused code.

3. **Complexity Cost**: Unused features add cognitive load when reading and modifying the codebase.

4. **Requirements Clarity**: Future requirements are clearer when you're actually implementing them.

5. **Flexibility**: Code without speculative features is easier to refactor and evolve.

6. **Faster Delivery**: Ship what's needed now, iterate based on real feedback.

7. **Better Design**: Features designed with real requirements and user feedback are better than guessed features.
