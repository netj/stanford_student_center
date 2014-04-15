<!--
\\
\\ Student Center
\\ Sbastien Robaszkiewicz
\\ Justin Cheng
\\ Mike Chrzanowski
\\ 2014
\\
-->

<?php

require_once("constants.php");

// Gets the SUNetID.
$webAuthUser = $_SERVER['REMOTE_USER'];

// Fetches the Google Spreadsheet as a CSV file.
// To change the Google Spreadsheet, use the key corresponding to your new document.
$url = "https://docs.google.com/spreadsheet/pub?key=".$key."&output=csv";

// csv_to_array($filename, $delimiter) converts a CSV file to an associative array
//   - Takes the first line of the CSV as the header (key)
//   - Creates a row in the associative array for each new line of the CSV file (value)
// Put differently, the keys are the column headers of the Google Spreadsheet.
//
// @@ For instance, if the CSV file gotten from the Google Spreadsheet is:
// @@
// @@ sunetid, hw1, hw2
// @@ jure, 98, 99
// @@ robinio, 95, 100
// @@
// @@ and we call $students = csv_to_array($filename) on it,
// @@ then $student[0]["sunetid"] would be "jure",
// @@ and $student[1]["hw2"] would be "100".
//
function csv_to_array($filename='', $delimiter=',') {
	global $error;
    $header = NULL;
    $data = array();
    if (($handle = @fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    } else {
		$error .= "We're experiencing connectivity issues right now... :(";
	}
    return $data;
}


// $students is the associative array with all the rows from the CSV file.
$students = csv_to_array($url);

// Finds the row corresponding to the logged in student and assign it to $student.
// If that SUNetID is not in the Google Spreadsheet, assign $student = NULL.
$student = NULL;
foreach($students as $stud) {
    $sunetid = $stud["sunetid"];
    if ($sunetid == $webAuthUser) {
        $student = $stud;
    }
    if ($stud["sunetid"] == "0_class_fullscore") {
        $fullscore = $stud;
    }
    if ($stud["sunetid"] == "0_class_avg") {
        $averageStats = $stud;
    }
    if ($stud["sunetid"] == "0_class_max") {
        $maxStats = $stud;
    }
    if ($stud["sunetid"] == "0_class_sd") {
        $stdevStats = $stud;
    }
    if ($stud["sunetid"] == "0_class_median") {
        $medianStats = $stud;
    }
}

function lateDisplay($lateVal, $datetime) {
	if($lateVal === "0") {
		return "<span class=\"label label-success\" title=\"$datetime\">Received</span>";
	} elseif ($lateVal === "") {
		return "<span class=\"label label-info\" title=\"$datetime\">Not Received</span>";
	} else {
		return "<span class=\"label label-warning\" title=\"$datetime\">Turned in Late ($lateVal hours)</span>";
	}
}

function coverDisplay($coverVal) {
	if($coverVal === "-2") {
		return "<span class=\"label label-warning\">None!</span>";
	} else {
		return "<span class=\"label label-success\">Present!</span>";
	}
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $className." ".$termName; ?> Student Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <![endif]-->
    </head>

    <body>

<div style="background-color: #ddd; border-bottom: 1px solid #444; margin-bottom: 15px;">
<div class="container">
<nav class="navbar navbar-default" role="navigation" style="background: 0; border: 0; margin-bottom: 0;">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <a class="navbar-brand" href="#" style="padding-left: 0;"><strong><?php echo $className." ".$termName; ?></strong> Student Center</a>
  </div>

  <!--<div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav">
      <li><a href="#">Homework</a></li>
      <li><a href="#">Final</a></li>
    </ul>
  </div>-->
</nav>
</div>
</div>

<div class="container">

<?php if (strlen($error) > 0) { ?>
<div class="alert alert-danger"><strong><?php echo $error; ?></strong></div>
<?php } ?>

<?php if(!isset($student)) { ?>

    <div class="row-fluid">
        <div class="span12">
            <div class="hero-unit">
                <h1>Sorry <?php echo $webAuthUser; ?>, we couldn't find you in our database.</h1>
                <h2><a href="mailto:<?php echo $staffEmail; ?>?Subject=[<?php echo $className; ?> Student Center] Can't find &quot;<?php echo $webAuthUser; ?>&quot; in database">
Please contact us</a> if you think this is a mistake.</h2>
            </div>
        </div>
    </div>

<?php } else { ?>

    <div class="row-fluid">
        <div class="span12">
			<h2>Hi, <strong><?php echo $student["first_name"] . " " . $student["last_name"]; ?></strong>!</h2>
<?php { ?>
Check out your grades, as well as late periods used. If there are any discrepancies between your actual and recorded grades, <a href="mailto:<?php echo $staffEmail; ?>?Subject=[<?php echo $className; ?> Student Center] Grade discrepancy for <?php echo $webAuthUser; ?>">contact us</a>!
<?php } ?>
        </div>
    </div>

<?php { ?>

    <div class="row-fluid">
        <div class="span12">
		<h3>Late Periods</h3>
            <?php
            $late_hours = $student["lateperiod_used"];
            if ($late_hours == 0)       {$alertType = "alert-success"; $alertMessage = "Yay!";}
            else if ($late_hours <= 24) {$alertType = "alert-info";    $alertMessage = "Heads-up!";}
            else if ($late_hours <= 60) {$alertType = "alert-warning"; $alertMessage = "Warning!";}
            else                        {$alertType = "alert-danger";  $alertMessage = "Warning!";}
            ?>
            <div class="alert alert-block <?php echo $alertType; ?>" >
                <strong><?php echo $alertMessage ?></strong> You have used <strong><?php echo $late_hours ?></strong> out of your allowed 60 late hours.
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="hero-unit">
                <h3>Project</h3>
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th>Status</th>
                            <th>Test Result</th>
                            <th>Functionality</th>
                            <th>Robustness</th>
                            <th>Documentation</th>
                            <th>Design</th>
                            <th class="total"><big>Total</big></th>
                            <th>Late Penalty</th>
                            <th>Late Hours Used</th>
                            <th class="stat">Class Average</th>
                            <th class="stat">Class StDev.</th>
                            <th class="stat">Class Median</th>
                            <th class="stat">Class Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($student["rm_total"] != "") { ?>
                        <tr>
                            <td><big><strong>RM</strong></big></td>
                            <td><?php echo lateDisplay($student["rm_latehours"], $student["rm_submitted"]); ?></td>
                            <td><a class="btn btn-default" href="results/<?php echo $student["sunetid"]; ?>/rm.html">Details</a></td>
                            <td><strong><?php echo number_format($student["rm_functionality"] /100 * $fullscore["rm_functionality"], 2); ?></strong>/<?php echo $fullscore["rm_functionality"]; ?> <br><small class="text-muted">(<?php echo $student["rm_functionality"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["rm_robustness"]    /100 * $fullscore["rm_robustness"]   , 2); ?></strong>/<?php echo $fullscore["rm_robustness"];    ?> <br><small class="text-muted">(<?php echo $student["rm_robustness"]   ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["rm_documentation"] /100 * $fullscore["rm_documentation"], 2); ?></strong>/<?php echo $fullscore["rm_documentation"]; ?> <br><small class="text-muted">(<?php echo $student["rm_documentation"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["rm_design"]        /100 * $fullscore["rm_design"]       , 2); ?></strong>/<?php echo $fullscore["rm_design"];        ?> <br><small class="text-muted">(<?php echo $student["rm_design"]       ; ?>)</small></td>
                            <td class="total"><big><strong><?php echo $student["rm_total"]; ?></strong>/<?php echo $fullscore["rm_total_raw"]; ?></big></td>
                            <td><?php echo $student["rm_penalty"]; ?></td>
                            <td><?php echo $student["rm_lateperiod_used"]; ?></td>
                            <td class="stat"><?php echo number_format($averageStats["rm_total"], 2); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["rm_total"]  , 2); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["rm_total"] , 2); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["rm_total"]    , 2); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php } ?>

<hr />
<footer class="footer">
<?php { ?>
	<p><small>
</small></p>
<?php } ?>
    <p><small><a href="<?php echo $classWebsite; ?>">Back to <?php echo $className." ".$termName; ?></a></small></p>
</footer>

</div>

</body>
</html>
