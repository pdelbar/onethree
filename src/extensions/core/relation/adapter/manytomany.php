<?php

  /**
   * The manyToMany link type is used to link multiple objects to each
   * other. A secundary lookup table is used for this purpose.
   *
   * ONEDISCLAIMER
   **/
  class One_Relation_Adapter_Manytomany extends One_Relation_Adapter_Abstract
  {
    /**
     * Returns the name of this linktype
     *
     * @return string
     */
    public function getName()
    {
      return 'manytomany';
    }

    /**
     * Return all related items
     *
     * @return array
     */
    public function getRelated(One_Relation_Adapter $link, One_Model $model, array $options = array())
    {
      $linkName = $link->getName();

      // identify the target scheme
      $source = One_Repository::getScheme($model->getSchemeName());
      $target = One_Repository::getScheme($link->getTarget());

      $backlinks = $target->getLinks();
      $backlink  = $backlinks[$link->getLinkId()];
      if (!$backlink) {
        throw new One_Exception("There is no link with id " . $link->getLinkId() . " in scheme " . $link->getTarget());
      }

      $sourceId   = $source->getIdentityAttribute()->getName();
      $localValue = $model[$sourceId];
      $targetId   = $target->getIdentityAttribute()->getName();

      // TR20100615 if all stores are equal, do normal join, if not, fetch data in several steps
      // @TODO This should actually be handled in One_Query
      if ($source->getConnection()->getName() == $link->meta['connection'] && $link->meta['connection'] == $target->getConnection()->getName()) {
        $lQ = One_Repository::selectQuery($source);
        $lQ->setSelect(array($linkName . ':*'));
        $lQ->where($sourceId, 'eq', $localValue);

        if (array_key_exists('query', $options) && is_array($options['query']) && count($options['query']) > 0) {
          foreach ($options['query'] as $qKey => $qOption) {
            if (count($qOption) == 3) {
              $options['query'][$qKey][0] = $linkName . ':' . $options['query'][$qKey][0];
            }
          }
        }

        $lQ->setOptions($options);

        $related = $lQ->execute();
      }
      else {
        $fkLocal  = $link->meta['fk:local'];
        $fkRemote = $link->meta['fk:remote'];
        $omtms    = One_Repository::getScheme('onemanytomany');
        $omtms->setConnection(One_Repository::getConnection($link->meta['connection']));
        $omtms->setResources($link->meta);

        $localAtt  = new One_Scheme_Attribute($fkLocal, $source->getIdentityAttribute()->getType()->getName());
        $remoteAtt = new One_Scheme_Attribute($fkRemote, $target->getIdentityAttribute()->getType()->getName());

        $omtms->addAttribute($localAtt);
        $omtms->addAttribute($remoteAtt);

        $joinQ = One_Repository::selectQuery($omtms);
        $joinQ->setSelect(array($fkRemote));
        $joinQ->where($fkLocal, 'eq', $localValue);

        $rawRelatedIDs = $joinQ->execute(false);

        $related = array();
        if (count($rawRelatedIDs) > 0) {
          $relatedIDs = array();
          foreach ($rawRelatedIDs as $row) {
            $relatedIDs[] = $row->$fkRemote;
          }

          $lQ = One_Repository::selectQuery($target);
          $lQ->where($targetId, 'in', $relatedIDs);

          $lQ->setOptions($options);

          $related = $lQ->execute();
        }
      }

      if (count($related) > 0) {
        return $related;
      }
      else {
        return array();
      }
    }


    public function countRelated(One_Relation_Adapter $link, One_Model $model, array $options = array())
    {
      $nRelated = 0;

      $linkName = $link->getName();

      // identify the target scheme
      $source = One_Repository::getScheme($model->getSchemeName());
      $target = One_Repository::getScheme($link->getTarget());

      $backlinks = $target->getLinks();
      $backlink  = $backlinks[$link->getLinkId()];
      if (!$backlink) {
        throw new One_Exception("There is no link with id " . $link->getLinkId() . " in scheme " . $link->getTarget());
      }

      $sourceId   = $source->getIdentityAttribute()->getName();
      $localValue = $model[$sourceId];
      $targetId   = $target->getIdentityAttribute()->getName();

      // TR20100615 if all stores are equal, do normal join, if not, fetch data in several steps
      // @TODO This should actually be handled in One_Query
      if ($source->getConnection()->getName() == $link->meta['connection'] && $link->meta['connection'] == $target->getConnection()->getName()) {
        $lQ = One_Repository::selectQuery($source);
        $lQ->setSelect(array($linkName . ':*'));
        $lQ->where($sourceId, 'eq', $localValue);

        if (array_key_exists('query', $options) && is_array($options['query']) && count($options['query']) > 0) {
          foreach ($options['query'] as $qKey => $qOption) {
            if (count($qOption) == 3) {
              $options['query'][$qKey][0] = $linkName . ':' . $options['query'][$qKey][0];
            }
          }
        }

        $lQ->setOptions($options);

        $nRelated = $lQ->getCount();
      }
      else {
        $fkLocal  = $link->meta['fk:local'];
        $fkRemote = $link->meta['fk:remote'];
        $omtms    = One_Repository::getScheme('onemanytomany');
        $omtms->setConnection(One_Repository::getConnection($link->meta['connection']));
        $omtms->setResources($link->meta);

        $localAtt  = new One_Scheme_Attribute($fkLocal, $source->getIdentityAttribute()->getType()->getName());
        $remoteAtt = new One_Scheme_Attribute($fkRemote, $target->getIdentityAttribute()->getType()->getName());

        $omtms->addAttribute($localAtt);
        $omtms->addAttribute($remoteAtt);

        $joinQ = One_Repository::selectQuery($omtms);
        $joinQ->setSelect(array($fkRemote));
        $joinQ->where($fkLocal, 'eq', $localValue);

        $rawRelatedIDs = $joinQ->execute(false);

        $related = array();
        if (count($rawRelatedIDs) > 0) {
          $relatedIDs = array();
          foreach ($rawRelatedIDs as $row) {
            $relatedIDs[] = $row->$fkRemote;
          }

          $lQ = One_Repository::selectQuery($target);
          $lQ->where($targetId, 'in', $relatedIDs);

          $lQ->setOptions($options);

          $nRelated = $lQ->getCount();
        }
      }

      return $nRelated;
    }

    /**
     * Return the string version of the value
     *
     * @return string
     */
    public function toString($value)
    {
      return $value;
    }
  }
