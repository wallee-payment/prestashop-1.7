<?php
if (! defined('_PS_VERSION_')) {
    exit();
}

/**
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

class Wallee_Webhook_Entity {
	private $id;
	private $name;
	private $states;
	private $notifyEveryChange;
	private $handlerClassName;

	public function __construct($id, $name, array $states, $handlerClassName, $notifyEveryChange = false){
		$this->id = $id;
		$this->name = $name;
		$this->states = $states;
		$this->notifyEveryChange = $notifyEveryChange;
		$this->handlerClassName = $handlerClassName;
	}

	public function getId(){
		return $this->id;
	}

	public function getName(){
		return $this->name;
	}

	public function getStates(){
		return $this->states;
	}

	public function isNotifyEveryChange(){
		return $this->notifyEveryChange;
	}

	public function getHandlerClassName(){
		return $this->handlerClassName;
	}
}