# Building one|content

To build @oc, you will need to clone the [one|content for Joomla 3.x repository](https://github.com/pdelbar/onethree) at Github. We'll assume you need no assistance in making that happen.
   
The structure of that repository is 

    .
        build/  contains the phing build script and tooling
        src/    contains the source for the various parts of one|content
        doc/    contains something akin to documentation 
        tools/  tools for developers

## Running the phing build script

Currently, you will need phing to build the extensions needed to install one|content in Joomla 3.x. Run 

```bash
$ cd /gitrepo/onethree/build
$ phing
```

to get a list of targets you can build. 

