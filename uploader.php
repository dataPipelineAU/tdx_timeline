<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Timeline updater</title>
</head>
<body>

<?php
if (isset($_GET['message'])) {
?>
    <div class="message">
        <?php echo $_GET['message'] ?>
    </div>

<?php
}
?>

<h1>CSV Uploader</h1>

<h2>Format:</h2>
<p>
The csv file needs to have the following columns, in this order.
</p>
<h3>events table</h3>
<ol>
    <li>id: auto incrementing number, ignored, but required</li>
    <li>external_key: external identifier, passed into timeline.php</li>
    <li>status_property: status number of event</li>
    <li>start: datetime</li>
    <li>end: datetime, nullable (if null, event is a single point in time)</li>
    <li>title: Human readable name</li>
    <li>content: Description or other content</li>
    <li>properties: JSON with any other properties in it</li>
</ol>

<p>A common property you will want to set in properties is className.</p>

<h2>Upload</h2>
<form action="api/csv_loader.php" method="post" enctype="multipart/form-data">
    <input type="file" name="csv" value="" />
    <input type="submit" name="submit" value="Save" />
</form>

</body>
</html>