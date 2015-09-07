# Forms

```xml
<?xml version="1.0" encoding="UTF-8"?>
<form class="oneform form-horizontal">
    <fieldset>

        <select-dropdown role="faqs_faqcategory:faqcategory" targetAttribute="name" label="Category"/>

        <textfield attribute="question" label="Vraag" required="required" class="input-xlarge"/>
        <joomla-html attribute="answer" label="Antwoord" width="600" height="200"/>

        <checkbox attribute="published" label="Published"/>

    </fieldset>
    <defaultactions />
</form>
```

