# Development plan

Initially, this component will be quite simple and allow basic parameter setting only. We can develop it into
a more elaborate component with dropdowns populated as a function of the actual scheme selected etc.

## Routes

Other option is to introduce the concept of view or data route equivalents instead of scheme/task/view triplets. The
route concept is closer to the encapsulation of functionality inside OC.

For instance, it would be possible to link a menu item to the invoices/open route. Question is how we would handle 
something like user/ID/invoices/open as a construct ...

It is likely that the routes option will be handled in a later version of the component, and we will focus for now 
on the basics.

## Versions

### Dawn

Basically, a component that works, full stop.

* simple views close to what we had in prior versions 
* add a simple one entry point, taking all its parameters from the URL, for flexibility

### High Noon

A component following better coding standards.

* ability to define task/view sets in a way depending on the 'published' actions per scheme, which would be defined
in the scheme definition (akin to the permissions set)
* use of routes as a way to specify what is needed
* add a script option (use separate controller)
* make it possible to add controllers etc. stored outside the component
