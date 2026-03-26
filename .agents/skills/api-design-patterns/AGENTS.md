# API Design Patterns - Complete Reference

**Version:** 2.0.0
**Date:** March 2026
**License:** MIT

## Abstract

RESTful API design principles for building consistent, developer-friendly APIs. Contains 38 rules across 7 categories covering resource design, error handling, security, pagination, versioning, response format, and documentation. Each rule includes incorrect and correct examples with problems and benefits.

## References

- [RESTful API Guidelines](https://restfulapi.net)
- [Zalando RESTful API Guidelines](https://zalando.github.io/restful-api-guidelines)
- [Microsoft API Guidelines](https://github.com/microsoft/api-guidelines)
- [Google API Design Guide](https://cloud.google.com/apis/design)
- [OpenAPI Specification](https://swagger.io/specification)

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Resource Design (rest)

**Impact:** CRITICAL
**Description:** Foundational REST principles for API endpoint design. Proper resource naming with nouns, plural collections, correct HTTP method semantics, appropriate status codes, idempotency, and HATEOAS links ensure APIs are intuitive, predictable, and follow industry standards.

## 2. Error Handling (error)

**Impact:** CRITICAL
**Description:** Consistent error response format across all endpoints. Machine-readable error codes, field-level validation details, meaningful messages, request IDs for debugging, and never exposing stack traces in production enable clients to handle errors programmatically.

## 3. Security (sec)

**Impact:** CRITICAL
**Description:** API security fundamentals. Authentication (OAuth2/JWT), authorization (RBAC), rate limiting, input validation and sanitization, CORS configuration with whitelists, HTTPS enforcement, and sensitive data protection prevent unauthorized access and common attack vectors.

## 4. Pagination & Filtering (page, filter, sort)

**Impact:** HIGH
**Description:** Efficient data retrieval for collections. Cursor pagination for large datasets, offset pagination for simple cases, consistent parameter naming, pagination metadata in responses, query parameter filtering, and flexible sorting enable clients to efficiently navigate large datasets.

## 5. Versioning (ver)

**Impact:** HIGH
**Description:** API versioning strategies for evolving APIs without breaking existing consumers. URL path versioning, header-based versioning, backward compatibility rules, and deprecation strategy with Sunset headers ensure smooth API evolution.

## 6. Response Format (resp)

**Impact:** MEDIUM
**Description:** Consistent response structure and conventions. Response envelopes, JSON naming conventions (camelCase vs snake_case), sparse fieldsets for bandwidth optimization, and response compression reduce payload sizes and improve developer experience.

## 7. Documentation (doc)

**Impact:** MEDIUM
**Description:** API documentation standards. OpenAPI/Swagger specifications, complete request/response examples, and API changelogs ensure consumers can discover, understand, and track changes to your API.


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


---

## Use Plural Nouns for Resource Collections

**Impact: CRITICAL (Improves API consistency and predictability)**

Resource names should consistently use plural nouns to represent collections, maintaining uniformity across your API.

## Incorrect

```json
// ❌ Inconsistent singular/plural usage
GET /user          // Singular for collection
GET /user/123      // Singular for individual
GET /products      // Plural for collection
GET /product/123   // Singular for individual
GET /order         // Inconsistent
POST /person       // Mixed conventions
```

```yaml
# ❌ OpenAPI spec with inconsistent naming
paths:
  /user:
    get:
      summary: Get all users
  /user/{id}:
    get:
      summary: Get single user
  /products:
    get:
      summary: Get all products
  /product/{id}:
    get:
      summary: Get single product
```

**Problems:**
- Developers must guess whether a resource uses singular or plural form
- Inconsistent patterns across endpoints reduce predictability
- Ambiguity: `/user` could mean "current user" or "user collection"
- Misalignment with database table naming conventions
- Harder to generate documentation and client SDKs

## Correct

```json
// ✅ Consistent plural nouns
GET /users         // Collection of users
GET /users/123     // Single user from collection
POST /users        // Create user in collection
PUT /users/123     // Update user in collection
DELETE /users/123  // Remove user from collection

GET /products      // Collection
GET /products/456  // Single item
GET /orders        // Collection
GET /orders/789    // Single item
```

```yaml
# ✅ OpenAPI spec with consistent plurals
openapi: 3.0.0
paths:
  /users:
    get:
      summary: List all users
      responses:
        '200':
          description: Array of users
    post:
      summary: Create a new user

  /users/{userId}:
    get:
      summary: Get a specific user
    put:
      summary: Update a specific user
    delete:
      summary: Delete a specific user

  /products:
    get:
      summary: List all products

  /products/{productId}:
    get:
      summary: Get a specific product
```

```javascript
// ✅ Express router with consistent plurals
const router = express.Router();

// Users resource
router.get('/users', listUsers);
router.post('/users', createUser);
router.get('/users/:id', getUser);
router.put('/users/:id', updateUser);
router.delete('/users/:id', deleteUser);

// Products resource
router.get('/products', listProducts);
router.post('/products', createProduct);
router.get('/products/:id', getProduct);
router.put('/products/:id', updateProduct);
router.delete('/products/:id', deleteProduct);

// Orders resource
router.get('/orders', listOrders);
router.post('/orders', createOrder);
router.get('/orders/:id', getOrder);
```

**Benefits:**
- Consistent naming eliminates guesswork for developers
- Predictable patterns: knowing `/users` lets you predict `/products`, `/orders`
- Plural names clearly indicate collection semantics
- `/users/123` reads naturally as "user 123 from the users collection"
- Aligns with database table naming conventions (users, products, orders)
- Matches expectations of REST frameworks and documentation generators

Reference: [REST API Tutorial - Resource Naming](https://restfulapi.net/resource-naming/)


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


---

## Design Nested Resources for Hierarchical Relationships

**Impact: CRITICAL (Clarifies resource relationships and authorization boundaries)**

Use nested URLs to represent parent-child relationships between resources, but avoid deep nesting beyond two levels.

## Incorrect

```json
// ❌ Deeply nested resources (3+ levels)
GET /companies/123/departments/456/employees/789/projects/101/tasks/202
POST /organizations/1/teams/2/members/3/assignments/4/subtasks

// ❌ Flat structure losing context
GET /tasks/202          // Which project? Which employee?
GET /comments/999       // Comment on what?

// ❌ Inconsistent nesting
GET /users/123/orders   // Nested
GET /order-items?orderId=456  // Query param
GET /products/789/reviews     // Nested again
```

```javascript
// ❌ Overly deep nesting
app.get('/companies/:companyId/departments/:deptId/employees/:empId/reviews/:reviewId',
  (req, res) => {
    // 4 levels deep - too complex!
    const { companyId, deptId, empId, reviewId } = req.params;
    // ...
  }
);
```

**Problems:**
- URLs become unwieldy and difficult to construct at 3+ levels deep
- Each nesting level adds required path parameters, complicating client code
- Inconsistent nesting patterns confuse API consumers
- Flat structures lose important relationship context
- Deep nesting makes authorization checks more complex

## Correct

```json
// ✅ Maximum 2 levels of nesting
GET /users/123/orders           // User's orders
GET /orders/456/items           // Order's items
GET /posts/789/comments         // Post's comments

// Access deep resources directly when needed
GET /tasks/202                  // Direct access with task ID
GET /employees/789              // Direct access with employee ID

// Use query parameters for filtering
GET /tasks?projectId=101        // Filter tasks by project
GET /tasks?employeeId=789&status=active
```

```javascript
// ✅ Express router with appropriate nesting
const router = express.Router();

// Parent resource
router.get('/users', listUsers);
router.get('/users/:userId', getUser);
router.post('/users', createUser);

// Nested child resource (1 level)
router.get('/users/:userId/orders', getUserOrders);
router.post('/users/:userId/orders', createUserOrder);
router.get('/users/:userId/orders/:orderId', getUserOrder);

// Second-level nested resource (2 levels max)
router.get('/users/:userId/orders/:orderId/items', getOrderItems);
router.post('/users/:userId/orders/:orderId/items', addOrderItem);

// Direct access for deep resources
router.get('/orders/:orderId', getOrder);
router.get('/order-items/:itemId', getOrderItem);
router.patch('/order-items/:itemId', updateOrderItem);
```

```python
# ✅ FastAPI with nested resources
from fastapi import APIRouter

router = APIRouter()

# Users - parent resource
@router.get("/users/{user_id}")
def get_user(user_id: int):
    return db.get_user(user_id)

# Posts - nested under users
@router.get("/users/{user_id}/posts")
def get_user_posts(user_id: int, skip: int = 0, limit: int = 10):
    return db.get_posts_by_user(user_id, skip, limit)

@router.post("/users/{user_id}/posts")
def create_user_post(user_id: int, post: PostCreate):
    return db.create_post(user_id, post)

# Comments - nested under posts (2 levels)
@router.get("/posts/{post_id}/comments")
def get_post_comments(post_id: int):
    return db.get_comments_by_post(post_id)

# Direct access for comments when needed
@router.get("/comments/{comment_id}")
def get_comment(comment_id: int):
    return db.get_comment(comment_id)

@router.patch("/comments/{comment_id}")
def update_comment(comment_id: int, update: CommentUpdate):
    return db.update_comment(comment_id, update)
```

```yaml
# ✅ OpenAPI spec with nested resources
openapi: 3.0.0
paths:
  /users/{userId}/orders:
    get:
      summary: Get all orders for a user
      parameters:
        - name: userId
          in: path
          required: true
          schema:
            type: integer

  /users/{userId}/orders/{orderId}:
    get:
      summary: Get a specific order for a user

  /orders/{orderId}/items:
    get:
      summary: Get all items in an order
    post:
      summary: Add item to order

  # Direct access endpoint
  /orders/{orderId}:
    get:
      summary: Get order by ID directly
```

**Benefits:**
- Nested URLs clearly show ownership and hierarchy (e.g., `/users/123/orders`)
- URL structure makes it easy to enforce authorization boundaries
- Limiting to 2 levels keeps URLs manageable and predictable
- Both nested and direct access patterns accommodate different use cases
- Creating under a parent automatically establishes the relationship
- Enables specific error messages like "Order 456 not found for user 123"

Reference: [REST API Design - Resource Relationships](https://restfulapi.net/resource-naming/)


---

## Use Appropriate HTTP Status Codes

**Impact: CRITICAL (Enables proper client handling, caching, and monitoring)**

Return semantically correct HTTP status codes that accurately describe the result of the operation.

## Incorrect

```javascript
// ❌ Always returning 200
app.post('/users', async (req, res) => {
  try {
    const user = await db.createUser(req.body);
    res.status(200).json(user); // Should be 201 Created
  } catch (error) {
    res.status(200).json({ error: error.message }); // Error with 200!
  }
});

app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  if (!user) {
    res.status(200).json({ error: 'Not found' }); // Should be 404
  }
  res.status(200).json(user);
});

app.delete('/users/:id', async (req, res) => {
  await db.deleteUser(req.params.id);
  res.status(200).json({ message: 'Deleted' }); // 204 is more appropriate
});
```

```json
// ❌ Error responses with 200 status
HTTP/1.1 200 OK
{
  "success": false,
  "error": "User not found"
}
```

**Problems:**
- Clients cannot distinguish success from failure without parsing the body
- HTTP caches will cache error responses as successful
- Monitoring and APM tools cannot track real error rates
- Retry logic cannot determine whether to retry based on status code
- Breaks HTTP standards and confuses developers

## Correct

```javascript
const express = require('express');
const router = express.Router();

// ✅ 200 OK - Successful GET, PUT, PATCH
router.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  if (!user) {
    return res.status(404).json({
      error: 'not_found',
      message: 'User not found'
    });
  }
  res.status(200).json(user);
});

// ✅ 201 Created - Successful POST that creates a resource
router.post('/users', async (req, res) => {
  const user = await db.createUser(req.body);
  res.status(201)
    .location(`/users/${user.id}`)
    .json(user);
});

// ✅ 204 No Content - Successful DELETE or update with no response body
router.delete('/users/:id', async (req, res) => {
  const deleted = await db.deleteUser(req.params.id);
  if (!deleted) {
    return res.status(404).json({
      error: 'not_found',
      message: 'User not found'
    });
  }
  res.status(204).send();
});

// ✅ 400 Bad Request - Invalid input
router.post('/users', async (req, res) => {
  if (!req.body.email) {
    return res.status(400).json({
      error: 'validation_error',
      message: 'Email is required',
      field: 'email'
    });
  }
  // ...
});

// ✅ 401 Unauthorized - Not authenticated
router.use((req, res, next) => {
  if (!req.headers.authorization) {
    return res.status(401).json({
      error: 'unauthorized',
      message: 'Authentication required'
    });
  }
  next();
});

// ✅ 403 Forbidden - Authenticated but not authorized
router.delete('/users/:id', async (req, res) => {
  if (req.user.id !== req.params.id && !req.user.isAdmin) {
    return res.status(403).json({
      error: 'forbidden',
      message: 'You cannot delete other users'
    });
  }
  // ...
});

// ✅ 409 Conflict - Resource conflict
router.post('/users', async (req, res) => {
  const exists = await db.userExists(req.body.email);
  if (exists) {
    return res.status(409).json({
      error: 'conflict',
      message: 'User with this email already exists'
    });
  }
  // ...
});

// ✅ 422 Unprocessable Entity - Semantic validation error
router.post('/orders', async (req, res) => {
  const product = await db.findProduct(req.body.productId);
  if (product.stock < req.body.quantity) {
    return res.status(422).json({
      error: 'unprocessable_entity',
      message: 'Insufficient stock',
      available: product.stock
    });
  }
  // ...
});
```

## Common Status Codes Reference

### Success (2xx)
| Code | Name | Use Case |
|------|------|----------|
| 200 | OK | Successful GET, PUT, PATCH |
| 201 | Created | Successful POST creating resource |
| 202 | Accepted | Request accepted for async processing |
| 204 | No Content | Successful DELETE or update with no body |

### Client Errors (4xx)
| Code | Name | Use Case |
|------|------|----------|
| 400 | Bad Request | Malformed syntax, invalid JSON |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated but not authorized |
| 404 | Not Found | Resource doesn't exist |
| 405 | Method Not Allowed | HTTP method not supported |
| 409 | Conflict | Resource conflict (duplicate) |
| 422 | Unprocessable Entity | Validation/business logic error |
| 429 | Too Many Requests | Rate limit exceeded |

### Server Errors (5xx)
| Code | Name | Use Case |
|------|------|----------|
| 500 | Internal Server Error | Unexpected server error |
| 502 | Bad Gateway | Upstream service error |
| 503 | Service Unavailable | Server temporarily unavailable |
| 504 | Gateway Timeout | Upstream service timeout |

**Benefits:**
- Status codes convey meaning before clients parse the response body
- HTTP clients, browsers, and tools handle different status codes appropriately
- Correct codes enable proper caching (2xx cached, 4xx/5xx not)
- Infrastructure and APM tools use status codes to track error rates and API health
- Clients can implement smart retry logic (retry 503, don't retry 400)
- Following HTTP standards ensures interoperability with tools and services

Reference: [MDN HTTP Status Codes](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)


---

## Implement Idempotency for Safe Retries

**Impact: CRITICAL (Prevents duplicate operations and enables safe retries)**

Idempotent operations produce the same result regardless of how many times they're executed. Implement idempotency keys for non-idempotent operations to enable safe retries.

## Incorrect

```javascript
// ❌ Non-idempotent POST without protection
app.post('/payments', async (req, res) => {
  // Each retry creates a duplicate payment!
  const payment = await db.createPayment({
    amount: req.body.amount,
    customerId: req.body.customerId
  });
  await chargeCard(payment);
  res.status(201).json(payment);
});

// ❌ No idempotency key checking
app.post('/orders', async (req, res) => {
  // Network timeout after processing = client retries = duplicate order
  const order = await db.createOrder(req.body);
  await processOrder(order);
  res.status(201).json(order);
});
```

```json
// ❌ Client retries without idempotency key
POST /payments
{
  "amount": 100,
  "customerId": "cust_123"
}
// Timeout... retry... duplicate payment created!
```

**Problems:**
- Duplicate payments or orders when clients retry after network timeouts
- No way for the server to detect repeated requests
- Financial losses from double-charging customers
- Data inconsistency in distributed systems with message retries
- Clients must implement complex tracking logic to avoid duplicates

## Correct

```javascript
// ✅ Idempotency key middleware
const express = require('express');
const router = express.Router();

const idempotencyStore = new Map(); // Use Redis in production

async function idempotencyMiddleware(req, res, next) {
  const idempotencyKey = req.headers['idempotency-key'];

  if (!idempotencyKey) {
    return res.status(400).json({
      error: 'missing_idempotency_key',
      message: 'Idempotency-Key header is required for this operation'
    });
  }

  const cacheKey = `${req.path}:${idempotencyKey}`;
  const cached = idempotencyStore.get(cacheKey);

  if (cached) {
    // Return cached response
    return res.status(cached.status).json(cached.body);
  }

  // Store original json function
  const originalJson = res.json.bind(res);

  // Override to cache response
  res.json = function(body) {
    idempotencyStore.set(cacheKey, {
      status: res.statusCode,
      body: body
    });
    // Set TTL (24 hours typical)
    setTimeout(() => idempotencyStore.delete(cacheKey), 24 * 60 * 60 * 1000);
    return originalJson(body);
  };

  next();
}

// ✅ Apply to non-idempotent operations
router.post('/payments', idempotencyMiddleware, async (req, res) => {
  const payment = await db.createPayment({
    amount: req.body.amount,
    customerId: req.body.customerId,
    idempotencyKey: req.headers['idempotency-key']
  });

  await chargeCard(payment);
  res.status(201).json(payment);
});

// ✅ Idempotent by design using upsert
router.put('/users/:id/preferences', async (req, res) => {
  // PUT is idempotent - same request always produces same result
  const preferences = await db.upsertPreferences(
    req.params.id,
    req.body
  );
  res.json(preferences);
});

// ✅ Natural idempotency with unique constraints
router.post('/subscriptions', async (req, res) => {
  try {
    const subscription = await db.createSubscription({
      userId: req.body.userId,
      planId: req.body.planId
    });
    res.status(201).json(subscription);
  } catch (error) {
    if (error.code === 'UNIQUE_VIOLATION') {
      // Return existing subscription
      const existing = await db.findSubscription(
        req.body.userId,
        req.body.planId
      );
      return res.status(200).json(existing);
    }
    throw error;
  }
});
```

```python
# ✅ FastAPI with idempotency
from fastapi import FastAPI, Header, HTTPException
from functools import wraps
import redis

app = FastAPI()
redis_client = redis.Redis()

def idempotent(ttl_seconds: int = 86400):
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, idempotency_key: str = Header(...), **kwargs):
            cache_key = f"idempotency:{func.__name__}:{idempotency_key}"

            # Check cache
            cached = redis_client.get(cache_key)
            if cached:
                return json.loads(cached)

            # Execute operation
            result = await func(*args, **kwargs)

            # Cache result
            redis_client.setex(cache_key, ttl_seconds, json.dumps(result))

            return result
        return wrapper
    return decorator

@app.post("/payments")
@idempotent(ttl_seconds=86400)
async def create_payment(payment: PaymentCreate):
    result = await process_payment(payment)
    return {"id": result.id, "status": result.status}
```

```json
// ✅ Client request with idempotency key
POST /payments HTTP/1.1
Host: api.example.com
Content-Type: application/json
Idempotency-Key: unique-request-id-12345

{
  "amount": 100,
  "customerId": "cust_123"
}

// Response (same for retries)
HTTP/1.1 201 Created
Idempotency-Key: unique-request-id-12345

{
  "id": "pay_789",
  "amount": 100,
  "customerId": "cust_123",
  "status": "completed"
}
```

## Idempotency by HTTP Method

| Method | Naturally Idempotent | Notes |
|--------|---------------------|-------|
| GET | Yes | Always safe to retry |
| HEAD | Yes | Always safe to retry |
| OPTIONS | Yes | Always safe to retry |
| PUT | Yes | Full replacement is idempotent |
| DELETE | Yes | Deleting twice = same result |
| POST | No | Needs idempotency key |
| PATCH | Usually | Depends on implementation |

**Benefits:**
- Clients can safely retry requests without causing duplicate operations
- Prevents duplicate payments or orders that cause financial and data issues
- Users can safely click "submit" multiple times without fear
- Works well in distributed systems with at-least-once delivery guarantees
- Idempotency keys provide request correlation across systems
- Simplifies client code by removing complex success-tracking logic

Reference: [Stripe Idempotency Guide](https://stripe.com/docs/api/idempotent_requests)


---

## Include HATEOAS Links for Discoverability

**Impact: CRITICAL (Improves API discoverability and reduces client coupling)**

HATEOAS (Hypermedia as the Engine of Application State) provides links in responses that guide clients to related resources and available actions.

## Incorrect

```json
// ❌ No links, client must construct URLs
{
  "id": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "orderId": 456
}
// Client must know to call GET /orders/456 to get order details
// No indication of available actions
```

```javascript
// ❌ Response without navigation
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  res.json(user); // Raw data only
});
```

**Problems:**
- Clients must hardcode URL patterns, creating tight coupling
- No indication of what actions are available on a resource
- API URL changes break all clients
- New features are not automatically discoverable
- Clients cannot adapt behavior based on resource state

## Correct

```javascript
// ✅ Response with HATEOAS links
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  const baseUrl = `${req.protocol}://${req.get('host')}`;

  res.json({
    id: user.id,
    name: user.name,
    email: user.email,
    _links: {
      self: {
        href: `${baseUrl}/users/${user.id}`,
        method: 'GET'
      },
      update: {
        href: `${baseUrl}/users/${user.id}`,
        method: 'PUT'
      },
      delete: {
        href: `${baseUrl}/users/${user.id}`,
        method: 'DELETE'
      },
      orders: {
        href: `${baseUrl}/users/${user.id}/orders`,
        method: 'GET'
      },
      createOrder: {
        href: `${baseUrl}/users/${user.id}/orders`,
        method: 'POST'
      }
    }
  });
});

// ✅ Collection with pagination links
app.get('/users', async (req, res) => {
  const page = parseInt(req.query.page) || 1;
  const limit = parseInt(req.query.limit) || 20;
  const { users, total } = await db.findUsers({ page, limit });
  const baseUrl = `${req.protocol}://${req.get('host')}`;
  const totalPages = Math.ceil(total / limit);

  res.json({
    data: users.map(user => ({
      ...user,
      _links: {
        self: { href: `${baseUrl}/users/${user.id}` }
      }
    })),
    _links: {
      self: { href: `${baseUrl}/users?page=${page}&limit=${limit}` },
      first: { href: `${baseUrl}/users?page=1&limit=${limit}` },
      last: { href: `${baseUrl}/users?page=${totalPages}&limit=${limit}` },
      ...(page > 1 && {
        prev: { href: `${baseUrl}/users?page=${page - 1}&limit=${limit}` }
      }),
      ...(page < totalPages && {
        next: { href: `${baseUrl}/users?page=${page + 1}&limit=${limit}` }
      })
    },
    _meta: {
      currentPage: page,
      totalPages,
      totalItems: total,
      itemsPerPage: limit
    }
  });
});
```

```json
// ✅ Example response with HATEOAS
{
  "id": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "status": "active",
  "_links": {
    "self": {
      "href": "https://api.example.com/users/123",
      "method": "GET"
    },
    "update": {
      "href": "https://api.example.com/users/123",
      "method": "PUT"
    },
    "deactivate": {
      "href": "https://api.example.com/users/123/deactivate",
      "method": "POST"
    },
    "orders": {
      "href": "https://api.example.com/users/123/orders",
      "method": "GET"
    },
    "avatar": {
      "href": "https://api.example.com/users/123/avatar",
      "method": "GET",
      "type": "image/png"
    }
  },
  "_embedded": {
    "latestOrder": {
      "id": 456,
      "total": 99.99,
      "_links": {
        "self": { "href": "https://api.example.com/orders/456" }
      }
    }
  }
}
```

```python
# ✅ FastAPI with HATEOAS helper
from fastapi import FastAPI, Request
from pydantic import BaseModel
from typing import Dict, List, Optional, Any

app = FastAPI()

class Link(BaseModel):
    href: str
    method: str = "GET"
    type: Optional[str] = None

class HATEOASResponse(BaseModel):
    data: Any
    _links: Dict[str, Link]
    _embedded: Optional[Dict[str, Any]] = None

def build_user_links(request: Request, user_id: int) -> Dict[str, Link]:
    base_url = str(request.base_url).rstrip('/')
    return {
        "self": Link(href=f"{base_url}/users/{user_id}"),
        "update": Link(href=f"{base_url}/users/{user_id}", method="PUT"),
        "delete": Link(href=f"{base_url}/users/{user_id}", method="DELETE"),
        "orders": Link(href=f"{base_url}/users/{user_id}/orders"),
    }

@app.get("/users/{user_id}")
async def get_user(user_id: int, request: Request):
    user = await db.get_user(user_id)
    return {
        **user.dict(),
        "_links": build_user_links(request, user_id)
    }

@app.get("/orders/{order_id}")
async def get_order(order_id: int, request: Request):
    order = await db.get_order(order_id)
    base_url = str(request.base_url).rstrip('/')

    cancel_link = (
        {"href": f"{base_url}/orders/{order_id}/cancel", "method": "POST"}
        if order.status == "pending"
        else None
    )

    return {
        **order.dict(),
        "_links": {
            "self": {"href": f"{base_url}/orders/{order_id}"},
            "customer": {"href": f"{base_url}/users/{order.customer_id}"},
            "items": {"href": f"{base_url}/orders/{order_id}/items"},
            "cancel": cancel_link,
            "invoice": {"href": f"{base_url}/orders/{order_id}/invoice", "type": "application/pdf"}
        }
    }
```

## HAL Format (Common Standard)

```json
{
  "_links": {
    "self": { "href": "/orders/123" },
    "customer": { "href": "/customers/456", "title": "John Doe" },
    "items": { "href": "/orders/123/items" }
  },
  "id": 123,
  "total": 99.99,
  "status": "shipped",
  "_embedded": {
    "items": [
      {
        "_links": { "self": { "href": "/products/789" } },
        "name": "Widget",
        "quantity": 2
      }
    ]
  }
}
```

**Benefits:**
- Responses tell clients exactly what actions are available and how to perform them
- Clients follow links dynamically instead of hardcoding URL patterns
- APIs can change URL structures without breaking clients
- New features are automatically discoverable through new links
- Links can vary based on resource state (e.g., "cancel" only for pending orders)
- Links guide users through multi-step processes naturally

Reference: [HAL Specification](https://stateless.group/hal_specification.html)


---

## Handle Non-CRUD Actions on Resources

**Impact: CRITICAL (Proper handling of complex operations and state transitions)**

Some operations don't fit standard CRUD patterns. Use sub-resources or action endpoints for operations that represent state transitions or complex actions.

## Incorrect

```json
// ❌ Verbs in main resource path
POST /activateUser/123
POST /deactivateUser/123
POST /sendEmailToUser/123
POST /approveOrder/456
POST /shipOrder/456

// ❌ Query parameters for actions
POST /users/123?action=activate
POST /orders/456?action=ship&tracking=ABC123
```

```javascript
// ❌ Complex PATCH with action semantics
app.patch('/orders/:id', (req, res) => {
  // Trying to use PATCH for everything
  if (req.body.status === 'shipped') {
    // Shipping logic, tracking number, notifications...
  } else if (req.body.status === 'cancelled') {
    // Cancellation logic, refund, restock...
  }
});
```

**Problems:**
- Verb-based paths break RESTful naming conventions
- Query parameter actions are unpredictable and hard to document
- Overloaded PATCH endpoints hide complex business logic and side effects
- Difficult to apply fine-grained authorization per action
- State transition validation gets tangled in a single handler
- No clear audit trail of specific operations performed

## Correct

```javascript
// ✅ Sub-resource actions (noun-based)
const router = express.Router();

// User lifecycle actions
router.post('/users/:id/activation', activateUser);      // Activate user
router.delete('/users/:id/activation', deactivateUser);  // Deactivate user
router.post('/users/:id/password-reset', resetPassword); // Reset password
router.post('/users/:id/verification', sendVerification); // Send verification

// Order workflow actions
router.post('/orders/:id/shipment', shipOrder);          // Ship order
router.post('/orders/:id/cancellation', cancelOrder);    // Cancel order
router.post('/orders/:id/refund', refundOrder);          // Refund order

// Controller actions as verbs when necessary
router.post('/orders/:id/ship', async (req, res) => {
  const { trackingNumber, carrier } = req.body;
  const order = await orderService.ship(req.params.id, {
    trackingNumber,
    carrier
  });
  res.json(order);
});

router.post('/orders/:id/cancel', async (req, res) => {
  const { reason } = req.body;
  const order = await orderService.cancel(req.params.id, reason);
  res.json(order);
});
```

```python
# ✅ FastAPI with action endpoints
from fastapi import APIRouter, HTTPException
from enum import Enum

router = APIRouter()

class OrderStatus(str, Enum):
    PENDING = "pending"
    CONFIRMED = "confirmed"
    SHIPPED = "shipped"
    DELIVERED = "delivered"
    CANCELLED = "cancelled"

# State transition actions
@router.post("/orders/{order_id}/confirm")
async def confirm_order(order_id: int):
    order = await db.get_order(order_id)
    if order.status != OrderStatus.PENDING:
        raise HTTPException(
            status_code=422,
            detail=f"Cannot confirm order in {order.status} status"
        )
    order.status = OrderStatus.CONFIRMED
    await db.save_order(order)
    await notification_service.send_confirmation(order)
    return order

@router.post("/orders/{order_id}/ship")
async def ship_order(order_id: int, shipment: ShipmentInfo):
    order = await db.get_order(order_id)
    if order.status != OrderStatus.CONFIRMED:
        raise HTTPException(
            status_code=422,
            detail=f"Cannot ship order in {order.status} status"
        )

    order.status = OrderStatus.SHIPPED
    order.tracking_number = shipment.tracking_number
    order.carrier = shipment.carrier
    order.shipped_at = datetime.utcnow()

    await db.save_order(order)
    await notification_service.send_shipping_notification(order)
    return order

@router.post("/orders/{order_id}/cancel")
async def cancel_order(order_id: int, cancellation: CancellationRequest):
    order = await db.get_order(order_id)
    if order.status in [OrderStatus.SHIPPED, OrderStatus.DELIVERED]:
        raise HTTPException(
            status_code=422,
            detail="Cannot cancel shipped or delivered orders"
        )

    order.status = OrderStatus.CANCELLED
    order.cancellation_reason = cancellation.reason

    await db.save_order(order)
    await payment_service.refund(order)
    await inventory_service.restock(order.items)

    return order

# Batch actions
@router.post("/orders/bulk-ship")
async def bulk_ship_orders(request: BulkShipRequest):
    results = []
    for order_id in request.order_ids:
        try:
            result = await ship_order(order_id, request.shipment_info)
            results.append({"order_id": order_id, "status": "shipped"})
        except HTTPException as e:
            results.append({"order_id": order_id, "status": "failed", "error": e.detail})
    return {"results": results}
```

```yaml
# ✅ OpenAPI spec for actions
openapi: 3.0.0
paths:
  /orders/{orderId}/ship:
    post:
      summary: Ship an order
      description: Transitions order to shipped status
      parameters:
        - name: orderId
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - trackingNumber
                - carrier
              properties:
                trackingNumber:
                  type: string
                carrier:
                  type: string
                  enum: [ups, fedex, usps, dhl]
      responses:
        '200':
          description: Order shipped successfully
        '422':
          description: Order cannot be shipped in current state

  /orders/{orderId}/cancel:
    post:
      summary: Cancel an order
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                reason:
                  type: string
      responses:
        '200':
          description: Order cancelled successfully
        '422':
          description: Order cannot be cancelled
```

## Patterns for Actions

| Action Type | Pattern | Example |
|-------------|---------|---------|
| State transition | POST /resource/{id}/action | POST /orders/123/ship |
| Sub-resource creation | POST /resource/{id}/sub-resource | POST /users/123/password-reset |
| Batch operation | POST /resources/bulk-action | POST /orders/bulk-ship |
| Controller action | POST /action (no resource ID) | POST /search, POST /calculate |

**Benefits:**
- Action endpoints explicitly show what operation is being performed
- Each action can have specific validation rules and business logic
- Complex state transitions are better modeled as explicit actions than PATCH
- Side effects (notifications, payments) get dedicated endpoints
- Fine-grained authorization (can ship orders vs. can cancel orders)
- Each action creates a clear, auditable record of what happened

Reference: [REST API Design - Actions](https://restfulapi.net/rest-api-design-tutorial-with-example/)


---

## Consistent Error Response Format

**Impact: CRITICAL (Enables predictable error handling across API)**

Inconsistent error formats force API consumers to handle multiple error structures, leading to fragile client code. A consistent error format makes APIs predictable, easier to debug, and simpler to integrate.

## Incorrect

```json
// ❌ Different formats across endpoints
// Endpoint A
{ "error": "Not found" }

// Endpoint B
{ "message": "Invalid email", "status": 400 }

// Endpoint C
{ "errors": ["Field required", "Invalid format"] }

// Endpoint D
{
  "success": false,
  "errorMessage": "Something went wrong"
}

// Endpoint E - just a string
"User not found"
```

**Problems:**
- Clients can't predict error structure
- Different parsing logic needed for each endpoint
- Hard to build generic error handlers
- Inconsistent developer experience

## Correct

### Standard Error Envelope

```json
// ✅ Every error follows the same structure
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable message",
    "details": [],
    "request_id": "req_abc123"
  }
}
```

### Validation Errors (422)

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The request contains invalid data",
    "details": [
      {
        "field": "email",
        "code": "INVALID_FORMAT",
        "message": "Please provide a valid email address"
      },
      {
        "field": "password",
        "code": "TOO_SHORT",
        "message": "Password must be at least 8 characters",
        "meta": {
          "min_length": 8,
          "actual_length": 5
        }
      },
      {
        "field": "age",
        "code": "OUT_OF_RANGE",
        "message": "Age must be between 18 and 120",
        "meta": {
          "min": 18,
          "max": 120,
          "actual": 15
        }
      }
    ],
    "request_id": "req_abc123"
  }
}
```

### Not Found (404)

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "User with ID 'usr_123' not found",
    "details": [
      {
        "resource": "user",
        "field": "id",
        "value": "usr_123"
      }
    ],
    "request_id": "req_def456"
  }
}
```

### Authentication Error (401)

```json
{
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required",
    "details": [
      {
        "code": "TOKEN_EXPIRED",
        "message": "Your access token has expired"
      }
    ],
    "request_id": "req_ghi789"
  }
}
```

### Authorization Error (403)

```json
{
  "error": {
    "code": "FORBIDDEN",
    "message": "You don't have permission to access this resource",
    "details": [
      {
        "resource": "order",
        "action": "delete",
        "reason": "Only admins can delete orders"
      }
    ],
    "request_id": "req_jkl012"
  }
}
```

### Conflict Error (409)

```json
{
  "error": {
    "code": "CONFLICT",
    "message": "A user with this email already exists",
    "details": [
      {
        "field": "email",
        "code": "DUPLICATE",
        "value": "john@example.com"
      }
    ],
    "request_id": "req_mno345"
  }
}
```

### Rate Limit Error (429)

```json
{
  "error": {
    "code": "RATE_LIMITED",
    "message": "Too many requests. Please retry after 60 seconds.",
    "details": [
      {
        "limit": 100,
        "window": "1 minute",
        "retry_after": 60
      }
    ],
    "request_id": "req_pqr678"
  }
}
```

### Server Error (500)

```json
{
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An unexpected error occurred. Please try again later.",
    "request_id": "req_stu901"
  }
}
```

**Note:** Never expose stack traces or internal details in production.

## Implementation

### TypeScript/Node.js

```typescript
// ✅ Error classes
abstract class AppError extends Error {
  abstract readonly code: string;
  abstract readonly statusCode: number;
  readonly details: ErrorDetail[];

  constructor(message: string, details: ErrorDetail[] = []) {
    super(message);
    this.details = details;
  }

  toJSON() {
    return {
      error: {
        code: this.code,
        message: this.message,
        details: this.details.length > 0 ? this.details : undefined,
      }
    };
  }
}

class ValidationError extends AppError {
  readonly code = 'VALIDATION_ERROR';
  readonly statusCode = 422;
}

class NotFoundError extends AppError {
  readonly code = 'NOT_FOUND';
  readonly statusCode = 404;
}

class UnauthorizedError extends AppError {
  readonly code = 'UNAUTHORIZED';
  readonly statusCode = 401;
}

// Error handler middleware
function errorHandler(err, req, res, next) {
  const requestId = req.id || generateRequestId();

  if (err instanceof AppError) {
    return res.status(err.statusCode).json({
      error: {
        ...err.toJSON().error,
        request_id: requestId,
      }
    });
  }

  // Log unexpected errors
  logger.error('Unexpected error', { error: err, requestId });

  // Generic response for unknown errors
  res.status(500).json({
    error: {
      code: 'INTERNAL_ERROR',
      message: 'An unexpected error occurred',
      request_id: requestId,
    }
  });
}
```

### Laravel/PHP

```php
<?php

// ✅ app/Exceptions/AppException.php
abstract class AppException extends Exception
{
    abstract public function getErrorCode(): string;
    abstract public function getStatusCode(): int;

    protected array $details = [];

    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->getErrorCode(),
                'message' => $this->getMessage(),
                'details' => $this->details ?: null,
                'request_id' => request()->id(),
            ],
        ], $this->getStatusCode());
    }
}

