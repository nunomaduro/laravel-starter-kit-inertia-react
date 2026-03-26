---
section: forms
priority: medium
description: Transform form data before submission using the transform method
keywords: [transform, data, conversion, formatting, preprocessing]
---

# Form Transform Data

Use the transform method to modify form data before submission without changing the original form state.

## Bad Example

```tsx
// Anti-pattern: Modifying data directly before submit
export default function CreateProduct() {
  const { data, setData, post } = useForm({
    name: '',
    price: '', // Stored as string for input
    tags: [], // Array that needs to be comma-separated
  });

  const submit = (e) => {
    e.preventDefault();

    // Mutating data directly - causes issues
    const submitData = {
      ...data,
      price: parseFloat(data.price) * 100, // Convert to cents
      tags: data.tags.join(','),
    };

    // This still sends original data, not submitData!
    post('/products');
  };
}

// Anti-pattern: Maintaining separate submission state
const [submitData, setSubmitData] = useState({});

const submit = (e) => {
  e.preventDefault();
  // Complex state synchronization prone to bugs
  setSubmitData({
    ...data,
    price: parseFloat(data.price) * 100,
  });
  // Then somehow use submitData...
};
```

## Good Example

```tsx
// resources/js/Pages/Products/Create.tsx
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface ProductForm {
  name: string;
  price: string; // String for controlled input
  discount_percent: string;
  tags: string[];
  publish_date: Date | null;
  is_featured: boolean;
}

export default function Create() {
  const { data, setData, post, processing, errors, transform } = useForm<ProductForm>({
    name: '',
    price: '',
    discount_percent: '',
    tags: [],
    publish_date: null,
    is_featured: false,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    // Transform data just before submission
    transform((data) => ({
      ...data,
      // Convert price to cents for backend
      price: Math.round(parseFloat(data.price) * 100),
      // Convert percentage string to decimal
      discount_percent: data.discount_percent
        ? parseFloat(data.discount_percent) / 100
        : null,
      // Format date for Laravel
      publish_date: data.publish_date
        ? data.publish_date.toISOString().split('T')[0]
        : null,
      // Convert tags array to comma-separated string
      tags: data.tags.join(','),
    }));

    post(route('products.store'));
  };

  return (
    <form onSubmit={submit} className="space-y-6">
      <div>
        <label htmlFor="name">Product Name</label>
        <input
          id="name"
          value={data.name}
          onChange={(e) => setData('name', e.target.value)}
        />
      </div>

      <div>
        <label htmlFor="price">Price ($)</label>
        <input
          id="price"
          type="number"
          step="0.01"
          min="0"
          value={data.price}
          onChange={(e) => setData('price', e.target.value)}
          placeholder="29.99"
        />
        {/* User sees dollars, backend receives cents */}
      </div>

      <div>
        <label htmlFor="discount">Discount (%)</label>
        <input
          id="discount"
          type="number"
          min="0"
          max="100"
          value={data.discount_percent}
          onChange={(e) => setData('discount_percent', e.target.value)}
          placeholder="10"
        />
        {/* User enters 10, backend receives 0.10 */}
      </div>

      <TagInput
        tags={data.tags}
        onChange={(tags) => setData('tags', tags)}
      />

      <button type="submit" disabled={processing}>
        Create Product
      </button>
    </form>
  );
}

// More complex transformation example
interface RegistrationForm {
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  address: {
    street: string;
    city: string;
    zip: string;
  };
}

function Registration() {
  const { data, setData, post, transform } = useForm<RegistrationForm>({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: {
      street: '',
      city: '',
      zip: '',
    },
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    transform((data) => ({
      // Combine names for backend
      name: `${data.first_name} ${data.last_name}`.trim(),
      email: data.email.toLowerCase().trim(),
      // Normalize phone number
      phone: data.phone.replace(/\D/g, ''),
      // Flatten nested address for backend
      street: data.address.street,
      city: data.address.city,
      zip: data.address.zip,
    }));

    post(route('register'));
  };

  return (
    <form onSubmit={submit}>
      {/* Form fields use the structured data shape */}
      <input
        value={data.first_name}
        onChange={(e) => setData('first_name', e.target.value)}
        placeholder="First Name"
      />
      <input
        value={data.last_name}
        onChange={(e) => setData('last_name', e.target.value)}
        placeholder="Last Name"
      />
      <input
        value={data.address.street}
        onChange={(e) => setData('address', { ...data.address, street: e.target.value })}
        placeholder="Street Address"
      />
      {/* ... */}
    </form>
  );
}
```

## Why

The transform method provides clean data transformation:

1. **Separation of Concerns**: UI-friendly data shapes vs backend-required formats
2. **Immutability**: Original form data remains unchanged for continued editing
3. **Type Flexibility**: Input types (string) can differ from submission types (number)
4. **Validation Compatibility**: Errors still map to original field names
5. **Clean Components**: No manual data conversion scattered throughout submit handlers
6. **Reusability**: Same form can submit to different endpoints with different transformations
7. **Testability**: Transform logic is isolated and easy to test
