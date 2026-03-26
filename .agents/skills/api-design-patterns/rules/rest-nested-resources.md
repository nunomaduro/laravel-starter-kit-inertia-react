---
title: Design Nested Resources for Hierarchical Relationships
impact: CRITICAL
impactDescription: "Clarifies resource relationships and authorization boundaries"
tags: rest, resources, nesting, hierarchy
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
