---
title: Use Nouns, Not Verbs for Resource Names
impact: CRITICAL
impactDescription: "Foundation of REST architecture"
tags: rest, resources, naming, http-methods
---

## Use Nouns, Not Verbs for Resource Names

**Impact: CRITICAL (Foundation of REST architecture)**

REST API endpoints should represent resources (nouns), not actions (verbs). HTTP methods already convey the action being performed.

## Incorrect

```json
// ❌ Verbs in endpoint names
GET /getUsers
POST /createUser
PUT /updateUser/123
DELETE /deleteUser/123
GET /fetchAllOrders
POST /addNewProduct
```

```javascript
// ❌ Express routes with verb-based endpoints
app.get('/getUsers', getUsers);
app.post('/createUser', createUser);
app.get('/fetchUserById/:id', getUserById);
app.put('/updateUserProfile/:id', updateUser);
app.delete('/removeUser/:id', deleteUser);
```

**Problems:**
- Redundant action verbs when HTTP methods already describe the operation
- Inconsistent naming across endpoints (get, fetch, create, add)
- More endpoints than necessary for the same resource
- URLs become unpredictable and hard to discover
- Breaks RESTful conventions that developers expect
- Cannot leverage HTTP method semantics for caching and retry logic

## Correct

```json
// ✅ Nouns representing resources
GET /users
POST /users
GET /users/123
PUT /users/123
DELETE /users/123
GET /orders
POST /products
```

```javascript
// ✅ Express routes with noun-based endpoints
app.get('/users', listUsers);
app.post('/users', createUser);
app.get('/users/:id', getUser);
app.put('/users/:id', updateUser);
app.delete('/users/:id', deleteUser);
```

```python
# ✅ FastAPI with noun-based resources
from fastapi import FastAPI

app = FastAPI()

@app.get("/users")
def list_users():
    return users

@app.post("/users")
def create_user(user: UserCreate):
    return new_user

@app.get("/users/{user_id}")
def get_user(user_id: int):
    return user

@app.put("/users/{user_id}")
def update_user(user_id: int, user: UserUpdate):
    return updated_user

@app.delete("/users/{user_id}")
def delete_user(user_id: int):
    return {"deleted": True}
```

**Benefits:**
- RESTful convention: URLs are resource identifiers, HTTP methods describe actions
- Consistent and predictable API structure developers can easily understand
- Fewer endpoints needed since one resource path handles multiple operations
- Self-documenting resources that map to domain model entities
- GET requests to noun-based endpoints can be cached effectively
- Leverages built-in HTTP method semantics

Reference: [REST Resource Naming Guide](https://restfulapi.net/resource-naming/)
