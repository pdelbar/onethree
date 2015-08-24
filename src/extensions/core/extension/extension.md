# Extensions

There's a lot you can do with one|content if you just use the core framework itself. But you will want to include 
some additional classes from ime to time, such as the Joomla-specific store, behaviors and script plugins. To help
in structuring the one library, these would be located in a separate **extension**.

## What is an extension?

It's basically a folder containing the 'regular' folder structure used by the class loader. For instance, to add a 
publishing behavior specific to Joomla, we would create `ONE_LIB_CUSTOM/joomla/behavior/scheme/jpublish.php`.

Note that extension folders are loaded **before** the standard library, so you *can* overload core library classes, 
even though it's generally a bad idea. However, if you want to understand how a standard class works, or you want to 
try and extend it, *just copy it into a custom extension* and play with it.

## Extension installation

When one|content bootstraps, it goes though a number of configuration steps. You can add your own initialization by 
creating a file called `extension.php`in the extension's folder. For example, the **talk** extension could contain 
this `talk/extension.php`file:

    <?php
    
      One_Config::set('extensions.talk', 'One_Extension_Talk');
    
      class One_Extension_Talk extends One_Extension
      {
        public static function onAfterInitialise($arguments = array())
        {
          parent::onAfterInitialise($arguments);
    
          // set your own parameters
          One_Config::set('talk.status','active');
    
          echo '<b>Talk mode activated.</b>';
        }
      }

The class name can be anything you want, as long as you register it in the configuration:

    One_Config::set('extensions.talk', 'One_Extension_Talk');

## Using extensions

### Consider creating an extension for every coherent and reusable set of classes and files

Let's imagine you regularly use Google maps in your work. You will probably reuse the same HTML (views) in different
projects, the same code to generate and/or parse map data, and perhaps your own standard address lookup functionality
(in a behavior) in all of these projects. Creating a **googlemap** extension allows you to simply copy all of these 
one|content elements into your next project in a clean fashion.

### Consider recurring custom schemes/views as extensions

A complex project will contain a lot of schemes. Feel free to structure your custom directory as a set of separate 
extensions, each focusing on a group of related items. For instance, use a **hr** extension for teams and employees,
a **catalog** extension for your training courses, trainers, locations and registrations, and a **payments** extension
for all things related to transactions for members, event participation and the like. 

## Plans for the future

* allow plugins to be installed as separate Joomla installers
* additional events for the extension manager classes
* extension weights to affect load order