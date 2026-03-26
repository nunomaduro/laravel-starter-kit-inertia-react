---
id: core-dry-extraction
title: DRY - Code Extraction
category: core-principles
priority: critical
tags: [DRY, refactoring, extraction, code-reuse]
related: [core-dry, core-dry-single-source, solid-srp-function]
---

# DRY - Code Extraction

Don't Repeat Yourself. When you find duplicated code, extract it into a reusable function, method, or module. Every piece of knowledge should have a single, unambiguous representation.

## Bad Example

```typescript
// Anti-pattern: Same validation logic repeated in multiple places

class UserController {
  async createUser(req: Request, res: Response): Promise<void> {
    const { email, password, name } = req.body;

    // Email validation - duplicated
    if (!email) {
      res.status(400).json({ error: 'Email is required' });
      return;
    }
    if (!email.includes('@') || !email.includes('.')) {
      res.status(400).json({ error: 'Invalid email format' });
      return;
    }
    if (email.length > 255) {
      res.status(400).json({ error: 'Email too long' });
      return;
    }

    // Password validation - duplicated
    if (!password) {
      res.status(400).json({ error: 'Password is required' });
      return;
    }
    if (password.length < 8) {
      res.status(400).json({ error: 'Password must be at least 8 characters' });
      return;
    }
    if (!/[A-Z]/.test(password)) {
      res.status(400).json({ error: 'Password must contain uppercase letter' });
      return;
    }
    if (!/[0-9]/.test(password)) {
      res.status(400).json({ error: 'Password must contain a number' });
      return;
    }

    // Create user...
  }

  async updateUser(req: Request, res: Response): Promise<void> {
    const { email, password } = req.body;

    // Same email validation repeated
    if (email) {
      if (!email.includes('@') || !email.includes('.')) {
        res.status(400).json({ error: 'Invalid email format' });
        return;
      }
      if (email.length > 255) {
        res.status(400).json({ error: 'Email too long' });
        return;
      }
    }

    // Same password validation repeated
    if (password) {
      if (password.length < 8) {
        res.status(400).json({ error: 'Password must be at least 8 characters' });
        return;
      }
      if (!/[A-Z]/.test(password)) {
        res.status(400).json({ error: 'Password must contain uppercase letter' });
        return;
      }
      if (!/[0-9]/.test(password)) {
        res.status(400).json({ error: 'Password must contain a number' });
        return;
      }
    }

    // Update user...
  }

  async resetPassword(req: Request, res: Response): Promise<void> {
    const { email, newPassword } = req.body;

    // Email validation repeated again
    if (!email) {
      res.status(400).json({ error: 'Email is required' });
      return;
    }
    if (!email.includes('@') || !email.includes('.')) {
      res.status(400).json({ error: 'Invalid email format' });
      return;
    }

    // Password validation repeated again
    if (!newPassword) {
      res.status(400).json({ error: 'New password is required' });
      return;
    }
    if (newPassword.length < 8) {
      res.status(400).json({ error: 'Password must be at least 8 characters' });
      return;
    }
    if (!/[A-Z]/.test(newPassword)) {
      res.status(400).json({ error: 'Password must contain uppercase letter' });
      return;
    }
    if (!/[0-9]/.test(newPassword)) {
      res.status(400).json({ error: 'Password must contain a number' });
      return;
    }

    // Reset password...
  }
}
```

## Good Example

