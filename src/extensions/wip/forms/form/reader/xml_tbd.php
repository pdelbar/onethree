<?php

/**
 * The One_Form_Reader_Xml class loads One_Form definitions and instantiates them
 *
 * @TODO review this file and clean up historical code/comments
 * @TODO I am sure this code could be a LOT cleaner
 *
 * ONEDISCLAIMER
 **/
class One_Form_Reader_Xml implements One_Form_Reader_Interface {
  /**
   * @var DOMDocument
   */
  protected static $_dom;

  /**
   * @var string
   */
  protected static $_defaultWidget;

  /**
   * Loads a form definition
   *
   * @param One_Scheme $scheme
   * @param $formFile
   * @return One_Form_Container_Form
   */
  public static function load($filepath, $scheme, $language = NULL, $formName = 'oneForm', $action = '', $method = 'post') {
    // -----------------
    // TODO: this section of code is absolute horror
//    $templater = One_Repository::getTemplater(NULL, false);


//    $filepath = One::getInstance()->locate('meta'.DIRECTORY_SEPARATOR.'scheme'.DIRECTORY_SEPARATOR.$fileName.'.xml');

//    $oldSearchpaths = $templater->getSearchpath();
//    $templater->clearSearchpath();

//    $pattern = "%ROOT%/views/"
//      . "{" . ($scheme->getName() != '' ? "%APP%/{$scheme->getName()}," : "") . "%APP%,default}" . DIRECTORY_SEPARATOR
//      . "{%LANG%" . DIRECTORY_SEPARATOR . ",}";
//
//    $templater->addSearchPath($pattern);

//    $templater->setFile('form.xml');
//    if ($templater->hasError()) {
//      return self::createDefaultForm($scheme, $formFile, $language, $formName, $action, $method);
////      throw new One_Exception($templater->getError());
//    }

//    $templater->setSearchpath($oldSearchpaths);

    $script = new One_Script();
    $script->load($filepath);
    $parsedContent = $script->execute();
    echo "<pre>$parsedContent</pre>";
    echo '<hr>';

    if ($templater->hasError()) {
      throw new One_Exception($templater->getError());
    }
    // -----------------

    self::$_dom = new DOMDocument('1.0', 'utf-8');
    $validFile = self::$_dom->loadXML($parsedContent);

    if ($validFile !== false) {
      // load rules if any
      self::loadConditions();

      // load redirects if any
      $redirects = self::loadRedirects();

      // first element in the xml file should be a container of the type 'form'
      if (strtolower(self::$_dom->firstChild->localName) == 'form') {
        $formEle = self::$_dom->firstChild;
        $formName = trim($formEle->getAttribute('id')) != '' ? trim($formEle->getAttribute('id')) : $formName;
        $action = trim($formEle->getAttribute('action')) != '' ? trim($formEle->getAttribute('action')) : $action;

        $attributes = array();
        $rawAttributes = $formEle->attributes;
        for ($i = 0; $i < $rawAttributes->length; $i++) {
          $attribute = $rawAttributes->item($i);
          $attributes[$attribute->localName] = $attribute->value;
        }

        if (isset($attributes['type']) && trim(strtolower($attributes['type'])) == 'search') {
          self::$_defaultWidget = 'search';
        } else {
          self::$_defaultWidget = 'scalar';
        }

        if (count($redirects) > 0) {
          $attributes['redirects'] = $redirects;
        }

        $form = new One_Form_Container_Form($formName, $action, $method, $attributes);

        foreach ($formEle->childNodes as $child) {
          if ($child instanceof DOMElement) {
            self::_parseToForm($child, $form);
          }
        }
      } else {
        throw new One_Exception('Form definition "' . $fileName . '" found, but no form defined');
      }
    } else // if we haven't defined an xml file, generate a default form for the given scheme
    {
      return self::createDefaultForm($scheme, $formFile, $language, $formName, $action, $method);
    }


    self::addObligatedWidgets($form, $scheme);

//    $templater->clearSearchpath();
//    $templater->setSearchpath($oldSearchpaths);

    return $form;
  }



