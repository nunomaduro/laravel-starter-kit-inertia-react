---
id: solid-isp-clients
title: SOLID - Interface Segregation (Client-Specific)
category: solid-principles
priority: critical
tags: [SOLID, ISP, interface-segregation, client-design]
related: [solid-isp-interfaces, solid-srp-class, solid-lsp-contracts]
---

# Interface Segregation Principle - Client-Specific Interfaces

Clients should not be forced to depend on interfaces they do not use. Design interfaces from the client's perspective, not the implementation's.

## Bad Example

```typescript
// Anti-pattern: Fat interface that forces clients to depend on methods they don't use

interface UserService {
  // Authentication
  login(email: string, password: string): Promise<AuthToken>;
  logout(userId: string): Promise<void>;
  refreshToken(token: string): Promise<AuthToken>;
  validateToken(token: string): Promise<boolean>;

  // User management
  createUser(data: CreateUserData): Promise<User>;
  updateUser(id: string, data: UpdateUserData): Promise<User>;
  deleteUser(id: string): Promise<void>;
  getUser(id: string): Promise<User>;
  listUsers(filters: UserFilters): Promise<User[]>;

  // Profile
  updateProfile(userId: string, profile: ProfileData): Promise<Profile>;
  uploadAvatar(userId: string, image: Buffer): Promise<string>;
  getProfile(userId: string): Promise<Profile>;

  // Notifications
  sendNotification(userId: string, notification: Notification): Promise<void>;
  getNotificationPreferences(userId: string): Promise<NotificationPrefs>;
  updateNotificationPreferences(userId: string, prefs: NotificationPrefs): Promise<void>;

  // Analytics
  trackUserEvent(userId: string, event: AnalyticsEvent): Promise<void>;
  getUserAnalytics(userId: string): Promise<UserAnalytics>;
}

// Login page only needs authentication but must depend on entire interface
class LoginPage {
  constructor(private userService: UserService) {} // Depends on 15+ methods!

  async handleLogin(email: string, password: string): Promise<void> {
    // Only uses 1 method
    const token = await this.userService.login(email, password);
    this.storeToken(token);
  }
}

// Profile component forced to depend on authentication and analytics
class ProfileEditor {
  constructor(private userService: UserService) {} // Depends on 15+ methods!

  async saveProfile(userId: string, data: ProfileData): Promise<void> {
    // Only uses 2 methods
    await this.userService.updateProfile(userId, data);
    const profile = await this.userService.getProfile(userId);
  }
}

// Mock implementation nightmare for testing
class MockUserService implements UserService {
  // Must implement ALL methods even for simple tests
  login = jest.fn();
  logout = jest.fn();
  refreshToken = jest.fn();
  validateToken = jest.fn();
  createUser = jest.fn();
  updateUser = jest.fn();
  deleteUser = jest.fn();
  getUser = jest.fn();
  listUsers = jest.fn();
  updateProfile = jest.fn();
  uploadAvatar = jest.fn();
  getProfile = jest.fn();
  sendNotification = jest.fn();
  getNotificationPreferences = jest.fn();
  updateNotificationPreferences = jest.fn();
  trackUserEvent = jest.fn();
  getUserAnalytics = jest.fn();
}
```

## Good Example

