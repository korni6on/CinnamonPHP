# CinnamonPHP
CinnamonPHP is Simple PHP Template builder

You can create HTML code (or other string) and replace text with variables in PHP. 
When you are done you can take generated string and use where you need.

PHP Example usage:
```PHP
include './CinnamonPHP/CinnamonPHP.php';
$cinnamon = new CinnamonPHP();
$cinnamon->AddTemplatePath("./templates");
$cinnamon->ForceRegenerateCache(FALSE);
$cinnamon->SetCacheDire("./cache2", TRUE);
$test = "Hello  world!!!";
echo $cinnamon->LoadTemplate('template1.html', FALSE);
```

in this exaple you create object. Add template path(Path to search teplates). Generate cache when needed.  After that we said to save cache files in extarnal directory and create if not exist. After that you create a string variable and load template.

Template code:
```HTML
<!DOCTYPE html>
<html>
    <head>
        <title>Simple template</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>{{test}}</div>
        <div>\{{test}}</div>
        <div>{{test}}</div>
    </body>
</html>
```
Template builder replace all variable in {{ }} are re4placed with theit values. If you need to escape key jist add slash infront of first bracket.

If you compile code you can see this output:
```HTML
<!DOCTYPE html>
<html>
    <head>
        <title>Simple template</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>Hello  world!!!</div>
        <div>\{{test}}</div>
        <div>Hello  world!!!</div>
    </body>
</html>
```