  /**
   * Parse the given DOMElement into containers and widgets and add them to the form
   *
   * @param DOMElement $element
   * @param One_Form_Container_Abstract $container
   */
  protected static function _parseToForm(DOMElement $element, One_Form_Container_Abstract $container) {
    $current = strtolower($element->localName);

    if ($current == 'constraints' || $current == 'constraint') {
      return NULL;
    }

    $widgetClass = self::identifyElement($current);
    if (is_null($widgetClass)) {
      return NULL;
    }

    if (is_object($widgetClass)) {
      $widgetClass = get_class($widgetClass);
    }

    if (preg_match('/^One_Form_Container/', $widgetClass) && $widgetClass != 'One_Form_Container_Form') {
      $attributes = array();
      $rawAttributes = $element->attributes;
      for ($i = 0; $i < $rawAttributes->length; $i++) {
        $attribute = $rawAttributes->item($i);
        $attributes[$attribute->localName] = $attribute->value;
      }

      if (false === isset($attributes['language'])) {
        $attributes['language'] = strtolower(One_Config::get('app.language'));
      }

      $subContainerId = (isset($attributes['id'])) ? $attributes['id'] : NULL;
      $subContainer = new $widgetClass($subContainerId, $attributes);

      // containers can contain other containers or widgets
      if ($element->hasChildNodes()) {
        foreach ($element->childNodes as $child) {
          if ($child instanceof DOMElement) {
            self::_parseToForm($child, $subContainer);
          }
        }
      }

      $container->addContainer($subContainer);
    } else {
      if ($element->hasAttribute('role')) {
        // Get cache in preparation of possible option-caching
        $session = One_Repository::getSession();

        $parts = explode(':', $element->getAttribute('role'));

        if (count($parts) != 2) {
          throw new One_Exception('You must define a valid role');
        }

        $relation = One_Repository::getRelation($parts[0]);
        $role = $relation->getRole($parts[1]);
        $relScheme = One_Repository::getScheme($role->schemeName);

        // set the name and id of the so it will be recognised as a related field
        $element->setAttribute('id', 'r__' . $element->getAttribute('role'));
        $element->setAttribute('name', 'r__' . $element->getAttribute('role'));

        if ($element->hasAttribute('optionsFrom')) // set more complex options by getting a json-string from a view
        {
          $sessionCacheName = md5($relScheme->getName() . '#' . $element->getAttribute('optionsFrom'));
          if (false === $element->hasAttribute('cacheOptions') || ($element->hasAttribute('cacheOptions') && false === $session->varExists($sessionCacheName, 'One_Form_Cache'))) {
            $relView = new One_View($relScheme->getName(), $element->getAttribute('optionsFrom'));
            $rawOptions = trim($relView->show());
            $options = json_decode($rawOptions, 1);
            if (false === is_array($options)) {
              $options = array();
            }

            if ($element->hasAttribute('cacheOptions')) {
              $session->set($sessionCacheName, $options, 'One_Form_Cache');
            }
          } else {
            $options = $session->get($sessionCacheName, 'One_Form_Cache');
          }
        } else {
          if (!$element->hasAttribute('targetAttribute')) {
            $targetAttr = $relScheme->getIdentityAttribute()->getName();
          } else {
            $targetAttr = $element->getAttribute('targetAttribute');
          }

          $sessionCacheName = md5($relScheme->getName() . '#' . $targetAttr . '#' . $element->getAttribute('publishedOnly'));
          if (false === $element->hasAttribute('cacheOptions') || ($element->hasAttribute('cacheOptions') && false === $session->varExists($sessionCacheName, 'One_Form_Cache'))) {
            $q = One_Repository::selectQuery($relScheme->getName());

            $tparts = explode(':', $targetAttr);
            foreach ($tparts as $tkey => $tval) {
              if (in_array(substr($tval, 0, 1), array('(', '['))) {
                $tparts[$tkey] = substr($tval, 1) . '+';
              } else {
                $tparts[$tkey] = $tval . '+';
              }
            }


            $q->setOrder($tparts);
            $results = $q->execute(false);

            $options = array();
            foreach ($results as $result) {
              $idAttr = $relScheme->getIdentityAttribute()->getName();
              $valAttr = $targetAttr;

              $val = '';
              foreach (explode(':', $targetAttr) as $tkey => $tval) {
                switch (substr($tval, 0, 1)) {
                  case '(':
                    $tval = substr($tval, 1);
                    $val .= '(' . $result->$tval . ') ';
                    break;
                  case '[':
                    $tval = substr($tval, 1);
                    $val .= '[' . $result->$tval . '] ';
                    break;
                  default:
                    $val .= $result->$tval . ' ';
                    break;
                }
              }

              $options[$result->$idAttr] = trim($val);
            }

            if ($element->hasAttribute('cacheOptions')) {
              $session->set($sessionCacheName, $options, 'One_Form_Cache');
            }
          } else {
            $options = $session->get($sessionCacheName, 'One_Form_Cache');
          }
        }

        $widget = self::determineWidget($container, $element, $widgetClass);

        if (method_exists($widget, 'setOptionsOnDetermination') && $widget->setOptionsOnDetermination()) {
          $widget->setOptions($options);
        }
      } else {
        $widget = self::determineWidget($container, $element, $widgetClass);
      }
    }
  }

