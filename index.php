<?php

    /*
     * Simple test example
     */
    include './CinnamonPHP/CinnamonPHP.php';
    $cinnamon = new CinnamonPHP();
    $cinnamon->AddTemplatePath("./templates");
    $cinnamon->ForceRegenerateCache(TRUE);
    $cinnamon->SetCacheDire("./cache2", TRUE);
    $test = "Hello  world!!!";
    echo $cinnamon->LoadTemplate('template1.html', FALSE);
?>
