# Admin guide — Crypay PrestaShop module

## Configuration

Modules → Module Manager → Crypay → Configure.

| Field | Description |
|---|---|
| Gateway URL | `https://gateway.crypay.com` (prod) or `https://gateway.dev.crypay.com` (testnet) |
| API key | From Crypay merchant dashboard |
| Sandbox mode | ON for testing, OFF for production |

## Webhook

The webhook endpoint is `/module/crypay/webhook`. Configure this URL in your Crypay merchant dashboard.

## Refunds

Coming in 0.2.x. For now refund manually via the Crypay merchant dashboard, then mark the PrestaShop order as **Refunded**.
