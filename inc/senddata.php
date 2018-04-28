<?php
include('getData.php');

if (isset($_GET['teststart'])) {
	$formdata = $_POST['formdata']; 
	echo TestData::teststart($formdata);
} else if (isset($_GET['testresults'])) {
	$testdata = $_POST['testdata'];
	echo TestData::testresults($testdata);
} else if (isset($_GET['nextquestion'])) {
	$answerid = $_POST['answerid'];
	echo TestData::nextquestion($answerid);
}
