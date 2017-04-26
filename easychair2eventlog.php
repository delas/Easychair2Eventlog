<?php

if (count($argv) != 2) {
	print "Use: php -f " . $argv[0] . " LOG_FILE";
	die();
}

function processLogEntry($logEntry) {
	$matches = array(
		"/^file upload for submission (\d+) \(paper\)$/" => array("paper upload", "$1"),
		"/^file upload for submission (\d+) \(Camera ready\)$/" => array("camera ready upload", "$1"),
		"/^submission (\d+) withdrawn$/" => array("submission withdrawn", "$1"),
		"/^file deleted for submission (\d+) \(paper\)$/" => array("paper file deleted", "$1"),
		"/^file upload for submission (\d+) \(paper\)$/" => array("paper file uploaded", "$1"),
		
		"/^review by [\w\s\.]+ on paper (\d+)$/" => array("review added", "$1"),
		"/^review by [\w\s\.]+ \(for [\w\s\.]+\) on paper (\d+)$/" => array("subreview added", "$1"),
		"/^revised review by [\w\s\.]+ on paper (\d+)$/" => array("review revised", "$1"),
		"/^Decision ACCEPT on paper (\d+)$/" => array("paper accepted", "$1"),
		"/^Decision REJECT on paper (\d+)$/" => array("paper rejected", "$1"),
		"/^Decision   on paper (\d+)$/" => array("paper decision removed", "$1"),
		"/^comment on paper (\d+)$/" => array("added comment", "$1"),
	);

	foreach($matches as $pattern => $values) {
		if (preg_match($pattern, $logEntry)) {
			return array($values[0], preg_replace($pattern, $values[1], $logEntry));
		}
	}

	return false;
}

$handle = fopen($argv[1], "r");
if ($handle) {
	while (($line = fgetcsv($handle, 0, "\t")) !== false) {
		$date = $line[0] . " " . $line[1];
		$originator = $line[3];
		$activityCase = processLogEntry($line[2]);
		if ($activityCase !== false) {
			$activity = $activityCase[0];
			$caseId = $activityCase[1];
			print $date . "\t" . $originator . "\t". $activity ."\t". $caseId ."\n";
		}
	}
	fclose($handle);
}

?>
