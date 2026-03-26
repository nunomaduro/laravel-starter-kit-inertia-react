---
title: Use HTTP Methods Correctly
impact: CRITICAL
impactDescription: "Enables caching, retry logic, and semantic API operations"
tags: rest, http-methods, idempotency, safety
---

## Use HTTP Methods Correctly

**Impact: CRITICAL (Enables caching, retry logic, and semantic API operations)**

HTTP methods have specific semantics and should be used according to their intended purpose. Each method has distinct characteristics for safety and idempotency.

## Incorrect

```json
// ❌ Incorrect method usage
POST /users/123          // Should use GET to retrieve
GET /users/create        // Should use POST to create
POST /users/123/delete   // Should use DELETE method
GET /orders/123/update   // Should use PUT/PATCH
POST /search             // GET is better for safe operations
```

```javascript
// ❌ Incorrect method usage
app.post('/users/:id', (req, res) => {
  // Fetching user with POST - wrong!
  const user = db.findUser(req.params.id);
  res.json(user);
});

app.get('/users/delete/:id', (req, res) => {
  // Deleting with GET - dangerous!
  db.deleteUser(req.params.id);
  res.json({ deleted: true });
});

app.post('/users/:id/update', (req, res) => {
  // Custom action verb with POST
  db.updateUser(req.params.id, req.body);
  res.json({ updated: true });
});
```

**Problems:**
- GET requests that modify data can be triggered by crawlers or link prefetchers
- POST for retrieval prevents browser and CDN caching
- Clients cannot implement safe retry logic without correct method semantics
- Security tools and load balancers cannot apply method-based rules
- API documentation tools generate incorrect specifications

## Correct

```javascript
// ✅ Correct HTTP method usage
const express = require('express');
const router = express.Router();

// GET - Retrieve resource(s), safe and idempotent
router.get('/users', async (req, res) => {
  const users = await db.findUsers(req.query);
  res.json(users);
});

router.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  if (!user) return res.status(404).json({ error: 'User not found' });
  res.json(user);
});

// POST - Create new resource, not idempotent
router.post('/users', async (req, res) => {
  const user = await db.createUser(req.body);
  res.status(201).json(user);
});

// PUT - Replace entire resource, idempotent
router.put('/users/:id', async (req, res) => {
  const user = await db.replaceUser(req.params.id, req.body);
  res.json(user);
});

// PATCH - Partial update, not necessarily idempotent
router.patch('/users/:id', async (req, res) => {
  const user = await db.updateUser(req.params.id, req.body);
  res.json(user);
});

// DELETE - Remove resource, idempotent
router.delete('/users/:id', async (req, res) => {
  await db.deleteUser(req.params.id);
  res.status(204).send();
});

// HEAD - Same as GET but no body, for checking existence
router.head('/users/:id', async (req, res) => {
  const exists = await db.userExists(req.params.id);
  res.status(exists ? 200 : 404).send();
});

// OPTIONS - Return allowed methods
router.options('/users', (req, res) => {
  res.set('Allow', 'GET, POST, OPTIONS');
  res.status(204).send();
});
```

```python
# ✅ FastAPI with correct HTTP methods
from fastapi import FastAPI, HTTPException, status

app = FastAPI()

# GET - Retrieve
@app.get("/users")
def list_users(skip: int = 0, limit: int = 10):
    return db.get_users(skip=skip, limit=limit)

@app.get("/users/{user_id}")
def get_user(user_id: int):
    user = db.get_user(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    return user

# POST - Create
@app.post("/users", status_code=status.HTTP_201_CREATED)
def create_user(user: UserCreate):
    return db.create_user(user)

# PUT - Full replacement
@app.put("/users/{user_id}")
def replace_user(user_id: int, user: UserUpdate):
    return db.replace_user(user_id, user)

# PATCH - Partial update
@app.patch("/users/{user_id}")
def update_user(user_id: int, user: UserPatch):
    return db.update_user(user_id, user.dict(exclude_unset=True))

# DELETE - Remove
@app.delete("/users/{user_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_user(user_id: int):
    db.delete_user(user_id)
    return None
```

## HTTP Methods Reference

| Method  | Purpose | Safe | Idempotent | Request Body | Response Body |
|---------|---------|------|------------|--------------|---------------|
| GET     | Retrieve | Yes  | Yes        | No           | Yes           |
| POST    | Create   | No   | No         | Yes          | Yes           |
| PUT     | Replace  | No   | Yes        | Yes          | Yes           |
| PATCH   | Update   | No   | Not guaranteed | Yes      | Yes           |
| DELETE  | Remove   | No   | Yes        | Optional     | Optional      |
| HEAD    | Headers  | Yes  | Yes        | No           | No            |
| OPTIONS | Methods  | Yes  | Yes        | No           | No            |

**Benefits:**
- Each method has clear, well-defined purpose all developers understand
- GET requests can be cached by browsers and CDNs
- Browsers handle different methods appropriately (e.g., warn before resubmitting POST)
- Security tools, load balancers, and proxies understand HTTP semantics
- Idempotent methods (GET, PUT, DELETE) can be safely retried on network failures
- Tools like Swagger/OpenAPI rely on correct method usage for accurate documentation

Reference: [MDN HTTP Methods](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods)
