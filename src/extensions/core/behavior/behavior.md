# Behaviors

Behaviors make it possible to create reusable collections of, well, how things behave. It means you can wrap behavior 
which is similar in different schemes in a single class, and blend it into multiple schemes.

## Creating a behavior

An example will help to explain the concept. Let's say we have a **contact** scheme representing a person. Each time 
the contact is updated, we would like to update a timestamp field as well as remember which user made the change. In
terms of code, we would need to affect how the *update* function works. 
 
This is how a **tracechanges** behavior could be defined:

    class One_Behavior_Tracechanges extends One_Behavior
    {
    	public function getName()
    	{
    		return 'tracechanges';
    	}
    
    	public function onBeforeUpdateModel( One_Scheme $scheme, One_Model $model )
    	{
            $model->updatedOn = date('Y-m-d H:i:s');
            $model->updatedBy = JFactory::getUser()->id;
    	}
    }

and we can add the behavior to the scheme by adding this in the scheme definition:

    <?xml version="1.0" encoding="UTF-8"?>
    <scheme name="contact">
        ...    
    	<behaviors>
            <behavior name="tracechanges" />
    	</behaviors>
    	...
    </scheme>

## Behavior options

Useful as this behavior already is, if we want to reuse it, the fact that the attribute name is fixed is too 
restrictive. Fortunately, every scheme can insert options into the behavior:

    <?xml version="1.0" encoding="UTF-8"?>
    <scheme name="contact">
        ...    
    	<behaviors>
            <behavior name="tracechanges" updatedOn="date_updated" updatedBy="user_updated" />
    	</behaviors>
    	...
    </scheme>

which can be retrieved by the behavior as follows:

    class One_Behavior_Tracechanges extends One_Behavior
    {
    	public function getName()
    	{
    		return 'tracechanges';
    	}
    
    	public function onBeforeUpdateModel( One_Scheme $scheme, One_Model $model )
    	{
    		$options = $scheme->get('behaviorOptions.tracechanges' );

        	$dateField  = $options['updatedOn'];
    		if (!$dateField) 
    			throw new One_Exception( 'The tracechanges behavior requires that you define a date field.' );
        	
        	$userField  = $bOptions['updatedBy'];
    		if (!$userField) 
    			throw new One_Exception( 'The tracechanges behavior requires that you define a user field.' );
        
            $model->$dateField = date('Y-m-d H:i:s');
            $model->$userField = JFactory::getUser()->id;
    	}
    }

Got the idea? Could you add something similar for creation date/user ?

## The power of behaviors

### Insert, update, delete

Many interesting behaviors involve things which need to be done before or after CRUD-methods. Here's a list of 
methods behaviors can implement:

* onBeforeInsertModel 
* onAfterInsertModel  
* onBeforeUpdateModel 
* onAfterUpdateModel  
* onBeforeDeleteModel 
* onAfterDeleteModel  

If you want total control over cascading deletes, and the database does not allow you to handle it using triggers, these
babies are going to be your best friends. Take a look at this (partial) example:

    class One_Behavior_Deleterestrict extends One_Behavior
    {
    	public function getName()
    	{
    		return 'deleterestrict';
    	}
    
    	public function onBeforeDeleteModel( One_Scheme $scheme, One_Model $model )
    	{
    	    // determine whether this model has dependent objects
    	    $hasChildren = ...
    	    
    		if ($hasChildren) {
    		  $idField = $scheme->getIdentityAttribute();
    		  throw new One_Exception('Not sure you want me to delete ' . $scheme->get('name') . ':' . $model->$dField);
              }
    	}
    }

You will need to wrap the delete instruction in a try/catch block for this to work:

    try {
      $model->delete();
      }
    catch (One_Exception $e) {
      ...
    }

Oh yes, and even define your own One_Exception subclass to hold more details on the model you cannot delete.

### Special events 

But that's not all. We have a few special methods that come in very handy:

| method | when called |
| -- | -- |
| onAfterLoadModel | after a model has been instantiated, making it possible to calculate specific fields |
| onCreateModel | before a model is created, making it possible to affect the class used |
| onLoadScheme | after the scheme has been instantiated from its definition file |


**Note:** it is also possible to override the model class used for a scheme, if more substantial or complex changes
need to be made. However, in most cases, behaviors are more flexible as they are reusable. Functionally, they are the
same.



## Plans for the future

* ways to restrict delete etc. as used in ripples (could be exception throwing)