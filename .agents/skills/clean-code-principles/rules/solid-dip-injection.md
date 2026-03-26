---
id: solid-dip-injection
title: SOLID - Dependency Inversion (Injection)
category: solid-principles
priority: critical
tags: [SOLID, DIP, dependency-injection, testability]
related: [solid-dip-abstractions, solid-srp-class, core-composition]
---

# Dependency Inversion Principle - Dependency Injection

Dependencies should be injected from outside rather than created inside a class. This enables loose coupling, testability, and flexibility in how dependencies are provided.

## Bad Example

```typescript
// Anti-pattern: Creating dependencies inside the class

class UserRegistrationService {
  private userRepository: UserRepository;
  private passwordHasher: PasswordHasher;
  private emailService: EmailService;
  private logger: Logger;
  private analyticsService: AnalyticsService;

  constructor() {
    // Dependencies created inside - tight coupling
    this.userRepository = new PostgresUserRepository(
      new PostgresConnection('localhost', 5432, 'mydb')
    );
    this.passwordHasher = new BcryptPasswordHasher(10);
    this.emailService = new SendGridEmailService(process.env.SENDGRID_KEY!);
    this.logger = new WinstonLogger('UserRegistration');
    this.analyticsService = new MixpanelAnalytics(process.env.MIXPANEL_TOKEN!);
  }

  async register(email: string, password: string): Promise<User> {
    this.logger.info(`Registering user: ${email}`);

    const existingUser = await this.userRepository.findByEmail(email);
    if (existingUser) {
      throw new Error('User already exists');
    }

    const hashedPassword = await this.passwordHasher.hash(password);
    const user = await this.userRepository.create({ email, password: hashedPassword });

    await this.emailService.send({
      to: email,
      subject: 'Welcome!',
      body: 'Thanks for registering'
    });

    await this.analyticsService.track('user_registered', { userId: user.id });

    return user;
  }
}

// Problems:
// 1. Cannot test without real Postgres, SendGrid, Mixpanel
// 2. Cannot reuse with different implementations
// 3. Hard-coded configuration scattered through constructors
// 4. Class responsible for creating its dependencies
```

## Good Example

