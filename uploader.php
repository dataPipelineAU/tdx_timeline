<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload :: Event Timeline</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="css/jquery-ui.css.DONTLOAD" rel="stylesheet" type="text/css">
    <link href="css/timeline.css" rel="stylesheet" type="text/css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
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
<div class="centered">
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
    <p>Another important option for swimlane diagrams is group.</p>
    <p>
    <b>Download Examples:</b>
        <ul>
            <li><a href="test_data/simple_test.csv">Simple Example</a></li>
            <li><a href="test_data/swimlane_test.csv">Swimlane Example</a></li>
        </ul>
    </p>

    <h2>Upload</h2>
    <form action="api/csv_loader.php" method="post" enctype="multipart/form-data">
        <input type="file" name="csv" value=""/>
        <input type="submit" name="submit" value="Save"/>
    </form>


</div>
</body>
</html>