class ValidationException extends AppException
{
    public function getErrorCode(): string
    {
        return 'VALIDATION_ERROR';
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public static function fromValidator(Validator $validator): self
    {
        $details = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            $details[] = [
                'field' => $field,
                'code' => 'INVALID',
                'message' => $messages[0],
            ];
        }

        return (new self('The request contains invalid data'))
            ->setDetails($details);
    }
}

// Handler
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($e instanceof AppException) {
            return $e->render();
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Resource not found',
                    'request_id' => $request->id(),
                ],
            ], 404);
        }

        // Log and return generic error
        Log::error($e->getMessage(), ['exception' => $e]);

        return response()->json([
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'An unexpected error occurred',
                'request_id' => $request->id(),
            ],
        ], 500);
    }
}
```

## Error Code Naming

```
// ✅ Use SCREAMING_SNAKE_CASE
VALIDATION_ERROR
NOT_FOUND
UNAUTHORIZED
FORBIDDEN
RATE_LIMITED
INTERNAL_ERROR

// ✅ Be specific
INVALID_EMAIL_FORMAT
PASSWORD_TOO_SHORT
DUPLICATE_EMAIL
TOKEN_EXPIRED
INSUFFICIENT_FUNDS

// ❌ Avoid vague codes
ERROR
FAILED
BAD_REQUEST
```

**Benefits:**
- Predictable API behavior across all endpoints
- Reusable client-side error handling logic
- Easier debugging with request IDs
- Clear error codes for programmatic handling
- Human-readable messages for display
- Detailed validation feedback for field-level errors

Reference: [RFC 7807 - Problem Details for HTTP APIs](https://www.rfc-editor.org/rfc/rfc7807)


---

## Provide Meaningful Error Messages

**Impact: CRITICAL (Reduces support burden and improves developer experience)**

Error messages should be clear, actionable, and help users understand what went wrong and how to fix it.

## Incorrect

```json
// ❌ Vague or unhelpful messages
{
  "error": "Error"
}

{
  "error": "Bad request"
}

{
  "error": "Invalid input"
}

{
  "error": "Something went wrong"
}

{
  "error": "null"
}

{
  "error": "Error code: 0x8004005"
}

// ❌ Technical jargon users can't understand
{
  "error": "SQLITE_CONSTRAINT_FOREIGNKEY"
}

{
  "error": "MongoServerError: E11000 duplicate key error"
}
```

```javascript
// ❌ Unhelpful error responses
app.post('/users', async (req, res) => {
  try {
    await db.createUser(req.body);
  } catch (error) {
    res.status(400).json({ error: 'Bad request' }); // What's bad about it?
  }
});
```

**Problems:**
- Users cannot determine what went wrong or how to fix it
- Developers waste time debugging vague error messages
- Support burden increases with every unclear error
- Internal database errors leak implementation details
- No actionable guidance for the next step

## Correct

```javascript
// ✅ Clear, actionable error messages
const errorMessages = {
  email_required: 'Email address is required to create an account',
  email_invalid: 'Please provide a valid email address (e.g., user@example.com)',
  email_taken: 'An account with this email already exists. Try signing in instead',
  password_weak: 'Password must be at least 8 characters with one uppercase, one lowercase, and one number',
  rate_limited: 'Too many requests. Please wait 60 seconds before trying again',
  resource_not_found: 'The requested user could not be found. It may have been deleted',
  permission_denied: 'You do not have permission to access this resource. Contact your administrator',
  payment_failed: 'Your payment could not be processed. Please check your card details and try again'
};

app.post('/users', async (req, res, next) => {
  try {
    const { email, password, name } = req.body;

    // Specific validation messages
    if (!email) {
      return res.status(400).json({
        error: {
          code: 'validation_error',
          message: 'Email address is required to create an account',
          field: 'email'
        }
      });
    }

    if (!isValidEmail(email)) {
      return res.status(400).json({
        error: {
          code: 'validation_error',
          message: 'Please provide a valid email address (e.g., user@example.com)',
          field: 'email',
          provided: email
        }
      });
    }

    const existingUser = await db.findUserByEmail(email);
    if (existingUser) {
      return res.status(409).json({
        error: {
          code: 'resource_conflict',
          message: 'An account with this email already exists. Try signing in or resetting your password',
          field: 'email',
          links: {
            signin: '/auth/signin',
            passwordReset: '/auth/password-reset'
          }
        }
      });
    }

    const user = await db.createUser({ email, password, name });
    res.status(201).json(user);

  } catch (error) {
    next(error);
  }
});
```

```python
# ✅ FastAPI with meaningful errors
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, validator, EmailStr

