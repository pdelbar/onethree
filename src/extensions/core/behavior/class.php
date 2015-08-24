<?php

  /**
   * Behavior that lets you override the One_Model class for the scheme
   *
   * <behavior name="class" [ className="Not_One_Model" ] />
   *
   * ONEDISCLAIMER
   **/
  class One_Behavior_Class extends One_Behavior
  {
    /**
     * Return the name of the behaviour
     *
     * @return string
     */
    public function getName()
    {
      return 'class';
    }

    /**
     * Returns the class that overrides the default One_Model
     *
     * @param One_Scheme $scheme
     * @return One_Model
     */
    public function onCreateModel(One_Scheme $scheme)
    {
      $options = $scheme->get('behaviorOptions.class');

      $className = $options['className'];
      if (!$className) {
        $className = 'One_Model_' . ucFirst($scheme->getName());
      }

      return new $className($scheme);
    }
  }
