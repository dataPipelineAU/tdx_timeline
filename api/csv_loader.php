<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 27/11/16
 * Time: 4:18 PM
 */

require_once("database.php");

$db = get_connection();

/*

events table
------------
id: autoinc
external_key: external identifier, passed into timeline.php
status_property: status number of event
start: datetime
end: datetime, nullable (if null, event is a single point in time)
title: Human readable name
content: Description or other content
properties: JSON with any other properties in it

*/


function save_row($db, $row)
{
    $result = array();
    $sql_save_data = "INSERT INTO `events` (`external_key`, `status_property`, `start`, `end`, `title`, `content`, `properties`) VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($s_stmt = $db->prepare($sql_save_data)) {
        $error = False;
        try {
            if ($row['end'] == ""){
                $row['end'] = null;
            }

            $s_stmt->bind_param("sisssss", $row['external_key'], $row['status_property'],
                $row['start'], $row['end'], $row['title'], $row['content'], $row['properties']);
            //$s_stmt->bind_result($computed_id, $template_id, $geojson, $date_created, $notes);
            if (!$s_stmt->execute()) {
                $result['error'] = 1;
                $result['note'] = htmlspecialchars($s_stmt->error);
            }
        } catch (Exception $e) {
            $result['error'] = 1;
            $result['note'] = $e->getMessage();
        }
        // Got to here -- everything seemed to work
        if (!$error) {
            $result['error'] = 0;
            $result['id'] = $db->insert_id;
            $result['row'] = $row;
        }
    } else {
        $result['error'] = 1;
        $result['note'] = htmlspecialchars($db->error);
    }

    return $result;
}

function _str_getcsv($file){
    return str_getcsv($file, null, "'");
}

$tmpName = $_FILES['csv']['tmp_name'];
$csvAsArray = array_map('_str_getcsv', file($tmpName));

//print_r($csvAsArray);

$errors = 0;
$successes = 0;
$all_results = array();

for ($i=1; $i<count($csvAsArray); $i++){
    $row = array_combine($csvAsArray[0], $csvAsArray[$i]);

    $current_result = save_row($db, $row);

    if ($current_result['error'] > 0){
        $errors += 1;
    }else{
        $successes += 1;
    }
    $all_results[] = $current_result;
}


$new_url = "../uploader.php?message=Upload complete with $successes successful and $errors errors";
header('Location: ' . $new_url);
//print_r($all_results);
die();  // You should always call die after setting header to redirect
// Funny (depressing?) story on why: http://thedailywtf.com/articles/WellIntentioned-Destruction