<?php

require_once("conn.php");
require_once("json.php");
require_once("put_delete.php");

$uri = preg_replace("/\/api\/v\d+\//", "", $_SERVER["REQUEST_URI"]);

$uri_matches = array(
	"/^webapps\/(\w+)(?:|\?.*)$/" => "WebAppItems",
	"/^webapps\/(\w+)\/(\d+)(|\?.*)$/" => "WebAppItem",
	"/^webapps\/(\w+)\/([\d,]+)(|\?.*)$/" => "WebAppItems",

	"/^admin\/webapps(|\?.*)$/" => "WebApps",
	"/^admin\/webapps\/(\w+)(|\?.*)$/" => "WebApp",
	"/^admin\/webapps\/(\w+)\/(\w+)(|\?.*)$/" => "WebAppField"
);


try {
	foreach ($uri_matches as $regexp => $function) {
		if (preg_match($regexp, $uri, $matches)) {
			$function = $_SERVER["REQUEST_METHOD"] . '_' . $function;
			if (function_exists($function)) {
				$function(@$matches[1], @$matches[2]);
				break;
			}
		}
	}
} catch(Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"] . " " . $e->getMessage());
	die();
}

print_json(false);

function GET_WebAppItems($webAppName, $idList='') {

	$orderBy = '_id';
	$orderDir = 'DESC';
	
	if (isset($_GET['_orderBy'])) {
		$orderBy = $_GET['_orderBy'];
		if (!preg_match("/^\w+$/", $orderBy)) return;
		unset($_GET['_orderBy']);
	}

	if (isset($_GET['_orderDir'])) {
		$orderDir = $_GET['_orderDir'];
		if (!preg_match("/^\w+$/", $orderDir)) return;
		unset($_GET['_orderDir']);
	}
	
	print_json(selectMultiple($webAppName, $_GET, $idList, $orderBy, $orderDir));
}

function GET_WebAppItem($webAppName, $id) {
	print_json(selectOne($webAppName, $id));
}

function POST_WebAppItems($webAppName) {
	print_json(insert($webAppName, $_POST));
}

function PUT_WebAppItem($webAppName, $id) {
	print_json(update($webAppName, $id, $GLOBALS['_PUT']));
}

function PUT_WebAppItems($webAppName, $idList) {
	print_json(update($webAppName, $idList, $GLOBALS['_PUT']));
}


function DELETE_WebAppItem($webAppName, $id) {
	print_json(delete($webAppName, $id));
}

function DELETE_WebAppItems($webAppName, $idList) {
	print_json(delete($webAppName, $idList));
}


function GET_WebApps() {
	print_json(listWebApps($_GET));
}

function POST_WebApps() {
	$name = $_POST['name'];
	if (preg_match("/^\w+$/", $name)) {
		print_json(createWebApp($name));
	}
}

function GET_WebApp($webAppName) {
	print_json(listWebAppStructure($webAppName));
}

function DELETE_WebApp($webAppName) {
	print_json(deleteWebApp($webAppName));
}

function POST_WebApp($webAppName) {
	$fieldName = $_POST['name'];
	if (!preg_match("/^\w+$/", $fieldName)) return;

	$fieldType = strtoupper($_POST['type']);
	
	if ($fieldType != 'TEXT' && $fieldType != 'INT') return;

	print_json(addWebAppField($webAppName, $fieldName, $fieldType));
}

function DELETE_WebAppField($webAppName, $fieldName) {
	print_json(deleteWebAppField($webAppName, $fieldName));
}

?>