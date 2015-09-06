# Introduction to @oc

Welcome to &oc. This document discusses a flexible rich content framework, whose primary use is to make it easy to use structured and linked data in Joomla, avoiding having to squeeze it into articles or writing code. 

But I'm giving away too much already. Let me tell you about why we created @oc.

## How it all began

The idea for @oc was born when the notion of a CCK for Joomla was nonexistent. We were building a large corporate site, using Joomla 1.5 [^joomlarc]. Trying to keep the structure of content as close as possible to what our customer described, we considered standard articles inadequate to represent more structured datatypes such as offices, training locations, departments and the like. So, we decided to build custom components. 

[^joomlarc]: We were charmed by the 1.5 MVC architecture, but Joomla 1.5 itself was not quite ready, so we developed on release candidate 2. We firmly believed the GA release would be ready in time for the golive. We went live on RC4. Can't really recommend it. Oh, and we were running on IIS, not Apache.

So we created a component for offices, services, teams, projects, topics ... basically turning very end-user concept into a special data type. Our customer just loved it: they understood instinctively what each administration screen was for, because they recognized the fields and structure of an event or a location -- after all, they had told us what it was. 

The downside is that it took us on average 2-3 mandays to write the frontend and backend logic for a single datatype, even using copy/paste. We ended up creating 18 of these, so you can do the math. In addition, small changes to the data model or the view required code changes resulting in small but annoying errors in the site. There did not seem to be a point at which a small addition required less work than earlier ones did -- it actually got worse and worse.

The really painful thing through was that a lot of the concepts our customer introduced to us were **linked** to each other. *Events* happen in *locations*, like *products* belong to *categories* and are created by *vendors*, or *invoices* contain *invoice lines* and are linked to *customers*. On both sides of such a relation, we needed to code the link and handle filtering, dependencies ... leading to more complex code. 

And then we were asked to use live data from an AS400 database, coming in through a SOAP webservice. That meant taking the (then novel) concept of a Joomla component controller accessing a data table to a whole new level. We made it, but it was not pretty, it was not fast and we certainly did not plan to ver do it again.

## Lessons learnt

It's experiences like these that make you stronger when you survive them. We vowed to never ever find ourselves in a position to have to create all of this very similar code again, and find a solution to dramatically reduce the effort to create something to manage and display linked rich content.

Reflecting on what the project had taught us, we came away with two simple mantras, which together form the tagline for @oc.

## Everything is content

If you are an integrator or web builder, you'll understand this. Your customers don't see the world as articles in categories. They see questions, reports, testimonials, product descriptions, contacts. Whatever is in the real world is what they will describe to you, and will form the semantic building blocks of their communication to their visitors. Basically, *everything is content*.

IT communities still debate the famous communication gap between developer and end user, describing how hard it is to transform a user's description of a possible solution to a problem into code structures. That observation led to higher-level programming languages, OOP and better analysis and design methodologies. Web builders have the same problem.
 
@oc needed to be very descriptive, staying as close to the real-world concepts as possible. While the syntax to describe data structures and relations is still formal, I think we did a good job on this front. Delay your judgement until you see some examples. 

## Content is everything

Every CMS uses some form of navigation to allow visitors to reach parts of the content. There's a whole spectrum of options to go from one place to another, from in-content links to the traditional hierarchical menu structure. But tags, breadcrumbs, related article lists and the like are also ways to 'glue' together pieces of content.

But when you build a site using semantically rich concepts, the 'normal' navigation follows the **relations** between content types. From a **product** page, you expect a link to the **creator**, and there you hope to see a list of all its products. Or you want to lookup other products in the same **category**. In these 'rich content clouds', visitors follow the semantic relations between things, leaving your poor menu underutilized. Content is not only the destination, but also the way you travel to it, hence the phrase '*content is everything*'.
 
 If there is one thing @oc excels at, it is in managing this kind of relations. We make it look ridiculously simple. 
