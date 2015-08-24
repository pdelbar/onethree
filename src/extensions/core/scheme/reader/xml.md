# XML structure for scheme definition

    <?xml version="1.0" encoding="UTF-8"?>
    <scheme name="...">
     
        <info>
            <title>...</title>
            <description>...</description>
            <image>...</image>
            <group>...</group>
            <grouporder>...</grouporder>
        </info>
        
        <attributes>
            <attribute name="..." type="..." ... />
            ...
        </attributes>
        
        <relations>
            <relation name="..." />
            ...
        </relations>
        
        <behaviors>
            <behavior name="..." ... />
        </behaviors>
        
        <tasks>
            <task name="...">
                <conditions>
                    <condition>
                        <or>
                            <rule type="..." />
                            ...
                        </or>
                    </condition>
                </conditions>
            </task>
            ...
        </tasks>
        
        <routings>
            <routing alias="..." useId=""/>
            ...
        </routings>
        
        <connection name="..." ... />
    </scheme>

## Attribute definitions

    <attribute
        name="..."
        column="..."        // equivalent name used by the store
        type"..."           // should be one of teh available types
        key"..."            // true,1,yes if this is the identity attribute
        readonly"..."       // keeps the update from writing this value if set to true,1,yes
        autoinc"..."        // does the store handle incrementing this attribute itself ? true,1,yes or false,0,no
    />

## Examples

    <?xml version="1.0" encoding="UTF-8"?>
    <scheme name="author">
        <info>
            <title>Author</title>
        </info>
    
        <attributes>
            <attribute name="id" type="int" identity="true" />
            <attribute name="name" type="string" />
            <attribute name="bio" type="text" />
            <attribute name="created" type="datetime" />
            <attribute name="updated" type="datetime" />
        </attributes>
    
        <relations>
        </relations>
    
        <tasks>
            <task name="edit;remove;update">
                <conditions>
                    <condition>
                        <or>
                            <rule type="jbackend" />
                            <rule type="frontadmin" />
                        </or>
                    </condition>
                </conditions>
            </task>
        </tasks>
    
        <behaviors>
            <behavior name="restable" route="authors" />
        </behaviors>
    
        <connection name="joomla" table="#__authors" />
    </scheme>