```typescript
// Correct approach: Extract reusable validation functions

// Validation result type
interface ValidationResult {
  isValid: boolean;
  errors: string[];
}

// Reusable validation functions
class Validators {
  static email(email: string | undefined, options: { required?: boolean } = {}): ValidationResult {
    const errors: string[] = [];

    if (!email) {
      if (options.required) {
        errors.push('Email is required');
      }
      return { isValid: !options.required, errors };
    }

    if (!email.includes('@') || !email.includes('.')) {
      errors.push('Invalid email format');
    }

    if (email.length > 255) {
      errors.push('Email must be 255 characters or less');
    }

    return { isValid: errors.length === 0, errors };
  }

  static password(password: string | undefined, options: { required?: boolean } = {}): ValidationResult {
    const errors: string[] = [];

    if (!password) {
      if (options.required) {
        errors.push('Password is required');
      }
      return { isValid: !options.required, errors };
    }

    if (password.length < 8) {
      errors.push('Password must be at least 8 characters');
    }

    if (!/[A-Z]/.test(password)) {
      errors.push('Password must contain at least one uppercase letter');
    }

    if (!/[a-z]/.test(password)) {
      errors.push('Password must contain at least one lowercase letter');
    }

    if (!/[0-9]/.test(password)) {
      errors.push('Password must contain at least one number');
    }

    return { isValid: errors.length === 0, errors };
  }

  static combine(...results: ValidationResult[]): ValidationResult {
    const errors = results.flatMap(r => r.errors);
    return { isValid: errors.length === 0, errors };
  }
}

// Reusable error response helper
function validationError(res: Response, errors: string[]): void {
  res.status(400).json({ errors });
}

// Clean controller using extracted validations
class UserController {
  async createUser(req: Request, res: Response): Promise<void> {
    const { email, password, name } = req.body;

    const validation = Validators.combine(
      Validators.email(email, { required: true }),
      Validators.password(password, { required: true })
    );

    if (!validation.isValid) {
      return validationError(res, validation.errors);
    }

    // Create user...
  }

  async updateUser(req: Request, res: Response): Promise<void> {
    const { email, password } = req.body;

    const validation = Validators.combine(
      Validators.email(email),
      Validators.password(password)
    );

    if (!validation.isValid) {
      return validationError(res, validation.errors);
    }

    // Update user...
  }

  async resetPassword(req: Request, res: Response): Promise<void> {
    const { email, newPassword } = req.body;

    const validation = Validators.combine(
      Validators.email(email, { required: true }),
      Validators.password(newPassword, { required: true })
    );

    if (!validation.isValid) {
      return validationError(res, validation.errors);
    }

    // Reset password...
  }
}

// Validators can be reused across the application
class AdminController {
  async inviteUser(req: Request, res: Response): Promise<void> {
    const { email } = req.body;

    const validation = Validators.email(email, { required: true });

    if (!validation.isValid) {
      return validationError(res, validation.errors);
    }

    // Send invite...
  }
}

// Easy to test in isolation
describe('Validators', () => {
  describe('email', () => {
    it('should reject invalid email format', () => {
      const result = Validators.email('invalid');
      expect(result.isValid).toBe(false);
      expect(result.errors).toContain('Invalid email format');
    });

    it('should accept valid email', () => {
      const result = Validators.email('user@example.com');
      expect(result.isValid).toBe(true);
    });

    it('should require email when required option is set', () => {
      const result = Validators.email(undefined, { required: true });
      expect(result.isValid).toBe(false);
      expect(result.errors).toContain('Email is required');
    });
  });

  describe('password', () => {
    it('should reject short passwords', () => {
      const result = Validators.password('short');
      expect(result.isValid).toBe(false);
      expect(result.errors).toContain('Password must be at least 8 characters');
    });

    it('should require uppercase letter', () => {
      const result = Validators.password('lowercase123');
      expect(result.isValid).toBe(false);
      expect(result.errors).toContain('Password must contain at least one uppercase letter');
    });
  });
});
```

## Why

1. **Single Source of Truth**: Password rules are defined once. Change them in one place, and all usages are updated.

2. **Consistency**: All email validations behave the same way. No risk of inconsistent error messages or rules.

3. **Easier Testing**: Test the validation logic once, thoroughly. No need to test the same logic in every controller.

4. **Bug Fixes Propagate**: Fix a bug in `Validators.email()`, and it's fixed everywhere.

5. **Reduced Code Size**: Less code means less to read, less to maintain, and fewer places for bugs.

6. **Better Abstraction**: Controllers focus on HTTP concerns, validators focus on validation.

7. **Reusability**: Same validators work in controllers, services, CLI tools, or anywhere else.
