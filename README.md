# RequireJS for ExpressionEngine
====================

This extension loads [RequireJS](http://requirejs.org) early into the ExpressionEngine CP and makes it available via a PHP API to all addon types throughout the entire Control Panel.

RequireJS is an asynchronous JavaScript file and module loader.  Using RequireJS to load JavaScript assets in your addons can drastically improve the Control Panel performance since the assets are loaded in parallel as well as in a manner that does not block the main browser UI.

The result is a faster ExpressionEngine Control Panel that everyone can enjoy.


## Usage
==========

### PHP

RequireJS exposes a PHP API that is attached to the ExpressionEngine instance.  This means that any part of your addon can load needed JavaScript resources in a standardised asynchronous manner that will not slow down the CP load.

The API will collect all the script load calls for all addons system wide and then load them in parallel before firing your callbacks.  All URLs are relative to the EE "themes" folder.

```php
<?php
//API is attached to the EE global instance, so fetch it if necessary
$this->EE =& get_instance();

//Load a JS file, relative to themes folder
$this->EE->requirejs->add("third_party/mymodule/filename.js");

//Alternate shorthand syntax
Requirejs::load("third_party/mymodule/filename.js");

//Load multiple JS files in parallel, relative to themes folder
$this->EE->requirejs->add(array("third_party/mymodule/filename1.js", "third_party/mymodule/filename2.js"));

//Load a JS file and fire a callback when done. Best practice is to read a separate JS file and pass in as an argument
$this->EE->requirejs->add("third_party/mymodule/filename.js", "alert('Script Loaded')");

//If you have JS file that depends on another JS file and they are not AMD modules then use the shim feature
//In this example, filename1.js requires filename2.js and filename3.js to be loaded before being evaluated
$this->EE->requirejs->shim("filename1.js", array("filename2.js", "filename3.js"));
```

### JavaScript

The RequireJS library is loaded before all other JavaScript in the CP.  Hence if your addon has added any javascript to the current page load, then you can simply call the "require" function as per normal RequireJS usage. Read the [RequireJS Docs](http://requirejs.org/docs/api.html#jsfiles) if unfamiliar.

Please note that when loading AMD modules or using the "require.toUrl" method, all URLs are relative to the EE "themes" folder.

```javascript
//Load JS files and then run code when loaded
require(['/themes/third_party/mymodule/filename1.js', '/themes/third_party/mymodule/filename2.js'], function(){
	// Code here will run when scripts have loaded
});

//Load JS files, relative to the themes folder (default)
require([require.toUrl("third_party/mymodule/filename1.js"), require.toUrl("third_party/mymodule/filename2.js")], function(){
	// Code here will run when scripts have loaded
});

//Load JS AMD modules, relative to the themes folder (default).  Note the '.js' extension is not included
require(["third_party/mymodule/filename1", "third_party/mymodule/filename2"], function(filename1, filename2){
	// Code here will run when scripts have loaded
});
```

## Loader Plugins

RequireJS supports loader plugins that certain files other than javascript to be loaded.  RequireJS-for-EE includes the following plugins

### CSS

Loads CSS files and automatically inserts it into the page DOM as a link tag.  Simply prepend "css!" when specifying CSS resources:

Javascript:
```javascript
require(['css!themes/third_party/mymodule/css/styles.css'], function(){
	// Code here will run when CSS has loaded
});
```

PHP:
```php
<?php
$this->EE->requirejs->add("css!themes/third_party/mymodule/css/styles.css");
```

### Text

Loads text files (useful for loading HTML templates).  Simply prepend "text!" when specifying text resources:

Javascript:
```javascript
require(['text!themes/third_party/mymodule/templates/fragment.html'], function(){
	// Code here will run when CSS has loaded
});
```

PHP:
```php
<?php
$this->EE->requirejs->add("text!themes/third_party/mymodule/templates/fragment.html");
```

## License
==========

See LICENSE file.


## Change Log
=============

- *1.3* - Added compatibility with FocusLab's EE Master Config setup, which allows third_party folder to moved outside of the system folder.
- *1.2* - Public Release.