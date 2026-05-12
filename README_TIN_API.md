# REMIS – TIN Verification API Integration

## Overview

REMIS integrates with the **Tanzania Revenue Authority (TRA) VAT Verification API** to allow landlords to add tenants by looking up their Tax Identification Number (TIN). The system acts as a secure server-side proxy — the browser never contacts the TRA server directly; every request goes through the REMIS backend first.

---

## How the Add-Tenant Flow Works

The **Add Tenant** page offers two mutually exclusive modes:

| Mode | When to use |
|------|-------------|
| **Look up by TIN** | Tenant has a TIN registered with TRA — their name is fetched automatically |
| **Enter Manually** | Tenant has no TIN, or the TRA server is not yet connected |

**TIN lookup flow:**
1. Landlord enters the tenant's TIN and clicks **Verify**
2. REMIS calls the TRA VAT API server-side and returns the taxpayer's full name, VAT status, and VRN
3. A green confirmation card appears below the TIN field with the verified details
4. Landlord fills in email, phone, and gender, then clicks **Confirm & Add Tenant**
5. The tenant is created and immediately appears in the Tenants list

**Failure messages** appear inline below the TIN input — no page reload, no confusion.

---

## Architecture

```
Browser (landlord — REMIS dashboard)
        │
        │  POST /landlord/tenants/verify-tin
        │  { "tin": "100-200-300" }            ← AJAX, JSON
        ▼
REMIS Backend  (TinVerificationController)
        │
        │  GET {TRA_API_BASE_URL}/api/CheckVatStatusFull/{tin}
        │  Authorization: Bearer {TRA_API_KEY}  ← added server-side only
        ▼
TRA VAT API Server  (TRA-managed, runs on a fixed IP:PORT)
```

The browser never sees the `TRA_API_KEY` or the raw TRA server address — they live in `.env` only.

---

## TRA API Endpoints (as provided in the TRA specification)

### Endpoint 1 – Full Taxpayer Details *(used by REMIS)*
```
GET https://{PUBLIC_IP}:{PORT}/api/CheckVatStatusFull/{taxpayerId}
```
**Response:**
```json
{
  "taxpayerId":   12345,
  "taxpayerName": "JUMA HASSAN MWALIMU",
  "firstName":    "JUMA",
  "middleName":   "HASSAN",
  "lastName":     "MWALIMU",
  "hasVAT":       true,
  "vrn":          "40-012345-A",
  "tradingName":  "MWALIMU ENTERPRISES"
}
```

### Endpoint 2 – Basic VAT Status *(not used by REMIS — reference only)*
```
GET https://{PUBLIC_IP}:{PORT}/api/CheckVatStatus/{taxpayerId}
```
**Response:**
```json
{
  "TaxpayerId": 12345,
  "HasVAT":     true,
  "Vrn":        "40-012345-A"
}
```

---

## How to Obtain Your TRA API Credentials

The `{PUBLIC_IP}:{PORT}` in the endpoint URLs **is the base URL** you will set as `TRA_API_BASE_URL`. TRA provides this address, along with any authentication token, when they grant you access to the VAT verification service.

### Step-by-step process