  /**
   * Determine what type of widget the DOMElement is and load it's conditions
   *
   * @param One_Form_Container_Abstract $container
   * @param DOMElement $element
   * @param string $widgetClass
   * @return One_Form_Widget_Abstract
   */
  protected static function determineWidget($container, $element, $widgetClass) {
    $id = $element->hasAttribute('id') ? $element->getAttribute('id') : $element->getAttribute('attribute');
    $name = $element->hasAttribute('name') ? $element->getAttribute('name') : $element->getAttribute('attribute');
    $label = $element->hasAttribute('label') ? $element->getAttribute('label') : $element->getAttribute('attribute');

    $attributes = array();
    $rawAttributes = $element->attributes;
    for ($i = 0; $i < $rawAttributes->length; $i++) {
      $attribute = $rawAttributes->item($i);
      $attributes[$attribute->localName] = $attribute->value;
    }

    if (false === isset($attributes['language'])) {
      $attributes['language'] = strtolower(One_Config::get('app.language'));
    }

    $widget = new $widgetClass($id, $name, $label, $attributes);

    if ($element->hasAttribute('optionsFrom') && method_exists($widget, 'setOptions')) // set more complex options by getting a json-string from a view
    {
      $parts = explode(':', $element->getAttribute('optionsFrom'));
      if (2 == count($parts)) {
        $session = One_Repository::getSession();
        $sessionCacheName = md5($id . '#' . $name . '#' . $element->getAttribute('optionsFrom'));
        if (false === $element->hasAttribute('cacheOptions') || ($element->hasAttribute('cacheOptions') && false === $session->varExists($sessionCacheName, 'One_Form_Cache'))) {
          $optView = new One_View($parts[0], $parts[1]);
          try {
            $rawOptions = trim($optView->show());
            $options = json_decode($rawOptions, 1);
            if (false === is_array($options)) {
              $options = array();
            }

            $widget->setOptions($options);

            if ($element->hasAttribute('cacheOptions')) {
              $session->set($sessionCacheName, $options, 'One_Form_Cache');
            }
          } catch (Exception $e) {
            //					echo $e->getMessage();
            //					exit;
          }
        } else {
          $options = $session->get($sessionCacheName, 'One_Form_Cache');
          $widget->setOptions($options);
        }
      }
    }

    self::loadConstraints($element, $widget);

    $container->addWidget($widget);
    return $widget;
  }

  /**
   * Load all the conditions in the form description
   *
   * @param DOMDocument $dom
   * @param string $schemeName
   */
  protected static function loadConditions() {
    One_Form_Factory::$_conditions = array();

    $xpath = new DOMXPath(self::$_dom);
    $conditions = $xpath->query('/form/conditions');

    if ($conditions->length > 0) {
      $conditions = $conditions->item(0);
    } else {
      return;
    }

    foreach ($conditions->childNodes as $condition) {
      if ($condition instanceof DOMElement) {
        switch (strtolower($condition->localName)) {
          case 'cinclude':
            self::parseInclude($condition->getAttribute('file'));
            break;
          case 'condition':
            self::parseCondition($condition, $condition->getAttribute('name'));
            break;
        }
      }
    }
  }

  /**
   * Parse the given condition and return it
   *
   * @param DOMElement $element
   * @param string $name
   * @return One_Permission_Rule
   */
  protected static function parseCondition($element, $name = NULL) {
    $p = NULL;
    $xpath = new DOMXPath(self::$_dom);
    foreach (explode(':', 'rule:and:or') as $roa) {
      $curr = $xpath->query('./' . $roa, $element);

      if ($curr->length > 0) {
        $curr = $curr->item(0);

        if ($roa == 'and' || $roa == 'or') {
          $p = new One_Permission_Rule_Joiner(array('type' => $roa, 'not' => (($curr->getAttribute('not') == 'not') ? true : false)));

          $rules = $curr->getElementsByTagName('rule');
          if ($rules->length > 0) {
            foreach ($rules->childNodes as $rule) {
              $a = array();
              foreach ($rule->attributes as $attrName => $attrNode) {
                $val = $attrNode->value;
                if ($attrName == 'not') $val = ($val == 'not');
                $a[$attrName] = $val;
              }
              $p->addRule(new One_Permission_Rule($rule));
            }

            unset($curr->rule);
          }

          $and = $curr->getElementsByTagName('and');
          $or = $curr->getElementsByTagName('or');
          if ($and->length > 0 || $or->length > 0) {
            $p->addRule(self::parseCondition($curr));
          }

        } else if ($roa == 'rule') {
          $a = array();
          foreach ($curr->attributes as $attrName => $attrNode) {
            $val = $attrNode->value;
            if ($attrName == 'not') $val = ($val == 'not');
            $a[$attrName] = $val;
          }
          $p = new One_Permission_Rule($a);
        }
      }
    }

    if (!is_null($name)) {
      One_Form_Factory::$_conditions[$name] = $p;
    }

    return $p;
  }

