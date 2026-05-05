<?php
class CrypayWebhookModuleFrontController extends ModuleFrontController {
  public function postProcess() {
    $payload = file_get_contents('php://input');
    $data    = json_decode($payload, true);
    if (empty($data['reference']) || !preg_match('/^PS-(\d+)$/', $data['reference'], $m)) { http_response_code(400); echo 'invalid reference'; exit; }

    $cart_id = (int) $m[1];
    $order_id = Order::getOrderByCartId($cart_id);
    if (!$order_id) { http_response_code(404); echo 'order not found'; exit; }

    // TODO HMAC verification (CRY-785 phase 2)
    $order = new Order($order_id);
    $state = $data['state'] ?? '';
    if ($state === 'SUCCESS') {
      $order->setCurrentState((int) Configuration::get('PS_OS_PAYMENT'));
    } elseif (in_array($state, ['FAILED', 'CANCELLED'])) {
      $order->setCurrentState((int) Configuration::get('PS_OS_CANCELED'));
    }
    echo 'ok';
  }
}
