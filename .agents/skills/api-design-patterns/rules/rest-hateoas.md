---
title: Include HATEOAS Links for Discoverability
impact: CRITICAL
impactDescription: "Improves API discoverability and reduces client coupling"
tags: rest, hateoas, hypermedia, discoverability
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
