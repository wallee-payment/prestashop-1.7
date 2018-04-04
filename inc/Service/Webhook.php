<?php
if (! defined('_PS_VERSION_')) {
    exit();
}

/**
 * This service handles webhooks.
 */
class Wallee_Service_Webhook extends Wallee_Service_Abstract {
	
	/**
	 * The webhook listener API service.
	 *
	 * @var \Wallee\Sdk\Service\WebhookListenerService
	 */
	private $webhookListenerService;
	
	/**
	 * The webhook url API service.
	 *
	 * @var \Wallee\Sdk\Service\WebhookUrlService
	 */
	private $webhookUrlService;
	private $webhookEntities = array();

	/**
	 * Constructor to register the webhook entites.
	 */
	public function __construct(){
	    $this->webhookEntities[1487165678181] = new Wallee_Webhook_Entity(1487165678181, 'Manual Task', 
				array(
					\Wallee\Sdk\Model\ManualTaskState::DONE,
					\Wallee\Sdk\Model\ManualTaskState::EXPIRED,
					\Wallee\Sdk\Model\ManualTaskState::OPEN
				), 'Wallee_Webhook_ManualTask');
	    $this->webhookEntities[1472041857405] = new Wallee_Webhook_Entity(1472041857405, 'Payment Method Configuration', 
				array(
					\Wallee\Sdk\Model\CreationEntityState::ACTIVE,
					\Wallee\Sdk\Model\CreationEntityState::DELETED,
					\Wallee\Sdk\Model\CreationEntityState::DELETING,
					\Wallee\Sdk\Model\CreationEntityState::INACTIVE 
				), 'Wallee_Webhook_MethodConfiguration', true);
	    $this->webhookEntities[1472041829003] = new Wallee_Webhook_Entity(1472041829003, 'Transaction', 
				array(
					\Wallee\Sdk\Model\TransactionState::AUTHORIZED,
					\Wallee\Sdk\Model\TransactionState::DECLINE,
					\Wallee\Sdk\Model\TransactionState::FAILED,
					\Wallee\Sdk\Model\TransactionState::FULFILL,
					\Wallee\Sdk\Model\TransactionState::VOIDED,
					\Wallee\Sdk\Model\TransactionState::COMPLETED,
				), 'Wallee_Webhook_Transaction');
	    $this->webhookEntities[1472041819799] = new Wallee_Webhook_Entity(1472041819799, 'Delivery Indication', 
				array(
					\Wallee\Sdk\Model\DeliveryIndicationState::MANUAL_CHECK_REQUIRED 
				), 'Wallee_Webhook_DeliveryIndication');
		
	    $this->webhookEntities[1472041831364] = new Wallee_Webhook_Entity(1472041831364, 'Transaction Completion', 
				array(
					\Wallee\Sdk\Model\TransactionCompletionState::FAILED,
					\Wallee\Sdk\Model\TransactionCompletionState::SUCCESSFUL 
				), 'Wallee_Webhook_TransactionCompletion');
		
	    $this->webhookEntities[1472041867364] = new Wallee_Webhook_Entity(1472041867364, 'Transaction Void', 
				array(
					\Wallee\Sdk\Model\TransactionVoidState::FAILED,
					\Wallee\Sdk\Model\TransactionVoidState::SUCCESSFUL 
				), 'Wallee_Webhook_TransactionVoid');
		
	    $this->webhookEntities[1472041839405] = new Wallee_Webhook_Entity(1472041839405, 'Refund', 
				array(
					\Wallee\Sdk\Model\RefundState::FAILED,
					\Wallee\Sdk\Model\RefundState::SUCCESSFUL 
				), 'Wallee_Webhook_Refund');
	    $this->webhookEntities[1472041806455] = new Wallee_Webhook_Entity(1472041806455, 'Token', 
				array(
					\Wallee\Sdk\Model\CreationEntityState::ACTIVE,
					\Wallee\Sdk\Model\CreationEntityState::DELETED,
					\Wallee\Sdk\Model\CreationEntityState::DELETING,
					\Wallee\Sdk\Model\CreationEntityState::INACTIVE 
				), 'Wallee_Webhook_Token');
	    $this->webhookEntities[1472041811051] = new Wallee_Webhook_Entity(1472041811051, 'Token Version', 
				array(
					\Wallee\Sdk\Model\TokenVersionState::ACTIVE,
					\Wallee\Sdk\Model\TokenVersionState::OBSOLETE 
				), 'Wallee_Webhook_TokenVersion');
	}

	
	/**
	 * Installs the necessary webhooks in wallee.
	 */
	public function install()
	{
	    $spaceIds = array();
	    foreach (Shop::getShops(true, null, true) as $shopId) {
	        $spaceId = Configuration::get(Wallee::CK_SPACE_ID, null, null, $shopId);
	        if ($spaceId && ! in_array($spaceId, $spaceIds)) {
	            $webhookUrl = $this->getWebhookUrl($spaceId);
	            if ($webhookUrl == null) {
	                $webhookUrl = $this->createWebhookUrl($spaceId);
	            }	            
	            $existingListeners = $this->getWebhookListeners($spaceId, $webhookUrl);
	            foreach ($this->webhookEntities as $webhookEntity) {
	                /* @var Wallee_Webhook_Entity $webhookEntity */
	                $exists = false;
	                foreach ($existingListeners as $existingListener) {
	                    if ($existingListener->getEntity() == $webhookEntity->getId()) {
	                        $exists = true;
	                    }
	                }	                
	                if (! $exists) {
	                    $this->createWebhookListener($webhookEntity, $spaceId, $webhookUrl);
	                }
	            }	            
	            $spaceIds[] = $spaceId;
	        }
	    }
	}

