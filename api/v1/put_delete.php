<?php

    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "PUT" || $method == "DELETE") {
        parse_str(file_get_contents('php://input'), $params);
        $GLOBALS["_{$method}"] = $params;
    }

?>