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
```
PerryvanderMeer\Minify\MinifyServiceProvider::class,

'Minify' => PerryvanderMeer\Minify\Facades\Minify::class,
```

Publish the config file:

```
php artisan vendor:publish --tag=minify
```

When you've added the ```MinifyServiceProvider```, an extra ```Minify``` facade is available.
You can use this Facade anywhere in your application

#### Stylesheet

```php
// app/views/hello.blade.php

# Minify one file
{!! Minify::stylesheet('/css/main.css') !!}

# Minify multiple files
{!! Minify::stylesheet(['/css/main.css', '/css/bootstrap.css']) !!}

# Add custom attributes to the HTML element
{!! Minify::stylesheet(['/css/main.css', '/css/bootstrap.css'], ['foo' => 'bar']) !!}

# Add the full resource URL to the HTML element
{!! Minify::stylesheet(['/css/main.css', '/css/bootstrap.css'])->withFullUrl() !!}

# Load an external resource
{!! Minify::stylesheet('//fonts.googleapis.com/css?family=Roboto') !!}

# Minify and combine all files in a given folder
{!! Minify::stylesheetDir('/css/') !!}

# Add custom attributes to the HTML element
{!! Minify::stylesheetDir('/css/', ['foo' => 'bar']) !!}

# Add the full resource URL to the HTML element
{!! Minify::stylesheetDir('/css/', ['foo' => 'bar'])->withFullUrl() !!}
```

#### Javascript

```php
// app/views/hello.blade.php

# Minify one file
{!! Minify::javascript('/js/jquery.js') !!}

# Minify multiple files
{!! Minify::javascript(['/js/jquery.js', '/js/jquery-ui.js']) !!}

# Add custom attributes to the HTML element
{!! Minify::javascript(['/js/jquery.js', '/js/jquery-ui.js'], ['foo' => 'bar']) !!}

# Add the full resource URL to the HTML element
{!! Minify::javascript(['/js/jquery.js', '/js/jquery-ui.js'], ['foo' => 'bar'])->withFullUrl() !!}

# Load an external resource
{!! Minify::javascript('//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js') !!}

# Minify and combine all files in a given folder
{!! Minify::javascriptDir('/js/') !!}

# Add custom attributes to the HTML element
{!! Minify::javascriptDir('/js/', ['foo' => 'bar']) !!}

# Add the full resource URL to the HTML element
{!! Minify::javascriptDir('/js/', ['foo' => 'bar'])->withFullUrl() !!}
```
