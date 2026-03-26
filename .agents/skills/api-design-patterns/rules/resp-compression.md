---
title: Response Compression
impact: MEDIUM
impactDescription: "Reduces JSON payload transfer size by 60-80%"
tags: compression, gzip, performance, bandwidth
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
