---
title: Typing Form Events
impact: HIGH
impactDescription: "prevents runtime errors from untyped form data access"
tags: events, forms, ChangeEvent, FormEvent
---

## Typing Form Events

**Impact: HIGH (prevents runtime errors from untyped form data access)**

Form events require specific types for each input element. Using `any` or omitting types loses all safety around `e.target.value` and form data access.

## Incorrect

```tsx
// ❌ Bad
function LoginForm() {
  const [email, setEmail] = useState("");
  const [role, setRole] = useState("");

  // Untyped event — no safety on e.target
  const handleChange = (e) => {
    setEmail(e.target.value);
  };

  // Using 'any' — defeats TypeScript
  const handleSubmit = (e: any) => {
    e.preventDefault();
    console.log(e.target.email.value);
  };

  // Wrong event type for select
  const handleSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    setRole(e.target.value); // Should be HTMLSelectElement
  };

  return (
    <form onSubmit={handleSubmit}>
      <input onChange={handleChange} />
      <select onChange={handleSelect}>
        <option value="admin">Admin</option>
      </select>
      <button type="submit">Login</button>
    </form>
  );
}
```

**Problems:**
- `any` and untyped handlers provide no autocomplete or type checking
- Wrong element types (e.g., `HTMLInputElement` for a `<select>`) cause silent mismatches
- `e.target` is typed as `EventTarget`, not the specific element, without proper generics

## Correct

```tsx
// ✅ Good
function LoginForm() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [role, setRole] = useState("");

  const handleEmailChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setEmail(e.target.value); // e.target is HTMLInputElement
  };

  const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setPassword(e.target.value);
  };

  const handleRoleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setRole(e.target.value); // e.target is HTMLSelectElement
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const data = {
      email: formData.get("email") as string,
      password: formData.get("password") as string,
      role: formData.get("role") as string,
    };
    console.log("Submitting:", data);
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        name="email"
        type="email"
        value={email}
        onChange={handleEmailChange}
      />
      <input
        name="password"
        type="password"
        value={password}
        onChange={handlePasswordChange}
      />
      <select name="role" value={role} onChange={handleRoleChange}>
        <option value="">Select role</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
      <textarea
        name="notes"
        onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => {
          console.log(e.target.value); // e.target is HTMLTextAreaElement
        }}
      />
      <button type="submit">Login</button>
    </form>
  );
}
```

**Benefits:**
- Each input element gets its correct event type (`HTMLInputElement`, `HTMLSelectElement`, `HTMLTextAreaElement`)
- `e.target` properties are fully typed with autocomplete
- `React.FormEvent<HTMLFormElement>` gives typed access to `e.currentTarget` for `FormData`
- Compile-time errors if you access properties that don't exist on the element

Reference: [React TypeScript Cheatsheet — Forms and Events](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/forms_and_events)
