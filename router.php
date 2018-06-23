<?php

$uri = $_SERVER["REQUEST_URI"];

/* The root address of the page */
define("PAGE_ADDR", "http://" . $_SERVER["HTTP_HOST"]);

// router.php
if (preg_match('/^\/public\//', $uri))
{
    return false;    // serve the requested resource as-is.
}
else 
{ 
    include "index.php";
}