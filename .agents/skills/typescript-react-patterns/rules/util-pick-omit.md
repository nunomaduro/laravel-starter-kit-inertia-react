---
title: Pick, Omit, Partial for Props
impact: LOW
impactDescription: "eliminates prop duplication between related components"
tags: utility-types, Pick, Omit, Partial, composition
---

## Pick, Omit, Partial for Props

**Impact: LOW (eliminates prop duplication between related components)**

TypeScript's built-in utility types let you derive new prop types from existing ones instead of duplicating properties across related components.

## Incorrect

```tsx
// ❌ Bad
// Full user props — the "source of truth"
interface UserProps {
  id: string;
  name: string;
  email: string;
  avatar: string;
  role: "admin" | "user";
  createdAt: Date;
  lastLogin: Date;
}

// Duplicated subset for the header
interface UserHeaderProps {
  name: string;     // Duplicated from UserProps
  email: string;    // Duplicated from UserProps
  avatar: string;   // Duplicated from UserProps
}

// Duplicated subset for editing — must stay in sync manually
interface EditUserProps {
  name: string;     // Duplicated
  email: string;    // Duplicated
  avatar: string;   // Duplicated
  role: "admin" | "user"; // Duplicated
}

// If UserProps.name changes to firstName + lastName, you must update 3 places
```

**Problems:**
- Props are copied between interfaces and quickly fall out of sync
- Renaming or changing a type in the source requires updating every duplicate
- No compiler enforcement that derived types stay consistent with the source
- More code to maintain with no added safety

## Correct

```tsx
// ✅ Good

// Single source of truth
interface UserProps {
  id: string;
  name: string;
  email: string;
  avatar: string;
  role: "admin" | "user";
  createdAt: Date;
  lastLogin: Date;
}

// Pick — select only the properties you need
type UserHeaderProps = Pick<UserProps, "name" | "email" | "avatar">;

function UserHeader({ name, email, avatar }: UserHeaderProps) {
  return (
    <div className="header">
      <img src={avatar} alt={name} />
      <h2>{name}</h2>
      <p>{email}</p>
    </div>
  );
}

// Omit — exclude properties that don't apply
type EditUserProps = Omit<UserProps, "id" | "createdAt" | "lastLogin">;

function EditUserForm({ name, email, avatar, role }: EditUserProps) {
  return (
    <form>
      <input defaultValue={name} />
      <input defaultValue={email} />
      <input defaultValue={avatar} />
      <select defaultValue={role}>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
    </form>
  );
}

// Partial — make all properties optional (useful for update payloads)
type UpdateUserPayload = Partial<Omit<UserProps, "id" | "createdAt">>;

function updateUser(id: string, updates: UpdateUserPayload) {
  // Only changed fields are required
  // updateUser("1", { name: "New Name" }) — valid
  // updateUser("1", { email: "new@email.com", role: "admin" }) — valid
}

// Required — make optional properties required
interface FormFields {
  name?: string;
  email?: string;
  phone?: string;
}

type CompleteFormFields = Required<FormFields>;
// All fields are now required: { name: string; email: string; phone: string }

// Combining utility types
type UserSummary = Pick<UserProps, "id" | "name"> & {
  postCount: number;
};

function UserCard({ id, name, postCount }: UserSummary) {
  return (
    <div>
      <h3>{name}</h3>
      <p>{postCount} posts</p>
    </div>
  );
}

// Readonly — prevent mutation
type ImmutableUser = Readonly<UserProps>;

function UserDisplay({ user }: { user: ImmutableUser }) {
  // user.name = "new"; // Compile error — cannot assign to readonly property
  return <span>{user.name}</span>;
}
```

**Benefits:**
- `Pick` and `Omit` derive from a single source of truth, so changes propagate automatically
- `Partial` creates update/patch types without listing every optional field
- Combining utility types (`Partial<Omit<T, ...>>`) expresses precise constraints concisely
- Compiler catches inconsistencies immediately when the source type changes

Reference: [React TypeScript Cheatsheet — Useful Patterns by Use Case](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase)
