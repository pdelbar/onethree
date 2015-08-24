<?php

/**
 * The introduction of a One_Factory as a means to create instances of a particular scheme
 * makes it possible to extend the selection of models beyond select using query and select a single
 * instance. In many cases, it is desirable to encapsulate specific selections in a class and avoid a
 * situation where a controller needs to manipulate a query to select recurring definitions. For instance,
 * for a scheme 'invoice' it may be useful to select open invoices, paid invoices, ... without having
 * to specify the details of how the selection works outside the model factory.
 *
 * The One_Repository currently resolves the select functionality. This should be deferred internally
 * to the appropriate One_Factory (subclassed for a particular scheme). The use of One_Repository
 * for creating selections should be deprecated, leading to a meta-level only use of One_Repository.
 *
 * ONEDISCLAIMER
 **/
class One_Factory {
  /**
   * @var One_Scheme One_Scheme concerning the factory
   */
  protected $scheme = NULL;

  /**
   * Class constructor
   *
   * @param One_Scheme $scheme
   */
  public function __construct(One_Scheme $scheme) {
    $this->scheme = $scheme;
  }


  /**
   * Select a single instance.
   *
   * @param string $schemeName
   * @param mixed $identityValue
   * @return One_Model
   */
  public function selectOne($identityValue) {
    $connection = $this->scheme->getConnection();
    $store = $connection->getStore();
    return $store->selectOne($this->scheme, $identityValue);
  }

  /**
   * Get a One_Query instance
   *
   * @param string $schemeName
   * @param mixed $identityValue
   * @return One_Query
   */
  public function selectQuery() {
    $connection = $this->scheme->getConnection();
    $store = $connection->getStore();
    $query = $store->getQuery($this->scheme);

    $behaviors = $this->scheme->get('behaviors');
    if ($behaviors) foreach ($behaviors as $behavior) {
      $behavior->onSelect($query);
    }

    return $query;

  }

  /**
   * Return the number of One_Models of the specified kind
   *
   * @param string $schemeName
   * @return int
   *
   * //TODO is this useful at all ? total count ?
   */
  public function selectCount() {
    $scheme = $this->scheme;
    $query = One_Repository::selectQuery($scheme);

    $behaviors = $scheme->get('behaviors');
    if ($behaviors) {
      foreach ($behaviors as $behavior) {
        $behavior->onSelect($query);
      }
    }

    return $query->getCount();
  }

  /**
   * Return an empty instance of this model
   *
   * @return One_Model
   */
  public function getInstance() {
    return One::make($this->scheme->getName());
  }

  /**
   * Returns the One_Scheme used in this factory
   *
   * @return One_Scheme
   */
  public function getScheme() {
    return $this->scheme;
  }
}