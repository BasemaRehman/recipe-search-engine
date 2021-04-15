<?php
$start = "http://localhost:8888/Bakery_Search_Engine/sites.html";
$pdo = new PDO('mysql:host=127.0.0.1;port=8889;dbname=RecipeSearch;', 'root', 'root');

$crawled_site = array();
$crawling = array();

function get_details($url) {

	$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: howBot/0.1\n"));
	$context = stream_context_create($options);
	$doc = new DOMDocument();
	@$doc->loadHTML(@file_get_contents($url, false, $context));

	$title = $doc->getElementsByTagName("title");
	$title = $title->item(0)->nodeValue;
	$description = "";
	$keywords = "";
	$metas = $doc->getElementsByTagName("meta");
	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
		if (strtolower($meta->getAttribute("name")) == "description")
			$description = $meta->getAttribute("content");
		if (strtolower($meta->getAttribute("name")) == "keywords")
			$keywords = $meta->getAttribute("content");

	}
	return '{ "Title": "'.str_replace("\n", "", $title).'", "Description": "'.str_replace("\n", "", $description).'", "Keywords": "'.str_replace("\n", "", $keywords).'", "URL": "'.$url.'"}';

}

function follow($url) {
	global $crawled_site;
	global $crawling;
	global $pdo;
	$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: howBot/0.1\n"));
	$context = stream_context_create($options);
	$doc = new DOMDocument();
	@$doc->loadHTML(@file_get_contents($url, false, $context));
	$linklist = $doc->getElementsByTagName("a");
	foreach ($linklist as $link) {
		$l =  $link->getAttribute("href");
		if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
		} else if (substr($l, 0, 2) == "//") {
			$l = parse_url($url)["scheme"].":".$l;
		} else if (substr($l, 0, 2) == "./") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
		} else if (substr($l, 0, 1) == "#") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
		} else if (substr($l, 0, 3) == "../") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		} else if (substr($l, 0, 11) == "javascript:") {
			continue;
		} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		}
		if (!in_array($l, $crawled_site)) {
				$crawled_site[] = $l;
				$crawling[] = $l;
				$details = json_decode(get_details($l));
				echo $details->Keywords;


				$details->URL;
				$rows = $pdo->query("SELECT * FROM `recipe_index` WHERE url= '$details->URL'");
				$rows = $rows->fetchColumn();

				$data = ['title' => $details->Title, 'description' => $details->Description, 'keywords' => $details->Keywords, 'url' => $details->URL, 'url_hash'=> md5($details->URL),];

				if ($rows > 0){
				    echo "UPDATE";

				} else {
				// This adds in a new row to the database. Even though there is an id column, it is autoincremented so
				// need to set a value in the code
                    $sql = "INSERT INTO recipe_index (title, description, keywords, url, url_hash) VALUES (:title,:description,:keywords,:url,:url_hash)";
                    $stmt= $pdo->prepare($sql);
                    $stmt->execute($data);
				}
		}

	}

	array_shift($crawling);
	foreach ($crawling as $site) {
		follow($site);
	}

}
follow($start);
