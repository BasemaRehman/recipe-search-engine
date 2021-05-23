<?php

$pdo = new PDO('mysql:host=127.0.0.1;port=8889;dbname=RecipeSearch;', 'root', 'root');

$search = $_GET['q'];

$searche = explode(" ", $search);

$x = 0;
$construct = "";
$params = array();
foreach ($searche as $term){
    $x++;
    if ($x == 1) {
        $construct .= "title LIKE CONCAT('%',:search$x,'%') or keywords LIKE CONCAT('%',:search$x,'%')";
    } else {
        $construct .= " AND title LIKE CONCAT('%',:search$x,'%') or keywords LIKE CONCAT('%',:search$x,'%')";
    }
    $params["search$x"] = $term;
}

$results = $pdo->prepare("SELECT * FROM `recipe_index` WHERE $construct");
$results->execute($params);

if ($results->rowCount() == 0){
    echo "0 results found";
} else {
    echo $results->rowCount()." results found! <hr />";
}
foreach ($results->fetchAll() as $result){
    $title = $result ['title'];
    if ($result ['description'] == ""){
        $desc = "No description available";
    } else {
        $desc = $result ['description'];
    }

    $url = $result ['url'];
    echo "<a href='$url'> $title </a> <br> $desc <br> <a href='$url'> $url </a><hr />";


}
