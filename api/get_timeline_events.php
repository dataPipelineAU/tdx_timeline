<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 19/05/16
 * Time: 1:04 PM
 */
require_once("database.php");

header('Content-type: text/plain');


function get_events($external_key){
    $db = get_connection();
    $results = array();
    $sql_get_template = "SELECT `status_property`, `start`, `end`, `title`, `content`, `properties` FROM `events` WHERE `external_key` = ?";
    if ($s_stmt = $db->prepare($sql_get_template)) {
        $s_stmt->bind_param("s", $external_key);
        $s_stmt->bind_result($status_property, $start, $end, $title, $content, $properties);
        $s_stmt->execute();
        while($s_stmt->fetch()) {
            $row = array();
            $row['external_key'] = $external_key;
            $row['status_property'] = $status_property;
            $row['start'] = $start;
            $row['end'] = $end;
            $row['title'] = $title;
            $row['content'] = $content;
            $row['properties'] = $properties;
            $results[] = $row;
        }
    }
    return $results;
}

$data = get_events($_GET['external_key']);

$event_number = 0;


function _json_decode($string) {
    if (get_magic_quotes_gpc()) {
        $string = stripslashes($string);
    }

    return json_decode($string);
}

foreach ($data as $key => $row){
    print("Event$event_number:");
    print("\n");
    print("    status_property: {$row['status_property']}");
    print("\n");
    print("    start: {$row['start']}");
    print("\n");
    if ($row['end']) {
        // The end can be null (unlike other options)
        print("    end: {$row['end']}");
        print("\n");
    }
    print("    title: {$row['title']}");
    print("\n");
    print("    content: {$row['content']}");
    print("\n");
    $extra_data = _json_decode($row['properties']);
    foreach($extra_data as $inner_key => $value){
        print("    $inner_key: $value");
        print("\n");
    }
    print("\n");

    print("\n");

    $event_number += 1;
}


/* EXAMPLE:

# Event names don't matter, as long as there is no whitespace beforehand
Event1:
# Properties of events are any line that starts with a space. It doesn't matter how many spaces
# Standard fields: title, start (date), end (date), content
    title: New Year's Party

    # Dates must be in this format, or another format javascript reads natively: http://javascript.info/tutorial/datetime-functions#parsing-from-string-date-parse
    # Start date of the event
    start: 2014-12-31T18:00:00

    # blank lines are simply ignored, wherever they are

    # End date of the event
    end: 2015-01-01T02:00:00

    # description and content
    content: A party to celebrate the new year!

    # onclick will send user to this website when the item is clicked
    onclick: /newyearsparty/link

    # className gives the styling of the event an additional CSS class
    # put whatever styling against that css class
    className: important
    # The type of event. This is used for the legend. Types need to correspond to className -- i.e. one className to one type
    classType: Party

    # Any other attributes are read and attached as properties of the event, so are readable after the fact
    abc: 123



Event2:
    title: Dinner
    start: 2015-01-02T17:30:00
    end: 2015-01-02T21:00:00
    content: Dinner at restaurant
    className: notimportant
    classType: Dinner
*/