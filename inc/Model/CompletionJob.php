<?php
if (! defined('_PS_VERSION_')) {
    exit();
}

class Wallee_Model_CompletionJob extends ObjectModel
{

    const STATE_CREATED = 'created';
    
    const STATE_ITEMS_UPDATED = 'item';

    const STATE_SENT = 'sent';

    const STATE_SUCCESS = 'success';
    
    const STATE_FAILURE = 'failure';

    public $id_completion_job;

    public $completion_id;

    public $state;

    public $space_id;

    public $transaction_id;

    public $order_id;

    public $failure_reason;

    public $date_add;

    public $date_upd;

    /**
     *
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'wle_completion_job',
        'primary' => 'id_completion_job',
        'fields' => array(
            'completion_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isAnything',
            ),
            'state' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'size' => 255
            ),
            'space_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isAnything',
                'required' => true
            ),
            'transaction_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isAnything',
                'required' => true
            ),
            'order_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'failure_reason' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            )
        )
    );

    public function getId()
    {
        return $this->id;
    }

    public function getCompletionId()
    {
        return $this->completion_id;
    }

    public function setCompletionId($id)
    {
        $this->completion_id = $id;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getSpaceId()
    {
        return $this->space_id;
    }

    public function setSpaceId($id)
    {
        $this->space_id = $id;
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public function setTransactionId($id)
    {
        $this->transaction_id = $id;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setOrderId($id)
    {
        $this->order_id = $id;
    }

    public function getFailureReason()
    {
        return unserialize($this->failure_reason);
    }

    public function setFailureReason($failureReason)
    {
        $this->failure_reason = serialize($failureReason);
    }

    /**
     *
     * @param int $spaceId
     * @param int $completionId
     * @return Wallee_Model_CompletionJob
     */
    public static function loadByCompletionId($spaceId, $completionId)
    {
        $completionJobs = new PrestaShopCollection('Wallee_Model_CompletionJob');
        $completionJobs->where('space_id', '=', $spaceId);
        $completionJobs->where('completion_id', '=', $completionId);
        $result = $completionJobs->getFirst();
        if ($result === false) {
            $result = new Wallee_Model_CompletionJob();
        }
        return $result;
    }
    
    /**
     *
     * @param int $spaceId
     * @param int $transactionId
     * @return Wallee_Model_CompletionJob[]
     */
    public static function loadByTransactionId($spaceId, $transactionId)
    {
        $completionJobs = new PrestaShopCollection('Wallee_Model_CompletionJob');
        $completionJobs->where('space_id', '=', $spaceId);
        $completionJobs->where('transaction_id', '=', $transactionId);
        $result = $completionJobs->getResults();
        if(!$result){
            return array();
        }
        return $result;
    }
    

    public static function isCompletionRunningForTransaction($spaceId, $transactionId)
    {
        $result = DB::getInstance()->getValue(
            'SELECT id_completion_job FROM ' . _DB_PREFIX_ . 'wle_completion_job WHERE space_id = "' .
                 pSQL($spaceId) . '" AND transaction_id="' . pSQL($transactionId) .
                 '" AND state != "' . pSQL(self::STATE_SUCCESS) . '" AND state != "' . pSQL(self::STATE_FAILURE) . '"', false);
        
        if ($result !== false) {
            return true;
        }
        return false;
    }

    public static function loadRunningCompletionForTransaction($spaceId, $transactionId)
    {
        $completionJobs = new PrestaShopCollection('Wallee_Model_CompletionJob');
        $completionJobs->where('space_id', '=', $spaceId);
        $completionJobs->where('transaction_id', '=', $transactionId);
        $completionJobs->where('state', '!=', self::STATE_SUCCESS);
        $completionJobs->where('state', '!=', self::STATE_FAILURE);
        $result = $completionJobs->getFirst();
        if ($result === false) {
            $result = new Wallee_Model_CompletionJob();
        }
        return $result;
    }
    public static function loadNotSentJobIds()
    {
        $time = new DateTime();
        $time->sub(new DateInterval('PT10M'));
        $result = DB::getInstance()->query(
            'SELECT id_completion_job FROM ' . _DB_PREFIX_ . 'wle_completion_job WHERE (state = "' .
            pSQL(self::STATE_CREATED) . '" OR state = "'.pSQL(self::STATE_ITEMS_UPDATED).'") AND date_upd < "' .
            pSQL($time->format('Y-m-d H:i:s')) . '"', false);
        $ids = array();
        while ($row = DB::getInstance()->nextRow($result)) {
            $ids[] = $row['id_completion_job'];
        }
        return $ids;
    }
}