<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>View :: Event Timeline</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="css/jquery-ui.css.DONTLOAD" rel="stylesheet" type="text/css">
    <link href="css/timeline.css" rel="stylesheet" type="text/css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script type="text/javascript"> if (!window.console) console = {
            log: function () {
            }
        }; </script>
    <!--<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,400,300,600,' rel='stylesheet'
          type='text/css'>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    -->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery-ui.js"></script>

    <script type="text/javascript" src="js/timeline.js"></script>
    <script type="text/javascript" src="js/jquery.csv.js"></script>

    <script type="text/javascript" src="js/tdxtimeline.js"></script>

    <script type="text/javascript">
        var settings = {};
        settings['legend'] = "#legend";


        $(document).ready(function () {
            console.debug("Drawing timeline started");
            var external_key = "<?php echo $_GET['external_key'] ?>";
            $.ajax({
                url: "api/get_timeline_events.php?external_key=" + external_key,
                success: function (result) {
                    $("#timeline_container").tdxTimeline(result, settings);
                    $("#timeline_container").prepend("<h4><hr class='style-17'> Order: " + 1 + "</hr></h4>");
                }
            });
        });


    </script>


    <style type="text/css">
        .graph_div {
            width: 100%;
            height: 500px;
        }
        div.chartdiv {
            width: 100%;
            font-size: 11px;
            min-width: 800px;
        }


        div.special{
            background-color: red;
            width: 70px;
            padding:10px;

        }
    </style>
</head>

<body>


<div id="timeline_container" class="chartdiv">

</div>

<div id="legend" class="legend">

</div>
<br>
<table border="0">
    <tr>
        <td id="chart_reference" colspan="10">
            Chart Reference: Chart:thw:101
            <br style="clear:both;"/>
        </td>
    </tr>
</table>

</body>

</html>
