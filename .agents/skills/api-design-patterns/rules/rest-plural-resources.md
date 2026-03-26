---
title: Use Plural Nouns for Resource Collections
impact: CRITICAL
impactDescription: "Improves API consistency and predictability"
tags: rest, resources, naming, conventions
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
