<?php
if (! defined('_PS_VERSION_')) {
    exit();
}


/**
 * Wallee_Service_Abstract Class.
 */
abstract class Wallee_Service_Abstract {
	private static $instances = array();

	/**
	 * 
	 * @return static
	 */
	public static function instance(){
		$class = get_called_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();
		}
		$object = self::$instances[$class];
		
		return $object;
	}


	/**
	 * Returns the fraction digits for the given currency.
	 *
	 * @param string $currencyCode
	 * @return number
	 */
	protected function getCurrencyFractionDigits($currencyCode){
		return Wallee_Helper::getCurrencyFractionDigits($currencyCode);
	}

	/**
	 * Rounds the given amount to the currency's format.
	 *
	 * @param float $amount
	 * @param string $currencyCode
	 * @return number
	 */
	protected function roundAmount($amount, $currencyCode){
	    return Wallee_Helper::roundAmount($amount, $currencyCode);
	}
	
	/**
	 * Returns the resource part of the resolved url
	 *
	 * @param String $resolved_url
	 * @return string
	 */
	protected function getResourcePath($resolvedUrl) {
	    if(empty($resolvedUrl)){
	        return $resolvedUrl;
	    }
	    $index = strpos($resolvedUrl, 'resource/');
	    return substr($resolvedUrl, $index + strlen('resource/'));
	}
	

	/**
	 * Creates and returns a new entity filter.
	 *
	 * @param string $fieldName
	 * @param mixed $value
	 * @param string $operator
	 * @return \Wallee\Sdk\Model\EntityQueryFilter
	 */
	protected function createEntityFilter($fieldName, $value, $operator = \Wallee\Sdk\Model\CriteriaOperator::EQUALS){
		$filter = new \Wallee\Sdk\Model\EntityQueryFilter();
		$filter->setType(\Wallee\Sdk\Model\EntityQueryFilterType::LEAF);
		$filter->setOperator($operator);
		$filter->setFieldName($fieldName);
		$filter->setValue($value);
		return $filter;
	}

	/**
	 * Creates and returns a new entity order by.
	 *
	 * @param string $fieldName
	 * @param string $sortOrder
	 * @return \Wallee\Sdk\Model\EntityQueryOrderBy
	 */
	protected function createEntityOrderBy($fieldName, $sortOrder = \Wallee\Sdk\Model\EntityQueryOrderByType::DESC){
		$orderBy = new \Wallee\Sdk\Model\EntityQueryOrderBy();
		$orderBy->setFieldName($fieldName);
		$orderBy->setSorting($sortOrder);
		return $orderBy;
	}

	/**
	 * Changes the given string to have no more characters as specified.
	 *
	 * @param string $string
	 * @param int $maxLength
	 * @return string
	 */
	protected function fixLength($string, $maxLength){
	    return mb_substr($string, 0, $maxLength, 'UTF-8');
	}

	/**
	 * Removes all non printable ASCII chars
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function removeNonAscii($string){
		return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $string);
	}
}