<?php
if (! defined('_PS_VERSION_')) {
    exit();
}
/**
 * Provider of currency information from the gateway.
 */
class Wallee_Provider_Currency extends Wallee_Provider_Abstract {

	protected function __construct(){
		parent::__construct('wallee_currencies');
	}

	/**
	 * Returns the currency by the given code.
	 *
	 * @param string $code
	 * @return \Wallee\Sdk\Model\RestCurrency
	 */
	public function find($code){
		return parent::find($code);
	}

	/**
	 * Returns a list of currencies.
	 *
	 * @return \Wallee\Sdk\Model\RestCurrency[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
	    $currencyService = new \Wallee\Sdk\Service\CurrencyService(Wallee_Helper::getApiClient());
		return $currencyService->all();
	}

	protected function getId($entry){
		/* @var \Wallee\Sdk\Model\RestCurrency $entry */
		return $entry->getCurrencyCode();
	}
}