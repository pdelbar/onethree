<?php

  /**
   * The One_Relation_Reader_Xml class loads One_Relation definitions from an Xml and instantiates them
   * as a factory pattern.
   *
   * ONEDISCLAIMER
   **/
  class One_Relation_Reader_Xml
  {
    /**
     * Loads a relation by it's name
     *
     * @param string $relationName
     * @return One_Relation
     * @static
     */
    public static function load($relationName)
    {
      $relation = new One_Relation($relationName);
      $roles    = array();

      if (preg_match('/^subscheme%([^%]+)%(.+)/', $relationName, $relMatch) > 0) // create a subscheme-relation
      {
        $rS                  = new One_Relation_Role($relMatch[1], $relMatch[1], array('cardinality' => 'one'));
        $rSu                 = new One_Relation_Role($relMatch[2], $relMatch[1] . '%' . $relMatch[2], array('cardinality', 'subscheme'));
        $roles[$relMatch[1]] = $rS;
        $roles[$relMatch[2]] = $rSu;
      }
      else // create relations using the relationfiles
      {
        $relationpath = One_Locator::locate('meta' . DIRECTORY_SEPARATOR . 'relation' . DIRECTORY_SEPARATOR . $relationName . '.xml');
        if ($relationpath === null) {
          throw new One_Exception('Could not load the relation "' . $relationName . '"');
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        if ($dom->load($relationpath)) {
          $relAttributes = array();
          $relationSpec  = $dom->getElementsByTagName('relation');
          if ($relationSpec->length > 0) {
            $relEle     = $relationSpec->item(0);
            $attributes = $relEle->attributes;

            for ($i = 0; $i < $attributes->length; $i++) {
              $attr                       = $attributes->item($i);
              $relAttributes[$attr->name] = $attr->value;
            }
          }

          $relation->setMeta($relAttributes);

          $xpath = new DOMXPath($dom);
          // create the attribute set for this scheme
          $roleSpecs = $xpath->query('/relation/roles/role');
          for ($i = 0; $i < $roleSpecs->length; $i++) {
            unset($r);
            $roleOptions = array();
            $roleSpec    = $roleSpecs->item($i);
            $attributes  = $roleSpec->attributes;
            for ($j = 0; $j < $attributes->length; $j++) {
              $attr = $attributes->item($j);
              switch ($attr->name) {
                case 'name':
                  $name = $attr->value;
                  break;
                case 'scheme':
                  $scheme = $attr->value;
                  break;
                default:
                  $roleOptions[$attr->name] = $attr->value;
                  break;
              }
            }

            $r               = new One_Relation_Role($name, $scheme, $roleOptions);
            $roles[$r->name] = $r;
          }
        }
      }

      $relation->setRoles($roles);

      return $relation;
    }
  }
