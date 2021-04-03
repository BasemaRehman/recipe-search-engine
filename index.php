<?php
$start = "http://localhost:8888/Bakery_Search_Engine/sites.html";

function follow($url) {
    $doc = new DOMDocument();
    $doc->loadHTML(file_get_contents($url));

    $links = $doc->getElementsByTagName("a");

    foreach ($links as $link){
        echo $link->getAttribute("href")."\n";
    }

}

follow($start);