app = FastAPI()

class UserCreate(BaseModel):
    email: EmailStr
    password: str
    name: str

    @validator('password')
    def password_strength(cls, v):
        if len(v) < 8:
            raise ValueError(
                'Password must be at least 8 characters long. '
                'Strong passwords help protect your account.'
            )
        if not any(c.isupper() for c in v):
            raise ValueError(
                'Password must contain at least one uppercase letter (A-Z)'
            )
        if not any(c.islower() for c in v):
            raise ValueError(
                'Password must contain at least one lowercase letter (a-z)'
            )
        if not any(c.isdigit() for c in v):
            raise ValueError(
                'Password must contain at least one number (0-9)'
            )
        return v

    @validator('name')
    def name_not_empty(cls, v):
        if not v or not v.strip():
            raise ValueError(
                'Name is required. Please enter your full name as you would '
                'like it to appear on your profile.'
            )
        if len(v) > 100:
            raise ValueError(
                f'Name must be 100 characters or less. You entered {len(v)} characters.'
            )
        return v.strip()

@app.post("/users")
async def create_user(user: UserCreate):
    existing = await db.find_user_by_email(user.email)
    if existing:
        raise HTTPException(
            status_code=409,
            detail={
                "code": "email_already_registered",
                "message": (
                    f"The email '{user.email}' is already registered. "
                    "If this is your email, try signing in or resetting your password."
                ),
                "suggestions": [
                    "Sign in with your existing account",
                    "Reset your password if you forgot it",
                    "Use a different email address"
                ]
            }
        )
    return await db.create_user(user)
```

```json
// ✅ Good error response examples

// Validation error with guidance
{
  "error": {
    "code": "validation_error",
    "message": "The email address format is invalid",
    "details": [
      {
        "field": "email",
        "message": "Please provide a valid email address (e.g., user@example.com)",
        "provided": "not-an-email",
        "suggestion": "Check for typos and ensure the email includes an @ symbol"
      }
    ]
  }
}

// Resource not found with context
{
  "error": {
    "code": "resource_not_found",
    "message": "Order #12345 could not be found",
    "details": [
      {
        "message": "This order may have been deleted or the ID may be incorrect",
        "suggestions": [
          "Verify the order ID is correct",
          "Check your order history for the correct ID",
          "The order may have been archived after 90 days"
        ]
      }
    ]
  }
}

// Permission error with next steps
{
  "error": {
    "code": "permission_denied",
    "message": "You don't have permission to delete this project",
    "details": [
      {
        "message": "Only project owners and administrators can delete projects",
        "currentRole": "member",
        "requiredRoles": ["owner", "admin"],
        "suggestion": "Contact the project owner to request deletion or elevated permissions"
      }
    ]
  }
}

// Rate limit with retry info
{
  "error": {
    "code": "rate_limit_exceeded",
    "message": "You've made too many requests. Please slow down.",
    "details": [
      {
        "limit": 100,
        "window": "1 minute",
        "retryAfter": 45,
        "message": "You can make another request in 45 seconds"
      }
    ]
  }
}
```

**Benefits:**
- Clear messages help users fix problems without contacting support
- Self-explanatory errors reduce support ticket volume
- API consumers can debug issues faster with meaningful context
- Actionable guidance tells users what to do next, not just what went wrong
- Professional error messages build confidence in your API
- Clear messages are easier to translate for localization

Reference: [Microsoft REST API Guidelines - Errors](https://github.com/microsoft/api-guidelines/blob/vNext/Guidelines.md#7102-error-condition-responses)


---

## Include Validation Error Details

**Impact: CRITICAL (Enables field-level error feedback for better UX)**

When validation fails, provide specific details about which fields failed and why, enabling clients to display targeted error messages.

## Incorrect

```json
// ❌ Single vague validation error
{
  "error": "Validation failed"
}

// ❌ List without field association
{
  "errors": [
    "Invalid email",
    "Password too short",
    "Name required"
  ]
}

// ❌ Boolean flags without messages
{
  "valid": false,
  "emailValid": false,
  "passwordValid": false
}
```

```javascript
// ❌ Unhelpful validation response
app.post('/users', (req, res) => {
  const errors = validate(req.body);
  if (errors.length > 0) {
    res.status(400).json({ error: 'Validation failed' });
  }
});
```

**Problems:**
- Clients cannot highlight specific form fields with their errors
- Users must guess which fields need attention
- Multiple form submissions required to discover all errors
- Frontend validation libraries cannot map errors to form fields
- No constraint context (e.g., min length, allowed range) for UI feedback

## Correct

```javascript
// ✅ Detailed validation errors
const { body, validationResult } = require('express-validator');

const validateUser = [
  body('email')
    .notEmpty().withMessage('Email is required')
    .isEmail().withMessage('Must be a valid email address')
    .normalizeEmail(),

  body('password')
    .notEmpty().withMessage('Password is required')
    .isLength({ min: 8 }).withMessage('Password must be at least 8 characters')
    .matches(/[A-Z]/).withMessage('Password must contain an uppercase letter')
    .matches(/[a-z]/).withMessage('Password must contain a lowercase letter')
    .matches(/[0-9]/).withMessage('Password must contain a number'),

  body('age')
    .optional()
    .isInt({ min: 0, max: 150 }).withMessage('Age must be between 0 and 150'),

  body('username')
    .notEmpty().withMessage('Username is required')
    .isLength({ min: 3, max: 30 }).withMessage('Username must be 3-30 characters')
    .matches(/^[a-zA-Z0-9_]+$/).withMessage('Username can only contain letters, numbers, and underscores')
];

app.post('/users', validateUser, (req, res) => {
  const errors = validationResult(req);

  if (!errors.isEmpty()) {
    return res.status(400).json({
      error: {
        code: 'validation_error',
        message: 'One or more fields have invalid values',
        details: errors.array().map(err => ({
          field: err.path,
          message: err.msg,
          value: err.value,
          location: err.location  // body, query, params
        }))
      }
    });
  }

  // Create user...
});
```

```json
// ✅ Detailed validation error response
{
  "error": {
    "code": "validation_error",
    "message": "One or more fields have invalid values",
    "details": [
      {
        "field": "email",
        "message": "Must be a valid email address",
        "value": "not-an-email",
        "location": "body"
      },
      {
        "field": "password",
        "message": "Password must be at least 8 characters",
        "value": "short",
        "location": "body",
        "constraints": {
          "minLength": 8,
          "actualLength": 5
        }
      },
      {
        "field": "age",
        "message": "Age must be between 0 and 150",
        "value": -5,
        "location": "body",
        "constraints": {
          "min": 0,
          "max": 150
        }
      }
    ]
  }
}
```

```python
# ✅ FastAPI with detailed validation
from fastapi import FastAPI, HTTPException, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse
from pydantic import BaseModel, EmailStr, validator, Field
from typing import Optional, List

app = FastAPI()

class UserCreate(BaseModel):
    email: EmailStr
    password: str = Field(..., min_length=8, max_length=100)
    username: str = Field(..., min_length=3, max_length=30, regex=r'^[a-zA-Z0-9_]+$')
    age: Optional[int] = Field(None, ge=0, le=150)

    @validator('password')
    def password_complexity(cls, v):
        errors = []
        if not any(c.isupper() for c in v):
            errors.append('must contain an uppercase letter')
        if not any(c.islower() for c in v):
            errors.append('must contain a lowercase letter')
        if not any(c.isdigit() for c in v):
            errors.append('must contain a number')
        if errors:
            raise ValueError(f"Password {', '.join(errors)}")
        return v

@app.exception_handler(RequestValidationError)
async def validation_exception_handler(request: Request, exc: RequestValidationError):
    details = []
    for error in exc.errors():
        field = '.'.join(str(loc) for loc in error['loc'] if loc != 'body')
        details.append({
            'field': field,
            'message': error['msg'],
            'type': error['type'],
            'context': error.get('ctx', {})
        })

    return JSONResponse(
        status_code=422,
        content={
            'error': {
                'code': 'validation_error',
                'message': f'{len(details)} validation error(s) found',
                'details': details
            }
        }
    )

@app.post("/users")
async def create_user(user: UserCreate):
    return {"id": 1, **user.dict()}
```

```typescript
// ✅ TypeScript/Zod validation with detailed errors
import { z } from 'zod';
import express from 'express';

const UserSchema = z.object({
  email: z.string()
    .min(1, 'Email is required')
    .email('Must be a valid email address'),

  password: z.string()
    .min(8, 'Password must be at least 8 characters')
    .regex(/[A-Z]/, 'Password must contain an uppercase letter')
    .regex(/[a-z]/, 'Password must contain a lowercase letter')
    .regex(/[0-9]/, 'Password must contain a number'),

  username: z.string()
    .min(3, 'Username must be at least 3 characters')
    .max(30, 'Username cannot exceed 30 characters')
    .regex(/^[a-zA-Z0-9_]+$/, 'Username can only contain letters, numbers, and underscores'),

  age: z.number()
    .int('Age must be a whole number')
    .min(0, 'Age cannot be negative')
    .max(150, 'Age cannot exceed 150')
    .optional()
});

app.post('/users', (req, res) => {
  const result = UserSchema.safeParse(req.body);

  if (!result.success) {
    return res.status(400).json({
      error: {
        code: 'validation_error',
        message: 'Validation failed',
        details: result.error.errors.map(err => ({
          field: err.path.join('.'),
          message: err.message,
          code: err.code
        }))
      }
    });
  }

  // Create user with result.data
});
```

## Nested Object Validation

```json
// ✅ Validation errors for nested objects
{
  "error": {
    "code": "validation_error",
    "message": "Validation failed",
    "details": [
      {
        "field": "address.zipCode",
        "message": "ZIP code must be 5 digits",
        "value": "123"
      },
      {
        "field": "address.country",
        "message": "Country is required"
      },
      {
        "field": "contacts[0].email",
        "message": "Invalid email format",
        "value": "bad-email"
      },
      {
        "field": "contacts[1].phone",
        "message": "Phone number must include country code",
        "value": "555-1234"
      }
    ]
  }
}
```

**Benefits:**
- Clients can highlight specific form fields with their errors
- Users see exactly which fields need attention without guessing
- Developers can quickly identify validation issues during development
- Users can fix all issues at once instead of submitting multiple times
- Frontend validation libraries can map errors directly to form fields
- Clear validation responses help document expected input formats

Reference: [JSON:API Error Objects](https://jsonapi.org/format/#error-objects)


---

## Use Machine-Readable Error Codes

**Impact: CRITICAL (Enables programmatic error handling and client recovery)**

Include standardized, machine-readable error codes alongside human-readable messages to enable programmatic error handling.

## Incorrect

```json
// ❌ Only human-readable messages
{
  "error": "The user was not found"
}

// ❌ HTTP status codes only
{
  "status": 404
}

// ❌ Inconsistent or vague codes
{
  "error_code": "ERR001"
}

{
  "code": 1234
}

{
  "error_type": "bad_thing_happened"
}
```

```javascript
// ❌ No error codes for programmatic handling
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  if (!user) {
    // Client can't reliably detect "not found" vs other 404s
    res.status(404).json({ message: 'User not found' });
  }
});
```

**Problems:**
- Clients must parse human-readable messages which change with localization
- No stable identifier for programmatic error handling
- Numeric codes are meaningless without a reference table
- Inconsistent code formats across endpoints
- Cannot implement targeted recovery strategies per error type
- Monitoring dashboards cannot categorize errors precisely

## Correct

```javascript
// ✅ Define error codes as constants
const ErrorCodes = {
  // Authentication & Authorization
  AUTH_TOKEN_MISSING: 'auth_token_missing',
  AUTH_TOKEN_INVALID: 'auth_token_invalid',
  AUTH_TOKEN_EXPIRED: 'auth_token_expired',
  AUTH_INSUFFICIENT_PERMISSIONS: 'auth_insufficient_permissions',

  // Validation
  VALIDATION_ERROR: 'validation_error',
  VALIDATION_REQUIRED_FIELD: 'validation_required_field',
  VALIDATION_INVALID_FORMAT: 'validation_invalid_format',
  VALIDATION_OUT_OF_RANGE: 'validation_out_of_range',

  // Resources
  RESOURCE_NOT_FOUND: 'resource_not_found',
  RESOURCE_ALREADY_EXISTS: 'resource_already_exists',
  RESOURCE_CONFLICT: 'resource_conflict',
  RESOURCE_DELETED: 'resource_deleted',

  // Rate Limiting
  RATE_LIMIT_EXCEEDED: 'rate_limit_exceeded',

  // Business Logic
  INSUFFICIENT_FUNDS: 'insufficient_funds',
  INVENTORY_UNAVAILABLE: 'inventory_unavailable',
  ORDER_CANNOT_BE_CANCELLED: 'order_cannot_be_cancelled',
  SUBSCRIPTION_EXPIRED: 'subscription_expired',

  // Server Errors
  INTERNAL_ERROR: 'internal_error',
  SERVICE_UNAVAILABLE: 'service_unavailable',
  DEPENDENCY_ERROR: 'dependency_error'
};

// Error factory
class APIError extends Error {
  constructor(code, message, statusCode = 400, details = null) {
    super(message);
    this.code = code;
    this.statusCode = statusCode;
    this.details = details;
  }
}

// Usage in routes
app.get('/users/:id', async (req, res, next) => {
  try {
    const user = await db.findUser(req.params.id);
    if (!user) {
      throw new APIError(
        ErrorCodes.RESOURCE_NOT_FOUND,
        'User not found',
        404,
        { resourceType: 'user', resourceId: req.params.id }
      );
    }
    res.json(user);
  } catch (error) {
    next(error);
  }
});

app.post('/orders', async (req, res, next) => {
  try {
    const product = await db.findProduct(req.body.productId);

    if (product.stock < req.body.quantity) {
      throw new APIError(
        ErrorCodes.INVENTORY_UNAVAILABLE,
        'Not enough items in stock',
        422,
        {
          requested: req.body.quantity,
          available: product.stock,
          productId: req.body.productId
        }
      );
    }

    // Process order...
  } catch (error) {
    next(error);
  }
});

// Error handler
app.use((err, req, res, next) => {
  if (err instanceof APIError) {
    return res.status(err.statusCode).json({
      error: {
        code: err.code,
        message: err.message,
        details: err.details
      }
    });
  }

  // Unknown error
  res.status(500).json({
    error: {
      code: ErrorCodes.INTERNAL_ERROR,
      message: 'An unexpected error occurred'
    }
  });
});
```

```python
# ✅ Python with error codes
from enum import Enum
from fastapi import FastAPI, HTTPException
from typing import Optional, Any

class ErrorCode(str, Enum):
    # Authentication
    AUTH_TOKEN_MISSING = "auth_token_missing"
    AUTH_TOKEN_INVALID = "auth_token_invalid"
    AUTH_TOKEN_EXPIRED = "auth_token_expired"
    AUTH_INSUFFICIENT_PERMISSIONS = "auth_insufficient_permissions"

    # Validation
    VALIDATION_ERROR = "validation_error"
    VALIDATION_REQUIRED_FIELD = "validation_required_field"

    # Resources
    RESOURCE_NOT_FOUND = "resource_not_found"
    RESOURCE_ALREADY_EXISTS = "resource_already_exists"
    RESOURCE_CONFLICT = "resource_conflict"

    # Business Logic
    INSUFFICIENT_FUNDS = "insufficient_funds"
    INVENTORY_UNAVAILABLE = "inventory_unavailable"

    # Server
    INTERNAL_ERROR = "internal_error"
    SERVICE_UNAVAILABLE = "service_unavailable"

class APIError(Exception):
    def __init__(
        self,
        code: ErrorCode,
        message: str,
        status_code: int = 400,
        details: Optional[dict] = None
    ):
        self.code = code
        self.message = message
        self.status_code = status_code
        self.details = details

app = FastAPI()

@app.exception_handler(APIError)
async def api_error_handler(request, exc: APIError):
    return JSONResponse(
        status_code=exc.status_code,
        content={
            "error": {
                "code": exc.code.value,
                "message": exc.message,
                "details": exc.details
            }
        }
    )

@app.get("/users/{user_id}")
async def get_user(user_id: int):
    user = await db.get_user(user_id)
    if not user:
        raise APIError(
            code=ErrorCode.RESOURCE_NOT_FOUND,
            message=f"User with ID {user_id} not found",
            status_code=404,
            details={"resource_type": "user", "resource_id": user_id}
        )
    return user
```

```json
// ✅ Error response with code
{
  "error": {
    "code": "inventory_unavailable",
    "message": "Not enough items in stock to fulfill your order",
    "details": {
      "productId": "prod_123",
      "productName": "Widget Pro",
      "requested": 10,
      "available": 3
    }
  }
}
```

```typescript
// ✅ Client-side error handling
async function createOrder(orderData: OrderData): Promise<Order> {
  const response = await fetch('/api/orders', {
    method: 'POST',
    body: JSON.stringify(orderData)
  });

  if (!response.ok) {
    const error = await response.json();

    // Programmatic handling based on error code
    switch (error.error.code) {
      case 'inventory_unavailable':
        showInventoryWarning(error.error.details);
        break;

      case 'insufficient_funds':
        redirectToPaymentUpdate();
        break;

      case 'auth_token_expired':
        await refreshToken();
        return createOrder(orderData); // Retry

      case 'rate_limit_exceeded':
        await delay(error.error.details.retryAfter * 1000);
        return createOrder(orderData); // Retry

      default:
        showGenericError(error.error.message);
    }

    throw new APIError(error);
  }

  return response.json();
}
```

## Error Code Naming Conventions

| Category | Pattern | Examples |
|----------|---------|----------|
| Auth | `auth_*` | `auth_token_expired`, `auth_invalid_credentials` |
| Validation | `validation_*` | `validation_error`, `validation_invalid_email` |
| Resource | `resource_*` | `resource_not_found`, `resource_conflict` |
| Business | Domain-specific | `insufficient_funds`, `inventory_unavailable` |
| Rate Limit | `rate_limit_*` | `rate_limit_exceeded` |
| Server | `internal_*` or `service_*` | `internal_error`, `service_unavailable` |

**Benefits:**
- Code can switch on error codes to take appropriate recovery action
- Error codes remain stable even when messages change or are localized
- Error codes can be documented and referenced in API docs
- Precise alerting and monitoring dashboards based on error categories
- Tests can assert on specific error codes rather than message strings
- Messages can be translated while codes stay constant across locales

Reference: [Google Cloud API Error Model](https://cloud.google.com/apis/design/errors)


---

## Never Expose Stack Traces in Production

**Impact: CRITICAL (Prevents security vulnerabilities and information disclosure)**

Stack traces and internal error details should never be exposed to API clients in production environments, as they reveal implementation details and potential vulnerabilities.

## Incorrect

```json
// ❌ Full stack trace in production response
{
  "error": "Cannot read property 'id' of undefined",
  "stack": "TypeError: Cannot read property 'id' of undefined\n    at getUserOrders (/app/src/controllers/orders.js:45:23)\n    at Layer.handle [as handle_request] (/app/node_modules/express/lib/router/layer.js:95:5)\n    at next (/app/node_modules/express/lib/router/route.js:137:13)\n    at authenticate (/app/src/middleware/auth.js:28:5)\n    at /app/node_modules/express/lib/router/index.js:284:15"
}

// ❌ Database error details exposed
{
  "error": "SequelizeConnectionError: Connection refused to host 'db.internal.company.com' port 5432",
  "sql": "SELECT * FROM users WHERE id = 1 AND deleted_at IS NULL"
}

// ❌ Internal paths and configuration
{
  "error": "ENOENT: no such file or directory, open '/var/app/config/secrets.json'"
}
```

```javascript
// ❌ Exposing all error details
app.use((err, req, res, next) => {
  res.status(500).json({
    error: err.message,
    stack: err.stack,  // Never do this in production!
    code: err.code
  });
});
```

**Problems:**
- Stack traces reveal file paths, dependencies, and code structure attackers can exploit
- Database error messages may expose schemas, connection strings, or query logic
- Internal file paths reveal server configuration and directory structure
- Framework and version information helps attackers find known vulnerabilities
- Violates security standards like PCI-DSS and SOC 2

## Correct

```javascript
// ✅ Secure error handler
const isProduction = process.env.NODE_ENV === 'production';

app.use((err, req, res, next) => {
  // Log full error internally
  logger.error('Request error', {
    error: err.message,
    stack: err.stack,
    requestId: req.id,
    path: req.path,
    method: req.method,
    userId: req.user?.id
  });

  // Determine if error is safe to expose
  const isOperationalError = err.isOperational || err.expose;
  const statusCode = err.statusCode || 500;

  // Build safe response
  const errorResponse = {
    error: {
      code: err.code || 'internal_error',
      message: isOperationalError
        ? err.message
        : 'An unexpected error occurred. Please try again later.',
      requestId: req.id
    }
  };

  // Only include details in development
  if (!isProduction && err.stack) {
    errorResponse.error._debug = {
      message: err.message,
      stack: err.stack.split('\n')
    };
  }

  res.status(statusCode).json(errorResponse);
});