1. **Register your business with TRA**
   Visit [www.tra.go.tz](https://www.tra.go.tz) → Services → Taxpayer Registration.
   You must have an active TIN before applying for API access.

2. **Apply for EFD/VFD integration**
   TRA's VAT verification endpoints are part of their **Electronic Fiscal Device Management System (EFDMS)**.
   Go to [www.tra.go.tz/page/know-about-e-fiscal-devices-efd](https://www.tra.go.tz/page/know-about-e-fiscal-devices-efd)
   and contact TRA to request API/VFD integration credentials.

3. **What TRA will provide**
   After approval TRA issues:
   - **Public IP address** of their API server (the `{PUBLIC_IP}` in the endpoint)
   - **Port number** (the `{PORT}` in the endpoint — commonly `8090`, `8443`, or similar)
   - **Bearer token / API key** for authenticated requests (may be optional for some deployments)

4. **Set these values in `.env`**
   ```env
   TRA_API_BASE_URL=https://196.xxx.xxx.xxx:8090
   TRA_API_KEY=your-bearer-token-from-tra
   TRA_API_TIMEOUT=35
   TRA_DEV_MODE=false
   ```
   Then run:
   ```bash
   php artisan config:cache
   ```

5. **Contact TRA directly**
   - Website: [www.tra.go.tz](https://www.tra.go.tz)
   - Phone (TRA HQ, Dar es Salaam): +255 22 211 9591
   - Email: taxpayerservices@tra.go.tz
   - For technical/API queries, ask to speak to the **EFDMS technical team**

---

## Running Locally (XAMPP / No TRA Server)

Because this project runs on `localhost`, it cannot reach a live TRA server (which is hosted on a remote government IP). There are two approaches:

### Option A — Dev Mode (recommended for local development)

Set `TRA_DEV_MODE=true` in `.env`:

```env
TRA_DEV_MODE=true
```

The controller will return **realistic mock data** instantly without needing any TRA connection. This lets you test the entire add-tenant-by-TIN flow right now.

- Any TIN number → returns a fake verified taxpayer (`JUMA HASSAN MWALIMU`)
- TIN `000-000-000` → simulates a "not found" failure so you can test error handling

When credentials are received from TRA, set `TRA_DEV_MODE=false` and fill in the real `TRA_API_BASE_URL` and `TRA_API_KEY`.

### Option B — Manual Entry mode

Leave `TRA_DEV_MODE=false` and `TRA_API_BASE_URL` empty. The TIN lookup mode will display a clear message:

> *"The TRA verification service is not yet configured… Use 'Enter Manually' to add the tenant."*

Users can still add tenants through the **Enter Manually** tab without any issues.

### Firewall / Network note (for when credentials arrive)

The TRA API server runs on a specific public IP and port. When you deploy on a server (not localhost), make sure:

- Outbound TCP to `{TRA_IP}:{TRA_PORT}` is allowed through your server's firewall
- If the TRA server uses a **self-signed SSL certificate** (common), REMIS already handles this — `withoutVerifying()` is enabled in the controller for this connection only

---

## Configuration Reference

All values are set in `.env` and read via `config/services.php`:

| `.env` key | Default | Description |
|-----------|---------|-------------|
| `TRA_API_BASE_URL` | *(empty)* | Full base URL from TRA, e.g. `https://196.x.x.x:8090` |
| `TRA_API_KEY` | *(empty)* | Bearer token from TRA (omit if not required) |
| `TRA_API_TIMEOUT` | `35` | Seconds before the request times out (TRA servers can be slow) |
| `TRA_DEV_MODE` | `false` | Set `true` locally to return mock data without a TRA connection |

After changing `.env` values, always run:
```bash
php artisan config:cache
```

---

## REMIS Internal Endpoint Reference

### `POST /landlord/tenants/verify-tin`

| Property | Value |
|----------|-------|
| Method | POST |
| URL | `/landlord/tenants/verify-tin` |
| Auth | Session (landlord must be logged in) |
| CSRF | Required (`X-CSRF-TOKEN` header) |
| Content-Type | `application/json` |

**Request body:**
```json
{ "tin": "100-200-300" }
```

**Success (HTTP 200):**
```json
{
  "success": true,
  "data": {
    "taxpayer_id":   12345,
    "taxpayer_name": "JUMA HASSAN MWALIMU",
    "first_name":    "JUMA",
    "middle_name":   "HASSAN",
    "last_name":     "MWALIMU",
    "has_vat":       true,
    "vrn":           "40-012345-A",
    "trading_name":  "MWALIMU ENTERPRISES"
  }
}
```

**Failure responses:**

| HTTP | Condition | Message shown to user |
|------|-----------|----------------------|
| 422 | TIN not in TRA system | "Taxpayer not found. The TIN number is not registered…" |
| 422 | TRA returned unexpected status | "The TRA service returned an unexpected response (HTTP N)." |
| 422 | TRA returned empty body | "The TRA service returned an empty response." |
| 503 | API not configured | "The TRA verification service is not yet configured… Use 'Enter Manually'…" |
| 503 | Auth failure (bad key) | "Authentication with the TRA service failed. Check TRA_API_KEY…" |
| 503 | Cannot reach TRA server | "Could not reach the TRA verification server… Check network or firewall." |

---

## Files Changed / Created

| File | Purpose |
|------|---------|
| `app/Http/Controllers/TinVerificationController.php` | TRA API proxy controller |
| `resources/views/landlord/tenants/create.blade.php` | Add Tenant page with TIN/Manual toggle |
| `app/Http/Controllers/LandlordController.php` | `tenantsStore` handles both modes |
| `routes/web.php` | `POST /landlord/tenants/verify-tin` route |
| `config/services.php` | `tra` service config block |
| `.env` | `TRA_API_BASE_URL`, `TRA_API_KEY`, `TRA_API_TIMEOUT`, `TRA_DEV_MODE` |

---

## Error Handling & Logging

All TRA API errors are written to `storage/logs/laravel.log` with context:

```
[ERROR] TRA API connection error {"tin":"100-200-300","error":"cURL error 7: Failed to connect…"}
[ERROR] TRA API authentication failure {"tin":"100-200-300","status":401}
[WARNING] TRA API non-success response {"tin":"100-200-300","status":500,"body":"…"}
```

To watch the log in real time (Git Bash / WSL on Windows):
```bash
tail -f storage/logs/laravel.log | grep TRA
```

---

## Security Notes

- `TRA_API_KEY` never leaves the server — it is injected in PHP, not exposed to JavaScript
- The verify endpoint requires an authenticated landlord session (Laravel `auth` + `role:landlord` middleware)
- CSRF is enforced — all AJAX requests must include the `X-CSRF-TOKEN` header (already handled in the frontend JS)
- Only the fields needed to create a tenant are forwarded from the TRA response — the raw TRA payload is not passed through
- SSL verification is disabled **only** for the TRA HTTP call (`withoutVerifying()`) to accommodate TRA's self-signed certificates; all other HTTPS connections in the app use normal verification
