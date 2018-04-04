<?php
if (! defined('_PS_VERSION_')) {
    exit();
}
/**
 * Provider of label descriptor group information from the gateway.
 */
class Wallee_Provider_LabelDescriptionGroup extends Wallee_Provider_Abstract {

	protected function __construct(){
		parent::__construct('wallee_label_description_group');
	}

	/**
	 * Returns the label descriptor group by the given code.
	 *
	 * @param int $id
	 * @return \Wallee\Sdk\Model\LabelDescriptorGroup
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of label descriptor groups.
	 *
	 * @return \Wallee\Sdk\Model\LabelDescriptorGroup[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
	    $labelDescriptorGroupService = new \Wallee\Sdk\Service\LabelDescriptionGroupService(Wallee_Helper::getApiClient());
		return $labelDescriptorGroupService->all();
	}

	protected function getId($entry){
		/* @var \Wallee\Sdk\Model\LabelDescriptorGroup $entry */
		return $entry->getId();
	}
}