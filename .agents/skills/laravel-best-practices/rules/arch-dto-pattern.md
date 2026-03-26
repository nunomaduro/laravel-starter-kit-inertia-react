---
title: Data Transfer Objects (DTOs)
impact: MEDIUM
impactDescription: Type safety and validation between layers
tags: architecture, dto, type-safety, validation
---

## Data Transfer Objects (DTOs)

**Impact: MEDIUM (Type safety and validation between layers)**

Use DTOs to transfer data between layers with type safety and validation.

## Bad Example

```php
// Passing arrays everywhere
class UserService
{
    public function createUser(array $data): User
    {
        // No type safety, uncertain what keys exist
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null, // May or may not exist
            'address' => $data['address'] ?? null,
        ]);
    }
}

// Controller passing raw array
class UserController extends Controller
{
    public function store(Request $request, UserService $service)
    {
        $user = $service->createUser($request->all());

        return new UserResource($user);
    }
}
```

## Good Example

```php
// app/DTOs/CreateUserDTO.php
namespace App\DTOs;

use App\Http\Requests\CreateUserRequest;
use Illuminate\Support\Facades\Hash;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?string $address = null,
    ) {}

    public static function fromRequest(CreateUserRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone: $request->validated('phone'),
            address: $request->validated('address'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'phone' => $this->phone,
            'address' => $this->address,
        ];
    }
}
```

```php
// app/Services/UserService.php
namespace App\Services;

use App\DTOs\CreateUserDTO;
use App\Models\User;

class UserService
{
    public function createUser(CreateUserDTO $dto): User
    {
        return User::create($dto->toArray());
    }
}
```

```php
// Controller creating DTO from request
namespace App\Http\Controllers;

use App\DTOs\CreateUserDTO;
use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{
    public function store(CreateUserRequest $request, UserService $service)
    {
        $dto = CreateUserDTO::fromRequest($request);
        $user = $service->createUser($dto);

        return new UserResource($user);
    }
}
```

```php
// Complex DTO with nested objects
namespace App\DTOs;

readonly class OrderDTO
{
    public function __construct(
        public int $userId,
        public AddressDTO $shippingAddress,
        public AddressDTO $billingAddress,
        /** @var OrderItemDTO[] */
        public array $items,
        public ?string $couponCode = null,
    ) {}

    public static function fromRequest(CreateOrderRequest $request): self
    {
        return new self(
            userId: auth()->id(),
            shippingAddress: AddressDTO::fromArray($request->validated('shipping_address')),
            billingAddress: AddressDTO::fromArray($request->validated('billing_address')),
            items: array_map(
                fn($item) => OrderItemDTO::fromArray($item),
                $request->validated('items')
            ),
            couponCode: $request->validated('coupon_code'),
        );
    }
}

readonly class AddressDTO
{
    public function __construct(
        public string $street,
        public string $city,
        public string $state,
        public string $zipCode,
        public string $country,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'],
            city: $data['city'],
            state: $data['state'],
            zipCode: $data['zip_code'],
            country: $data['country'],
        );
    }
}

readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
            notes: $data['notes'] ?? null,
        );
    }
}
```

## Why

- **Type safety**: IDE autocompletion and static analysis support
- **Self-documenting**: DTO properties clearly define expected data
- **Immutable**: Using `readonly` prevents accidental modifications
- **Validation**: Data is validated before DTO creation
- **Refactoring**: Easier to track data usage across the codebase
- **Testing**: Easy to create DTOs with known values for tests
