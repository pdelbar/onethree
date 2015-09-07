# Basic concepts

This chapter explains the concepts of @oc using a simple example and as little code as possible. We'll build a company directory. 

## Schemes, attributes and models

Let's first describe the information used to represent a person. In @oc, we call this a **scheme**.

### The **employee** scheme

Schemes are one of the most important pieces of 'meta' knowledge in @oc. They centralize a lot of what we know about a data type, starting with a name that describes the type. Use whatever you want, and stay as close to the real world example as you can. For our example, we'll call the first scheme **employee**. 

Sounds simple? It can be, but you may get into semantic discussions at times. For instance, should we model any person, or are we talking *only* about employees? Should we call them *employees* of *team members* (implying there's a concept called *team* as well)? 

Bottom line is, pick a semantic scope and document it in the scheme definition as well. You could write it up like this:

	Scheme: employee
		Description:
			An employee represents any employee of our company, listed in the directory.
		
There. Your first scheme definition is done. Let's add some bits of information we want  to use.

### Attributes

We need a way to identify an employee. At last one of their characteristics  will be a *unique identifier*, in our case the employee number or **id**. They also have a first and last name, but those are not unique. 

Schemes use **attributes** to represents individual characteristics shared by every instance of the scheme. Writing this up, we would end up with this:

	Scheme: employee
		Description:
			An employee represents any employee of our company, listed in the directory.
		Attributes:
			int id(identity)
			string firstName
			string lastName
			
This is the template which we could fill out for each instance of this scheme.  

### Models

When we talk about a real employee like Brian Seaman, we describe a **model**, an instance of the scheme **employee**. The models have values for each of the attributes. For instance, Brian's employee number is 2. 

If at this point you're already experiencing a mild headache, I recommend Wordpress. Or even better, joomla.com. 

## Stores and connections

### Stores

In most cases, we need a data store  to persist models. In @oc, this is called the **store**. It can be a database table -- it very often is -- but to @oc, it doesn't really matter *how* the store persists models, as long as it does. 

When you're working in Joomla, the Joomla database will be a store you will use often. But you may use another MySQL database, or an Oracle one, or a webservice. An Excel file. The details of *how* a store works are defined by the *type*  of store, and @oc comes with a few of them (and you can add others). 

### Connections

Getting models out of and into the store may require some detailed parameters. For instance, which database table a person instance lives in. The **connection** describes where in the store the data lives. In our description, we would add:

	Scheme: employee
		...
		Store: joomla
			Connection: uses the #__employee table

## View concepts

@oc is primarily designed to turn data into *visible representations*. Any translation of one or more models is done by a **view**.

### What's a view?

Our employee directory will most likely include a list of all employees. It will look something like this:

<div class="well">
<li>2. Brian Seaman</li>
<li>3. Pjotr Eastman</li>
<li>5. Sandra Kim</li>
</div>

Another view would represent a single employee in a card-style box:

<div style="border: 1px solid #cccccc; border-radius: 2px; box-shadow: #999999 4px 4px 4px; padding: 0.5em 1.5em; margin-bottom: 1.5em;">
  <i class="fa fa-user fa-5x pull-left"></i>
  <div class="pull-left">
  <h4>SEAMAN, Brian</h4>
  <p><i>Employee #2</i></p>
  </div>
  <br style="clear: both;">
</div>


Representing employees in the same visual way across our site makes the UX experience more logical for our visitors. @oc promotes this type of 'semantic view' by allowing (or forcing) you to build scheme-specific views, and use them as OO-style **building blocks**. For instance, we can also build a list of employees by repeating the card view, instead of the initial approach. If our `list` view is composed of repeated `card` views, and we change how a card is represented, our entire UX changes and remains consistent. 

### The standard views

While there is no such thing as a standard representation that works for all schemes, @oc recognizes a few view names as **special**. These are

| View name | When used |
| ------------- | ------------- |
| list  | the standard view used to list multiple models  |
| list_item  | the standard view representing each of the models in the list |
| detail | the standard view to display a single model | 

You're not forced to implement these views for your scheme, but it's a handy convention which other @oc integrators will understand. It's particularly handy because they go hand in hand (pun intended) with the standard **actions**.

## Actions and controllers

Every MVC triplet has its controller element, the part that 'does' something. As @oc is generally used to display models, the most logical actions are called **list** and **detail**. 

### The `detail` action

The `detail` action instantiates a single model of a scheme and displays a view (by default, the `detail` view). This means you'll need to give this action a value to identify the model with, and that matches its identity attribute.

If you want to provide a different view to the detail action, you can. In our example, we'll create a `card` view to show a single employee as shown above. 

### The `list` action

The `list` action instantiates a list of models of a scheme ad displays a view (y default, the `list` view). The view will typically iterate over the models provided, but @oc does not limit the format of the view. It could be an HTML UL/LI set, or a series of DIVs in a container DIV, but it could also be a Google map, an Excel or CSV file ... your site will define what the right view is. And as is the case with the detail action, you can pick a different view to use.

Of course, we don't always want to show **all** instances of a scheme. To define our selection, we use a **query**.
 
### Queries and filters

Queries are not limited to what you can do with a SQL query -- but as data is often stored in databases, a lot of ways to determine a selection of instances are borrowed from that type of query. You can select ordering by specifying attributes and a sort direction; you can page through a selection using a start and limit value. But the most flexible way to limit a list of instances is to use a **filter**.

A **filter** is a class that can be scheme-specific or generic, and which affects the query passed to it. One example would be a `published` filter, which would add a condition to the query that the *published* field is equal to 1. Another more complex one would select the ones which are published OR authored by the current user. Filters can affect anything a query supports, including ordering and paging.  

It can be useful to make your filters are simple as possible, affecting the query in only one way. 
 
 // filter on team leaders and on a name field
 
Filters accept a parameters array in their constructor.

## Relations

### Defining relations

### Navigating relations

### Linking views

## More controller stuff

### Behaviors

### Routings