// Custom error class for operational errors
class APIError extends Error {
  constructor(message, statusCode = 500, code = 'internal_error') {
    super(message);
    this.statusCode = statusCode;
    this.code = code;
    this.isOperational = true; // Safe to expose
  }
}

// Database error handling
app.get('/users/:id', async (req, res, next) => {
  try {
    const user = await db.findUser(req.params.id);
    if (!user) {
      throw new APIError('User not found', 404, 'resource_not_found');
    }
    res.json(user);
  } catch (error) {
    if (error instanceof APIError) {
      return next(error);
    }

    // Log the actual database error
    logger.error('Database error', {
      error: error.message,
      stack: error.stack,
      query: 'findUser',
      params: { id: req.params.id }
    });

    // Return generic error to client
    next(new APIError(
      'Unable to retrieve user information',
      500,
      'service_error'
    ));
  }
});
```

```python
# ✅ FastAPI with secure error handling
import logging
import traceback
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import os

app = FastAPI()
logger = logging.getLogger(__name__)
IS_PRODUCTION = os.getenv("ENVIRONMENT") == "production"

class APIError(Exception):
    def __init__(self, message: str, status_code: int = 500, code: str = "internal_error"):
        self.message = message
        self.status_code = status_code
        self.code = code
        self.is_operational = True

@app.exception_handler(APIError)
async def api_error_handler(request: Request, exc: APIError):
    return JSONResponse(
        status_code=exc.status_code,
        content={
            "error": {
                "code": exc.code,
                "message": exc.message,
                "request_id": request.state.request_id
            }
        }
    )

@app.exception_handler(Exception)
async def generic_error_handler(request: Request, exc: Exception):
    # Log full error internally
    logger.error(
        "Unhandled exception",
        extra={
            "error": str(exc),
            "traceback": traceback.format_exc(),
            "request_id": request.state.request_id,
            "path": request.url.path,
            "method": request.method
        }
    )

    # Return safe response
    content = {
        "error": {
            "code": "internal_error",
            "message": "An unexpected error occurred. Please try again later.",
            "request_id": request.state.request_id
        }
    }

    # Include debug info only in development
    if not IS_PRODUCTION:
        content["error"]["_debug"] = {
            "type": type(exc).__name__,
            "message": str(exc),
            "traceback": traceback.format_exc().split("\n")
        }

    return JSONResponse(status_code=500, content=content)
```

```json
// ✅ Production error response (safe)
{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred. Please try again later.",
    "requestId": "req-abc123"
  }
}

// ✅ Development error response (with debug info)
{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred. Please try again later.",
    "requestId": "req-abc123",
    "_debug": {
      "type": "TypeError",
      "message": "Cannot read property 'id' of undefined",
      "traceback": [
        "Traceback (most recent call last):",
        "  File \"app.py\", line 45, in get_user",
        "    return user.id",
        "TypeError: Cannot read property 'id' of undefined"
      ]
    }
  }
}
```

## What to Log vs. What to Return

| Information | Log Internally | Return to Client |
|-------------|----------------|------------------|
| Error message | Yes | Generic only |
| Stack trace | Yes | Never in production |
| SQL queries | Yes | Never |
| File paths | Yes | Never |
| Internal IPs | Yes | Never |
| Request ID | Yes | Yes |
| Error code | Yes | Yes |
| User ID | Yes | No |
| Timestamps | Yes | Optional |

**Benefits:**
- Prevents attackers from exploiting revealed file paths, dependencies, and code structure
- Protects database schemas, API keys, and other secrets from leaking
- Hides framework versions that could expose known vulnerabilities
- Clean error messages present a professional API to consumers
- Meets compliance requirements for PCI-DSS, SOC 2, and similar standards
- Request IDs enable correlation between client reports and internal logs

Reference: [OWASP Improper Error Handling](https://owasp.org/www-community/Improper_Error_Handling)


---

## Include Request ID in Error Responses

**Impact: CRITICAL (Enables log correlation and efficient debugging)**

Every API request should have a unique identifier that appears in both the response and server logs, enabling easy correlation for debugging.

## Incorrect

```json
// ❌ No request identifier
{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred"
  }
}
// User reports error, but support can't find it in logs
```

```javascript
// ❌ No request tracking
app.get('/users/:id', async (req, res) => {
  try {
    const user = await db.findUser(req.params.id);
    res.json(user);
  } catch (error) {
    console.log('Error:', error.message); // No way to correlate
    res.status(500).json({ error: 'Something went wrong' });
  }
});
```

**Problems:**
- Support cannot locate specific errors in logs when users report issues
- No way to correlate client-side errors with server-side logs
- Debugging requires time-based log searching which is imprecise
- Distributed systems cannot trace requests across services
- No audit trail for specific request flows

## Correct

```javascript
// ✅ Request ID middleware
const { v4: uuidv4 } = require('uuid');

app.use((req, res, next) => {
  // Use client-provided ID or generate new one
  req.id = req.headers['x-request-id'] || uuidv4();

  // Add to response headers
  res.setHeader('X-Request-ID', req.id);

  // Add to logger context
  req.log = logger.child({ requestId: req.id });

  next();
});

// Use in routes
app.get('/users/:id', async (req, res, next) => {
  req.log.info('Fetching user', { userId: req.params.id });

  try {
    const user = await db.findUser(req.params.id);
    if (!user) {
      return res.status(404).json({
        error: {
          code: 'resource_not_found',
          message: 'User not found',
          requestId: req.id
        }
      });
    }
    res.json(user);
  } catch (error) {
    req.log.error('Failed to fetch user', {
      error: error.message,
      stack: error.stack
    });
    next(error);
  }
});

// Error handler includes request ID
app.use((err, req, res, next) => {
  req.log.error('Request failed', {
    error: err.message,
    stack: err.stack,
    statusCode: err.statusCode || 500
  });

  res.status(err.statusCode || 500).json({
    error: {
      code: err.code || 'internal_error',
      message: err.message || 'An unexpected error occurred',
      requestId: req.id
    }
  });
});
```

```python
# ✅ FastAPI with request ID
import uuid
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import logging
from contextvars import ContextVar

app = FastAPI()
logger = logging.getLogger(__name__)

# Context variable for request ID
request_id_var: ContextVar[str] = ContextVar("request_id", default="")

@app.middleware("http")
async def request_id_middleware(request: Request, call_next):
    # Get or generate request ID
    request_id = request.headers.get("X-Request-ID", str(uuid.uuid4()))
    request.state.request_id = request_id
    request_id_var.set(request_id)

    # Process request
    response = await call_next(request)

    # Add request ID to response
    response.headers["X-Request-ID"] = request_id
    return response

# Custom log filter to include request ID
class RequestIdFilter(logging.Filter):
    def filter(self, record):
        record.request_id = request_id_var.get("")
        return True

# Configure logging
handler = logging.StreamHandler()
handler.addFilter(RequestIdFilter())
handler.setFormatter(logging.Formatter(
    '%(asctime)s [%(request_id)s] %(levelname)s: %(message)s'
))
logger.addHandler(handler)

@app.exception_handler(Exception)
async def error_handler(request: Request, exc: Exception):
    logger.error(f"Request failed: {exc}", exc_info=True)

    return JSONResponse(
        status_code=500,
        content={
            "error": {
                "code": "internal_error",
                "message": "An unexpected error occurred",
                "requestId": request.state.request_id
            }
        },
        headers={"X-Request-ID": request.state.request_id}
    )

@app.get("/users/{user_id}")
async def get_user(user_id: int, request: Request):
    logger.info(f"Fetching user {user_id}")

    user = await db.get_user(user_id)
    if not user:
        return JSONResponse(
            status_code=404,
            content={
                "error": {
                    "code": "resource_not_found",
                    "message": f"User {user_id} not found",
                    "requestId": request.state.request_id
                }
            }
        )
    return user
```

```json
// ✅ Error response with request ID
HTTP/1.1 500 Internal Server Error
X-Request-ID: req-550e8400-e29b-41d4-a716-446655440000

{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred. Please try again.",
    "requestId": "req-550e8400-e29b-41d4-a716-446655440000"
  }
}
```

```bash
# ✅ Server logs with request ID
2024-01-15 10:30:00 [req-550e8400-e29b-41d4-a716-446655440000] INFO: Fetching user 123
2024-01-15 10:30:00 [req-550e8400-e29b-41d4-a716-446655440000] ERROR: Database connection timeout
2024-01-15 10:30:00 [req-550e8400-e29b-41d4-a716-446655440000] ERROR: Request failed
```

## Distributed Tracing Integration

```javascript
// ✅ Integration with OpenTelemetry
const { trace, context } = require('@opentelemetry/api');

app.use((req, res, next) => {
  const span = trace.getActiveSpan();

  // Use trace ID as request ID for distributed tracing
  if (span) {
    const traceId = span.spanContext().traceId;
    req.id = traceId;
    req.spanContext = span.spanContext();
  } else {
    req.id = uuidv4();
  }

  res.setHeader('X-Request-ID', req.id);
  next();
});
```

```yaml
# ✅ OpenAPI documentation for request ID
components:
  headers:
    X-Request-ID:
      description: Unique identifier for the request, used for debugging and log correlation
      schema:
        type: string
        format: uuid
      example: "550e8400-e29b-41d4-a716-446655440000"

  schemas:
    Error:
      type: object
      properties:
        error:
          type: object
          properties:
            code:
              type: string
            message:
              type: string
            requestId:
              type: string
              description: Unique request identifier for support correlation
```

**Benefits:**
- Users can provide the request ID when reporting issues for instant log lookup
- Links all log entries for a single request across multiple services
- Request IDs propagate through microservices for end-to-end distributed tracing
- "Please provide the request ID" is faster than "describe what you did"
- Enables tracking individual request paths through infrastructure
- Audit trails require the ability to trace specific requests

Reference: [OpenTelemetry Tracing](https://opentelemetry.io/docs/concepts/signals/traces/)


---

## Implement Secure Authentication

**Impact: CRITICAL (Protects user data and prevents unauthorized access)**

Use industry-standard authentication mechanisms like OAuth 2.0, JWT, or API keys with proper security practices.

## Incorrect

```javascript
// ❌ Basic auth over HTTP
app.use((req, res, next) => {
  const auth = req.headers.authorization;
  const [user, pass] = Buffer.from(auth.split(' ')[1], 'base64')
    .toString().split(':');
  // Credentials sent in plain text!
  if (user === 'admin' && pass === 'password123') {
    next();
  }
});

// ❌ Token in URL
app.get('/users?token=secret123', (req, res) => {
  // Token visible in logs, browser history, referrer headers
});

// ❌ No token expiration
const token = jwt.sign({ userId: 123 }); // No expiration!

// ❌ Weak secret
const token = jwt.sign({ userId: 123 }, 'secret'); // Easily guessable
```

```json
// ❌ Credentials in response body
{
  "user": {
    "id": 123,
    "password": "hashedpassword",
    "apiKey": "sk_live_abc123"
  }
}
```

**Problems:**
- Plain text credentials can be intercepted over HTTP
- Tokens in URLs are logged by servers, proxies, and browsers
- Non-expiring tokens remain valid forever if compromised
- Weak secrets allow token forgery through brute force
- Exposing credentials in responses enables account takeover

## Correct

```javascript
// ✅ Secure JWT authentication
const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');

// Environment-based secrets
const JWT_SECRET = process.env.JWT_SECRET; // Long, random string
const JWT_EXPIRES_IN = '15m'; // Short-lived access tokens
const REFRESH_EXPIRES_IN = '7d';

// Password hashing
async function hashPassword(password) {
  const saltRounds = 12;
  return bcrypt.hash(password, saltRounds);
}

async function verifyPassword(password, hash) {
  return bcrypt.compare(password, hash);
}

// JWT token generation
function generateTokens(user) {
  const accessToken = jwt.sign(
    {
      sub: user.id,
      email: user.email,
      roles: user.roles
    },
    JWT_SECRET,
    {
      expiresIn: JWT_EXPIRES_IN,
      issuer: 'api.example.com',
      audience: 'example.com'
    }
  );

  const refreshToken = jwt.sign(
    { sub: user.id, type: 'refresh' },
    JWT_SECRET,
    { expiresIn: REFRESH_EXPIRES_IN }
  );

  return { accessToken, refreshToken };
}

// Authentication endpoint
app.post('/auth/login', async (req, res) => {
  const { email, password } = req.body;

  const user = await db.findUserByEmail(email);
  if (!user) {
    // Don't reveal if user exists
    return res.status(401).json({
      error: {
        code: 'invalid_credentials',
        message: 'Invalid email or password'
      }
    });
  }

  const valid = await verifyPassword(password, user.passwordHash);
  if (!valid) {
    return res.status(401).json({
      error: {
        code: 'invalid_credentials',
        message: 'Invalid email or password'
      }
    });
  }

  const tokens = generateTokens(user);

  // Set refresh token as HTTP-only cookie
  res.cookie('refreshToken', tokens.refreshToken, {
    httpOnly: true,
    secure: true,
    sameSite: 'strict',
    maxAge: 7 * 24 * 60 * 60 * 1000
  });

  res.json({
    accessToken: tokens.accessToken,
    expiresIn: 900, // 15 minutes in seconds
    tokenType: 'Bearer'
  });
});

// JWT verification middleware
function authenticate(req, res, next) {
  const authHeader = req.headers.authorization;

  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({
      error: {
        code: 'auth_token_missing',
        message: 'Authorization header with Bearer token required'
      }
    });
  }

  const token = authHeader.slice(7);

  try {
    const payload = jwt.verify(token, JWT_SECRET, {
      issuer: 'api.example.com',
      audience: 'example.com'
    });

    req.user = payload;
    next();
  } catch (error) {
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        error: {
          code: 'auth_token_expired',
          message: 'Access token has expired. Please refresh.'
        }
      });
    }

    return res.status(401).json({
      error: {
        code: 'auth_token_invalid',
        message: 'Invalid access token'
      }
    });
  }
}

// Token refresh endpoint
app.post('/auth/refresh', async (req, res) => {
  const refreshToken = req.cookies.refreshToken;

  if (!refreshToken) {
    return res.status(401).json({
      error: {
        code: 'refresh_token_missing',
        message: 'Refresh token required'
      }
    });
  }

  try {
    const payload = jwt.verify(refreshToken, JWT_SECRET);

    // Check if token is revoked
    const isRevoked = await db.isTokenRevoked(refreshToken);
    if (isRevoked) {
      return res.status(401).json({
        error: {
          code: 'refresh_token_revoked',
          message: 'Refresh token has been revoked'
        }
      });
    }

    const user = await db.findUser(payload.sub);
    const tokens = generateTokens(user);

    // Rotate refresh token
    await db.revokeToken(refreshToken);

    res.cookie('refreshToken', tokens.refreshToken, {
      httpOnly: true,
      secure: true,
      sameSite: 'strict',
      maxAge: 7 * 24 * 60 * 60 * 1000
    });

    res.json({
      accessToken: tokens.accessToken,
      expiresIn: 900
    });
  } catch (error) {
    return res.status(401).json({
      error: {
        code: 'refresh_token_invalid',
        message: 'Invalid refresh token'
      }
    });
  }
});

// Protected route
app.get('/users/me', authenticate, (req, res) => {
  res.json({ userId: req.user.sub });
});
```

```python
# ✅ FastAPI with OAuth2 and JWT
from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm
from jose import JWTError, jwt
from passlib.context import CryptContext
from datetime import datetime, timedelta
import os

app = FastAPI()

SECRET_KEY = os.getenv("JWT_SECRET")
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 15

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="auth/token")

def create_access_token(data: dict, expires_delta: timedelta = None):
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(minutes=15))
    to_encode.update({"exp": expire, "iss": "api.example.com"})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)

async def get_current_user(token: str = Depends(oauth2_scheme)):
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail={"code": "auth_token_invalid", "message": "Invalid token"},
        headers={"WWW-Authenticate": "Bearer"},
    )
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        user_id: str = payload.get("sub")
        if user_id is None:
            raise credentials_exception
    except JWTError:
        raise credentials_exception

    user = await db.get_user(user_id)
    if user is None:
        raise credentials_exception
    return user

@app.post("/auth/token")
async def login(form_data: OAuth2PasswordRequestForm = Depends()):
    user = await authenticate_user(form_data.username, form_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"code": "invalid_credentials", "message": "Invalid credentials"}
        )

    access_token = create_access_token(
        data={"sub": str(user.id)},
        expires_delta=timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    )
    return {"access_token": access_token, "token_type": "bearer"}

@app.get("/users/me")
async def read_users_me(current_user = Depends(get_current_user)):
    return current_user
```

**Benefits:**
- Short-lived tokens and refresh rotation limit damage from token theft
- Bcrypt hashing protects passwords even if the database is compromised
- HTTP-only cookies prevent XSS attacks on refresh tokens
- OAuth 2.0/JWT are well-understood, widely supported standards
- Stateless JWT tokens work well in distributed systems
- Token-based auth provides clear user identification for audit logging

Reference: [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)


---

## Implement Proper Authorization

**Impact: CRITICAL (Enforces access control and resource permissions)**

Authorization verifies what authenticated users can do. Implement role-based (RBAC) or attribute-based (ABAC) access control consistently.

## Incorrect

```javascript
// ❌ No authorization checks
app.delete('/users/:id', authenticate, async (req, res) => {
  // Anyone authenticated can delete any user!
  await db.deleteUser(req.params.id);
  res.status(204).send();
});

// ❌ Client-side only authorization
app.get('/admin/users', authenticate, async (req, res) => {
  // Relies on frontend hiding the button
  const users = await db.getAllUsers();
  res.json(users);
});

// ❌ Inconsistent checks
app.get('/documents/:id', async (req, res) => {
  const doc = await db.findDocument(req.params.id);
  // Sometimes checks, sometimes doesn't
  if (doc.isPublic) {
    res.json(doc);
  }
  // Private docs accessible without check!
  res.json(doc);
});
```

**Problems:**
- Any authenticated user can perform any operation on any resource
- Client-side authorization can be bypassed by calling the API directly
- Inconsistent checks leave gaps attackers can exploit
- No separation between authentication (who) and authorization (what)
- No audit trail of permission-based access decisions

## Correct

```javascript
// ✅ Role-Based Access Control (RBAC)
const ROLES = {
  ADMIN: 'admin',
  MANAGER: 'manager',
  USER: 'user'
};

const PERMISSIONS = {
  // Resource: action -> roles that can perform it
  users: {
    read: [ROLES.ADMIN, ROLES.MANAGER, ROLES.USER],
    create: [ROLES.ADMIN, ROLES.MANAGER],
    update: [ROLES.ADMIN, ROLES.MANAGER],
    delete: [ROLES.ADMIN]
  },
  reports: {
    read: [ROLES.ADMIN, ROLES.MANAGER],
    create: [ROLES.ADMIN, ROLES.MANAGER],
    delete: [ROLES.ADMIN]
  },
  settings: {
    read: [ROLES.ADMIN],
    update: [ROLES.ADMIN]
  }
};

// Authorization middleware
function authorize(resource, action) {
  return (req, res, next) => {
    const userRoles = req.user.roles || [];
    const allowedRoles = PERMISSIONS[resource]?.[action] || [];

    const hasPermission = userRoles.some(role =>
      allowedRoles.includes(role)
    );

    if (!hasPermission) {
      return res.status(403).json({
        error: {
          code: 'forbidden',
          message: `You don't have permission to ${action} ${resource}`,
          requiredRoles: allowedRoles,
          yourRoles: userRoles
        }
      });
    }

    next();
  };
}

// Resource ownership check
async function authorizeOwnership(req, res, next) {
  const resourceId = req.params.id;
  const userId = req.user.sub;

  const resource = await db.findResource(resourceId);

  if (!resource) {
    return res.status(404).json({
      error: { code: 'not_found', message: 'Resource not found' }
    });
  }

  // Admin can access anything
  if (req.user.roles.includes(ROLES.ADMIN)) {
    req.resource = resource;
    return next();
  }

  // Owner can access their own resources
  if (resource.ownerId !== userId) {
    return res.status(403).json({
      error: {
        code: 'forbidden',
        message: 'You can only access your own resources'
      }
    });
  }

  req.resource = resource;
  next();
}

// Usage
app.get('/users',
  authenticate,
  authorize('users', 'read'),
  async (req, res) => {
    const users = await db.getUsers();
    res.json(users);
  }
);

app.delete('/users/:id',
  authenticate,
  authorize('users', 'delete'),
  async (req, res) => {
    await db.deleteUser(req.params.id);
    res.status(204).send();
  }
);

app.get('/documents/:id',
  authenticate,
  authorizeOwnership,
  async (req, res) => {
    res.json(req.resource);
  }
);

app.put('/documents/:id',
  authenticate,
  authorizeOwnership,
  async (req, res) => {
    const updated = await db.updateDocument(req.params.id, req.body);
    res.json(updated);
  }
);
```

```python
# ✅ FastAPI with RBAC
from fastapi import FastAPI, Depends, HTTPException, status
from enum import Enum
from typing import List
from functools import wraps

