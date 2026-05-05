# Installation guide — Crypay PrestaShop module

## Requirements

- PrestaShop 1.7.8+ or 8.x
- PHP 7.4 / 8.0 / 8.1 / 8.2
- A Crypay merchant account + API key

## Install

### Option A — ZIP upload (recommended)

1. Download the latest release from https://github.com/crypay-crypto-pay/crypay-prestashop/releases
2. PrestaShop admin → **Modules → Module Manager**
3. Click **Upload a module** → choose the ZIP → Install

### Option B — manual

```bash
cd /var/www/html/modules
git clone https://github.com/crypay-crypto-pay/crypay-prestashop.git crypay
chown -R www-data:www-data crypay/
```

Then in PrestaShop admin → **Module Manager** → search "Crypay" → Install.

## Configure

1. Open the module's **Configure** screen.
2. Set **Gateway URL** to `https://gateway.dev.crypay.com` (testnet) or `https://gateway.crypay.com` (mainnet).
3. Paste your **API key**.
4. Tick **Sandbox mode** while testing.
5. Save.

## Test

1. Place an order in the front-office.
2. At checkout, choose **Pay with crypto via Crypay**.
3. You'll be redirected to Crypay; pay (testnet auto-confirms in 30 s).
4. Order status changes to **Payment accepted** in Order list.
