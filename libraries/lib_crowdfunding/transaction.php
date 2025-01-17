<?php
/**
 * @package      CrowdFunding
 * @subpackage   Transactions
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage transactions.
 *
 * @package      CrowdFunding
 * @subpackage   Transactions
 */
class CrowdFundingTransaction
{
    protected $id;
    protected $txn_date;
    protected $txn_amount;
    protected $txn_currency;
    protected $txn_status;
    protected $txn_id;
    protected $parent_txn_id;
    protected $extra_data;
    protected $status_reason;
    protected $project_id;
    protected $reward_id;
    protected $investor_id;
    protected $receiver_id;
    protected $service_provider;
    protected $reward_state;

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * <code>
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver  $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set the database object.
     *
     * <code>
     * $transaction    = new CrowdFundingTransaction();
     * $transaction->setDb(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(JDatabaseDriver $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Load transaction data from database.
     *
     * <code>
     * $txnId = 1;
     *
     * $transaction    = new CrowdFundingTransaction();
     * $transaction->setDb(JFactory::getDbo());
     * $transaction->load($txnId);
     * </code>
     *
     * @param int|array $keys Transaction ID or keys used to find a record.
     */
    public function load($keys)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.txn_date, a.txn_amount, a.txn_currency, a.txn_status, a.txn_id, a.parent_txn_id, " .
                "a.extra_data, a.status_reason, a.project_id, a.reward_id, a.investor_id, a.receiver_id, " .
                "a.service_provider, a.reward_state"
            )
            ->from($this->db->quoteName("#__crowdf_transactions", "a"));

        if (!is_array($keys)) {
            $query->where("a.id = " . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) . "=" . $this->db->quote($value));
            }
        }

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = array(
     *  "txn_amount" => "10.00",
     *  "txn_currency" => "GBP"
     * );
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored
     */
    public function bind($data, $ignored = array())
    {
        // Encode extra data to JSON format.
        foreach ($data as $key => $value) {

            if (!in_array($key, $ignored)) {

                // If it is extra data ( array or object ), encode the data to JSON string.
                if ((strcmp("extra_data", $key) == 0) and (is_array($value) or is_object($value))) {
                    $this->$key = json_encode($value);
                } else {
                    $this->$key = $value;
                }
            }

        }

    }

    /**
     * Store data to database.
     *
     * <code>
     * $data = array(
     *  "txn_amount" => "10.00",
     *  "txn_currency" => "GBP"
     * );
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->bind($data);
     * $transaction->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function updateObject()
    {
        // Prepare extra data value.
        $extraData = (!$this->extra_data) ? "NULL" : $this->db->quote($this->extra_data);

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__crowdf_transactions"))
            ->set($this->db->quoteName("txn_date") . "=" . $this->db->quote($this->txn_date))
            ->set($this->db->quoteName("txn_amount") . "=" . $this->db->quote($this->txn_amount))
            ->set($this->db->quoteName("txn_currency") . "=" . $this->db->quote($this->txn_currency))
            ->set($this->db->quoteName("txn_status") . "=" . $this->db->quote($this->txn_status))
            ->set($this->db->quoteName("txn_id") . "=" . $this->db->quote($this->txn_id))
            ->set($this->db->quoteName("parent_txn_id") . "=" . $this->db->quote($this->parent_txn_id))
            ->set($this->db->quoteName("extra_data") . "=" . $extraData)
            ->set($this->db->quoteName("status_reason") . "=" . $this->db->quote($this->status_reason))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") . "=" . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("investor_id") . "=" . $this->db->quote($this->investor_id))
            ->set($this->db->quoteName("receiver_id") . "=" . $this->db->quote($this->receiver_id))
            ->set($this->db->quoteName("service_provider") . "=" . $this->db->quote($this->service_provider))
            ->set($this->db->quoteName("reward_state") . "=" . $this->db->quote($this->reward_state))
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        // Prepare extra data value.
        $extraData = (!$this->extra_data) ? "NULL" : $this->db->quote($this->extra_data);
        $txnDate   = (!$this->txn_date) ? "NULL" : $this->db->quote($this->txn_date);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName("#__crowdf_transactions"))
            ->set($this->db->quoteName("txn_date") . "=" . $txnDate)
            ->set($this->db->quoteName("txn_amount") . "=" . $this->db->quote($this->txn_amount))
            ->set($this->db->quoteName("txn_currency") . "=" . $this->db->quote($this->txn_currency))
            ->set($this->db->quoteName("txn_status") . "=" . $this->db->quote($this->txn_status))
            ->set($this->db->quoteName("txn_id") . "=" . $this->db->quote($this->txn_id))
            ->set($this->db->quoteName("parent_txn_id") . "=" . $this->db->quote($this->parent_txn_id))
            ->set($this->db->quoteName("extra_data") . "=" . $extraData)
            ->set($this->db->quoteName("status_reason") . "=" . $this->db->quote($this->status_reason))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") . "=" . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("investor_id") . "=" . $this->db->quote($this->investor_id))
            ->set($this->db->quoteName("receiver_id") . "=" . $this->db->quote($this->receiver_id))
            ->set($this->db->quoteName("service_provider") . "=" . $this->db->quote($this->service_provider))
            ->set($this->db->quoteName("reward_state") . "=" . $this->db->quote($this->reward_state));

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Return transaction ID.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * if (!$transaction->getId()) {
     * ....
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Check if transaction is completed.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * if (!$transaction->isCompleted()) {
     * ....
     * }
     * </code>
     *
     * @return bool
     */
    public function isCompleted()
    {
        $result = (strcmp("completed", $this->txn_status) == 0);

        return (bool)$result;
    }

    /**
     * Check if transaction is pending.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * if (!$transaction->isPending()) {
     * ....
     * }
     * </code>
     *
     * @return bool
     */
    public function isPending()
    {
        $result = (strcmp("pending", $this->txn_status) == 0);

        return (bool)$result;
    }

    /**
     * Return transaction status.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $status = $transaction->getStatus();
     * </code>
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->txn_status;
    }

    /**
     * Return transaction amount.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $amount = $transaction->getAmount();
     * </code>
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->txn_amount;
    }

    /**
     * Return currency code of transaction.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $string = $transaction->getCurrency();
     * </code>
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->txn_currency;
    }

    /**
     * Return transaction ID that comes from payment gataway.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $txnId = $transaction->getTransactionId();
     * </code>
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->txn_id;
    }

    /**
     * Return ID of user who send an amount.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $investorId = $transaction->getInvestorId();
     * </code>
     *
     * @return int
     */
    public function getInvestorId()
    {
        return $this->investor_id;
    }

    /**
     * Return ID of user who receive the amount.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $receiverId = $transaction->getReceiverId();
     * </code>
     *
     * @return int
     */
    public function getReceiverId()
    {
        return $this->receiver_id;
    }

    /**
     * Return extra data about transaction that comes from payment gateway.
     *
     * <code>
     * $transactionId  = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $extraData = $transaction->getExtraData();
     * </code>
     *
     * @return array
     */
    public function getExtraData()
    {
        $extraData = array();

        if (is_string($this->extra_data)) {
            $extraData = json_decode($this->extra_data, true);
        }

        if (!$extraData or !is_array($extraData)) {
            $extraData = array();
        }

        return $extraData;
    }

    /**
     * Returns an associative array of object properties.
     *
     * <code>
     * $transactionId = 1;
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($transactionId);
     *
     * $properties = $transaction->getProperties();
     * </code>
     *
     * @return  array
     */
    public function getProperties()
    {
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if (strcmp("db", $key) == 0) {
                unset($vars[$key]);
            }
        }

        return $vars;
    }

    /**
     * Update reward state to SENT or NOT SENT.
     *
     * <code>
     * $keys = array(
     *  "id" = 1,
     *  "receiver_id" => 2
     * );
     *
     * $transaction    = new CrowdFundingTransaction(JFactory::getDbo());
     * $transaction->load($keys);
     *
     * // 0 = NOT SENT, 1 = SENT
     * $transaction->updateRewardState(CrowdFundingConstants::SENT);
     * </code>
     *
     * @param integer $state
     */
    public function updateRewardState($state)
    {
        $state = (!$state) ? 0 : 1;

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__crowdf_transactions"))
            ->set($this->db->quoteName("reward_state") . " = " . (int)$state)
            ->where($this->db->quoteName("id") . " = " . (int)$this->id)
            ->where($this->db->quoteName("receiver_id") . " = " . (int)$this->receiver_id);

        $this->db->setQuery($query);
        $this->db->execute();
    }
}
