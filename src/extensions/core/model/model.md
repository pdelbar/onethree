# One_Model

A One_Model represents an instance of a particular scheme. It's the basic data structure in one|content. 

## Attributes

## Relationships

## Using models

One_Model implements ArrayAccess to make it possible to use it as an object or an array, whichever tickles your fancy. This means that these expressions are identical:

    $m->name = 'John';
    $m['name'] = 'John';
    