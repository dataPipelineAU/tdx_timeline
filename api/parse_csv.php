<?php

require_once("database.php");


class cl_ii_tdx_timeline_v1
{

    function pf_a100_is_my_format($ps_class_name, $ps_filename, $ps_debug, $ps_site_id,$ps_run_id)
    {
        // Must be a csv file
        if (!endswith($ps_filename, ".csv")){
            return false;
        }

        // First row must be "tdx_timeline,v1,2016,,,,,"
        $f = fopen($ps_filename, 'r');
        $row = fgets($f);
        fclose($f);
        // This line specifies the file format
        if (!($row == "tdx_timeline,v1,2016,,,,,,\n")){
            return false;
        }

        // Passes all tests, should be correct format
        return true;
    }

// ################@#########################################################################################################################################################################
    function pf_a200_do_format($ps_class_name, $ps_is_my_format, $ps_full_filename_process_wrk, $ps_param_dodebug,$ps_site_id,$ps_run_id,$ps_filetoprocess,$ps_done_url,$ps_site_path)
    {
        $db = get_connection();
        $tmpName = $ps_full_filename_process_wrk;
        $csvAsArray = array_map('_str_getcsv', file($tmpName));


        $errors = 0;
        $successes = 0;
        $all_results = array();
        // Skip first two lines/rows:
        // 1. First row is a comment line
        // 2. Second row is headers
        for ($i=2; $i<count($csvAsArray); $i++){
            $row = array_combine($csvAsArray[1], $csvAsArray[$i]);

            $current_result = save_row($db, $row);

            if ($current_result['error'] > 0){
                $errors += 1;
            }else{
                $successes += 1;
            }
            $all_results[] = $current_result;
        }

    }
// ################@#########################################################################################################################################################################
    function pf_b100_get_details($ps_line,$ps_field_to_get,$ps_debug,$ps_proc_name)
    {
        return "unknown";
    }
}

/**
 * Saves a single row to the database.
 *
 * Updates
 *
 * @param $db: mysqli database connection object
 * @param $row: a row of data from a timeline event file
 * @return array: information on whether the command succeeded. If $result['error'] <> 0, then there was an error
 */
function save_row($db, $row)
{
    if (check_if_row_exists($db, $row)){
        return update_row($db, $row);
    }else{
        return insert_row($db, $row);
    }

}


/**
 * @param $db
 * @param $row
 * @return bool
 */
function check_if_row_exists($db, $row){
    $sql_query = "SELECT COUNT(*) FROM `events` WHERE `external_key` = ? and `event_key` = ?";
    /** @var mysqli $db */
if ($s_stmt = $db->prepare($sql_query)) {
        $s_stmt->bind_param("ss", $row['external_key'], $row['event_key']);
        $s_stmt->bind_result($count);
        //$s_stmt->bind_result($computed_id, $template_id, $geojson, $date_created, $notes);
        $s_stmt->execute();
        while($s_stmt->fetch()) {
            return $count > 0;
        }
    }
    return false;
}


function update_row($db, $row){
    $result = array();
    $sql_save_data = "UPDATE `events` SET `status_property` = ?, `start` = ?, `end` = ?, `title` = ?, `content` = ?, `properties` = ? WHERE `external_key` = ? and `event_key` = ? ";
    /** @var mysqli $db */
    if ($s_stmt = $db->prepare($sql_save_data)) {
        $error = False;
        try {
            if ($row['end'] == ""){
                $row['end'] = null;
            }

            $s_stmt->bind_param("isssssss", $row['status_property'],
                $row['start'], $row['end'], $row['title'], $row['content'], $row['properties'], $row['external_key'], $row['event_key']);
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


function insert_row($db, $row){
    $result = array();
    $sql_save_data = "INSERT INTO `events` (`external_key`, `event_key`, `status_property`, `start`, `end`, `title`, `content`, `properties`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    /** @var mysqli $db */
    if ($s_stmt = $db->prepare($sql_save_data)) {
        $error = False;
        try {
            if ($row['end'] == ""){
                $row['end'] = null;
            }

            $s_stmt->bind_param("ssisssss", $row['external_key'], $row['event_key'], $row['status_property'],
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

function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

?>