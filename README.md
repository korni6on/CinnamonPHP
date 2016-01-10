CinnamonPHP
====================

Simple PHP template builder.

- - -

CinnamonPHP is a HTML/Text pre-processor, adding features that allow using dynamic variable scope that to be replaced with a PHP variable.

Basically the CinnamonPHP walks through the loaded template and replaces all strings, wrapped with {{...}}, with the available PHP variables.

To prevent a specific string of replacing, add slash infront of it \{{...}}.

Usage:

Template code (user_bio.html):
```HTML
<!DOCTYPE html>
<html>
	<head>
		<title>User Bio Content Template</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
	<body>
		<div>{{full_name}}</div>
		<div>{{position}}</div>
		<div>{{email_address}}</div>
		<div>\{{not_for_replace}}</div>
	</body>
</html>
```

PHP Code:
```PHP
// template variables
$full_name     = "John Doe";
$position      = "CEO";
$email_address = "john_doe@example.com";
$not_for_replace = "this will not be replaced";

$template_name = 'user_bio.html';           // template name
$templates_directory = './templates/users'; // template directory

// include the library
require_once('CinnamonPHP/CinnamonPHP.php');

// CinnamonPHP settings
$compress_content = FALSE;
$force_regenerate_cache = FALSE;
$cache_directory = "./cache2";
$force_create_cache_directory = FALSE;

$cinnamon = new CinnamonPHP();
$cinnamon->AddTemplatePath( $templates_directory );
$cinnamon->ForceRegenerateCache( $force_regenerate_cache );
$cinnamon->SetCacheDire( $cache_directory, $force_create_cache_directory );
echo $cinnamon->LoadTemplate(
	$template_name,
	$compress_content
);
```

Expected output:
```HTML
<!DOCTYPE html>
<html>
	<head>
		<title>User Bio Content Template</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
	<body>
		<div>John Doe</div>
		<div>CEO</div>
		<div>john_doe@example.com</div>
		<div>{{not_for_replace}}</div>
	</body>
</html>
```
