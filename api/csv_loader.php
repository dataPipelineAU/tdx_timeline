<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 27/11/16
 * Time: 4:18 PM
 */
require_once("parse_csv.php");
// Path of the main folder, i.e. index.php
define ('SITE_ROOT', realpath(dirname(dirname(__FILE__))));

$upload_dir = "uploads/";

$ps_debug = true;
$ps_site_id = "my_site";
$ps_run_id = 1001;
$ps_class_name = "tdx_timeline";
$ps_param_dodebug = true;
$ps_done_url = "../uploader.php?message=Upload completed";
$ps_site_path = "Unknown";


/** saves a file to a given location
 * @param $file
 * @return mixed
 */
function save_file($tmp_filename, $filename){
    global $upload_dir;
    // Get just the name of the file, not the directory. Add csv to it
    $just_filename = basename($filename) . ".csv";
    // Create new filename in the upload directory
    $new_filename =  SITE_ROOT . "/" . $upload_dir . $just_filename;

    $successful = move_uploaded_file($tmp_filename,$new_filename);
    if ($successful) {
        return $new_filename;
    }else{
        echo 'Here is some more debugging info:';
        print_r($_FILES);
        throw new Exception("Could not move uploaded file");
    }
}

$filename = save_file($_FILES['csv']['tmp_name'], $_FILES['csv']['name']);

$c = new cl_ii_tdx_timeline_v1();

if ($c->pf_a100_is_my_format($ps_class_name, $filename, $ps_debug, $ps_site_id, $ps_run_id)){
    $c->pf_a200_do_format($ps_class_name, true, $filename, $ps_param_dodebug, $ps_site_id, $ps_run_id, $filename, $ps_done_url, $ps_site_path);
    // Redirect user to a page and say that the file was scucessful
    header('Location: ' . $ps_done_url);
    //print_r($all_results);
    die();  // You should always call die after setting header to redirect
    // Funny (depressing?) story on why: http://thedailywtf.com/articles/WellIntentioned-Destruction
}else{
    die("Incorrect format for file");
}