app = FastAPI()

class Role(str, Enum):
    ADMIN = "admin"
    MANAGER = "manager"
    USER = "user"

class Permission(str, Enum):
    READ_USERS = "read:users"
    WRITE_USERS = "write:users"
    DELETE_USERS = "delete:users"
    READ_REPORTS = "read:reports"
    ADMIN_SETTINGS = "admin:settings"

ROLE_PERMISSIONS = {
    Role.ADMIN: [
        Permission.READ_USERS,
        Permission.WRITE_USERS,
        Permission.DELETE_USERS,
        Permission.READ_REPORTS,
        Permission.ADMIN_SETTINGS
    ],
    Role.MANAGER: [
        Permission.READ_USERS,
        Permission.WRITE_USERS,
        Permission.READ_REPORTS
    ],
    Role.USER: [
        Permission.READ_USERS
    ]
}

def get_user_permissions(user) -> List[Permission]:
    permissions = set()
    for role in user.roles:
        permissions.update(ROLE_PERMISSIONS.get(role, []))
    return list(permissions)

def require_permission(permission: Permission):
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, current_user = Depends(get_current_user), **kwargs):
            user_permissions = get_user_permissions(current_user)

            if permission not in user_permissions:
                raise HTTPException(
                    status_code=status.HTTP_403_FORBIDDEN,
                    detail={
                        "code": "forbidden",
                        "message": f"Permission '{permission.value}' required",
                        "your_permissions": [p.value for p in user_permissions]
                    }
                )
            return await func(*args, current_user=current_user, **kwargs)
        return wrapper
    return decorator

def require_ownership_or_admin(resource_type: str):
    async def check_ownership(
        resource_id: int,
        current_user = Depends(get_current_user)
    ):
        resource = await db.get_resource(resource_type, resource_id)

        if not resource:
            raise HTTPException(status_code=404, detail="Not found")

        if Role.ADMIN in current_user.roles:
            return resource

        if resource.owner_id != current_user.id:
            raise HTTPException(
                status_code=403,
                detail={
                    "code": "forbidden",
                    "message": "You can only access your own resources"
                }
            )
        return resource
    return check_ownership

@app.get("/users")
@require_permission(Permission.READ_USERS)
async def list_users(current_user = Depends(get_current_user)):
    return await db.get_users()

@app.delete("/users/{user_id}")
@require_permission(Permission.DELETE_USERS)
async def delete_user(user_id: int, current_user = Depends(get_current_user)):
    await db.delete_user(user_id)
    return {"deleted": True}

@app.get("/documents/{document_id}")
async def get_document(
    document = Depends(require_ownership_or_admin("documents"))
):
    return document
```

```json
// ✅ Authorization error response
{
  "error": {
    "code": "forbidden",
    "message": "You don't have permission to delete users",
    "details": {
      "requiredPermission": "delete:users",
      "yourRoles": ["user", "manager"],
      "requiredRoles": ["admin"]
    }
  }
}
```

## Authorization Patterns

| Pattern | Use Case | Example |
|---------|----------|---------|
| RBAC | Role-based access | Admin, Manager, User roles |
| ABAC | Attribute-based | Department, location, time-based |
| Ownership | Resource owners | User owns their documents |
| Hierarchical | Org structure | Managers see team's data |
| Feature flags | Feature access | Premium features |

**Benefits:**
- Prevents unauthorized access to sensitive data and operations
- Principle of least privilege: users only get access they need
- Centralized permission definitions are easy to audit and update
- Server-side checks cannot be bypassed like client-side authorization
- RBAC/ABAC scales better than per-user permissions
- Clear authorization rules support compliance requirements

Reference: [OWASP Authorization Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html)


---

## Implement Rate Limiting

**Impact: CRITICAL (Prevents abuse and ensures service availability)**

Protect your API from abuse by limiting the number of requests clients can make within a time window.

## Incorrect

```javascript
// ❌ No rate limiting
app.post('/login', async (req, res) => {
  // Vulnerable to brute force attacks
  const user = await authenticate(req.body);
  res.json(user);
});

// ❌ Rate limit only on response
app.get('/api/data', async (req, res) => {
  const data = await expensiveQuery(); // Query runs every time!
  if (requestCount > 100) {
    res.status(429).json({ error: 'Too many requests' });
  }
  res.json(data);
});

// ❌ No rate limit headers
app.use((req, res, next) => {
  if (isRateLimited(req)) {
    res.status(429).send('Too many requests');
    // Client doesn't know when to retry
  }
  next();
});
```

**Problems:**
- Login endpoints without rate limits are vulnerable to brute force attacks
- Checking limits after processing wastes server resources
- Missing retry headers leave clients guessing when they can retry
- No per-user or per-endpoint differentiation allows abuse
- Expensive operations run before limits are checked

## Correct

```javascript
// ✅ Comprehensive rate limiting
const rateLimit = require('express-rate-limit');
const RedisStore = require('rate-limit-redis');
const Redis = require('ioredis');

const redis = new Redis(process.env.REDIS_URL);

// General API rate limiter
const apiLimiter = rateLimit({
  store: new RedisStore({
    client: redis,
    prefix: 'rl:api:'
  }),
  windowMs: 60 * 1000, // 1 minute
  max: 100, // 100 requests per minute
  standardHeaders: true, // Return rate limit info in headers
  legacyHeaders: false,
  message: {
    error: {
      code: 'rate_limit_exceeded',
      message: 'Too many requests. Please slow down.',
      retryAfter: 60
    }
  },
  keyGenerator: (req) => {
    // Rate limit by user ID if authenticated, otherwise by IP
    return req.user?.id || req.ip;
  }
});

// Stricter limiter for auth endpoints
const authLimiter = rateLimit({
  store: new RedisStore({
    client: redis,
    prefix: 'rl:auth:'
  }),
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 5, // 5 attempts per 15 minutes
  standardHeaders: true,
  message: {
    error: {
      code: 'rate_limit_exceeded',
      message: 'Too many login attempts. Please try again later.',
      retryAfter: 900
    }
  },
  keyGenerator: (req) => req.body.email || req.ip // Per email address
});

// Expensive endpoint limiter
const expensiveLimiter = rateLimit({
  store: new RedisStore({
    client: redis,
    prefix: 'rl:expensive:'
  }),
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 10, // 10 requests per hour
  standardHeaders: true,
  message: {
    error: {
      code: 'rate_limit_exceeded',
      message: 'Export limit reached. Try again in an hour.',
      retryAfter: 3600
    }
  }
});

// Apply rate limiters
app.use('/api/', apiLimiter);
app.use('/auth/login', authLimiter);
app.use('/auth/password-reset', authLimiter);
app.post('/api/export', expensiveLimiter, exportHandler);

// Custom sliding window implementation
class SlidingWindowRateLimiter {
  constructor(redis, options) {
    this.redis = redis;
    this.windowMs = options.windowMs;
    this.maxRequests = options.max;
  }

  async isAllowed(key) {
    const now = Date.now();
    const windowStart = now - this.windowMs;

    const pipeline = this.redis.pipeline();

    // Remove old entries
    pipeline.zremrangebyscore(key, 0, windowStart);

    // Count requests in window
    pipeline.zcard(key);

    // Add current request
    pipeline.zadd(key, now, `${now}-${Math.random()}`);

    // Set expiry
    pipeline.expire(key, Math.ceil(this.windowMs / 1000));

    const results = await pipeline.exec();
    const count = results[1][1];

    return {
      allowed: count < this.maxRequests,
      remaining: Math.max(0, this.maxRequests - count - 1),
      resetAt: new Date(now + this.windowMs)
    };
  }
}

// Tiered rate limiting based on plan
const tierLimits = {
  free: { requestsPerMinute: 60, requestsPerDay: 1000 },
  basic: { requestsPerMinute: 300, requestsPerDay: 10000 },
  pro: { requestsPerMinute: 1000, requestsPerDay: 100000 },
  enterprise: { requestsPerMinute: 5000, requestsPerDay: 1000000 }
};

async function tieredRateLimiter(req, res, next) {
  const user = req.user;
  const tier = user?.plan || 'free';
  const limits = tierLimits[tier];

  const minuteKey = `rl:${user?.id || req.ip}:minute`;
  const dayKey = `rl:${user?.id || req.ip}:day`;

  // Check minute limit
  const minuteCount = await redis.incr(minuteKey);
  if (minuteCount === 1) {
    await redis.expire(minuteKey, 60);
  }

  // Check daily limit
  const dayCount = await redis.incr(dayKey);
  if (dayCount === 1) {
    await redis.expire(dayKey, 86400);
  }

  // Set rate limit headers
  res.set('X-RateLimit-Limit', limits.requestsPerMinute);
  res.set('X-RateLimit-Remaining', Math.max(0, limits.requestsPerMinute - minuteCount));
  res.set('X-RateLimit-Reset', Math.ceil(Date.now() / 1000) + 60);

  if (minuteCount > limits.requestsPerMinute) {
    return res.status(429).json({
      error: {
        code: 'rate_limit_exceeded',
        message: 'Minute rate limit exceeded',
        limit: limits.requestsPerMinute,
        window: '1 minute',
        retryAfter: await redis.ttl(minuteKey)
      }
    });
  }

  if (dayCount > limits.requestsPerDay) {
    return res.status(429).json({
      error: {
        code: 'rate_limit_exceeded',
        message: 'Daily rate limit exceeded',
        limit: limits.requestsPerDay,
        window: '24 hours',
        upgradeUrl: '/pricing'
      }
    });
  }

  next();
}
```

```python
# ✅ FastAPI with rate limiting
from fastapi import FastAPI, Request, HTTPException
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded

limiter = Limiter(key_func=get_remote_address)
app = FastAPI()
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

@app.get("/api/data")
@limiter.limit("100/minute")
async def get_data(request: Request):
    return {"data": "value"}

@app.post("/auth/login")
@limiter.limit("5/15minutes")
async def login(request: Request):
    return {"token": "..."}

# Custom user-based rate limiting
async def get_rate_limit_key(request: Request):
    if request.user:
        return f"user:{request.user.id}"
    return f"ip:{request.client.host}"

@app.get("/api/premium")
@limiter.limit("1000/minute", key_func=get_rate_limit_key)
async def premium_endpoint(request: Request):
    return {"data": "premium"}
```

## Rate Limit Headers

```
HTTP/1.1 200 OK
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1705312800
Retry-After: 60
```

**Benefits:**
- Prevents denial-of-service attacks from overwhelming servers
- Limits brute force password guessing and credential stuffing attacks
- Ensures fair API access for all users
- Prevents runaway usage that increases infrastructure costs
- Enables tiered pricing based on usage limits
- Rate limit headers help clients implement proper backoff strategies

Reference: [IETF Rate Limiting Headers](https://datatracker.ietf.org/doc/draft-ietf-httpapi-ratelimit-headers/)


---

## Validate All Input Data

**Impact: CRITICAL (Prevents injection attacks and malformed data)**

Never trust client input. Validate, sanitize, and constrain all incoming data to prevent security vulnerabilities.

## Incorrect

```javascript
// ❌ No validation
app.post('/users', async (req, res) => {
  // Directly using user input!
  const user = await db.createUser(req.body);
  res.json(user);
});

// ❌ SQL injection vulnerability
app.get('/users', async (req, res) => {
  const query = `SELECT * FROM users WHERE name = '${req.query.name}'`;
  const users = await db.raw(query);
  res.json(users);
});

// ❌ NoSQL injection
app.post('/login', async (req, res) => {
  const user = await db.users.findOne({
    email: req.body.email,
    password: req.body.password  // Could be { $gt: '' }
  });
});

// ❌ Path traversal
app.get('/files/:filename', (req, res) => {
  const path = `./uploads/${req.params.filename}`;
  res.sendFile(path); // Could access ../../../etc/passwd
});
```

**Problems:**
- SQL injection allows attackers to read, modify, or delete database data
- NoSQL injection bypasses authentication with operator-based payloads
- Path traversal exposes sensitive server files
- Unbounded input sizes enable memory exhaustion and DoS attacks
- Unsanitized HTML/script input enables XSS attacks
- Type confusion attacks exploit loosely typed inputs

## Correct

```javascript
// ✅ Comprehensive input validation
const { body, param, query, validationResult } = require('express-validator');
const sanitizeHtml = require('sanitize-html');
const path = require('path');

// Validation middleware
const validateUser = [
  body('email')
    .trim()
    .notEmpty().withMessage('Email is required')
    .isEmail().withMessage('Must be a valid email')
    .normalizeEmail()
    .isLength({ max: 255 }).withMessage('Email too long'),

  body('password')
    .notEmpty().withMessage('Password is required')
    .isLength({ min: 8, max: 100 }).withMessage('Password must be 8-100 characters')
    .matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/).withMessage('Password too weak'),

  body('name')
    .trim()
    .notEmpty().withMessage('Name is required')
    .isLength({ min: 1, max: 100 }).withMessage('Name must be 1-100 characters')
    .matches(/^[a-zA-Z\s'-]+$/).withMessage('Name contains invalid characters')
    .customSanitizer(value => sanitizeHtml(value, { allowedTags: [] })),

  body('age')
    .optional()
    .isInt({ min: 0, max: 150 }).withMessage('Age must be 0-150')
    .toInt(),

  body('website')
    .optional()
    .trim()
    .isURL({ protocols: ['http', 'https'] }).withMessage('Invalid URL')
];

// Validation error handler
const handleValidation = (req, res, next) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({
      error: {
        code: 'validation_error',
        message: 'Invalid input data',
        details: errors.array().map(e => ({
          field: e.path,
          message: e.msg,
          value: e.value
        }))
      }
    });
  }
  next();
};

app.post('/users', validateUser, handleValidation, async (req, res) => {
  const user = await db.createUser(req.body);
  res.status(201).json(user);
});

// ✅ Parameterized queries (prevent SQL injection)
app.get('/users', async (req, res) => {
  const users = await db.query(
    'SELECT id, name, email FROM users WHERE name = ?',
    [req.query.name]
  );
  res.json(users);
});

// ✅ Safe MongoDB queries
app.post('/login', async (req, res) => {
  // Ensure email and password are strings
  const email = String(req.body.email || '');
  const password = String(req.body.password || '');

  const user = await db.users.findOne({ email });
  if (!user || !await bcrypt.compare(password, user.passwordHash)) {
    return res.status(401).json({ error: 'Invalid credentials' });
  }
  // ...
});

// ✅ Path traversal prevention
app.get('/files/:filename',
  param('filename')
    .matches(/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9]+$/)
    .withMessage('Invalid filename'),
  handleValidation,
  (req, res) => {
    const uploadsDir = path.resolve('./uploads');
    const filePath = path.join(uploadsDir, req.params.filename);

    // Ensure path is within uploads directory
    if (!filePath.startsWith(uploadsDir)) {
      return res.status(400).json({ error: 'Invalid path' });
    }

    res.sendFile(filePath);
  }
);

// ✅ Array size limits
app.post('/batch',
  body('items')
    .isArray({ min: 1, max: 100 })
    .withMessage('Items must be array of 1-100 elements'),
  body('items.*.id')
    .isUUID()
    .withMessage('Each item must have valid UUID'),
  handleValidation,
  async (req, res) => {
    const results = await processBatch(req.body.items);
    res.json(results);
  }
);

// ✅ JSON depth/size limits
const jsonParser = express.json({
  limit: '100kb', // Max request body size
  strict: true    // Only accept arrays and objects
});

app.use('/api', jsonParser);
```

```python
# ✅ FastAPI with Pydantic validation
from fastapi import FastAPI, Query, Path, Body, HTTPException
from pydantic import BaseModel, EmailStr, Field, validator, HttpUrl
from typing import Optional, List
import re
import bleach

app = FastAPI()

class UserCreate(BaseModel):
    email: EmailStr
    password: str = Field(..., min_length=8, max_length=100)
    name: str = Field(..., min_length=1, max_length=100)
    age: Optional[int] = Field(None, ge=0, le=150)
    website: Optional[HttpUrl] = None

    @validator('password')
    def password_strength(cls, v):
        if not re.search(r'[A-Z]', v):
            raise ValueError('Must contain uppercase letter')
        if not re.search(r'[a-z]', v):
            raise ValueError('Must contain lowercase letter')
        if not re.search(r'\d', v):
            raise ValueError('Must contain digit')
        return v

    @validator('name')
    def sanitize_name(cls, v):
        # Only allow letters, spaces, hyphens, apostrophes
        if not re.match(r"^[a-zA-Z\s'-]+$", v):
            raise ValueError('Name contains invalid characters')
        # Sanitize HTML
        return bleach.clean(v, tags=[], strip=True)

class BatchRequest(BaseModel):
    items: List[str] = Field(..., min_items=1, max_items=100)

    @validator('items', each_item=True)
    def validate_uuid(cls, v):
        if not re.match(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', v, re.I):
            raise ValueError('Invalid UUID format')
        return v

@app.post("/users")
async def create_user(user: UserCreate):
    return await db.create_user(user.dict())

@app.get("/users/{user_id}")
async def get_user(
    user_id: int = Path(..., ge=1, le=2147483647, description="User ID")
):
    return await db.get_user(user_id)

@app.get("/search")
async def search(
    q: str = Query(..., min_length=1, max_length=100),
    page: int = Query(1, ge=1, le=1000),
    limit: int = Query(20, ge=1, le=100)
):
    # q is automatically validated and constrained
    return await db.search(q, page, limit)
```

## Input Validation Checklist

| Check | Why |
|-------|-----|
| Type validation | Prevent type confusion attacks |
| Length limits | Prevent buffer overflows, DoS |
| Character whitelist | Prevent injection attacks |
| Range validation | Ensure business logic integrity |
| Format validation | Email, URL, UUID patterns |
| Sanitization | Remove/escape dangerous content |
| Array size limits | Prevent memory exhaustion |
| Nested depth limits | Prevent stack overflow |

**Benefits:**
- Prevents SQL/NoSQL injection attacks from manipulating queries
- Sanitizing input stops cross-site scripting (XSS) attacks
- Filename validation prevents unauthorized file access via path traversal
- Size limits prevent memory exhaustion and denial-of-service attacks
- Ensures only valid data enters your system, maintaining data integrity
- Catches bad data at the API boundary before it causes downstream errors

Reference: [OWASP Input Validation Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet.html)


---

## Configure CORS Properly

**Impact: CRITICAL (Prevents unauthorized cross-origin access)**

Cross-Origin Resource Sharing (CORS) must be configured correctly to allow legitimate cross-origin requests while preventing unauthorized access.

## Incorrect

```javascript
// ❌ Allow all origins
app.use(cors({
  origin: '*',
  credentials: true  // DANGEROUS: Can't use * with credentials!
}));

// ❌ Reflecting origin without validation
app.use((req, res, next) => {
  // Allows ANY origin - security vulnerability
  res.header('Access-Control-Allow-Origin', req.headers.origin);
  res.header('Access-Control-Allow-Credentials', 'true');
  next();
});

// ❌ Overly permissive headers
app.use(cors({
  origin: '*',
  methods: '*',
  allowedHeaders: '*',
  exposedHeaders: '*'
}));

// ❌ Missing CORS entirely for API
app.get('/api/data', (req, res) => {
  // Browser will block cross-origin requests
  res.json({ data: 'value' });
});
```

**Problems:**
- Wildcard origin with credentials allows any website to make authenticated requests
- Reflecting the Origin header blindly defeats the purpose of CORS
- Overly permissive headers expose the API to cross-origin attacks
- Missing CORS configuration blocks legitimate frontend applications
- Enables CSRF attacks that rely on cross-origin requests

## Correct

```javascript
// ✅ Properly configured CORS
const cors = require('cors');

// Allowed origins whitelist
const allowedOrigins = [
  'https://myapp.com',
  'https://www.myapp.com',
  'https://admin.myapp.com'
];

// Development origins (only in non-production)
if (process.env.NODE_ENV !== 'production') {
  allowedOrigins.push(
    'http://localhost:3000',
    'http://localhost:8080'
  );
}

// CORS configuration
const corsOptions = {
  origin: (origin, callback) => {
    // Allow requests with no origin (mobile apps, curl, etc.)
    if (!origin) {
      return callback(null, true);
    }

    if (allowedOrigins.includes(origin)) {
      callback(null, true);
    } else {
      callback(new Error('Not allowed by CORS'));
    }
  },
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: [
    'Content-Type',
    'Authorization',
    'X-Request-ID',
    'X-Requested-With'
  ],
  exposedHeaders: [
    'X-Request-ID',
    'X-RateLimit-Limit',
    'X-RateLimit-Remaining',
    'X-RateLimit-Reset'
  ],
  credentials: true,
  maxAge: 86400, // 24 hours - cache preflight requests
  optionsSuccessStatus: 204
};

app.use(cors(corsOptions));

// Handle CORS errors
app.use((err, req, res, next) => {
  if (err.message === 'Not allowed by CORS') {
    return res.status(403).json({
      error: {
        code: 'cors_error',
        message: 'Cross-origin request blocked',
        origin: req.headers.origin
      }
    });
  }
  next(err);
});

// Route-specific CORS (for public endpoints)
const publicCors = cors({
  origin: '*',
  methods: ['GET'],
  maxAge: 86400
});

app.get('/api/public/health', publicCors, (req, res) => {
  res.json({ status: 'ok' });
});

// Strict CORS for sensitive endpoints
const strictCors = cors({
  origin: 'https://admin.myapp.com',
  credentials: true,
  methods: ['GET', 'POST', 'DELETE']
});

app.use('/api/admin', strictCors, adminRouter);
```

```python
# ✅ FastAPI CORS configuration
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import os

app = FastAPI()

# Define allowed origins
allowed_origins = [
    "https://myapp.com",
    "https://www.myapp.com",
    "https://admin.myapp.com"
]

# Add development origins
if os.getenv("ENVIRONMENT") != "production":
    allowed_origins.extend([
        "http://localhost:3000",
        "http://localhost:8080"
    ])

app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["GET", "POST", "PUT", "PATCH", "DELETE"],
    allow_headers=[
        "Content-Type",
        "Authorization",
        "X-Request-ID",
        "X-Requested-With"
    ],
    expose_headers=[
        "X-Request-ID",
        "X-RateLimit-Limit",
        "X-RateLimit-Remaining"
    ],
    max_age=86400
)

