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

/**
 * Webhook processor to handle transaction completion state transitions.
 */
class Wallee_Webhook_TransactionCompletion extends Wallee_Webhook_OrderRelatedAbstract {

	/**
	 *
	 * @see Wallee_Webhook_OrderRelatedAbstract::loadEntity()
	 * @return \Wallee\Sdk\Model\TransactionCompletion
	 */
    protected function loadEntity(Wallee_Webhook_Request $request){
		$completionService = new \Wallee\Sdk\Service\TransactionCompletionService(Wallee_Helper::getApiClient());
		return $completionService->read($request->getSpaceId(), $request->getEntityId());
	}

	protected function getOrderId($completion){
		/* @var \Wallee\Sdk\Model\TransactionCompletion $completion */
		return $completion->getLineItemVersion()->getTransaction()->getMerchantReference();
	}

	protected function getTransactionId($completion){
		/* @var \Wallee\Sdk\Model\TransactionCompletion $completion */
		return $completion->getLinkedTransaction();
	}

	protected function processOrderRelatedInner(Order $order, $completion){
		/* @var \Wallee\Sdk\Model\TransactionCompletion $completion */
		switch ($completion->getState()) {
			case \Wallee\Sdk\Model\TransactionCompletionState::FAILED:
			    $this->update($completion, $order, false);
			    break;
			case \Wallee\Sdk\Model\TransactionCompletionState::SUCCESSFUL:
			    $this->update($completion, $order, true);
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	protected function update(\Wallee\Sdk\Model\TransactionCompletion $completion, Order $order, $success){
	    $completionJob = Wallee_Model_CompletionJob::loadByCompletionId($completion->getLinkedSpaceId(), $completion->getId());
	    if (!$completionJob->getId()) {
	        //We have no completion job with this id -> the server could not store the id of the completion after sending the request. (e.g. connection issue or crash)
	        //We only have on running completion which was not yet processed successfully and use it as it should be the one the webhook is for.
	        
	        $completionJob = Wallee_Model_CompletionJob::loadRunningCompletionForTransaction($completion->getLinkedSpaceId(),
	            $completion->getLinkedTransaction());
	        if (!$completionJob->getId()) {
	            return;
	        }
	        $completionJob->setCompletionId($completion->getId());
	    }
	    
	    if($success){
	       $completionJob->setState(Wallee_Model_CompletionJob::STATE_SUCCESS);
	    }
	    else{
	        if ($completion->getFailureReason() != null) {
	            $completionJob->setFailureReason($completion->getFailureReason()->getDescription());
	        }
	        $completionJob->setState(Wallee_Model_CompletionJob::STATE_FAILURE);
	    }
	    $completionJob->save();
	}

}