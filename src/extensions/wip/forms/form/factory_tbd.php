<?php

/**
 * One_Form creates a form and loads all conditions and constraints
 *
 * @TODO review this file and clean up historical code/comments
 * ONEDISCLAIMER
 **/
class One_Form_Factory {
  /**
   * @var array Available containers and widgets
   */
  public static $availableCW = NULL;

  /**
   * @var array
   */
  public static $_conditions;

  /**
   * Create a new OneFormContainerForm
   *
   * @param One_Scheme $scheme
   * @param string $formName
   * @param string $action
   * @param string $formFile location of the file, admin/form.xml by default
   * @return OneFormContainerForm
   */
  public static function createForm(One_Scheme $scheme, $formFile = NULL, $language = NULL, $formName = 'oneForm', $action = '', $method = 'post') {

    if (is_null($language)) {
      $language = One_Config::get('app.language');
    }

    // search for appropriate form.xml
    $pattern = "%ROOT%/views/"
      . "{" . ($scheme->getName() != '' ? "%APP%/{$scheme->getName()}," : "") . "%APP%,default}" . DIRECTORY_SEPARATOR
      . "{%LANG%" . DIRECTORY_SEPARATOR . ",}";
    $filepath = One_Locator::locateUsing(formFile.'.xml',$pattern);

    try {
      if ($filepath !== null) {
        $form = One_Form_Reader_Xml::load($filepath, $scheme, $language, $formName, $action, $method);
      }
      else {
        $form = self::createDefaultForm($scheme, $formFile, $language, $formName, $action, $method);
      }
    } catch (Exception $e) {
        $form = self::createDefaultForm($scheme, $formFile, $language, $formName, $action, $method);
    }

    self::addObligatedWidgets($form, $scheme);

    return $form;
  }

  private static function createDefaultForm($scheme, $formFile = 'form', $language = NULL, $formName = 'oneForm', $action = '', $method = 'post') {
    $formName = $formName ? $formName : $scheme->getName();
    $form = new One_Form_Container_Form($formName, $action, 'post');
    $form->setCfg('widgetsInContainer', 'One_Form_Container_Div');

    foreach ($scheme->get('attributes') as $attribute) {
      $type = $attribute->getType();
      $widgetClass = $type->defaultWidgetClass();
      $widget = new $widgetClass($attribute->getName(), $attribute->getName(), $attribute->getName());
//      switch ($attribute->getType()) {
//        case 'date':
//          $widget = new One_Form_Widget_Scalar_Date($attribute->getName(), $attribute->getName(), $attribute->getName());
//          break;
//        case 'boolean':
//          $widget = new One_Form_Widget_Scalar_Checkbox($attribute->getName(), $attribute->getName(), $attribute->getName());
//          break;
//        case 'text':
//          $widget = new One_Form_Widget_Scalar_Textarea($attribute->getName(), $attribute->getName(), $attribute->getName());
//          break;
//        case 'string':
//        default:
//          $widget = new One_Form_Widget_Scalar_Textfield($attribute->getName(), $attribute->getName(), $attribute->getName());
//          break;
//      }

      if ($widget) {
        $form->addWidget($widget);
      }
    }

    // add standard action buttons

    $form->addWidget(new One_Form_Widget_Defaultactions('action', 'action', 'action' ));

    return $form;
  }



  private static function addObligatedWidgets(One_Form_Container_Form $form, One_Scheme $scheme) {
    // the form should always have a (hidden) widget with the value of the identityAttribute unless there is no identityAttribute
    if (!is_null($scheme->getIdentityAttribute()) && !$form->hasWidget($scheme->getIdentityAttribute()->getName())) {
      $form->addWidget(new One_Form_Widget_Scalar_Hidden($scheme->getIdentityAttribute()->getName(), $scheme->getIdentityAttribute()->getName(), NULL, array('one' => 'one', 'language' => strtolower(One_Config::get('app.language')))));
    }
    if (!$form->hasWidget('task')) {
      $form->addWidget(new One_Form_Widget_Scalar_Hidden('task', 'task', NULL, array('default' => 'edit', 'one' => 'one', 'language' => strtolower(One_Config::get('app.language')))));
    }
    if (!$form->hasWidget('scheme')) {
      $form->addWidget(new One_Form_Widget_Scalar_Hidden('scheme', 'scheme', NULL, array('default' => $scheme->getName(), 'one' => 'one', 'language' => strtolower(One_Config::get('app.language')))));
    }

  }

  /**
   * Get all available containers and widgets
   *
   * @return array
   */
  public static function getAvailable() {
    if (is_null(self::$availableCW)) {
      $places = One_Locator::locateAllUsing('*.php', '%ROOT%/form/container/');
      $ignore = array('abstract', 'index', 'factory');
      foreach ($places as $container) {
        if (preg_match('|([^/]*).php$|', $container, $match)) {
          if (in_array($match[1], $ignore) || strpos($match[1], '.') !== false) {
            unset($containers[$cKey]);
          } else {
            $containers[$cKey] = strtolower($match[1]);
          }
        } else {
          unset($containers[$cKey]);
        }
      }
      sort($containers);

      $places = One_Locator::locateAllUsing('*.php', '%ROOT%/form/widget/{joomla,multi,scalar,select,search}/');
      foreach ($places as $widget) {
        if (preg_match('|([^/]*)/([^/]*).php$|', $widget, $match)) {
          if (!in_array($match[2], $ignore)) {
            $widgets[] = $match[1] . '-' . strtolower($match[2]);
          }
        }
      }
      $widgets[] = 'button';
      $widgets[] = 'file';
      $widgets[] = 'submit';
      $widgets[] = 'label';
      $widgets[] = 'button';
      $widgets[] = 'nscript';
      $widgets[] = 'inline'; //TODO: adding a widget here should not be necessary !!

      sort($widgets);

      self::$availableCW = array(
        'containers' => $containers,
        'widgets'    => $widgets
      );
    }

    return self::$availableCW;
  }

  /**
   * Check whether the user is authorized by this condition
   *
   * @param string $condition
   * @param array $args
   * @return boolean
   */
  public static function authorize($condition, $args) {
    if (array_key_exists((string)$condition, (self::$_conditions))) {
      $cond = self::$_conditions[(string)$condition];
      $status = $cond->authorize($args);
      return $status;
    } else
      throw new One_Exception('The rule "' . (string)$condition . '" does not exist');
  }

}