# For public endpoints, create a sub-application
public_app = FastAPI()
public_app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Public API
    allow_methods=["GET"],
    max_age=86400
)

app.mount("/public", public_app)
```

```typescript
// ✅ Dynamic CORS based on subdomain pattern
const corsOptions = {
  origin: (origin: string | undefined, callback: Function) => {
    if (!origin) {
      return callback(null, true);
    }

    // Allow all subdomains of myapp.com
    const allowedPattern = /^https:\/\/([a-z0-9-]+\.)?myapp\.com$/;

    if (allowedPattern.test(origin)) {
      callback(null, true);
    } else {
      callback(new Error('CORS not allowed'));
    }
  },
  credentials: true
};
```

## CORS Headers Reference

| Header | Purpose | Example |
|--------|---------|---------|
| Access-Control-Allow-Origin | Allowed origins | `https://myapp.com` |
| Access-Control-Allow-Methods | Allowed HTTP methods | `GET, POST, PUT` |
| Access-Control-Allow-Headers | Allowed request headers | `Content-Type, Authorization` |
| Access-Control-Expose-Headers | Headers client can read | `X-Request-ID` |
| Access-Control-Allow-Credentials | Allow cookies/auth | `true` |
| Access-Control-Max-Age | Preflight cache time | `86400` |

## Common Patterns

```javascript
// ✅ Pattern 1: Subdomain allowlist
const isAllowedOrigin = (origin) => {
  if (!origin) return true;
  const url = new URL(origin);
  return url.hostname.endsWith('.myapp.com');
};

// ✅ Pattern 2: Environment-based
const origins = {
  production: ['https://myapp.com'],
  staging: ['https://staging.myapp.com'],
  development: ['http://localhost:3000']
};
const allowedOrigins = origins[process.env.NODE_ENV];

// ✅ Pattern 3: Database-driven (for multi-tenant)
const corsOptions = {
  origin: async (origin, callback) => {
    const tenant = await db.findTenantByDomain(origin);
    callback(null, tenant?.corsEnabled ?? false);
  }
};
```

**Benefits:**
- Prevents malicious websites from making unauthorized API calls
- Proper configuration prevents credential leakage to untrusted origins
- Blocks CSRF attacks that rely on cross-origin requests
- Explicitly controls which domains can access your API
- Preflight caching reduces OPTIONS request overhead
- Route-specific CORS allows different policies for different endpoints

Reference: [MDN CORS Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)


---

## Enforce HTTPS Only

**Impact: CRITICAL (Protects data in transit from interception)**

All API traffic must use HTTPS to encrypt data in transit. Never allow unencrypted HTTP connections for APIs.

## Incorrect

```javascript
// ❌ HTTP server without TLS
const http = require('http');
const app = require('./app');

http.createServer(app).listen(80, () => {
  console.log('API running on http://localhost:80');
  // Credentials transmitted in plain text!
});

// ❌ Optional HTTPS
if (process.env.USE_HTTPS === 'true') {
  // HTTPS is optional, not enforced
}

// ❌ No redirect from HTTP to HTTPS
app.get('/', (req, res) => {
  // Allows HTTP access
  res.json({ message: 'Welcome' });
});

// ❌ Insecure cookie settings
res.cookie('session', token, {
  secure: false,  // Sent over HTTP!
  httpOnly: true
});
```

```json
// ❌ HTTP URLs in responses
{
  "user": {
    "id": 123,
    "avatar": "http://cdn.example.com/avatar.jpg",
    "profile": "http://api.example.com/users/123"
  }
}
```

**Problems:**
- Credentials and tokens transmitted in plain text can be intercepted
- Man-in-the-middle attacks can modify data in transit
- Cookies without the secure flag are sent over unencrypted connections
- HTTP URLs in responses encourage insecure client behavior
- Violates compliance requirements (PCI-DSS, HIPAA, GDPR)

## Correct

```javascript
// ✅ HTTPS with security headers
const https = require('https');
const fs = require('fs');
const express = require('express');
const helmet = require('helmet');

const app = express();

// Security headers including HSTS
app.use(helmet({
  hsts: {
    maxAge: 31536000, // 1 year
    includeSubDomains: true,
    preload: true
  }
}));

// Force HTTPS redirect (for direct access)
app.use((req, res, next) => {
  if (!req.secure && req.get('x-forwarded-proto') !== 'https') {
    return res.redirect(301, `https://${req.get('host')}${req.url}`);
  }
  next();
});

// Secure cookie settings
app.use((req, res, next) => {
  res.cookie = function(name, value, options = {}) {
    options.secure = true;      // HTTPS only
    options.httpOnly = true;    // No JavaScript access
    options.sameSite = 'strict'; // CSRF protection
    return res.cookie.call(this, name, value, options);
  };
  next();
});

// HTTPS server
const options = {
  key: fs.readFileSync('/path/to/private.key'),
  cert: fs.readFileSync('/path/to/certificate.crt'),
  ca: fs.readFileSync('/path/to/ca-bundle.crt'),
  minVersion: 'TLSv1.2',  // Minimum TLS version
  ciphers: [
    'ECDHE-ECDSA-AES128-GCM-SHA256',
    'ECDHE-RSA-AES128-GCM-SHA256',
    'ECDHE-ECDSA-AES256-GCM-SHA384',
    'ECDHE-RSA-AES256-GCM-SHA384'
  ].join(':')
};

https.createServer(options, app).listen(443, () => {
  console.log('Secure API running on https://localhost:443');
});

// Also listen on HTTP just to redirect
const http = require('http');
http.createServer((req, res) => {
  res.writeHead(301, { Location: `https://${req.headers.host}${req.url}` });
  res.end();
}).listen(80);
```

```python
# ✅ FastAPI with HTTPS enforcement
from fastapi import FastAPI, Request
from fastapi.middleware.httpsredirect import HTTPSRedirectMiddleware
from fastapi.middleware.trustedhost import TrustedHostMiddleware
from starlette.middleware.base import BaseHTTPMiddleware

app = FastAPI()

# Redirect HTTP to HTTPS
app.add_middleware(HTTPSRedirectMiddleware)

# Only allow specific hosts
app.add_middleware(
    TrustedHostMiddleware,
    allowed_hosts=["api.example.com", "*.example.com"]
)

# Add HSTS header
class HSTSMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request: Request, call_next):
        response = await call_next(request)
        response.headers["Strict-Transport-Security"] = (
            "max-age=31536000; includeSubDomains; preload"
        )
        return response

app.add_middleware(HSTSMiddleware)

# Secure cookie response
from fastapi.responses import JSONResponse

@app.post("/auth/login")
async def login(credentials: Credentials):
    token = create_token(credentials)
    response = JSONResponse({"status": "logged_in"})
    response.set_cookie(
        key="session",
        value=token,
        httponly=True,
        secure=True,
        samesite="strict",
        max_age=86400
    )
    return response
```

```nginx
# ✅ Nginx HTTPS configuration
server {
    listen 80;
    server_name api.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.example.com;

    ssl_certificate /etc/ssl/certs/certificate.crt;
    ssl_certificate_key /etc/ssl/private/private.key;

    # Modern TLS configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers on;

    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header X-Forwarded-Proto https;
    }
}
```

```yaml
# ✅ Docker Compose with Traefik for automatic HTTPS
version: '3.8'
services:
  traefik:
    image: traefik:v2.10
    command:
      - "--providers.docker=true"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--entrypoints.web.http.redirections.entryPoint.to=websecure"
      - "--certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web"
      - "--certificatesresolvers.letsencrypt.acme.email=admin@example.com"
      - "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - letsencrypt:/letsencrypt

  api:
    image: my-api
    labels:
      - "traefik.http.routers.api.rule=Host(`api.example.com`)"
      - "traefik.http.routers.api.tls.certresolver=letsencrypt"
      - "traefik.http.middlewares.hsts.headers.stsSeconds=31536000"
      - "traefik.http.routers.api.middlewares=hsts"
```

## TLS Configuration Checklist

| Setting | Recommendation |
|---------|----------------|
| Minimum TLS | TLSv1.2 |
| Preferred TLS | TLSv1.3 |
| HSTS max-age | 31536000 (1 year) |
| includeSubDomains | Yes |
| HSTS preload | Yes (after testing) |
| Certificate | Valid, not self-signed |
| Certificate chain | Complete |

**Benefits:**
- HTTPS encrypts all data in transit, protecting credentials and sensitive data
- TLS certificates verify server identity, preventing MITM attacks
- Data integrity ensures content is not modified in transit
- Meets compliance requirements for PCI-DSS, HIPAA, and GDPR
- HTTP/2 and many modern web APIs require HTTPS
- Secure cookies only work over HTTPS connections

Reference: [OWASP Transport Layer Protection](https://cheatsheetseries.owasp.org/cheatsheets/Transport_Layer_Security_Cheat_Sheet.html)


---

## Protect Sensitive Data in Responses

**Impact: CRITICAL (Prevents data leaks and privacy violations)**

Never expose sensitive information like passwords, tokens, internal IDs, or PII in API responses.

## Incorrect

```json
// ❌ Exposing password hash
{
  "id": 123,
  "email": "user@example.com",
  "passwordHash": "$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4.n",
  "name": "John Doe"
}

// ❌ Exposing API keys
{
  "user": {
    "id": 123,
    "apiKey": "sk_live_abc123xyz789",
    "secretKey": "whsec_secret_key_here"
  }
}

// ❌ Exposing internal IDs
{
  "user": {
    "id": 123,
    "internalDatabaseId": "mongo_507f1f77bcf86cd799439011",
    "stripeCustomerId": "cus_abc123"
  }
}

// ❌ Exposing other users' data
{
  "order": {
    "id": 456,
    "customer": {
      "ssn": "123-45-6789",
      "creditCard": "4111111111111111",
      "dateOfBirth": "1990-01-15"
    }
  }
}
```

```javascript
// ❌ Returning entire database record
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  res.json(user); // Exposes ALL fields including sensitive ones
});
```

**Problems:**
- Exposed password hashes enable offline brute force attacks
- API keys in responses allow unauthorized access to third-party services
- Internal IDs help attackers enumerate resources and map infrastructure
- PII exposure violates privacy regulations (GDPR, CCPA, PCI-DSS)
- Returning full database records exposes fields never intended for clients

## Correct

```javascript
// ✅ Explicit field selection per context
const userPublicFields = ['id', 'name', 'avatar', 'createdAt'];
const userPrivateFields = [...userPublicFields, 'email', 'settings'];
const userAdminFields = [...userPrivateFields, 'roles', 'lastLoginAt', 'status'];

function sanitizeUser(user, context = 'public') {
  const allowedFields = {
    public: userPublicFields,
    private: userPrivateFields,
    admin: userAdminFields
  }[context];

  return Object.fromEntries(
    Object.entries(user).filter(([key]) => allowedFields.includes(key))
  );
}

// Use explicit field selection
app.get('/users/:id', authenticate, async (req, res) => {
  const user = await db.findUser(req.params.id);

  if (!user) {
    return res.status(404).json({ error: 'User not found' });
  }

  // Determine context based on requester
  let context = 'public';
  if (req.user.id === user.id) {
    context = 'private';
  } else if (req.user.roles.includes('admin')) {
    context = 'admin';
  }

  res.json(sanitizeUser(user, context));
});

// Use DTOs/serializers
class UserResponse {
  constructor(user, includePrivate = false) {
    this.id = user.id;
    this.name = user.name;
    this.avatar = user.avatar;
    this.createdAt = user.createdAt;

    if (includePrivate) {
      this.email = user.email;
      this.settings = user.settings;
    }
  }
}

app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  const includePrivate = req.user?.id === user.id;
  res.json(new UserResponse(user, includePrivate));
});

// Mask sensitive data
function maskEmail(email) {
  const [local, domain] = email.split('@');
  const maskedLocal = local[0] + '*'.repeat(local.length - 2) + local.slice(-1);
  return `${maskedLocal}@${domain}`;
}

function maskPhone(phone) {
  return phone.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2');
}

function maskCreditCard(number) {
  return '**** **** **** ' + number.slice(-4);
}

// Apply masking in responses
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);

  res.json({
    id: user.id,
    name: user.name,
    email: req.user.id === user.id ? user.email : maskEmail(user.email),
    phone: maskPhone(user.phone),
    paymentMethod: {
      type: 'card',
      last4: user.creditCard.slice(-4),
      brand: user.cardBrand
    }
  });
});
```

```python
# ✅ FastAPI with Pydantic response models
from pydantic import BaseModel, EmailStr, Field
from typing import Optional

# Internal model (full data)
class UserInternal(BaseModel):
    id: int
    email: str
    password_hash: str
    name: str
    ssn: Optional[str]
    api_key: str
    stripe_customer_id: str

# Public response model (safe to expose)
class UserPublic(BaseModel):
    id: int
    name: str
    avatar_url: Optional[str]

    class Config:
        # Only include fields defined in this model
        extra = 'forbid'

# Private response model (for account owner)
class UserPrivate(UserPublic):
    email: EmailStr
    settings: dict

# Admin response model
class UserAdmin(UserPrivate):
    status: str
    roles: list
    last_login_at: Optional[str]

@app.get("/users/{user_id}", response_model=UserPublic)
async def get_user(user_id: int, current_user = Depends(get_current_user)):
    user = await db.get_user(user_id)

    if current_user.id == user_id:
        return UserPrivate(**user.dict())
    elif "admin" in current_user.roles:
        return UserAdmin(**user.dict())
    else:
        return UserPublic(**user.dict())

# Mask sensitive data in logs
import logging

class SensitiveDataFilter(logging.Filter):
    PATTERNS = [
        (r'"password":\s*"[^"]*"', '"password": "[REDACTED]"'),
        (r'"token":\s*"[^"]*"', '"token": "[REDACTED]"'),
        (r'"apiKey":\s*"[^"]*"', '"apiKey": "[REDACTED]"'),
        (r'\b\d{3}-\d{2}-\d{4}\b', '[SSN REDACTED]'),
        (r'\b\d{16}\b', '[CARD REDACTED]'),
    ]

    def filter(self, record):
        import re
        message = record.getMessage()
        for pattern, replacement in self.PATTERNS:
            message = re.sub(pattern, replacement, message)
        record.msg = message
        return True
```

```json
// ✅ Safe user response
{
  "id": 123,
  "name": "John Doe",
  "avatar": "https://cdn.example.com/avatars/123.jpg",
  "email": "j***n@example.com",
  "memberSince": "2023-01-15"
}

// ✅ Safe payment method response
{
  "paymentMethods": [
    {
      "id": "pm_abc123",
      "type": "card",
      "brand": "visa",
      "last4": "4242",
      "expiryMonth": 12,
      "expiryYear": 2025
    }
  ]
}
```

## Sensitive Data Checklist

| Field | Action | Reason |
|-------|--------|--------|
| Password hash | Never expose | Security |
| API keys | Never expose | Security |
| SSN/Tax ID | Never/mask | PII/Compliance |
| Full credit card | Never expose | PCI-DSS |
| Internal IDs | Usually hide | Information disclosure |
| Email (other users) | Mask | Privacy |
| Phone | Mask | Privacy |
| Address | Context-dependent | Privacy |

**Benefits:**
- Prevents account takeover from exposed credentials
- Meets privacy regulation requirements (GDPR, CCPA, PCI-DSS)
- Reduces attack surface by hiding internal system identifiers
- Context-based field exposure follows the principle of least privilege
- Data masking provides useful info without exposing full values
- Simplifies compliance audits with clear data handling policies

Reference: [OWASP Sensitive Data Exposure](https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/09-Testing_for_Weak_Cryptography/04-Testing_for_Weak_Encryption)


---

## Cursor-Based Pagination for Large Datasets

**Impact: HIGH (Constant O(1) pagination performance regardless of dataset depth)**

Offset pagination degrades linearly as page depth increases because the database must scan and discard all preceding rows. Cursor-based pagination uses an opaque pointer to the last retrieved item, enabling the database to seek directly to the next batch with constant performance.

## Incorrect

```http
GET /api/v1/orders?offset=500000&limit=20
```

```json
{
  "data": [
    { "id": 500001, "total": 49.99 },
    { "id": 500002, "total": 129.00 }
  ]
}
```

```sql
-- Behind the scenes: database scans 500,000 rows before returning 20
SELECT * FROM orders ORDER BY id LIMIT 20 OFFSET 500000;
```

**Problems:**
- Query time grows linearly with offset — page 25,000 is dramatically slower than page 1
- Database must scan and discard all rows before the offset
- Inconsistent results if rows are inserted or deleted between page requests
- Memory and CPU waste on large tables (millions of rows)

## Correct

```http
GET /api/v1/orders?cursor=eyJpZCI6NTAwMDAwfQ&limit=20
```

```json
{
  "data": [
    { "id": 500001, "total": 49.99 },
    { "id": 500002, "total": 129.00 }
  ],
  "meta": {
    "has_more": true,
    "next_cursor": "eyJpZCI6NTAwMDIwfQ"
  },
  "links": {
    "next": "/api/v1/orders?cursor=eyJpZCI6NTAwMDIwfQ&limit=20"
  }
}
```

```sql
-- Behind the scenes: index seek, constant performance
SELECT * FROM orders WHERE id > 500000 ORDER BY id LIMIT 20;
```

**Benefits:**
- Constant query time regardless of how deep into the dataset the client has paginated
- Stable results — no skipped or duplicated items when data changes between requests
- Efficient use of database indexes (seek instead of scan)
- Opaque cursor allows server-side implementation changes without breaking clients

Reference: [Slack API - Pagination](https://api.slack.com/docs/pagination)


---

## Offset Pagination for Simple Cases

**Impact: HIGH (Enables random page access and predictable navigation for small-to-medium datasets)**

Offset-based pagination is the most intuitive model for clients that need numbered pages, total counts, and the ability to jump to arbitrary pages. It works well for small-to-medium datasets and should always include metadata and navigation links so clients never have to guess the pagination state.

## Incorrect

```http
GET /api/v1/articles?page=2
```

```json
[
  { "id": 21, "title": "Introduction to REST" },
  { "id": 22, "title": "API Versioning" }
]
```

**Problems:**
- Bare array provides no pagination context — client cannot determine total pages or current position
- No navigation links — client must construct URLs manually and guess when to stop
- No indication of page size — unclear how many items per page the server returned
- Client cannot build a page selector UI without total count information

## Correct

```http
GET /api/v1/articles?page=2&per_page=20
```

```json
{
  "data": [
    { "id": 21, "title": "Introduction to REST" },
    { "id": 22, "title": "API Versioning" }
  ],
  "meta": {
    "current_page": 2,
    "per_page": 20,
    "total_pages": 10,
    "total_count": 195
  },
  "links": {
    "first": "/api/v1/articles?page=1&per_page=20",
    "prev": "/api/v1/articles?page=1&per_page=20",
    "next": "/api/v1/articles?page=3&per_page=20",
    "last": "/api/v1/articles?page=10&per_page=20"
  }
}
```

**Benefits:**
- Full pagination envelope lets clients render page selectors, "showing X of Y" indicators, and navigation controls
- Navigation links follow HATEOAS principles — clients follow links rather than constructing URLs
- `prev` and `next` are null-safe (omitted on first and last pages respectively)
- `per_page` parameter gives clients control over batch size within server-enforced limits

Reference: [JSON:API - Pagination](https://jsonapi.org/format/#fetching-pagination)


---

## Consistent Pagination Parameter Names

**Impact: HIGH (Reduces client integration time by 40-60% through predictable parameter conventions)**

When different endpoints use different parameter names for pagination, every client integration becomes a special case. Developers waste time reading docs for each endpoint instead of applying one convention everywhere. Pick one style and enforce it across the entire API.

## Incorrect

```http
# Users endpoint uses limit/offset
GET /api/v1/users?limit=20&offset=40

# Orders endpoint uses page/per_page
GET /api/v1/orders?page=3&per_page=20