  /**
   * Parse an include file that contains conditions
   *
   * @param string $include filename of the include
   */
  protected static function parseInclude($include) {
    $filename = One_Config::getInstance()->getCustomPath() . '/conditions/' . strtolower($include) . '.xml';
    $incDom = new DOMDocument('1.0', 'utf-8');

    if (file_exists($filename) && $incDom->load($filename)) {
      $xpath = new DOMXPath($incDom);

      $conditions = $xpath->query('/conditions');

      if ($conditions->length > 0) {
        $conditions = $conditions->item(0);
      } else {
        return;
      }

      foreach ($conditions->childNodes as $condition) {
        if ($condition instanceof DOMElement) {
          switch (strtolower($condition->localName)) {
            case 'cinclude':
              self::parseInclude($condition->getAttribute('file'));
              break;
            case 'condition':
              self::parseCondition($condition, $condition->getAttribute('name'));
              break;
          }
        }
      }
    }
  }

  /**
   * Load any constraints present for the widget
   *
   * @param DOMElement $element
   * @param One_Form_Widget_Abstract $widget
   */
  protected static function loadConstraints(DOMElement $element, One_Form_Widget_Abstract &$widget) {
    $xpath = new DOMXPath(self::$_dom);
    $constraints = $xpath->query('./constraints/constraint', $element);

    if ($constraints->length > 0) {
      for ($i = 0; $i < $constraints->length; $i++) {
        $child = $constraints->item($i);

        $type = 0;
        $rawType = $child->getAttribute('type');

        $hasRegExp = (stristr($rawType, 'R') !== false) ? true : false;
        for ($j = 0; $j < strlen($rawType); $j++) {
          switch (strtoupper($rawType[$j])) {
            case 'N':
              $type += One_Form_Constraint::N;
              break;
            case 'E':
              if (!$hasRegExp) {
                $type += One_Form_Constraint::E;
              }
              break;
            case 'L':
              if (!$hasRegExp) {
                $type += One_Form_Constraint::L;
              }
              break;
            case 'G':
              if (!$hasRegExp) {
                $type += One_Form_Constraint::G;
              }
              break;
            case 'M':
              if (!$hasRegExp) {
                $type += One_Form_Constraint::M;
              }
              break;
            case 'C':
              if (!$hasRegExp) {
                $type += One_Form_Constraint::C;
              }
              break;
            case 'R':
              $type += One_Form_Constraint::R;
              break;
          }
        }

        $value = trim($child->nodeValue);
        $error = $child->getAttribute('error');
        $widget->addConstraint($type, $value, $error);
      }
    }
  }

  /**
   * Identifies whether the passed name is a widget or a container and pass it's classname
   *
   * @param string $name
   * @return string
   */
  protected static function identifyElement($name) {
    $availableCW = One_Form_Factory::getAvailable();
    $className = NULL;

    if (in_array($name, $availableCW['containers'])) {
      $className = 'One_Form_Container_' . ucfirst($name);
    } elseif (in_array($name, $availableCW['widgets'])) {
      $single = array('submit', 'label', 'button', 'nscript', 'inline');
      if (in_array($name, $single)) {
        $className = 'One_Form_Widget_' . ucfirst($name);
      } else {
        $parts = explode('-', $name);
        $className = 'One_Form_Widget_' . ucfirst($parts[0]) . '_' . ucfirst($parts[1]);
      }

      return new $className();

    } elseif (in_array(self::$_defaultWidget . '-' . $name, $availableCW['widgets']))
      $className = 'One_Form_Widget_' . ucfirst(self::$_defaultWidget) . '_' . ucfirst($name);

    return $className;
  }

  /**
   * Load all the redirections in the form description
   */
  protected static function loadRedirects() {
    $xpath = new DOMXPath(self::$_dom);
    $fRedirects = $xpath->query('/form/redirects/redirect');

    $redirects = array();
    for ($i = 0; $i < $fRedirects->length; $i++) {
      $redirect = $fRedirects->item($i);

      $for = $redirect->getAttribute('for');
      parse_str(trim($redirect->textContent), $parts);
      $redirects[$for] = $parts;
    }

    return $redirects;
  }
}
