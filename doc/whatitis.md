# What is @oc?

## Is it a CCK?

**Content construction kits** (CCKs) make it easy to build your own type of content. Some work better for articles-with-benefits, some for articles with a standard structure or template, others are very flexible and allow you to construct whatever you can imagine. 

Considering @oc allows you to represent rich content, it qualifies as a CCK. If you add relations, the ability to define type-specific views in an object-oriented fashion and things like behaviours, it goes beyond. @oc also represents data that is not located in the Joomla database, or in any database for that matter. We have used @oc to handle SOAP, REST, XML and flat file data stores. Most CCKs don't.

What @oc lacks is the easy point-and-click interface many CCKs have to define fields and field types. It's still an integrator toolkit, and will probably never have a sexy UI like I've seen on other CCKs. That said, most people who use @oc tend to forgive that weakness rapidly.
 
 ## Is it a framework?
 
Frameworks are usually MVC-oriented, containing elaborate architectures on model, view and controller aspects. ORM frameworks would come closest to @oc because of their declarative approach to data modeling. But even though you will find lightweight and heavy ones, they are all about the code-level capabilities, so you'll be writing code at some point, or scaffolding it. 
 
@oc does not expect you to write code. In fact, most of the work we do with @oc has hardly any code in it -- I'm making an exception for pure view logic, which is not the kind of code we're talking about here. There's no scaffolding either. So in that sense, it's not a framework.

But underneath the 'standard' way to use @oc, there's a set of classes which you could call a framework. You *can* create a controller for a data type, adding specific functionality. You *can* create a custom data model. Concepts like *filters* and *behaviours*, even though they typically contain less than a dozen lines of code, *are* code-level extensions. In short, if you *want* to use @oc's class structure, you can, and for complex applications, chances are that you will need to. But it's not a general purpose framework like Symfony for instance.

## So what is it?

@oc was created to make an integrator's life simple. It started as a component capable of accessing data based on a meta-description and displaying structured views of it, with the ability to access related content easily. Gradually, the framework side of it was extended to allow us to handle things we needed a site to do, like filtering a selection of data. 

It was built by integrators, for integrators. It's a tool, a solution to a problem. It has no ambition to be more than that. But if you are looking for just that, you'll be happy you gave it a chance.
 
## A true story

At one of the first @oc bootcamps, I asked participants to describe the last projects they had been working on and list the semantic entities they were using. One of the described his attempts to build his company's new project portfolio. As he was explaining what he wanted the site to do, I wrote the data structure on the flipchart, looking something like this:

```
   CUSTOMER  ----1:many---- PROJECT ----many:many---- TECHNOLOGY
                               |
                           many:many
                               |
                            PARTNER
```

He admitted that he had been working on this for a week or two, but had not quite finished it all. Remember, CCKs had not been invented at that time. 

I sat down at the computer and started describing data types and relations. I created a few tables, added a few lines of data, created a menu item and coded a first @oc view. We had the list of projects up in under 15 minutes. When the hour was over, we had a working minisite, requiring nothing more than a designer's hand to make it look pretty (Bootstrap did not exist either). 
  
That was the very fist time I realized we had built something really nice. Good enough to make a grown man swear, bordering on crying. 