# Products endpoint uses size/number
GET /api/v1/products?size=20&number=3

# Search endpoint uses count/start
GET /api/v1/search?count=20&start=40
```

**Problems:**
- Client SDKs need endpoint-specific pagination logic instead of a shared helper
- Developers must consult documentation for every endpoint to find the right parameter names
- Generic pagination UI components cannot be reused across different resource types
- Increased chance of bugs when developers assume one convention but the endpoint uses another

## Correct

```http
# Offset-based: use "page" + "per_page" everywhere
GET /api/v1/users?page=3&per_page=20
GET /api/v1/orders?page=3&per_page=20
GET /api/v1/products?page=3&per_page=20

# Cursor-based: use "cursor" + "limit" everywhere
GET /api/v1/events?cursor=eyJpZCI6MTIzfQ&limit=20
GET /api/v1/notifications?cursor=eyJpZCI6NDU2fQ&limit=20
GET /api/v1/logs?cursor=eyJpZCI6Nzg5fQ&limit=20
```

**Benefits:**
- One pagination helper in the client SDK handles all endpoints
- Developers learn the convention once and apply it everywhere
- Generic UI components (pagers, infinite scroll) work with any resource
- API documentation is simpler — pagination is explained once, not per-endpoint

Reference: [Microsoft REST API Guidelines - Pagination](https://github.com/microsoft/api-guidelines/blob/vNext/azure/Guidelines.md#pagination)


---

## Include Pagination Metadata in Responses

**Impact: HIGH (Eliminates guesswork pagination and reduces unnecessary API calls by 30-50%)**

Without pagination metadata, clients must make an extra request to discover there are no more results, or blindly paginate until they receive an empty response. Including metadata in every paginated response gives clients everything they need to render UI controls and make efficient decisions about fetching more data.

## Incorrect

```http
GET /api/v1/products?page=2&per_page=20
```

```json
[
  { "id": 21, "name": "Widget A", "price": 9.99 },
  { "id": 22, "name": "Widget B", "price": 14.99 }
]
```

**Problems:**
- Client cannot distinguish "page has fewer items than per_page" from "this is the last page"
- No total count means page selector UIs and "X results found" labels are impossible
- Client must request the next page to discover it is empty — wasting a round trip
- No navigation links forces clients to manually construct pagination URLs

## Correct

```http
GET /api/v1/products?page=2&per_page=20
```

```json
{
  "data": [
    { "id": 21, "name": "Widget A", "price": 9.99 },
    { "id": 22, "name": "Widget B", "price": 14.99 }
  ],
  "meta": {
    "current_page": 2,
    "per_page": 20,
    "total_count": 195,
    "total_pages": 10,
    "has_more": true
  },
  "links": {
    "first": "/api/v1/products?page=1&per_page=20",
    "prev": "/api/v1/products?page=1&per_page=20",
    "next": "/api/v1/products?page=3&per_page=20",
    "last": "/api/v1/products?page=10&per_page=20"
  }
}
```

**Benefits:**
- `has_more` lets infinite-scroll UIs know when to stop fetching without an extra empty request
- `total_count` and `total_pages` enable "Showing 21-40 of 195 results" display
- Navigation links let clients follow links instead of constructing URLs, reducing coupling
- Consistent envelope structure makes client-side deserialization predictable across all endpoints

Reference: [GitHub REST API - Pagination](https://docs.github.com/en/rest/using-the-rest-api/using-pagination-in-the-rest-api)


---

## Filter via Query Parameters

**Impact: HIGH (Cacheable, bookmarkable filtering that leverages HTTP semantics correctly)**

Filtering is a read operation and belongs in GET requests with query parameters. Using POST bodies for filtering breaks HTTP cacheability, makes URLs non-shareable, and violates the semantic contract of HTTP methods. Standard query parameters are combinable, cacheable, and immediately understandable.

## Incorrect

```http
POST /api/v1/users/search
Content-Type: application/json

{
  "filters": {
    "status": "active",
    "role": "admin",
    "created_after": "2024-01-01"
  }
}
```

```http
# Or: custom filter syntax that requires a parser
GET /api/v1/users?filter=status:eq:active|role:eq:admin|created:gt:2024-01-01
```

**Problems:**
- POST for read operations breaks HTTP caching at every layer (CDN, browser, proxy)
- URLs cannot be bookmarked, shared, or logged meaningfully
- Custom filter syntax requires client-side query builders and server-side parsers
- Violates REST semantics — POST implies resource creation or mutation, not retrieval

## Correct

```http
GET /api/v1/users?status=active&role=admin&created_after=2024-01-01
```

```json
{
  "data": [
    {
      "id": 42,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "status": "active",
      "role": "admin",
      "created_at": "2024-03-15T10:30:00Z"
    }
  ],
  "meta": {
    "total_count": 12,
    "filters_applied": {
      "status": "active",
      "role": "admin",
      "created_after": "2024-01-01"
    }
  }
}
```

```http
# Multiple values for the same field (OR logic)
GET /api/v1/users?status=active&status=pending

# Range filters with clear suffixes
GET /api/v1/orders?total_min=100&total_max=500&created_after=2024-01-01

# Combine with search
GET /api/v1/users?role=admin&q=smith
```

**Benefits:**
- Fully cacheable by CDNs, reverse proxies, and browsers
- URLs are bookmarkable and shareable — useful for dashboards and saved views
- Filters are self-documenting and combinable with standard `&` syntax
- No custom parser needed — standard query string parsing libraries handle it

Reference: [Google API Design Guide - Standard Methods](https://cloud.google.com/apis/design/standard_methods)


---

## Flexible Sorting Options

**Impact: HIGH (Empowers clients to retrieve data in the exact order they need without server-side changes)**

APIs that return data in a single hardcoded order force clients to re-sort in memory, which is wasteful and breaks pagination. A flexible sorting API lets clients request the order they need, and the database handles it efficiently using indexes.

## Incorrect

```http
# No sort parameter — always returns by id ASC
GET /api/v1/products
```

```json
[
  { "id": 1, "name": "Alpha", "price": 29.99, "created_at": "2023-01-01T00:00:00Z" },
  { "id": 2, "name": "Beta", "price": 9.99, "created_at": "2024-06-15T00:00:00Z" }
]
```

```http
# Or: inconsistent sort parameters across endpoints
GET /api/v1/products?order_by=price&direction=desc
GET /api/v1/users?sortField=name&sortOrder=asc
```

**Problems:**
- Client must fetch all data and sort in memory, defeating the purpose of pagination
- No way to get "newest first" or "cheapest first" without client-side processing
- Inconsistent sort parameter names across endpoints increase integration complexity
- Hardcoded order may not match any client's primary use case

## Correct

```http
# Ascending sort (default direction)
GET /api/v1/products?sort=price

# Descending sort with "-" prefix
GET /api/v1/products?sort=-created_at

# Multiple sort fields (comma-separated)
GET /api/v1/products?sort=category,-price

# Combined with filtering and pagination
GET /api/v1/products?status=active&sort=-created_at&page=1&per_page=20
```

```json
{
  "data": [
    { "id": 47, "name": "New Widget", "price": 59.99, "created_at": "2024-06-15T00:00:00Z" },
    { "id": 32, "name": "Another Widget", "price": 39.99, "created_at": "2024-05-10T00:00:00Z" }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total_count": 84,
    "sort": "-created_at"
  }
}
```

**Benefits:**
- `-` prefix convention for descending is compact and widely adopted (JSON:API, many major APIs)
- Multiple sort fields let clients express complex ordering like "category ascending, then price descending"
- Sorting happens at the database level where indexes make it efficient
- Consistent `sort` parameter name across all endpoints simplifies client SDKs

Reference: [JSON:API - Sorting](https://jsonapi.org/format/#fetching-sorting)


---

## Version in URL Path

**Impact: HIGH (Explicit version visibility prevents accidental breaking-change consumption)**

URL path versioning is the most explicit and widely adopted approach to API versioning. The version is visible in every request, making it impossible to accidentally hit the wrong version. It works naturally with routing, caching, load balancing, and documentation tools.

## Incorrect

```http
# No version — all consumers share one contract
GET /api/users
```

```json
// v1 response shape
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com"
}
```

```json
// After a breaking change — same URL, different shape
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "name": null
}
```

**Problems:**
- Breaking changes immediately affect all consumers with no migration path
- No way to run old and new versions side-by-side during transition periods
- Clients cannot pin to a known-good contract — any deploy can break them
- Rollback requires reverting the entire API, not just routing rules

## Correct

```http
# Version 1 — original contract
GET /api/v1/users/1
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com"
}
```

```http
# Version 2 — new contract, coexists with v1
GET /api/v2/users/1
```

```json
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "profile": {
    "avatar_url": "https://cdn.example.com/avatars/1.jpg"
  }
}
```

**Benefits:**
- Version is immediately visible in URLs, logs, and documentation
- Old and new versions run simultaneously — consumers migrate at their own pace
- Load balancers and API gateways can route versions to different backends
- CDNs and proxies cache each version independently without conflict

Reference: [Stripe API - Versioning](https://stripe.com/docs/api/versioning)


---

## Version via Accept Header

**Impact: HIGH (Clean URLs with content negotiation-based versioning for API evolution)**

Header-based versioning uses the `Accept` header to specify the desired API version, keeping URLs clean and resource-centric. This approach aligns with HTTP content negotiation semantics and is preferred when URL aesthetics matter or when the same resource should be accessible across versions without changing its canonical URL.

## Incorrect

```http
# No version information at all — client gets whatever the current version is
GET /api/users/1
Accept: application/json
```

```http
# Or: custom non-standard header
GET /api/users/1
X-API-Version: 2
```

**Problems:**
- No standard mechanism to request a specific version of the response format
- Custom headers are not part of HTTP content negotiation and may be stripped by proxies
- Clients have no guarantee about the response shape they will receive
- `X-` prefixed headers are deprecated by RFC 6648 and signal non-standard behavior

## Correct

```http
# Request version 1
GET /api/users/1
Accept: application/vnd.myapi.v1+json
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com"
}
```

```http
# Request version 2
GET /api/users/1
Accept: application/vnd.myapi.v2+json
```

```json
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "profile": {
    "avatar_url": "https://cdn.example.com/avatars/1.jpg"
  }
}
```

```http
# Server responds with matching Content-Type
HTTP/1.1 200 OK
Content-Type: application/vnd.myapi.v2+json
```

| Aspect | URL Path (`/v1/`) | Accept Header |
|---|---|---|
| Visibility | Version in every URL | Hidden in headers |
| Caching | Simple (URL-based) | Requires `Vary: Accept` |
| Browser testing | Easy (type URL) | Needs tool (curl, Postman) |
| URL cleanliness | Extra path segment | Clean resource URLs |
| HTTP semantics | Convention-based | Proper content negotiation |

**Benefits:**
- URLs remain clean and resource-focused — `/api/users/1` is the canonical identifier
- Follows HTTP content negotiation standards (`Accept` / `Content-Type`)
- Server can default to the latest version when no version header is sent
- Multiple representations of the same resource at the same URL, which aligns with REST principles

Reference: [GitHub API - Media Types](https://docs.github.com/en/rest/using-the-rest-api/getting-started-with-the-rest-api#accept)


---

## Maintain Backward Compatibility

**Impact: HIGH (Prevents breaking existing integrations and avoids costly emergency client fixes)**

Breaking changes in a live API force every consumer to update simultaneously or face outages. Maintaining backward compatibility within a version means clients continue working after deployments, and new features are delivered through additive changes only. Reserve breaking changes for new major versions.

## Incorrect

```http
# Before: GET /api/v1/users/1
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "role": "admin"
}
```

```http
# After deploy (same v1): field renamed, field removed, type changed
```

```json
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "roles": ["admin", "editor"]
}
```

**Problems:**
- Renaming `name` to `full_name` breaks every client reading `response.name`
- Renaming `email` to `email_address` breaks form bindings and display logic
- Changing `role` (string) to `roles` (array) causes type errors in client deserialization
- Removing fields with no notice gives consumers zero time to adapt

## Correct

```http
# Additive changes only within v1: new fields added, old fields preserved
GET /api/v1/users/1
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "full_name": "Jane Smith",
  "email": "jane@example.com",
  "email_address": "jane@example.com",
  "role": "admin",
  "roles": ["admin", "editor"],
  "avatar_url": "https://cdn.example.com/avatars/1.jpg"
}
```

```http
# Deprecation communicated via response headers
HTTP/1.1 200 OK
Content-Type: application/json
X-Deprecated-Fields: name, email, role
```

```http
# New endpoints are always safe to add
GET /api/v1/users/1/preferences    # new endpoint, no existing contract
```

**Benefits:**
- Existing clients continue working without any code changes after every deploy
- New clients can adopt new field names immediately while old names remain available
- Deprecation headers give automated tooling a way to detect and flag stale usage
- New endpoints and new fields never conflict with existing client expectations

Reference: [Stripe API - Backward Compatibility](https://stripe.com/docs/upgrades)


---

## Deprecation Strategy

**Impact: HIGH (Structured deprecation prevents surprise outages and gives consumers a clear migration path)**

Removing an API version or endpoint without advance notice breaks consumer trust and causes production outages. A proper deprecation strategy uses standard HTTP headers, documented timelines, and migration guides to give consumers the time and information they need to transition safely.

## Incorrect

```http
# Monday: API v1 is working
GET /api/v1/users
HTTP/1.1 200 OK

# Tuesday: API v1 is gone without warning
GET /api/v1/users
HTTP/1.1 404 Not Found

{
  "error": "This endpoint no longer exists"
}
```

**Problems:**
- Consumers discover the removal through production failures, not proactive communication
- No migration period means every consumer must update simultaneously or break
- No documentation of what changed or where to go creates confusion and support burden
- Erodes trust — consumers cannot rely on the API for production workloads

## Correct

```http
# Phase 1: Announce deprecation (minimum 6 months before removal)
GET /api/v1/users
HTTP/1.1 200 OK
Deprecation: true
Sunset: Sat, 01 Jun 2025 00:00:00 GMT
Link: </api/v2/migration-guide>; rel="deprecation"
```

```json
{
  "data": [
    { "id": 1, "name": "Jane Smith" }
  ],
  "_deprecation": {
    "message": "API v1 is deprecated. Please migrate to v2 by June 1, 2025.",
    "migration_guide": "https://api.example.com/docs/v1-to-v2-migration",
    "sunset_date": "2025-06-01T00:00:00Z"
  }
}
```

```http
# Phase 2: After sunset date — return 410 Gone (not 404)
GET /api/v1/users
HTTP/1.1 410 Gone

{
  "error": {
    "code": "API_VERSION_SUNSET",
    "message": "API v1 was removed on June 1, 2025. Please use /api/v2/users.",
    "migration_guide": "https://api.example.com/docs/v1-to-v2-migration",
    "current_version": "/api/v2/users"
  }
}
```

```
Deprecation Timeline:
  T-6 months: Add Deprecation + Sunset headers, publish migration guide
  T-3 months: Send email/notification to API consumers
  T-1 month:  Final reminder, log consumers still using deprecated version
  T-0:        Return 410 Gone with redirect information
  T+3 months: Remove deprecated code from codebase
```

**Benefits:**
- `Sunset` header (RFC 8594) is a machine-readable standard that monitoring tools can detect automatically
- `410 Gone` correctly signals permanent removal (unlike `404` which implies the resource may return)
- Migration guide link in both headers and body ensures consumers can find upgrade instructions
- Minimum 6-month window gives teams of all sizes enough time to plan and execute migration

Reference: [RFC 8594 - The Sunset HTTP Header Field](https://datatracker.ietf.org/doc/html/rfc8594)


---

## Consistent Response Envelope

**Impact: MEDIUM (Reduces client-side parsing complexity by 40-60%)**

A consistent response envelope allows API consumers to build reusable parsing logic that works across every endpoint. Without it, clients must special-case each endpoint's response shape, leading to fragile integration code and slower onboarding.

## Incorrect

```json
// ❌ Different shapes for different endpoints

// GET /users/123 — bare object
{
  "id": 123,
  "name": "Jane Doe",
  "email": "jane@example.com"
}

// GET /users — bare array
[
  { "id": 123, "name": "Jane Doe" },
  { "id": 456, "name": "John Smith" }
]

// GET /orders — nested differently
{
  "orders": [
    { "id": 1, "total": 99.99 }
  ],
  "count": 1
}

// GET /products/42 — yet another shape
{
  "product": {
    "id": 42,
    "title": "Widget"
  },
  "status": "ok"
}
```

**Problems:**
- Clients cannot predict the response structure for new endpoints
- Every endpoint requires unique parsing logic
- Impossible to build a generic API client or SDK
- Adding metadata (pagination, rate limits) requires breaking changes

## Correct

### Simple Envelope Style

```json
// ✅ Single resource — GET /users/123
{
  "data": {
    "id": 123,
    "name": "Jane Doe",
    "email": "jane@example.com"
  },
  "meta": {
    "request_id": "req_abc123"
  }
}

// ✅ Collection — GET /users?page=1&per_page=20
{
  "data": [
    { "id": 123, "name": "Jane Doe" },
    { "id": 456, "name": "John Smith" }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 142,
    "total_pages": 8,
    "request_id": "req_def456"
  }
}

// ✅ Empty collection — GET /users?status=banned
{
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 0,
    "total_pages": 0,
    "request_id": "req_ghi789"
  }
}
```

### JSON:API Style

```json
// ✅ Single resource — GET /users/123
{
  "data": {
    "type": "users",
    "id": "123",
    "attributes": {
      "name": "Jane Doe",
      "email": "jane@example.com"
    },
    "relationships": {
      "company": {
        "data": { "type": "companies", "id": "7" }
      }
    }
  },
  "included": [
    {
      "type": "companies",
      "id": "7",
      "attributes": {
        "name": "Acme Corp"
      }
    }
  ]
}

// ✅ Collection — GET /users
{
  "data": [
    {
      "type": "users",
      "id": "123",
      "attributes": { "name": "Jane Doe" }
    },
    {
      "type": "users",
      "id": "456",
      "attributes": { "name": "John Smith" }
    }
  ],
  "meta": {
    "total": 142,
    "page": 1,
    "per_page": 20
  },
  "links": {
    "self": "/users?page=1",
    "next": "/users?page=2",
    "last": "/users?page=8"
  }
}
```

**Benefits:**
- Clients build one parser that works for every endpoint
- Metadata (pagination, request IDs, rate limits) has a predictable location
- New metadata can be added to `meta` without breaking existing clients
- SDKs and generic API wrappers become straightforward to implement

Reference: [JSON:API Specification](https://jsonapi.org/format/)


---

## JSON Naming Conventions

**Impact: MEDIUM (Eliminates field-name guessing and mapping errors)**

Inconsistent naming forces developers to guess field names and write tedious mapping code. Picking one convention and applying it everywhere makes the API predictable and reduces integration bugs.

## Incorrect

```json
// ❌ Mixed conventions in the same response
{
  "userId": 123,
  "first_name": "Jane",
  "LastName": "Doe",
  "Email": "jane@example.com",
  "created_at": "2024-01-15",
  "lastLogin": "Jan 20, 2024 3:45 PM",
  "isActive": true,
  "acct_type": "premium",
  "DOB": "1990-05-20",
  "addr": {
    "str": "123 Main St",
    "ZipCode": "90210"
  }
}
```

**Problems:**
- Developers cannot predict whether a field uses camelCase, snake_case, or PascalCase
- Abbreviations like `acct`, `str`, `DOB` are ambiguous
- Date formats vary across fields, requiring per-field parsing
- Mapping between API responses and client models becomes error-prone

## Correct

### snake_case (common for Ruby, Python, PHP APIs)

```json
// ✅ Consistent snake_case
{
  "user_id": 123,
  "first_name": "Jane",
  "last_name": "Doe",
  "email": "jane@example.com",
  "created_at": "2024-01-15T10:30:00Z",
  "last_login_at": "2024-01-20T15:45:00Z",
  "is_active": true,
  "account_type": "premium",
  "date_of_birth": "1990-05-20",
  "address": {
    "street": "123 Main St",
    "zip_code": "90210"
  }
}
```

### camelCase (common for JavaScript/TypeScript APIs)

```json
// ✅ Consistent camelCase
{
  "userId": 123,
  "firstName": "Jane",
  "lastName": "Doe",
  "email": "jane@example.com",
  "createdAt": "2024-01-15T10:30:00Z",
  "lastLoginAt": "2024-01-20T15:45:00Z",
  "isActive": true,
  "accountType": "premium",
  "dateOfBirth": "1990-05-20",
  "address": {
    "street": "123 Main St",
    "zipCode": "90210"
  }
}
```

### Date and Time — Always ISO 8601

```json
// ✅ ISO 8601 with timezone
{
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-03-01T14:22:33+05:30",
  "expires_on": "2024-12-31",
  "duration_seconds": 3600
}

// ❌ Avoid non-standard date formats
{
  "created_at": "Jan 15, 2024",
  "updated_at": "03/01/2024",
  "expires_on": "1704067200"
}
```

### Null vs Omitted Fields

```json
// ✅ Use null for "set but empty" — include the field
{
  "first_name": "Jane",
  "middle_name": null,
  "last_name": "Doe"
}

