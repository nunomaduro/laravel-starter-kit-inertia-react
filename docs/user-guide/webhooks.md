# Webhooks

## What Are Webhooks?

Webhooks send real-time notifications to external services when events occur in your organization. Instead of repeatedly polling your system for changes, webhooks push data to your endpoint as soon as an event happens.

Common use cases:
- **Integration Platforms**: Connect with Zapier, Make, or PipedreamWebhook services
- **Custom Integrations**: Trigger your own systems when users register, invitations are sent, etc.
- **Notifications**: Send Slack messages, create calendar events, log data to analytics platforms
- **Automations**: Synchronize with CRMs, billing systems, or data warehouses

## Default Events

| Event | Description |
|-------|-------------|
| `user.created` | A new user registered |
| `user.deleted` | A user was deleted |
| `organization.updated` | Organization settings changed |
| `invitation.sent` | An invitation was sent |
| `invitation.accepted` | An invitation was accepted |

Additional events may be available if your organization has enabled other modules (e.g., Contacts, Projects).

## Creating a Webhook Endpoint

1. Go to **Admin** → **Webhooks**
2. Click **New Endpoint**
3. Enter the following:
   - **URL**: The HTTPS endpoint that will receive webhook payloads
   - **Description** (optional): A note about this endpoint (e.g., "Slack Bot", "Zapier")
   - **Events**: Select one or more events to subscribe to
4. Click **Save**

The system generates a unique **Secret** for this endpoint—save it securely, as you'll need it to verify webhook authenticity.

## Webhook Payload Format

Every webhook sent to your endpoint includes:

```json
{
  "event": "user.created",
  "timestamp": "2026-03-30T10:00:00+00:00",
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

- **`event`**: The event name that triggered the webhook
- **`timestamp`**: ISO 8601 timestamp of when the event occurred
- **`data`**: Object containing the event details (varies per event)

## Testing Endpoints

To verify your endpoint is working:

1. Open the webhook endpoint
2. Click **Test**
3. Review the result:
   - ✓ **HTTP 200**: Success — your endpoint received and processed the webhook
   - ✓ **Other 2xx**: Success — your endpoint accepted the webhook (but may have processing notes in the response body)
   - ✗ **4xx / 5xx**: Failure — your endpoint returned an error

Results show the HTTP status code and response time in milliseconds.

## Endpoint Status

The endpoint list shows a **Status** indicator for each endpoint:

| Status | Color | Meaning |
|--------|-------|---------|
| Healthy | Green | Endpoint is receiving webhooks normally |
| Recovering | Amber | Endpoint had failures; system is testing recovery |
| Tripped | Red | Endpoint has failed too many times; webhooks are paused |
| Disabled | Grey | Endpoint is inactive (`is_active = false`) |

### Circuit Breaker

The system monitors endpoint health automatically:

- **Healthy**: Endpoint is working; webhooks are delivered normally
- **Recovering**: Recent requests failed; the system is testing if the endpoint has recovered
- **Tripped**: Failure rate exceeded 50%; webhooks are temporarily paused to prevent overload
  - The system will automatically attempt recovery after 1 hour
  - You can manually reset the circuit by clicking **Reset**
- **Disabled**: You manually turned off the endpoint; no webhooks are sent

## Resetting a Tripped Circuit

If an endpoint's circuit is tripped:

1. Open the endpoint
2. Click **Reset**
3. The circuit will attempt recovery immediately; if successful, status returns to Healthy

You should only need to reset if:
- Your endpoint was temporarily unavailable (server maintenance, etc.)
- You fixed an issue and want to resume webhooks immediately instead of waiting 1 hour

## Security: HMAC-SHA256 Signing

Every webhook is signed using HMAC-SHA256 with your endpoint's secret. This allows you to verify that webhooks came from your system and weren't tampered with.

### Verifying Webhook Signatures

All webhooks include an `X-Webhook-Signature` header in the format:

```
X-Webhook-Signature: sha256=<hash>
```

To verify:

1. Extract the raw request body (as bytes, before any parsing)
2. Compute: `hash = HMAC-SHA256(secret, body)`
3. Compare the computed hash (in hex) with the signature header value
4. Accept only if they match exactly

**Example (Node.js)**:

```javascript
const crypto = require('crypto');

const secret = process.env.WEBHOOK_SECRET; // Your endpoint's secret
const signature = req.headers['x-webhook-signature']; // From webhook header
const body = req.rawBody; // Raw request body as bytes

const hash = crypto
  .createHmac('sha256', secret)
  .update(body)
  .digest('hex');

const expected = `sha256=${hash}`;
const verified = crypto.timingSafeEqual(expected, signature);

if (!verified) {
  return res.status(403).send('Invalid signature');
}
```

**Example (Python)**:

```python
import hmac
import hashlib

secret = os.environ['WEBHOOK_SECRET']
signature = request.headers['X-Webhook-Signature']
body = request.get_data()

hash_obj = hmac.new(
    secret.encode(),
    body,
    hashlib.sha256
)
expected = f"sha256={hash_obj.hexdigest()}"

if not hmac.compare_digest(expected, signature):
    return 'Invalid signature', 403
```

## Rotating Secrets

If you suspect a secret has been compromised or exposed:

1. Open the endpoint
2. Click **Regenerate Secret**
3. A new secret is created immediately
4. Update your webhook consumer with the new secret
5. The old secret becomes invalid and will be rejected

**Important**: Regenerating the secret invalidates the old secret everywhere it's used. Ensure all integrations are updated before regenerating.

## Viewing Webhook Logs

To see a history of webhook deliveries:

1. Go to **Admin** → **Webhooks**
2. Click on an endpoint to see recent deliveries
3. View:
   - Event type
   - HTTP status code
   - Response time
   - Timestamp
   - Payload (for debugging)

## Disabling Webhooks

To temporarily stop sending webhooks to an endpoint:

1. Open the endpoint
2. Toggle **Active** to off
3. No webhooks will be sent to this endpoint
4. Status shows as "Disabled" (grey)

Re-enable by toggling **Active** back on.

## Troubleshooting

### Webhooks Aren't Being Delivered

1. **Check Status**: Is the endpoint status Healthy?
   - If Tripped: Click Reset and verify the endpoint is working
   - If Disabled: Toggle Active back on
2. **Check Events**: Verify the endpoint is subscribed to the event you're testing
3. **Check URL**: Ensure the URL is HTTPS and reachable from the internet
4. **Test Endpoint**: Click Test to verify connectivity
5. **Check Logs**: Review recent webhook attempts in the endpoint's logs

### Endpoint Returns 5xx Errors

Your endpoint code is throwing an error. Check:
- Server logs
- Application error tracking (e.g., Sentry)
- Whether required environment variables or integrations are configured

### Signature Verification Fails

Ensure:
- You're using the **correct secret** (check the endpoint details)
- The secret hasn't been regenerated (old secret won't work)
- You're hashing the **raw request body** (before parsing JSON)
- You're using HMAC-SHA256 (not SHA-1, SHA-256 without HMAC, etc.)

## Permissions

You must have the **`org.webhooks.manage`** permission to create, edit, delete, or test webhook endpoints. Organization owners and admins have this permission by default.

If you don't have permission, contact your organization owner.
