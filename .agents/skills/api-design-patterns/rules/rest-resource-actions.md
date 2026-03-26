---
title: Handle Non-CRUD Actions on Resources
impact: CRITICAL
impactDescription: "Proper handling of complex operations and state transitions"
tags: rest, actions, state-transitions, workflows
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
