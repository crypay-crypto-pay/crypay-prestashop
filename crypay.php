<?php
/**
 * Crypay payment module for PrestaShop 8.x / 1.7.x
 */
if (!defined('_PS_VERSION_')) exit;

class Crypay extends PaymentModule {
  public $hooks = ['paymentOptions', 'paymentReturn', 'displayPaymentReturn'];

  public function __construct() {
    $this->name = 'crypay';
    $this->tab = 'payments_gateways';
    $this->version = '0.1.0';
    $this->author = 'Crypay';
    $this->bootstrap = true;
    $this->controllers = ['validation', 'webhook'];
    $this->is_eu_compatible = 1;
    $this->currencies = true;
    $this->currencies_mode = 'checkbox';
    parent::__construct();
    $this->displayName = 'Crypay (Crypto)';
    $this->description = 'Accept BTC, ETH, USDC, EURC and other cryptocurrencies via Crypay.';
    $this->confirmUninstall = 'Are you sure you want to uninstall the Crypay module?';
    $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '8.99.99'];
  }

  public function install() {
    return parent::install()
      && $this->registerHook('paymentOptions')
      && $this->registerHook('paymentReturn')
      && Configuration::updateValue('CRYPAY_GATEWAY_URL', 'https://gateway.dev.crypay.com')
      && Configuration::updateValue('CRYPAY_API_KEY', '')
      && Configuration::updateValue('CRYPAY_SANDBOX', 1);
  }

  public function uninstall() {
    return parent::uninstall()
      && Configuration::deleteByName('CRYPAY_GATEWAY_URL')
      && Configuration::deleteByName('CRYPAY_API_KEY')
      && Configuration::deleteByName('CRYPAY_SANDBOX');
  }

  public function getContent() {
    if (Tools::isSubmit('submitCrypay')) {
      Configuration::updateValue('CRYPAY_GATEWAY_URL', Tools::getValue('CRYPAY_GATEWAY_URL'));
      Configuration::updateValue('CRYPAY_API_KEY', Tools::getValue('CRYPAY_API_KEY'));
      Configuration::updateValue('CRYPAY_SANDBOX', (int) Tools::getValue('CRYPAY_SANDBOX'));
    }
    return $this->renderForm();
  }

  protected function renderForm() {
    $fields_form = [['form' => [
      'legend' => ['title' => $this->l('Crypay settings'), 'icon' => 'icon-cogs'],
      'input' => [
        ['type' => 'text', 'label' => $this->l('Gateway URL'), 'name' => 'CRYPAY_GATEWAY_URL', 'required' => true],
        ['type' => 'text', 'label' => $this->l('API key'),     'name' => 'CRYPAY_API_KEY',     'required' => true],
        ['type' => 'switch', 'label' => $this->l('Sandbox mode'), 'name' => 'CRYPAY_SANDBOX',
         'values' => [['id'=>'on','value'=>1,'label'=>'Yes'],['id'=>'off','value'=>0,'label'=>'No']]],
      ],
      'submit' => ['title' => $this->l('Save')],
    ]]];
    $helper = new HelperForm();
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
    $helper->submit_action = 'submitCrypay';
    $helper->fields_value = [
      'CRYPAY_GATEWAY_URL' => Configuration::get('CRYPAY_GATEWAY_URL'),
      'CRYPAY_API_KEY'     => Configuration::get('CRYPAY_API_KEY'),
      'CRYPAY_SANDBOX'     => Configuration::get('CRYPAY_SANDBOX'),
    ];
    return $helper->generateForm($fields_form);
  }

  public function hookPaymentOptions($params) {
    if (!$this->active || !$this->checkCurrency($params['cart'])) return;
    $option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
    $option->setCallToActionText($this->l('Pay with crypto via Crypay'))
      ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
      ->setLogo('https://crypay.com/images/crypay-logo.svg')
      ->setAdditionalInformation($this->l('You will be redirected to Crypay to complete the payment.'));
    return [$option];
  }

  public function checkCurrency($cart) {
    $currency_order = new Currency($cart->id_currency);
    $currencies_module = $this->getCurrency($cart->id_currency);
    if (is_array($currencies_module)) foreach ($currencies_module as $c) if ($currency_order->id == $c['id_currency']) return true;
    return false;
  }
}