```typescript
// Correct approach: Client-specific interfaces

// Authentication client interface
interface Authenticator {
  login(email: string, password: string): Promise<AuthToken>;
  logout(userId: string): Promise<void>;
  refreshToken(token: string): Promise<AuthToken>;
  validateToken(token: string): Promise<boolean>;
}

// User management client interface
interface UserManager {
  createUser(data: CreateUserData): Promise<User>;
  updateUser(id: string, data: UpdateUserData): Promise<User>;
  deleteUser(id: string): Promise<void>;
  getUser(id: string): Promise<User>;
  listUsers(filters: UserFilters): Promise<User[]>;
}

// Profile client interface
interface ProfileManager {
  updateProfile(userId: string, profile: ProfileData): Promise<Profile>;
  uploadAvatar(userId: string, image: Buffer): Promise<string>;
  getProfile(userId: string): Promise<Profile>;
}

// Notification client interface
interface NotificationManager {
  sendNotification(userId: string, notification: Notification): Promise<void>;
  getNotificationPreferences(userId: string): Promise<NotificationPrefs>;
  updateNotificationPreferences(userId: string, prefs: NotificationPrefs): Promise<void>;
}

// Analytics client interface
interface UserAnalyticsTracker {
  trackUserEvent(userId: string, event: AnalyticsEvent): Promise<void>;
  getUserAnalytics(userId: string): Promise<UserAnalytics>;
}

// Read-only user lookup for components that only need to fetch
interface UserLookup {
  getUser(id: string): Promise<User>;
  getProfile(userId: string): Promise<Profile>;
}

// Login page depends only on what it needs
class LoginPage {
  constructor(private auth: Authenticator) {} // Only 4 methods

  async handleLogin(email: string, password: string): Promise<void> {
    const token = await this.auth.login(email, password);
    this.storeToken(token);
  }
}

// Profile editor depends only on profile operations
class ProfileEditor {
  constructor(private profileManager: ProfileManager) {} // Only 3 methods

  async saveProfile(userId: string, data: ProfileData): Promise<void> {
    await this.profileManager.updateProfile(userId, data);
  }

  async loadProfile(userId: string): Promise<Profile> {
    return this.profileManager.getProfile(userId);
  }
}

// User display component only needs read access
class UserCard {
  constructor(private userLookup: UserLookup) {} // Only 2 methods

  async render(userId: string): Promise<void> {
    const user = await this.userLookup.getUser(userId);
    const profile = await this.userLookup.getProfile(userId);
    // Render user card
  }
}

// Admin panel needs user management
class AdminUserPanel {
  constructor(private userManager: UserManager) {} // Only 5 methods

  async deleteUserAccount(id: string): Promise<void> {
    await this.userManager.deleteUser(id);
  }
}

// Implementation can still implement multiple interfaces
class UserServiceImpl implements
  Authenticator,
  UserManager,
  ProfileManager,
  NotificationManager,
  UserAnalyticsTracker,
  UserLookup
{
  // Implement all methods...
  async login(email: string, password: string): Promise<AuthToken> { /* ... */ }
  async logout(userId: string): Promise<void> { /* ... */ }
  // ... rest of implementation
}

// Testing is now simple - only mock what you need
describe('LoginPage', () => {
  it('should login user', async () => {
    const mockAuth: Authenticator = {
      login: jest.fn().mockResolvedValue({ token: 'abc123' }),
      logout: jest.fn(),
      refreshToken: jest.fn(),
      validateToken: jest.fn()
    };

    const loginPage = new LoginPage(mockAuth);
    await loginPage.handleLogin('test@example.com', 'password');

    expect(mockAuth.login).toHaveBeenCalledWith('test@example.com', 'password');
  });
});

describe('ProfileEditor', () => {
  it('should save profile', async () => {
    const mockProfileManager: ProfileManager = {
      updateProfile: jest.fn().mockResolvedValue({}),
      uploadAvatar: jest.fn(),
      getProfile: jest.fn()
    };

    const editor = new ProfileEditor(mockProfileManager);
    await editor.saveProfile('user1', { name: 'John' });

    expect(mockProfileManager.updateProfile).toHaveBeenCalled();
  });
});
```

## Why

1. **Minimal Dependencies**: Each client depends only on the methods it actually uses, reducing coupling.

2. **Easier Testing**: Mocks are small and focused. Testing `LoginPage` requires mocking 4 methods, not 15+.

3. **Better Encapsulation**: Clients can't accidentally call methods they shouldn't have access to.

4. **Clearer Intent**: Interface names describe what clients need: `Authenticator`, `ProfileManager`, `UserLookup`.

5. **Independent Evolution**: Changes to analytics don't affect authentication clients. Interfaces can evolve separately.

6. **Flexible Composition**: Different implementations can provide different subsets of functionality.

7. **Single Responsibility**: Each interface has a focused responsibility, making the system easier to understand.
