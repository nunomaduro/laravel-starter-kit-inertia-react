---
id: solid-srp-class
title: SOLID - Single Responsibility Principle (Class Level)
category: solid-principles
priority: critical
tags: [SOLID, SRP, single-responsibility, class-design]
related: [solid-srp-function, core-separation-concerns, solid-isp-interfaces]
---

# Single Responsibility Principle - Class Level

A class should have only one reason to change, meaning it should have only one job or responsibility.

## Bad Example

```typescript
// Anti-pattern: Class handles multiple responsibilities
class UserManager {
  private db: Database;
  private mailer: EmailService;

  constructor() {
    this.db = new Database();
    this.mailer = new EmailService();
  }

  createUser(userData: UserData): User {
    // Validation logic
    if (!userData.email.includes('@')) {
      throw new Error('Invalid email');
    }
    if (userData.password.length < 8) {
      throw new Error('Password too short');
    }

    // Database logic
    const user = this.db.insert('users', userData);

    // Email logic
    const htmlContent = `<h1>Welcome ${user.name}!</h1>`;
    this.mailer.send(user.email, 'Welcome', htmlContent);

    // Logging logic
    console.log(`User created: ${user.id} at ${new Date()}`);
    this.writeToFile(`/logs/users.log`, `Created: ${user.id}`);

    return user;
  }

  private writeToFile(path: string, content: string): void {
    // File writing implementation
  }
}
```

## Good Example

```typescript
// Correct approach: Each class has a single responsibility

// Handles user validation only
class UserValidator {
  validate(userData: UserData): ValidationResult {
    const errors: string[] = [];

    if (!userData.email.includes('@')) {
      errors.push('Invalid email format');
    }
    if (userData.password.length < 8) {
      errors.push('Password must be at least 8 characters');
    }

    return { isValid: errors.length === 0, errors };
  }
}

// Handles user persistence only
class UserRepository {
  constructor(private db: Database) {}

  save(userData: UserData): User {
    return this.db.insert('users', userData);
  }

  findById(id: string): User | null {
    return this.db.findOne('users', { id });
  }
}

// Handles welcome email only
class WelcomeEmailService {
  constructor(private mailer: EmailService) {}

  sendWelcomeEmail(user: User): void {
    const htmlContent = this.buildTemplate(user);
    this.mailer.send(user.email, 'Welcome', htmlContent);
  }

  private buildTemplate(user: User): string {
    return `<h1>Welcome ${user.name}!</h1>`;
  }
}

// Handles user activity logging only
class UserActivityLogger {
  constructor(private logger: Logger) {}

  logCreation(user: User): void {
    this.logger.info(`User created: ${user.id}`, { userId: user.id });
  }
}

// Orchestrates the user creation process
class UserService {
  constructor(
    private validator: UserValidator,
    private repository: UserRepository,
    private welcomeEmail: WelcomeEmailService,
    private activityLogger: UserActivityLogger
  ) {}

  createUser(userData: UserData): User {
    const validation = this.validator.validate(userData);
    if (!validation.isValid) {
      throw new ValidationError(validation.errors);
    }

    const user = this.repository.save(userData);
    this.welcomeEmail.sendWelcomeEmail(user);
    this.activityLogger.logCreation(user);

    return user;
  }
}
```

## Why

1. **Easier Testing**: Each class can be unit tested in isolation without mocking unrelated dependencies.

2. **Reduced Coupling**: Changes to email templates don't affect database logic or validation rules.

3. **Better Reusability**: The `UserValidator` can be reused for profile updates, the `WelcomeEmailService` can be triggered from different flows.

4. **Clearer Ownership**: Teams can own specific classes without stepping on each other's work.

5. **Simpler Maintenance**: Bug in email formatting? Look only at `WelcomeEmailService`. Validation issue? Check `UserValidator`.

6. **Flexible Composition**: Easy to add features like async email sending or different logging strategies without touching core logic.
