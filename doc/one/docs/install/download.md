# Version

The current version is called **predawn** which kind of gives you a hint where we are with one|content. Below is a short summary of where each part is at in its development cycle.

**I'm warning you formally that there will be significant changes to class names, file structures etc. before we have the official first GA release.** 

## plg_one

System plugin needs better parameter config but basically does what it needs to do. 

## libraries aka. one|content extensions

Most of one|content's stuff is stored in `/libraries/one/lib`. The principal library is **core** and it contains the one|content framework itself. The **joomla** extension contains whatever is joomla-specific. The **script** extension contains the default templater, but I've already tested a PHP-ony templater as an alternative, but not thoroughly.  

The code for these is refactored from the previous J2.x version of one|content and heavily work in progress. I assume that most of the short-term changes are going to be located here. To give you an idea, any file named `whatever_tbd.php` has not been fully sanitized -- which does not by any standard mean that 'regular' files are completely sanitized.
  
You will also find other src/extension folders containing unmigrated material.

## demo

This is a fun extension I am working on, intended to provide a standard set of one|content material as an introduction. It will use mainly the standard Joomla database tables.  