// ✅ Omit fields that don't apply to this resource
// A "company" user has a company_name; a "personal" user omits it
{
  "first_name": "Jane",
  "last_name": "Doe",
  "account_type": "personal"
}
```

### Boolean Naming

```json
// ✅ Use is_, has_, can_, should_ prefixes
{
  "is_active": true,
  "is_verified": false,
  "has_two_factor": true,
  "can_edit": false,
  "should_notify": true
}

// ❌ Ambiguous boolean names
{
  "active": true,
  "verified": 1,
  "two_factor": "yes",
  "edit": false,
  "notification": true
}
```

**Benefits:**
- Developers can predict any field name without checking the docs
- Automated serialization/deserialization works without custom mappings
- ISO 8601 dates are natively parseable in every language
- Boolean prefixes make the type and intent immediately clear

Reference: [Google JSON Style Guide](https://google.github.io/styleguide/jsoncstyleguide.xml)


---

## Field Selection (Sparse Fieldsets)

**Impact: MEDIUM (Reduces payload size by 50-90% for field-heavy resources)**

When an endpoint returns 50+ fields but the client only needs 3, the wasted bandwidth slows mobile apps, increases server serialization time, and drives up data transfer costs. Field selection lets clients request only what they need.

## Incorrect

```http
// ❌ Client only needs name and avatar, but gets everything
GET /api/users/123

// Response: 2.4 KB
{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@example.com",
    "phone": "+1-555-0100",
    "avatar_url": "https://cdn.example.com/avatars/123.jpg",
    "date_of_birth": "1990-05-20",
    "bio": "Software engineer with 10 years of experience...",
    "address": {
      "street": "123 Main St",
      "city": "Springfield",
      "state": "IL",
      "zip_code": "62701",
      "country": "US"
    },
    "preferences": {
      "language": "en",
      "timezone": "America/Chicago",
      "theme": "dark",
      "notifications": {
        "email": true,
        "sms": false,
        "push": true
      }
    },
    "social_links": {
      "twitter": "https://twitter.com/janedoe",
      "linkedin": "https://linkedin.com/in/janedoe",
      "github": "https://github.com/janedoe"
    },
    "created_at": "2023-01-15T10:30:00Z",
    "updated_at": "2024-03-01T14:22:33Z",
    "last_login_at": "2024-03-10T09:15:00Z"
  }
}
```

**Problems:**
- Wasted bandwidth — client discards 90% of the response
- Slower responses on mobile or low-bandwidth connections
- Server serializes and queries for unused data
- Higher data transfer costs at scale

## Correct

### Query Parameter Approach

```http
// ✅ Client requests only the fields it needs
GET /api/users/123?fields=id,first_name,last_name,avatar_url

// Response: 180 bytes (93% smaller)
{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "last_name": "Doe",
    "avatar_url": "https://cdn.example.com/avatars/123.jpg"
  }
}
```

### Nested Fields

```http
// ✅ Dot notation for nested field selection
GET /api/users/123?fields=id,first_name,address.city,address.country

{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "address": {
      "city": "Springfield",
      "country": "US"
    }
  }
}
```

### Collections with Field Selection

```http
// ✅ Sparse fieldsets on collections — big savings at scale
GET /api/users?fields=id,first_name,avatar_url&per_page=50

// Instead of 50 × 2.4 KB = 120 KB
// Now 50 × 120 bytes = 6 KB
{
  "data": [
    { "id": 123, "first_name": "Jane", "avatar_url": "https://cdn.example.com/avatars/123.jpg" },
    { "id": 456, "first_name": "John", "avatar_url": "https://cdn.example.com/avatars/456.jpg" }
  ],
  "meta": {
    "total": 142,
    "page": 1,
    "per_page": 50
  }
}
```

### JSON:API Sparse Fieldsets

```http
// ✅ JSON:API uses typed field selection
GET /api/users?fields[users]=first_name,avatar_url&fields[companies]=name

{
  "data": [
    {
      "type": "users",
      "id": "123",
      "attributes": {
        "first_name": "Jane",
        "avatar_url": "https://cdn.example.com/avatars/123.jpg"
      },
      "relationships": {
        "company": { "data": { "type": "companies", "id": "7" } }
      }
    }
  ],
  "included": [
    {
      "type": "companies",
      "id": "7",
      "attributes": { "name": "Acme Corp" }
    }
  ]
}
```

**Benefits:**
- Payload size drops 50-90% for typical use cases
- Faster responses, especially on mobile networks
- Server can optimize database queries to fetch only requested columns
- Reduced data transfer costs at high request volumes

Reference: [Google API Design Guide — Standard Fields](https://cloud.google.com/apis/design/standard_fields)


---

## Response Compression

**Impact: MEDIUM (Reduces JSON payload transfer size by 60-80%)**

JSON is highly compressible because of its repetitive structure (keys, braces, quotes). Enabling compression is one of the simplest performance wins for any API, dramatically reducing transfer times with minimal CPU overhead.

## Incorrect

```http
// ❌ No compression — client doesn't advertise, server doesn't compress
GET /api/users?per_page=100 HTTP/1.1
Host: api.example.com

HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 245760

// 240 KB uncompressed JSON transferred over the wire
{
  "data": [
    { "id": 1, "name": "Jane Doe", "email": "jane@example.com", ... },
    { "id": 2, "name": "John Smith", "email": "john@example.com", ... },
    // ... 98 more records
  ]
}
```

**Problems:**
- 240 KB transferred when 48 KB (gzip) or 38 KB (Brotli) would suffice
- Slower time-to-first-byte, especially on mobile connections
- Higher bandwidth costs for both server and client
- Poor experience for users on metered or slow networks

## Correct

### Client Requests Compression

```http
// ✅ Client advertises supported encodings
GET /api/users?per_page=100 HTTP/1.1
Host: api.example.com
Accept-Encoding: gzip, br
```

### Server Responds with Compressed Content

```http
// ✅ gzip compression — widely supported
HTTP/1.1 200 OK
Content-Type: application/json
Content-Encoding: gzip
Vary: Accept-Encoding
Content-Length: 48200

// Same 240 KB JSON, now 48 KB over the wire (80% reduction)
```

```http
// ✅ Brotli compression — better ratio for modern clients
HTTP/1.1 200 OK
Content-Type: application/json
Content-Encoding: br
Vary: Accept-Encoding
Content-Length: 38400

// Same 240 KB JSON, now 38 KB over the wire (84% reduction)
```

### Typical Compression Ratios for JSON

```
Payload Type           | Raw     | gzip    | Brotli  | gzip %  | Brotli %
-----------------------+---------+---------+---------+---------+---------
Small object (1 KB)    | 1 KB    | 0.5 KB  | 0.4 KB  | 50%     | 60%
List of 100 records    | 240 KB  | 48 KB   | 38 KB   | 80%     | 84%
Large nested response  | 1.2 MB  | 180 KB  | 140 KB  | 85%     | 88%
Paginated collection   | 500 KB  | 85 KB   | 65 KB   | 83%     | 87%
```

### Important Headers

```http
// ✅ Always include Vary header so caches store compressed
// and uncompressed versions separately
Vary: Accept-Encoding

// ✅ Set minimum size threshold — don't compress tiny responses
// Most servers skip compression for responses under 1 KB
```

### Nginx Configuration Example

```nginx
# Enable gzip compression
gzip on;
gzip_types application/json application/javascript text/plain;
gzip_min_length 1024;
gzip_comp_level 6;
gzip_vary on;

# Enable Brotli (if module installed)
brotli on;
brotli_types application/json application/javascript text/plain;
brotli_min_length 1024;
brotli_comp_level 6;
```

### curl — Verifying Compression

```bash
# Request with compression and see headers
curl -s -H "Accept-Encoding: gzip, br" \
     -D - -o /dev/null \
     https://api.example.com/users

# Decompress and inspect
curl -s -H "Accept-Encoding: gzip" \
     --compressed \
     https://api.example.com/users | jq .
```

**Benefits:**
- 60-80% bandwidth reduction with gzip, 70-88% with Brotli
- Faster response delivery, especially over high-latency connections
- Lower bandwidth costs at scale
- Negligible CPU overhead on modern hardware (1-2% for gzip level 6)
- Transparent to clients — `curl --compressed` and all HTTP libraries handle it automatically

Reference: [MDN — Content-Encoding](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding)


---

## OpenAPI/Swagger Specification

**Impact: MEDIUM (Cuts API integration time by 50% with machine-readable docs)**

An OpenAPI specification serves as both documentation and a contract. It enables auto-generated client SDKs, interactive docs, and automated testing. Without it, developers rely on outdated wikis or reverse-engineering API behavior from code.

## Incorrect

```
// ❌ No formal API documentation

// Option A: Nothing at all — developers read source code
// Option B: Outdated wiki page last edited 18 months ago
// Option C: Slack messages like "hey how does the /users endpoint work?"
// Option D: README with a few curl examples that no longer match the API
```

**Problems:**
- Developers spend hours discovering endpoints and request formats by trial and error
- No single source of truth — information scattered across wikis, Slack, and code comments
- Documentation drifts out of sync with the actual API
- Cannot auto-generate client SDKs, mock servers, or test suites

## Correct

### Minimal OpenAPI 3.1 Specification

```yaml
# ✅ openapi.yaml — machine-readable, always up to date
openapi: "3.1.0"
info:
  title: User Management API
  version: "1.2.0"
  description: API for managing user accounts
  contact:
    name: API Support
    email: api-support@example.com

servers:
  - url: https://api.example.com/v1
    description: Production
  - url: https://staging-api.example.com/v1
    description: Staging

security:
  - bearerAuth: []

paths:
  /users:
    get:
      summary: List users
      operationId: listUsers
      tags:
        - Users
      parameters:
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          schema:
            type: integer
            default: 20
            maximum: 100
        - name: status
          in: query
          schema:
            type: string
            enum: [active, inactive, suspended]
      responses:
        "200":
          description: A paginated list of users
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: "#/components/schemas/User"
                  meta:
                    $ref: "#/components/schemas/PaginationMeta"
              example:
                data:
                  - id: 123
                    first_name: Jane
                    last_name: Doe
                    email: jane@example.com
                    status: active
                    created_at: "2024-01-15T10:30:00Z"
                meta:
                  page: 1
                  per_page: 20
                  total: 142
                  total_pages: 8
        "401":
          $ref: "#/components/responses/Unauthorized"

    post:
      summary: Create a user
      operationId: createUser
      tags:
        - Users
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: "#/components/schemas/CreateUserRequest"
            example:
              first_name: Jane
              last_name: Doe
              email: jane@example.com
              password: secureP@ssw0rd
      responses:
        "201":
          description: User created successfully
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    $ref: "#/components/schemas/User"
        "422":
          $ref: "#/components/responses/ValidationError"

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    User:
      type: object
      properties:
        id:
          type: integer
        first_name:
          type: string
        last_name:
          type: string
        email:
          type: string
          format: email
        status:
          type: string
          enum: [active, inactive, suspended]
        created_at:
          type: string
          format: date-time
      required:
        - id
        - first_name
        - last_name
        - email
        - status

    CreateUserRequest:
      type: object
      properties:
        first_name:
          type: string
          minLength: 1
          maxLength: 100
        last_name:
          type: string
          minLength: 1
          maxLength: 100
        email:
          type: string
          format: email
        password:
          type: string
          minLength: 8
      required:
        - first_name
        - last_name
        - email
        - password

    PaginationMeta:
      type: object
      properties:
        page:
          type: integer
        per_page:
          type: integer
        total:
          type: integer
        total_pages:
          type: integer

  responses:
    Unauthorized:
      description: Authentication required
      content:
        application/json:
          schema:
            type: object
            properties:
              error:
                type: object
                properties:
                  code:
                    type: string
                    example: UNAUTHORIZED
                  message:
                    type: string
                    example: Authentication required

    ValidationError:
      description: Request validation failed
      content:
        application/json:
          schema:
            type: object
            properties:
              error:
                type: object
                properties:
                  code:
                    type: string
                    example: VALIDATION_ERROR
                  message:
                    type: string
                  details:
                    type: array
                    items:
                      type: object
                      properties:
                        field:
                          type: string
                        code:
                          type: string
                        message:
                          type: string
```

### Recommended Tools

```
Swagger UI      — Interactive docs with "Try it out" button
Redoc           — Clean, responsive three-panel documentation
Stoplight       — Visual OpenAPI editor with linting
Spectral        — OpenAPI linting and validation CLI
openapi-generator — Auto-generate client SDKs in 40+ languages
```

**Benefits:**
- Single source of truth that stays in sync with the codebase
- Auto-generate client SDKs, mock servers, and test stubs
- Interactive documentation lets developers test endpoints in the browser
- CI/CD can validate the spec to catch breaking changes before deploy

Reference: [OpenAPI Specification 3.1](https://spec.openapis.org/oas/v3.1.0)


---

## Request/Response Examples

**Impact: MEDIUM (Reduces time-to-first-successful-call from hours to minutes)**

Schema definitions tell developers what is possible, but examples show them what to actually do. A developer can copy a curl command, run it, and have a working integration in minutes instead of interpreting abstract schemas for hours.

## Incorrect

```
// ❌ Documentation with only schema definitions, no examples

POST /users
  Request Body: CreateUserRequest schema
  Response: User schema (201) | Error schema (422)

Parameters:
  first_name: string, required
  last_name: string, required
  email: string, required, format: email
  password: string, required, minLength: 8

// Developer must guess: What does a real request look like?
// What headers are needed? What does the response actually contain?
// What does a validation error look like?
```

**Problems:**
- Developers must mentally construct requests from abstract schema descriptions
- No way to quickly copy-paste and test an endpoint
- Error response shapes are a mystery until they happen in production
- Onboarding new API consumers takes hours instead of minutes

## Correct

### Complete Request Example

```bash
# ✅ Create a user — complete curl example
curl -X POST https://api.example.com/v1/users \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@example.com",
    "password": "secureP@ssw0rd"
  }'
```

### Success Response Example

```http
HTTP/1.1 201 Created
Content-Type: application/json
Location: /v1/users/123
```

```json
// ✅ 201 Created — show exact response body
{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@example.com",
    "status": "active",
    "created_at": "2024-03-15T10:30:00Z"
  },
  "meta": {
    "request_id": "req_abc123"
  }
}
```

### Validation Error Example

```bash
# ✅ Show what happens with invalid input
curl -X POST https://api.example.com/v1/users \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "",
    "email": "not-an-email",
    "password": "short"
  }'
```

```json
// ✅ 422 Unprocessable Entity — show exact error shape
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The request contains invalid data",
    "details": [
      {
        "field": "first_name",
        "code": "REQUIRED",
        "message": "First name is required"
      },
      {
        "field": "last_name",
        "code": "REQUIRED",
        "message": "Last name is required"
      },
      {
        "field": "email",
        "code": "INVALID_FORMAT",
        "message": "Please provide a valid email address"
      },
      {
        "field": "password",
        "code": "TOO_SHORT",
        "message": "Password must be at least 8 characters"
      }
    ],
    "request_id": "req_def456"
  }
}
```

### Authentication Error Example

```bash
# ✅ Show what happens with missing/invalid token
curl -X GET https://api.example.com/v1/users \
  -H "Content-Type: application/json"
```

```json
// ✅ 401 Unauthorized
{
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required. Provide a valid Bearer token.",
    "request_id": "req_ghi789"
  }
}
```

### List Endpoint Example

```bash
# ✅ GET with query parameters
curl -X GET "https://api.example.com/v1/users?status=active&page=2&per_page=10" \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Accept: application/json"
```

```json
// ✅ 200 OK — show pagination metadata
{
  "data": [
    {
      "id": 123,
      "first_name": "Jane",
      "last_name": "Doe",
      "email": "jane@example.com",
      "status": "active"
    },
    {
      "id": 456,
      "first_name": "John",
      "last_name": "Smith",
      "email": "john@example.com",
      "status": "active"
    }
  ],
  "meta": {
    "page": 2,
    "per_page": 10,
    "total": 142,
    "total_pages": 15,
    "request_id": "req_jkl012"
  }
}
```

### Documentation Checklist

```
Every endpoint should include:
  ✅ Complete curl command with headers and body
  ✅ At least one success response (with realistic data)
  ✅ At least one error response (validation or auth)
  ✅ Query parameter examples for GET endpoints
  ✅ Response headers when relevant (Location, Link, etc.)
```

**Benefits:**
- Developers copy-paste and have a working request in seconds
- Error examples set correct expectations for client-side error handling
- Realistic data in examples clarifies field formats and semantics
- Reduces support tickets from confused API consumers

Reference: [Stripe API Documentation](https://stripe.com/docs/api) — gold standard for API examples


---

## API Changelog

**Impact: MEDIUM (Prevents production outages from undiscovered breaking changes)**

Without a changelog, API consumers discover breaking changes when their production integrations fail. A dated, categorized changelog gives consumers advance notice of changes so they can adapt their code before it breaks.

## Incorrect

```
// ❌ No changelog at all

// Consumers discover changes through:
// - Production errors after a deploy
// - Slack messages: "Did something change with /users?"
// - Trial and error comparing old vs new behavior
// - Reading git commit history (if the repo is even public)

// ❌ Or: a vague changelog with no useful detail

## Updates
- Fixed some bugs
- Improved performance
- Updated user endpoint
```

**Problems:**
- Breaking changes surprise consumers in production
- No way to know when a field was deprecated or removed
- Consumers cannot plan migration timelines for breaking changes
- Support teams fielding questions that a changelog would answer

## Correct

### Keep a Changelog Format

```markdown
# API Changelog

All notable changes to the API are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/).

## [1.5.0] - 2024-03-15

### Added
- `GET /users` now supports `?sort=created_at` and `?sort=last_login_at`
  query parameters for sorting results
- New `phone_verified` boolean field on User resource
- `POST /users/bulk` endpoint for creating up to 100 users in one request

### Changed
- `GET /users` default `per_page` changed from 50 to 20 for better
  performance. Use `?per_page=50` to restore previous behavior.

### Deprecated
- `GET /users` query parameter `?order` is deprecated in favor of `?sort`.
  `?order` will be removed in v2.0.0 (scheduled for 2024-09-01).
- User field `username` is deprecated. Use `email` as the unique identifier.
  Field will be removed in v2.0.0.

## [1.4.2] - 2024-02-28

### Fixed
- `PATCH /users/:id` now correctly returns 422 instead of 500 when email
  format is invalid
- Pagination `total_pages` calculation was off by one for exact multiples

## [1.4.1] - 2024-02-10

### Security
- Rate limiting on `POST /auth/login` reduced from 60 to 10 requests per
  minute to mitigate brute-force attacks

## [1.4.0] - 2024-01-20

### Added
- `GET /users/:id/activity` endpoint returning recent account activity
- Support for `fields` query parameter on all GET endpoints (sparse
  fieldsets)
- `X-Request-Id` response header on all endpoints

### Removed
- **BREAKING:** `GET /users/search` endpoint removed. Use
  `GET /users?q=search_term` instead. See [migration guide](https://docs.example.com/migration/search).

## [1.3.0] - 2024-01-05

### Added
- `Accept-Encoding: br` (Brotli) compression support
- `suspend` and `reactivate` actions on `POST /users/:id/actions`

### Changed
- Error responses now include `request_id` field in the error envelope
```

### Changelog Entry Categories

```
Added       — New endpoints, fields, query parameters, features
Changed     — Non-breaking changes to existing behavior
Deprecated  — Features that will be removed in a future version
Removed     — BREAKING: features removed in this version
Fixed       — Bug fixes
Security    — Security-related changes
```

### Communicating Breaking Changes

```http
// ✅ Deprecation header on responses for deprecated features
HTTP/1.1 200 OK
Deprecation: Sun, 01 Sep 2024 00:00:00 GMT
Sunset: Sun, 01 Sep 2024 00:00:00 GMT
Link: <https://docs.example.com/migration/search>; rel="deprecation"
```

```json
// ✅ Deprecation notice in response body
{
  "data": { ... },
  "meta": {
    "warnings": [
      {
        "code": "DEPRECATED_FIELD",
        "message": "Field 'username' is deprecated and will be removed on 2024-09-01. Use 'email' instead.",
        "see": "https://docs.example.com/migration/username"
      }
    ]
  }
}
```

### Best Practices

```
✅ Date every entry (ISO 8601: YYYY-MM-DD)
✅ Link changelog from your API documentation landing page
✅ Tag breaking changes with "BREAKING:" prefix
✅ Include migration guides for breaking changes
✅ Notify consumers via email/webhook before breaking changes
✅ Give at least 6 months notice before removing deprecated features
✅ Version the changelog alongside the API (same repo, same release)
```

**Benefits:**
- Consumers can review changes before updating their integrations
- Breaking changes come with advance notice and migration guides
- Deprecation timelines let consumers plan upgrades
- Reduces support burden by answering "what changed?" proactively

Reference: [Keep a Changelog](https://keepachangelog.com/)


---

