<?php
// Make a MySQL Connection
mysql_connect("localhost", "root", "", false, 2) or die(mysql_error());
mysql_select_db("webapps") or die(mysql_error());

// select one item from $tableName where $keyFieds is $keyValue
function selectOne($tableName, $keyValue) {

	$query = "SELECT * FROM " . $tableName;
	$query .= ' WHERE _id=';
	
	if (preg_match("/^\d+$/", $keyValue)) {
		$query .= $keyValue;
	} else {
		$query .= "'" . addslashes($keyValue) . "'";
	}
	
	$result = mysql_query($query);
	if (!$result) return false;

	$return = mysql_fetch_assoc($result);
	return $return;
}

// select all items from $tableName that match $filter
function selectMultiple($tableName, $filter, $idList, $orderBy, $orderDir) {
	$query = '';
	if ($idList != '') {
		$filter['_id'] = $idList;
	}
	foreach($filter as $field => $value) {
		if (!preg_match("/^\w+$/", $field)) continue;
		if ($query == '') {
			$query = 'WHERE ';
		} else {
			$query .= ' AND ';
		}

		$query .= $field;
		
		// <1, >10 are special cases, accepted as filter values
		if (preg_match("/^[<>]\d+/", $value)) {
			$query .= $value;
		// !23, !abc are special cases, accepted as filter values
		} elseif (preg_match("/^!\w+/", $value)) {
			$value = substr($value, 1);
			
			if (preg_match("/^\d+$/", $value)) {
				$query .= '!=' . $value;
			} else {
				$query .= "!='" . addslashes($value) . "'";
			}
		// ~abc is a special case, accepted. It means contains.
		} elseif (strrchr($value, '~')) {
			$query .= " LIKE '%" . addslashes(substr($value, 1)) . "%'";
		// integer
		} elseif (preg_match("/^\d+$/", $value)) {
			$query .= '=' . $value;
		// in (1,2,3)
		} elseif ($field == '_id') {
			$query .= " IN ($value)";
		// just a string
		} else {
			$query .= "='" . addslashes($value) . "'";
		}
	}

	$query = "SELECT * FROM $tableName $query ORDER BY $orderBy $orderDir";
	
	$result = mysql_query($query);
	if (!$result) return false;
	
	$return = array();
	while($row=mysql_fetch_assoc($result)) { 
		$return[]=$row;
	}

	return $return;
}

// insert new item into $tableName, with $fields values
function insert($tableName, $fields) {

	$fieldsList = '';
	$fieldValues = '';

	foreach($fields as $field => $value) {
		if (!preg_match("/^\w+$/", $field)) continue;

		if ($fieldsList != '') $fieldsList .= ', '; 
		$fieldsList .= $field;
		
		if ($fieldValues != '') $fieldValues .= ', ';
		if (preg_match("/^\d+$/", $value)) {
			$fieldValues .= $value;
		} else {
			$fieldValues .= "'" . addslashes($value) . "'";
		}
	}

	$query = "INSERT INTO " . $tableName . " ($fieldsList) VALUES ($fieldValues)";	

	$result = mysql_query($query);
	if (!$result) return false;
	
	return mysql_insert_id() . '';
}

// updates the item from $tableName where $keyFieds is $keyValue with $fields values
function update($tableName, $keyValue, $fields) {

	$query = '';
	
	foreach($fields as $field => $value) {
		if (!preg_match("/^\w+$/", $field)) continue;

		if ($query != '') {
			$query = ', ';
		}

		$query .= $field . '=';

		if (preg_match("/^\d+$/", $value)) {
			$query .= $value;
		} else {
			$query .= "'" . addslashes($value) . "'";
		}
	}

	$query = "UPDATE $tableName SET $query WHERE _id IN ($keyValue)";

	$result = mysql_query($query);
	if (!$result) return false;
	
	if (mysql_affected_rows() > 0) {
		if (preg_match("/^\d+$/", $keyValue)) {
			return $keyValue;
		}
		return explode(",", $keyValue);
	}
	return false;
}

// deletes the item from $tableName where $keyFieds is $keyValue
function delete($tableName, $keyValue) {

	$query = "DELETE FROM $tableName WHERE _id IN ($keyValue)";
	
	$result = mysql_query($query);
	if (!$result) return false;

	$affectedRows = mysql_affected_rows();
	if ($affectedRows>0) return $affectedRows . '';
	return false;
}

// lists all tables in the system
function listWebApps($filer) {
	$query = 'SHOW TABLES';

	$result = mysql_query($query);
	if (!$result) return false;

	$return = array();
	while($row=mysql_fetch_array($result)) { 
		$return[]=$row[0];
	}

	return $return;
}

function createWebApp($tableName) {
	$query = "CREATE TABLE $tableName (_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY)";

	$result = mysql_query($query);
	
	if (!$result) throw new Exception("409");
	return "1";

}

// lists the structure of a db table
function listWebAppStructure($tableName) {
	$query = 'DESCRIBE ' . $tableName;

	$result = mysql_query($query);
	if (!$result) return false;

	$return = array();
	while($row=mysql_fetch_assoc($result)) { 
		$return[$row['Field']] = preg_replace("/\(.*\)/", "", $row['Type']);
	}
	return $return;
}

// drops a database table
function deleteWebApp($tableName) {
	$query = 'DROP TABLE ' . $tableName;
	
	$result = mysql_query($query);
	if (!$result) return false;
	
	return "1";
}

function addWebAppField($tableName, $fieldName, $fieldType) {

	$query = "ALTER TABLE  $tableName ADD  $fieldName $fieldType NULL";
	
	$result = mysql_query($query);
	
	if (!$result) throw new Exception("409");
	return "1";
}

function deleteWebAppField($tableName, $fieldName) {

	$query = "ALTER TABLE $tableName DROP $fieldName";
	
	$result = mysql_query($query);
	if (!$result) return false;
	
	return "1";
}

?>