	/**
	 * @param int|string $id
	 * @return Wallee_Webhook_Entity
	 */
	public function getWebhookEntityForId($id){
		if (isset($this->webhookEntities[$id])) {
			return $this->webhookEntities[$id];
		}
		return null;
	}

	/**
	 * Create a webhook listener.
	 *
	 * @param Wallee_Webhook_Entity $entity
	 * @param int $spaceId
	 * @param \Wallee\Sdk\Model\WebhookUrl $webhookUrl
	 * @return \Wallee\Sdk\Model\WebhookListenerCreate
	 */
	protected function createWebhookListener(Wallee_Webhook_Entity $entity, $spaceId, \Wallee\Sdk\Model\WebhookUrl $webhookUrl){
		$webhookListener = new \Wallee\Sdk\Model\WebhookListenerCreate();
		$webhookListener->setEntity($entity->getId());
		$webhookListener->setEntityStates($entity->getStates());
		$webhookListener->setName('Prestashop ' . $entity->getName());
		$webhookListener->setState(\Wallee\Sdk\Model\CreationEntityState::ACTIVE);
		$webhookListener->setUrl($webhookUrl->getId());
		$webhookListener->setNotifyEveryChange($entity->isNotifyEveryChange());
		return $this->getWebhookListenerService()->create($spaceId, $webhookListener);
	}

	/**
	 * Returns the existing webhook listeners.
	 *
	 * @param int $spaceId
	 * @param \Wallee\Sdk\Model\WebhookUrl $webhookUrl
	 * @return \Wallee\Sdk\Model\WebhookListener[]
	 */
	protected function getWebhookListeners($spaceId, \Wallee\Sdk\Model\WebhookUrl $webhookUrl){
		$query = new \Wallee\Sdk\Model\EntityQuery();
		$filter = new \Wallee\Sdk\Model\EntityQueryFilter();
		$filter->setType(\Wallee\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('state', \Wallee\Sdk\Model\CreationEntityState::ACTIVE),
					$this->createEntityFilter('url.id', $webhookUrl->getId()) 
				));
		$query->setFilter($filter);
		return $this->getWebhookListenerService()->search($spaceId, $query);
	}

	/**
	 * Creates a webhook url.
	 *
	 * @param int $spaceId
	 * @return \Wallee\Sdk\Model\WebhookUrlCreate
	 */
	protected function createWebhookUrl($spaceId){
		$webhookUrl = new \Wallee\Sdk\Model\WebhookUrlCreate();
		$webhookUrl->setUrl($this->getUrl());
		$webhookUrl->setState(\Wallee\Sdk\Model\CreationEntityState::ACTIVE);
		$webhookUrl->setName('Prestashop');
		return $this->getWebhookUrlService()->create($spaceId, $webhookUrl);
	}

	/**
	 * Returns the existing webhook url if there is one.
	 *
	 * @param int $spaceId
	 * @return \Wallee\Sdk\Model\WebhookUrl
	 */
	protected function getWebhookUrl($spaceId){
		$query = new \Wallee\Sdk\Model\EntityQuery();
		$filter = new \Wallee\Sdk\Model\EntityQueryFilter();
		$filter->setType(\Wallee\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
		    array(
		        $this->createEntityFilter('state', \Wallee\Sdk\Model\CreationEntityState::ACTIVE),
		        $this->createEntityFilter('url', $this->getUrl())
		    ));
		$query->setFilter($filter);
		$query->setNumberOfEntities(1);
		$result = $this->getWebhookUrlService()->search($spaceId, $query);
		if (!empty($result)) {
			return $result[0];
		}
		else {
			return null;
		}
	}

	/**
	 * Returns the webhook endpoint URL.
	 *
	 * @return string
	 */
	protected function getUrl(){
	    $link = Context::getContext()->link;
	    return $link->getModuleLink('wallee', 'webhook', array(), true, 1, 1);
	}

	/**
	 * Returns the webhook listener API service.
	 *
	 * @return \Wallee\Sdk\Service\WebhookListenerService
	 */
	protected function getWebhookListenerService(){
		if ($this->webhookListenerService == null) {
		    $this->webhookListenerService = new \Wallee\Sdk\Service\WebhookListenerService(Wallee_Helper::getApiClient());
		}
		return $this->webhookListenerService;
	}

	/**
	 * Returns the webhook url API service.
	 *
	 * @return \Wallee\Sdk\Service\WebhookUrlService
	 */
	protected function getWebhookUrlService(){
		if ($this->webhookUrlService == null) {
		    $this->webhookUrlService = new \Wallee\Sdk\Service\WebhookUrlService(Wallee_Helper::getApiClient());
		}
		return $this->webhookUrlService;
	}
}