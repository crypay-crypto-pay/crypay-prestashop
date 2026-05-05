<?php
class CrypayValidationModuleFrontController extends ModuleFrontController {
  public function postProcess() {
    $cart = $this->context->cart;
    if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice || !$this->module->active)
      Tools::redirect('index.php?controller=order&step=1');

    $customer = new Customer($cart->id_customer);
    $total    = (float) $cart->getOrderTotal(true, Cart::BOTH);
    $currency = (new Currency($cart->id_currency))->iso_code;

    $body = json_encode([
      'amount'    => $total,
      'currency'  => $currency,
      'reference' => 'PS-'.$cart->id,
      'redirect'  => Context::getContext()->link->getPageLink('order-confirmation', null, null, ['id_cart' => $cart->id, 'id_module' => $this->module->id, 'id_order' => $this->module->currentOrder, 'key' => $customer->secure_key]),
      'webhook'   => Context::getContext()->link->getModuleLink($this->module->name, 'webhook'),
      'customer'  => ['email' => $customer->email],
    ]);

    $ch = curl_init(rtrim(Configuration::get('CRYPAY_GATEWAY_URL'), '/').'/api/v1/payments');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $body,
      CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'X-API-Key: '.Configuration::get('CRYPAY_API_KEY')],
      CURLOPT_TIMEOUT        => 20,
    ]);
    $resp = curl_exec($ch); curl_close($ch);
    $data = json_decode($resp, true);

    if (!empty($data['url'])) {
      $this->module->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total, $this->module->displayName, null, [], (int) $cart->id_currency, false, $customer->secure_key);
      Tools::redirect($data['url']);
    }
    Tools::redirect('index.php?controller=order&step=3&cart_error=1');
  }
}
