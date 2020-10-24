# Minify

[![Latest Stable Version](https://poser.pugx.org/PerryvanderMeer/minify/v/stable.svg)](https://packagist.org/packages/PerryvanderMeer/minify)
[![Total Downloads](https://poser.pugx.org/PerryvanderMeer/minify/downloads.svg)](https://packagist.org/packages/PerryvanderMeer/minify)
[![License](https://poser.pugx.org/PerryvanderMeer/minify/license.svg)](https://packagist.org/packages/PerryvanderMeer/minify)

With this package you can minify your existing stylessheet and javascript files within your Laravel environment.
This process can be a little tough, this package simplifies this process and automates it.

For older Laravel versions, please use [ceesvanegmond/minify](https://github.com/ceesvanegmond/minify) or [DevFactoryCH/minify](https://github.com/DevFactoryCH/minify).

## Installation

Begin by installing this package through Composer.


```js
{
    "require": {
    	"PerryvanderMeer/minify": "1.0.*"
	}
}
```

Or use ```composer require perryvandermeer/minify```


### Laravel installation

Then register the service provider and Facade by opening `config/app.php`

    'PerryvanderMeer\Minify\MinifyServiceProvider',

    'Minify'        => 'PerryvanderMeer\Minify\Facades\Minify',


Publish the config file:

```
	php artisan vendor:publish

```

When you've added the ```MinifyServiceProvider``` an extra ```Minify``` facade is available.
You can use this Facade anywhere in your application

#### Stylesheet

```php
// app/views/hello.blade.php

<html>
	<head>
		{!! Minify::stylesheet('/css/main.css') !!}
		// or by passing multiple files
		{!! Minify::stylesheet(array('/css/main.css', '/css/bootstrap.css')) !!}
		// add custom attributes
		{!! Minify::stylesheet(array('/css/main.css', '/css/bootstrap.css'), array('foo' => 'bar')) !!}
		// add full uri of the resource
		{!! Minify::stylesheet(array('/css/main.css', '/css/bootstrap.css'))->withFullUrl() !!}
		{!! Minify::stylesheet(array('//fonts.googleapis.com/css?family=Roboto')) !!}

		// minify and combine all stylesheet files in given folder
		{!! Minify::stylesheetDir('/css/') !!}
		// add custom attributes to minify and combine all stylesheet files in given folder
		{!! Minify::stylesheetDir('/css/', array('foo' => 'bar', 'defer' => true)) !!}
		// minify and combine all stylesheet files in given folder with full uri
		{!! Minify::stylesheetDir('/css/')->withFullUrl() !!}
	</head>
</html>
```

#### Javascript

```php
// app/views/hello.blade.php

<html>
	<body>
		<!-- -->
	</body>

	{!! Minify::javascript('/js/jquery.js') !!}
	// or by passing multiple files
	{!! Minify::javascript(array('/js/jquery.js', '/js/jquery-ui.js')) !!}
	// add custom attributes
	{!! Minify::javascript(array('/js/jquery.js', '/js/jquery-ui.js'), array('bar' => 'baz')) !!}
	// add full uri of the resource
	{!! Minify::javascript(array('/js/jquery.js', '/js/jquery-ui.js'))->withFullUrl() !!}
    {!! Minify::javascript(array('//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js')) !!}

	// minify and combine all javascript files in given folder
	{!! Minify::javascriptDir('/js/') !!}
	// add custom attributes to minify and combine all javascript files in given folder
	{!! Minify::javascriptDir('/js/', array('bar' => 'baz', 'async' => true)) !!}
	// minify and combine all javascript files in given folder with full uri
	{!! Minify::javascriptDir('/js/')->withFullUrl() !!}
</html>
```
