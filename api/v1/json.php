<?php

	function print_json($return) {
		if ($return === false) {
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			die();
		}
		
		$json = json_encode($return);
		$json = str_replace(array("{", "}", '","'), array("{
    ", "
}", '",
    "'), $json);

		echo $json;
		die();
	}


?>