# XML structure for connection definition

The connection provides the required parameters to access the data store for a particular scheme. In the scheme 
definition, some parameters can be passed, but the connection itself contains the basic information. 

# Examples

This is a connection to a local database table:

    <connection name="localdata" type="mysqli">
        <db host="localhost" user="app_user" password="(6'Ig9" database="db_local_whatever" />
    </connection>
    
Some connections require no parameters as they are all implicit :

    <?xml version="1.0" encoding="UTF-8"?>
    <connection name="joomla" type="joomla2" />
    
Connections also exist for non-database stores:

    <?xml version="1.0" encoding="UTF-8"?>
    <connection name="myapi" type="rest">
        <service entrypoint="http://my.domain.com/api"/>
    </connection>
    
