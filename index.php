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
    if ($stud["sunetid"] == "0_class_weight") {
        $weights = $stud;
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
	if($lateVal === "" or $datetime === "") {
		return "<span class=\"label label-danger\" title=\"$datetime\">Not Received</span>";
	} elseif($lateVal === "0") {
		return "<span class=\"label label-success\" title=\"$datetime\">Received</span>";
	} elseif($lateVal > 0) {
		return "<span class=\"label label-warning\" title=\"$datetime\">Turned in Late</span> <br> <span class=\"label label-warning\" title=\"$datetime\">($lateVal hours)</span>";
	} else {
                $lateVal = -$lateVal;
		return "<span class=\"label label-success\" title=\"$datetime\">Turned in Early!</span> <br> <span class=\"label label-success\" title=\"$datetime\">($lateVal hours)</span>";
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

    <?php if (!$student["scores finalized"]) { ?>
    <div class="row-fluid">
        <div class="span12">
	    <h3>Late Periods</h3>
            <?php
            $late_hours = $student["lateperiod_used"];
            if ($late_hours == 0)       {$alertType = "alert-success"; $alertMessage = "Yay!";}
            else if ($late_hours <= 24) {$alertType = "alert-info";    $alertMessage = "Heads-up!";}
            else if ($late_hours < 60)  {$alertType = "alert-warning"; $alertMessage = "Warning!";}
            else                        {$alertType = "alert-danger";  $alertMessage = "Warning!";}
            ?>
            <div class="alert alert-block <?php echo $alertType; ?>" >
                <strong><?php echo $alertMessage ?></strong> You have used <strong><?php echo $late_hours ?></strong> out of your allowed 60 late hours.
            </div>
        </div>
    </div>
    <?php } else { ?>

    <div class="row-fluid">
        <div class="span12">
	    <h3>Final Score</h3>
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>RM</th>
                            <th>IX</th>
                            <th>Quiz</th>
                            <th>SM</th>
                            <th>QL</th>
                            <th>EX</th>
                            <th>Class Participation</th>
                            <th>Grade Boost</th>
                            <th class="total"><big>Total</big></th>
                            <th class="stat">Class Average</th>
                            <th class="stat">Class StDev.</th>
                            <th class="stat">Class Median</th>
                            <th class="stat">Class Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total</td>
                            <td><a href="#rm"     ><?php echo number_format(100 * $student["rm_total"]   / $fullscore["rm_total"]  , 2); ?>% <small>(<?php echo $student["rm_total"]  ; ?>/<?php echo $fullscore["rm_total"]  ; ?>)</small></a></td>
                            <td><a href="#ix"     ><?php echo number_format(100 * $student["ix_total"]   / $fullscore["ix_total"]  , 2); ?>% <small>(<?php echo $student["ix_total"]  ; ?>/<?php echo $fullscore["ix_total"]  ; ?>)</small></a></td>
                            <td><a href="#quiz"   ><?php echo number_format(100 * $student["quiz_total"] / $fullscore["quiz_total"], 2); ?>% <small>(<?php echo $student["quiz_total"]; ?>/<?php echo $fullscore["quiz_total"]; ?>)</small></a></td>
                            <td><a href="#sm"     ><?php echo number_format(100 * $student["sm_total"]   / $fullscore["sm_total"]  , 2); ?>% <small>(<?php echo $student["sm_total"]  ; ?>/<?php echo $fullscore["sm_total"]  ; ?>)</small></a></td>
                            <td><a href="#ql"     ><?php echo number_format(100 * $student["ql_total"]   / $fullscore["ql_total"]  , 2); ?>% <small>(<?php echo $student["ql_total"]  ; ?>/<?php echo $fullscore["ql_total"]  ; ?>)</small></a></td>
                            <td><a href="#ex"     ><?php echo number_format(100 * $student["ex_total"]   / $fullscore["ex_total"]  , 2); ?>% <small>(<?php echo $student["ex_total"]  ; ?>/<?php echo $fullscore["ex_total"]  ; ?>)</small></a></td>
                            <td><?php echo $student["class participation"]; ?></td>
                            <td><a href="#contest"><?php echo $student["io contest grade boost"]; ?></a></td>
                            <td class="total"><big><strong><?php echo $student["total weighted sum score"]; ?></strong>/<?php echo $fullscore["total weighted sum score"]; ?></big></td>
                            <td class="stat"><?php echo number_format($averageStats["total weighted sum score"], 2); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["total weighted sum score"]  , 2); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["total weighted sum score"] , 2); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["total weighted sum score"]    , 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><small>Weights</small></td>
                            <td><small><?php echo $weights["rm_total"]                ; ?></small></td>
                            <td><small><?php echo $weights["ix_total"]                ; ?></small></td>
                            <td><small><?php echo $weights["quiz_total"]              ; ?></small></td>
                            <td><small><?php echo $weights["sm_total"]                ; ?></small></td>
                            <td><small><?php echo $weights["ql_total"]                ; ?></small></td>
                            <td><small><?php echo $weights["ex_total"]                ; ?></small></td>
                            <td><small><?php echo $weights["class participation"]     ; ?></small></td>
                            <td></td>
                            <td><small><?php echo $weights["total weighted sum score"]; ?></small></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
        </div>
    </div>
    <hr>

    <?php } ?>

    <div class="row-fluid">
        <div class="span12">
                <h3>Project</h3>
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th>Status</th>
                            <th>Functionality</th>
                            <th>Robustness</th>
                            <th>Documentation</th>
                            <th>Design/Correctness</th>
                            <th>Late Penalty, Hours Used</th>
                            <th class="total"><big>Total</big></th>
                            <th class="stat">Class Average</th>
                            <th class="stat">Class StDev.</th>
                            <th class="stat">Class Median</th>
                            <th class="stat">Class Max</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr id="rm">
                            <td rowspan="2"><big><strong>RM</strong></big></td>
                            <td rowspan="2"><?php echo lateDisplay($student["rm_latehours"], $student["rm_submitted"]); ?></td>
                        <?php if ($student["rm_total"] != "") { ?>
                            <td><strong><?php echo number_format($student["rm_functionality"] /100 * $fullscore["rm_functionality"], 2); ?></strong>/<?php echo $fullscore["rm_functionality"]; ?> <br><small class="text-muted">(<?php echo $student["rm_functionality"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["rm_robustness"]    /100 * $fullscore["rm_robustness"]   , 2); ?></strong>/<?php echo $fullscore["rm_robustness"];    ?> <br><small class="text-muted">(<?php echo $student["rm_robustness"]   ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["rm_documentation"] /100 * $fullscore["rm_documentation"], 2); ?></strong>/<?php echo $fullscore["rm_documentation"]; ?> <br><small class="text-muted">(<?php echo $student["rm_documentation"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["rm_design"]        /100 * $fullscore["rm_design"]       , 2); ?></strong>/<?php echo $fullscore["rm_design"];        ?> <br><small class="text-muted">(<?php echo $student["rm_design"]       ; ?>)</small></td>
                            <td class="text-warning"><?php echo $student["rm_penalty"]; ?>, <?php echo $student["rm_lateperiod_used"]; ?>hrs</td>
                            <td class="total"><big><strong><?php echo $student["rm_total"]; ?></strong>/<?php echo $fullscore["rm_total_raw"]; ?></big></td>
                            <td rowspan="2" class="stat"><?php echo number_format($averageStats["rm_total"], 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($stdevStats["rm_total"]  , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($medianStats["rm_total"] , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($maxStats["rm_total"]    , 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center" style="vertical-align:middle;">
                                <?php if ($student["rm_submitted"] !== "") { ?>
                                    <a class="btn btn-sm btn-default btn-primary" href="results/<?php echo $student["sunetid"]; ?>/rm.html">
                                        Open TA Test Report</a>
                                <?php } ?>
                            </td>
                            <td colspan="4" style="white-space:pre-wrap;"><?php echo $student["rm_comment"]; ?></td>
                        <?php } else {?>
                        </tr><tr><td colspan="6"></td><td colspan="4"></td>
                        <?php } ?>
                        </tr>

                        <tr id="ix">
                            <td rowspan="2"><big><strong>IX</strong></big></td>
                            <td rowspan="2"><?php echo lateDisplay($student["ix_latehours"], $student["ix_submitted"]); ?></td>
                        <?php if ($student["ix_total"] != "") { ?>
                            <td><strong><?php echo number_format($student["ix_functionality"] /100 * $fullscore["ix_functionality"], 2); ?></strong>/<?php echo $fullscore["ix_functionality"]; ?> <br><small class="text-muted">(<?php echo $student["ix_functionality"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ix_robustness"]    /100 * $fullscore["ix_robustness"]   , 2); ?></strong>/<?php echo $fullscore["ix_robustness"];    ?> <br><small class="text-muted">(<?php echo $student["ix_robustness"]   ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ix_documentation"] /100 * $fullscore["ix_documentation"], 2); ?></strong>/<?php echo $fullscore["ix_documentation"]; ?> <br><small class="text-muted">(<?php echo $student["ix_documentation"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ix_design"]        /100 * $fullscore["ix_design"]       , 2); ?></strong>/<?php echo $fullscore["ix_design"];        ?> <br><small class="text-muted">(<?php echo $student["ix_design"]       ; ?>)</small></td>
                            <td class="text-warning"><?php echo $student["ix_penalty"]; ?>, <?php echo $student["ix_lateperiod_used"]; ?>hrs</td>
                            <td class="total"><big><strong><?php echo $student["ix_total"]; ?></strong>/<?php echo $fullscore["ix_total_raw"]; ?></big></td>
                            <td rowspan="2" class="stat"><?php echo number_format($averageStats["ix_total"], 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($stdevStats["ix_total"]  , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($medianStats["ix_total"] , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($maxStats["ix_total"]    , 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center" style="vertical-align:middle;">
                                <?php if ($student["ix_submitted"] !== "") { ?>
                                    <a class="btn btn-sm btn-default btn-primary" href="results/<?php echo $student["sunetid"]; ?>/ix.html">
                                        Open TA Test Report</a>
                                <?php } ?>
                            </td>
                            <td colspan="4" style="white-space:pre-wrap;"><?php echo $student["ix_comment"]; ?></td>
                        <?php } else {?>
                        </tr><tr><td colspan="6"></td><td colspan="4"></td>
                        <?php } ?>
                        </tr>

                        <tr id="sm">
                            <td rowspan="2"><big><strong>SM</strong></big></td>
                            <td rowspan="2"><?php echo lateDisplay($student["sm_latehours"], $student["sm_submitted"]); ?></td>
                        <?php if ($student["sm_total"] != "") { ?>
                            <td><strong><?php echo number_format($student["sm_functionality"] /100 * $fullscore["sm_functionality"], 2); ?></strong>/<?php echo $fullscore["sm_functionality"]; ?> <br><small class="text-muted">(<?php echo $student["sm_functionality"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["sm_robustness"]    /100 * $fullscore["sm_robustness"]   , 2); ?></strong>/<?php echo $fullscore["sm_robustness"];    ?> <br><small class="text-muted">(<?php echo $student["sm_robustness"]   ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["sm_documentation"] /100 * $fullscore["sm_documentation"], 2); ?></strong>/<?php echo $fullscore["sm_documentation"]; ?> <br><small class="text-muted">(<?php echo $student["sm_documentation"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["sm_design"]        /100 * $fullscore["sm_design"]       , 2); ?></strong>/<?php echo $fullscore["sm_design"];        ?> <br><small class="text-muted">(<?php echo $student["sm_design"]       ; ?>)</small></td>
                            <td class="text-warning"><?php echo $student["sm_penalty"]; ?>, <?php echo $student["sm_lateperiod_used"]; ?>hrs</td>
                            <td class="total"><big><strong><?php echo $student["sm_total"]; ?></strong>/<?php echo $fullscore["sm_total_raw"]; ?></big></td>
                            <td rowspan="2" class="stat"><?php echo number_format($averageStats["sm_total"], 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($stdevStats["sm_total"]  , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($medianStats["sm_total"] , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($maxStats["sm_total"]    , 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center" style="vertical-align:middle;">
                                <?php if ($student["sm_submitted"] !== "") { ?>
                                    <a class="btn btn-sm btn-default btn-primary" href="results/<?php echo $student["sunetid"]; ?>/sm.html">
                                        Open TA Test Report</a>
                                <?php } ?>
                            </td>
                            <td colspan="4" style="white-space:pre-wrap;"><?php echo $student["sm_comment"]; ?></td>
                        <?php } else {?>
                        </tr><tr><td colspan="6"></td><td colspan="4"></td>
                        <?php } ?>
                        </tr>

                        <tr id="ql">
                            <td rowspan="2"><big><strong>QL</strong></big></td>
                            <td rowspan="2"><?php echo lateDisplay($student["ql_latehours"], $student["ql_submitted"]); ?></td>
                        <?php if ($student["ql_total"] != "") { ?>
                            <td><strong><?php echo number_format($student["ql_functionality"] /100 * $fullscore["ql_functionality"], 2); ?></strong>/<?php echo $fullscore["ql_functionality"]; ?> <br><small class="text-muted">(<?php echo $student["ql_functionality"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ql_robustness"]    /100 * $fullscore["ql_robustness"]   , 2); ?></strong>/<?php echo $fullscore["ql_robustness"];    ?> <br><small class="text-muted">(<?php echo $student["ql_robustness"]   ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ql_documentation"] /100 * $fullscore["ql_documentation"], 2); ?></strong>/<?php echo $fullscore["ql_documentation"]; ?> <br><small class="text-muted">(<?php echo $student["ql_documentation"]; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ql_design"]        /100 * $fullscore["ql_design"]       , 2); ?></strong>/<?php echo $fullscore["ql_design"];        ?> <br><small class="text-muted">(<?php echo $student["ql_design"]       ; ?>)</small></td>
                            <td class="text-warning"><?php echo $student["ql_penalty"]; ?>, <?php echo $student["ql_lateperiod_used"]; ?>hrs</td>
                            <td class="total"><big><strong><?php echo $student["ql_total"]; ?></strong>/<?php echo $fullscore["ql_total_raw"]; ?></big></td>
                            <td rowspan="2" class="stat"><?php echo number_format($averageStats["ql_total"], 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($stdevStats["ql_total"]  , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($medianStats["ql_total"] , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($maxStats["ql_total"]    , 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center" style="vertical-align:middle;">
                                <?php if ($student["ql_submitted"] !== "") { ?>
                                    <a class="btn btn-sm btn-default btn-primary" href="results/<?php echo $student["sunetid"]; ?>/ql.html">
                                        Open TA Test Report</a>
                                <?php } ?>
                            </td>
                            <td colspan="4" style="white-space:pre-wrap;"><?php echo $student["ql_comment"]; ?></td>
                        <?php } else {?>
                        </tr><tr><td colspan="6"></td><td colspan="4"></td>
                        <?php } ?>
                        </tr>

                        <tr id="contest">
                            <td><big><strong>IO Contest</strong></big></td>
                            <?php if ($student["ql_submitted"] !== "") { ?>
                            <td colspan="1"></td>
                            <td colspan="2" class="text-center" style="vertical-align:middle;">
                                    <a class="btn btn-sm btn-default btn-primary" href="results/<?php echo $student["sunetid"]; ?>/contest.html">
                                        Open Contest Result</a>
                            </td>
                            <td colspan="4"><big><strong><?php echo $student["io contest score rank"]; ?> place</strong> scoring <?php echo $student["io contest score"]; ?> points</big>
                                <div style="white-space:pre-wrap;"><?php echo $student["io contest comment"]; ?></div>
                            </td>
                            <?php } else { ?>
                            <td><?php echo lateDisplay($student["ql_latehours"], $student["ql_submitted"]); ?></td>
                            <td colspan="6"></td>
                            <?php } ?>
                            <td colspan="4"></td>
                        </tr>

                    </tbody>
                </table>

                <?php if ($student["quiz"] != "") { ?>
                <hr>
                <h3 id="quiz">Quiz</h3>
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Score</th>
                            <th class="stat">Class Average</th>
                            <th class="stat">Class StDev.</th>
                            <th class="stat">Class Median</th>
                            <th class="stat">Class Max</th>
                            <th></th>
                            <th>Bonus</th>
                            <th class="stat">Class Average</th>
                            <th class="stat">Class StDev.</th>
                            <th class="stat">Class Median</th>
                            <th class="stat">Class Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><big><strong><?php echo $student["quiz"]; ?></strong>/<?php echo $fullscore["quiz"]; ?></big></td>
                            <td class="stat"><?php echo number_format($averageStats["quiz"], 2); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["quiz"]  , 2); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["quiz"] , 2); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["quiz"]    , 2); ?></td>
                            <td></td>
                            <td><big><strong><?php echo $student["quiz_bonus"]; ?></strong>/<?php echo $fullscore["quiz_bonus"]; ?></big></td>
                            <td class="stat"><?php echo number_format($averageStats["quiz_bonus"], 2); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["quiz_bonus"]  , 2); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["quiz_bonus"] , 2); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["quiz_bonus"]    , 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                <?php } ?>

                <?php if ($student["ex_proposal_feedback"] != "") { ?>
                <hr>
                <h3 id="ex">Project Extension</h3>
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th>Completeness</th>
                            <th>Presentation</th>
                            <th>Documentation</th>
                            <th>Difficulty</th>
                            <th class="total"><big>Total</big></th>
                            <th class="stat">Class Average</th>
                            <th class="stat">Class StDev.</th>
                            <th class="stat">Class Median</th>
                            <th class="stat">Class Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($student["ex_total"] != "") { ?>
                        <tr>
                            <td rowspan="2"><big><strong>EX Demo</strong></big></td>
                            <td><strong><?php echo number_format($student["ex_completeness"]  /100 * $fullscore["ex_completeness"] , 2); ?></strong>/<?php echo $fullscore["ex_completeness"] ; ?> <br><small class="text-muted">(<?php echo $student["ex_completeness"]  ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ex_presentation"]  /100 * $fullscore["ex_presentation"] , 2); ?></strong>/<?php echo $fullscore["ex_presentation"] ; ?> <br><small class="text-muted">(<?php echo $student["ex_presentation"]  ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ex_documentation"] /100 * $fullscore["ex_documentation"], 2); ?></strong>/<?php echo $fullscore["ex_documentation"]; ?> <br><small class="text-muted">(<?php echo $student["ex_documentation"] ; ?>)</small></td>
                            <td><strong><?php echo number_format($student["ex_difficulty"]    /100 * $fullscore["ex_difficulty"]   , 2); ?></strong>/<?php echo $fullscore["ex_difficulty"]   ; ?> <br><small class="text-muted">(<?php echo $student["ex_difficulty"]    ; ?>)</small></td>
                            <td class="total"><big><strong><?php echo $student["ex_total"]; ?></strong>/<?php echo $fullscore["ex_total"]; ?></big></td>
                            <td rowspan="2" class="stat"><?php echo number_format($averageStats["ex_total"], 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($stdevStats["ex_total"]  , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($medianStats["ex_total"] , 2); ?></td>
                            <td rowspan="2" class="stat"><?php echo number_format($maxStats["ex_total"]    , 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="5" style="white-space:pre-wrap;"><?php echo $student["ex_comment"]; ?></td>
                        </tr>
                        <?php } ?>

                        <tr>
                            <td rowspan="1"><big><strong>EX Proposal</strong></big></td>
                            <td colspan="9" style="white-space:pre-wrap;"><?php echo $student["ex_proposal_feedback"]; ?></td>
                        </tr>
                    </tbody>
                </table>
                <?php } ?>

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
