# XML definition for relation definition

A relation definition explains how it is possible to go from one scheme to another and back. For example:

    <?xml version="1.0" encoding="UTF-8"?>
    <relation name="articlecreator">
    	<roles>
    		<role name="articles" scheme="jarticle" cardinality="many" fk="created_by" />
    		<role name="creator" scheme="juser" cardinality="one" />
    	</roles>
    </relation>

