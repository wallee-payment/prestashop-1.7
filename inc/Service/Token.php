<?php
if (! defined('_PS_VERSION_')) {
    exit();
}
/**
 * This service provides functions to deal with wallee tokens.
 */
class Wallee_Service_Token extends Wallee_Service_Abstract {
	
	/**
	 * The token API service.
	 *
	 * @var \Wallee\Sdk\Service\TokenService
	 */
	private $tokenService;
	
	/**
	 * The token version API service.
	 *
	 * @var \Wallee\Sdk\Service\TokenVersionService
	 */
	private $tokenVersionService;

	public function updateTokenVersion($spaceId, $tokenVersionId){
		$tokenVersion = $this->getTokenVersionService()->read($spaceId, $tokenVersionId);
		$this->updateInfo($spaceId, $tokenVersion);
	}

	public function updateToken($spaceId, $tokenId){
		$query = new \Wallee\Sdk\Model\EntityQuery();
		$filter = new \Wallee\Sdk\Model\EntityQueryFilter();
		$filter->setType(\Wallee\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('token.id', $tokenId),
					$this->createEntityFilter('state', \Wallee\Sdk\Model\CreationEntityState::ACTIVE) 
				));
		$query->setFilter($filter);
		$query->setNumberOfEntities(1);
		$tokenVersions = $this->getTokenVersionService()->search($spaceId, $query);
		if (!empty($tokenVersions)) {
			$this->updateInfo($spaceId, current($tokenVersions));
		}
		else {
			$info = Wallee_Model_TokenInfo::loadByToken($spaceId, $tokenId);
			if ($info->getId()) {
				$info->delete();
			}
		}
	}

	protected function updateInfo($spaceId, \Wallee\Sdk\Model\TokenVersion $tokenVersion){
		
		$info = Wallee_Model_TokenInfo::loadByToken($spaceId, $tokenVersion->getToken()->getId());
		if (!in_array($tokenVersion->getToken()->getState(), 
				array(
					\Wallee\Sdk\Model\CreationEntityState::ACTIVE,
					\Wallee\Sdk\Model\CreationEntityState::INACTIVE 
				))) {
			if ($info->getId()) {
				$info->delete();
			}
			return;
		}
		
		$info->setCustomerId($tokenVersion->getToken()->getCustomerId());
		$info->setName($tokenVersion->getName());
		
		$info->setPaymentMethodId($tokenVersion->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getId());
		$info->setConnectorId($tokenVersion->getPaymentConnectorConfiguration()->getConnector());
		
		$info->setSpaceId($spaceId);
		$info->setState($tokenVersion->getToken()->getState());
		$info->setTokenId($tokenVersion->getToken()->getId());
		$info->save();
	}

	public function deleteToken($spaceId, $tokenId){
		$this->getTokenService()->delete($spaceId, $tokenId);
	}

	/**
	 * Returns the token API service.
	 *
	 * @return \Wallee\Sdk\Service\TokenService
	 */
	protected function getTokenService(){
		if ($this->tokenService == null) {
			$this->tokenService = new \Wallee\Sdk\Service\TokenService(Wallee_Helper::getApiClient());
		}
		
		return $this->tokenService;
	}

	/**
	 * Returns the token version API service.
	 *
	 * @return \Wallee\Sdk\Service\TokenVersionService
	 */
	protected function getTokenVersionService(){
		if ($this->tokenVersionService == null) {
			$this->tokenVersionService = new \Wallee\Sdk\Service\TokenVersionService(Wallee_Helper::getApiClient());
		}
		
		return $this->tokenVersionService;
	}
}