```typescript
// Correct approach: Dependencies injected from outside

// Interfaces for all dependencies
interface UserRepository {
  findByEmail(email: string): Promise<User | null>;
  findById(id: string): Promise<User | null>;
  create(data: CreateUserData): Promise<User>;
  update(id: string, data: Partial<User>): Promise<User>;
}

interface PasswordHasher {
  hash(password: string): Promise<string>;
  verify(password: string, hash: string): Promise<boolean>;
}

interface EmailService {
  send(options: EmailOptions): Promise<void>;
}

interface Logger {
  info(message: string, meta?: Record<string, any>): void;
  error(message: string, error?: Error, meta?: Record<string, any>): void;
  warn(message: string, meta?: Record<string, any>): void;
}

interface AnalyticsService {
  track(event: string, properties?: Record<string, any>): Promise<void>;
  identify(userId: string, traits?: Record<string, any>): Promise<void>;
}

// Service with constructor injection
class UserRegistrationService {
  constructor(
    private readonly userRepository: UserRepository,
    private readonly passwordHasher: PasswordHasher,
    private readonly emailService: EmailService,
    private readonly logger: Logger,
    private readonly analyticsService: AnalyticsService
  ) {}

  async register(email: string, password: string): Promise<User> {
    this.logger.info('Registering user', { email });

    const existingUser = await this.userRepository.findByEmail(email);
    if (existingUser) {
      throw new UserAlreadyExistsError(email);
    }

    const hashedPassword = await this.passwordHasher.hash(password);
    const user = await this.userRepository.create({
      email,
      password: hashedPassword,
      createdAt: new Date()
    });

    await this.sendWelcomeEmail(user);
    await this.trackRegistration(user);

    this.logger.info('User registered successfully', { userId: user.id });
    return user;
  }

  private async sendWelcomeEmail(user: User): Promise<void> {
    try {
      await this.emailService.send({
        to: user.email,
        subject: 'Welcome!',
        body: 'Thanks for registering'
      });
    } catch (error) {
      this.logger.error('Failed to send welcome email', error as Error, { userId: user.id });
      // Don't fail registration if email fails
    }
  }

  private async trackRegistration(user: User): Promise<void> {
    try {
      await this.analyticsService.identify(user.id, { email: user.email });
      await this.analyticsService.track('user_registered', { userId: user.id });
    } catch (error) {
      this.logger.error('Failed to track registration', error as Error, { userId: user.id });
      // Don't fail registration if analytics fails
    }
  }
}

// Concrete implementations
class PostgresUserRepository implements UserRepository {
  constructor(private db: DatabaseConnection) {}

  async findByEmail(email: string): Promise<User | null> {
    const result = await this.db.query('SELECT * FROM users WHERE email = $1', [email]);
    return result.rows[0] || null;
  }

  async findById(id: string): Promise<User | null> {
    const result = await this.db.query('SELECT * FROM users WHERE id = $1', [id]);
    return result.rows[0] || null;
  }

  async create(data: CreateUserData): Promise<User> {
    const result = await this.db.query(
      'INSERT INTO users (email, password, created_at) VALUES ($1, $2, $3) RETURNING *',
      [data.email, data.password, data.createdAt]
    );
    return result.rows[0];
  }

  async update(id: string, data: Partial<User>): Promise<User> {
    // Implementation
  }
}

class BcryptPasswordHasher implements PasswordHasher {
  constructor(private rounds: number = 10) {}

  async hash(password: string): Promise<string> {
    return bcrypt.hash(password, this.rounds);
  }

  async verify(password: string, hash: string): Promise<boolean> {
    return bcrypt.compare(password, hash);
  }
}

// Dependency Injection Container (manual)
class Container {
  private services: Map<string, any> = new Map();

  register<T>(key: string, factory: () => T): void {
    this.services.set(key, factory);
  }

  resolve<T>(key: string): T {
    const factory = this.services.get(key);
    if (!factory) {
      throw new Error(`Service not registered: ${key}`);
    }
    return factory();
  }
}

// Composition root - wire up all dependencies
function configureContainer(): Container {
  const container = new Container();

  // Register infrastructure
  container.register('DatabaseConnection', () =>
    new PostgresConnection(process.env.DATABASE_URL!)
  );

  container.register('Logger', () =>
    new WinstonLogger({ level: process.env.LOG_LEVEL || 'info' })
  );

  // Register repositories
  container.register('UserRepository', () =>
    new PostgresUserRepository(container.resolve('DatabaseConnection'))
  );

  // Register services
  container.register('PasswordHasher', () =>
    new BcryptPasswordHasher(12)
  );

  container.register('EmailService', () =>
    new SendGridEmailService(process.env.SENDGRID_API_KEY!)
  );

  container.register('AnalyticsService', () =>
    new MixpanelAnalytics(process.env.MIXPANEL_TOKEN!)
  );

  // Register application services
  container.register('UserRegistrationService', () =>
    new UserRegistrationService(
      container.resolve('UserRepository'),
      container.resolve('PasswordHasher'),
      container.resolve('EmailService'),
      container.resolve('Logger'),
      container.resolve('AnalyticsService')
    )
  );

  return container;
}

// Application startup
const container = configureContainer();
const registrationService = container.resolve<UserRegistrationService>('UserRegistrationService');

// Testing is now trivial with mock implementations
describe('UserRegistrationService', () => {
  let service: UserRegistrationService;
  let mockUserRepository: jest.Mocked<UserRepository>;
  let mockPasswordHasher: jest.Mocked<PasswordHasher>;
  let mockEmailService: jest.Mocked<EmailService>;
  let mockLogger: jest.Mocked<Logger>;
  let mockAnalytics: jest.Mocked<AnalyticsService>;

  beforeEach(() => {
    mockUserRepository = {
      findByEmail: jest.fn(),
      findById: jest.fn(),
      create: jest.fn(),
      update: jest.fn()
    };
    mockPasswordHasher = {
      hash: jest.fn(),
      verify: jest.fn()
    };
    mockEmailService = { send: jest.fn() };
    mockLogger = { info: jest.fn(), error: jest.fn(), warn: jest.fn() };
    mockAnalytics = { track: jest.fn(), identify: jest.fn() };

    service = new UserRegistrationService(
      mockUserRepository,
      mockPasswordHasher,
      mockEmailService,
      mockLogger,
      mockAnalytics
    );
  });

  it('should register a new user', async () => {
    mockUserRepository.findByEmail.mockResolvedValue(null);
    mockPasswordHasher.hash.mockResolvedValue('hashed_password');
    mockUserRepository.create.mockResolvedValue({
      id: '1',
      email: 'test@example.com',
      password: 'hashed_password',
      createdAt: new Date()
    });

    const user = await service.register('test@example.com', 'password123');

    expect(user.id).toBe('1');
    expect(mockPasswordHasher.hash).toHaveBeenCalledWith('password123');
    expect(mockEmailService.send).toHaveBeenCalled();
    expect(mockAnalytics.track).toHaveBeenCalledWith('user_registered', { userId: '1' });
  });

  it('should throw error if user already exists', async () => {
    mockUserRepository.findByEmail.mockResolvedValue({ id: '1', email: 'test@example.com' } as User);

    await expect(service.register('test@example.com', 'password123'))
      .rejects.toThrow(UserAlreadyExistsError);

    expect(mockUserRepository.create).not.toHaveBeenCalled();
  });

  it('should not fail registration if email sending fails', async () => {
    mockUserRepository.findByEmail.mockResolvedValue(null);
    mockPasswordHasher.hash.mockResolvedValue('hashed_password');
    mockUserRepository.create.mockResolvedValue({ id: '1', email: 'test@example.com' } as User);
    mockEmailService.send.mockRejectedValue(new Error('SMTP error'));

    const user = await service.register('test@example.com', 'password123');

    expect(user.id).toBe('1');
    expect(mockLogger.error).toHaveBeenCalled();
  });
});
```

## Why

1. **Testability**: All dependencies can be mocked. Tests run fast without external services.

2. **Flexibility**: Switch implementations at configuration time, not code change time.

3. **Single Responsibility**: Classes focus on business logic, not on constructing dependencies.

4. **Configuration Centralization**: All wiring happens in one place (composition root), making it easy to see and change.

5. **Lifetime Management**: The container can manage singleton vs. transient instances.

6. **Environment Adaptation**: Easy to provide different implementations for dev, test, staging, and production.

7. **Explicit Dependencies**: Looking at the constructor tells you exactly what a class needs to function.
