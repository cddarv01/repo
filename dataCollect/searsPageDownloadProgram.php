<?php
$location = "lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0);
$date = date('Y-m-d H:i:s');
$dateMinusWeekProcess=strtotime("$date -1 week");
$dateMinusWeek = date("Y-m-d H:i:s", $dateMinusWeekProcess); 
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");
echo "Start\n";

$SQLAllLinksToProcess = "SELECT * FROM lgqtest.tblRetailertestlink WHERE RetailerID = 3 and qProcessed = 3";
GetResultSet($con32, $rsAllLinksToProcess, $SQLAllLinksToProcess);
$SQLLinkToProcess = "SELECT LinkID, Link FROM lgqtest.tblRetailertestlink WHERE qProcessed = 3 and RetailerID = 3";
GetResultSet($con32, $rsLinkToProcess, $SQLLinkToProcess);

//This shell_exec removes all files from '/opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/' before it starts to download new ones (the shell_exec on line 26).
shell_exec("rm /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/*");

while($rowToProcess = mysqli_fetch_assoc($rsAllLinksToProcess)){
	sleep(3);
	$LinkToProcess = rtrim($rowToProcess['Link'],'\r');
	$LinkID = $rowToProcess['LinkID'];
	echo $LinkToProcess."\n";
	/*This shell_exec uses a link from the database (retrieved from the link capture process) to fetch a webpage using curl with flags '-s' and '-D'. 
	see S:\danield\LGQDocumentation.docx for details on flags or https://curl.haxx.se/docs/manpage.html. */
	shell_exec('sudo curl -s -D - "'.$LinkToProcess.'" > /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/"'.$LinkID.'".txt');
	$SQLUpdateLinksProcessed = "UPDATE lgqtest.tblRetailertestlink SET qProcessed = 5 WHERE RetailerID = 3 and qProcessed = 3 and LinkID = ".$LinkID."";
	GetResultSet($con32, $rsUpdateLinksProcessed, $SQLUpdateLinksProcessed);
}
$date = date('Y-m-d H:i:s');
echo "Page downlading process complete - ".$date."\n";
$SQLUpdateLinksProcessed = "UPDATE lgqtest.tblRetailertestlink SET qProcessed = 3 WHERE RetailerID = 3 and qProcessed = 5 ";
GetResultSet($con32, $rsUpdateLinksProcessed, $SQLUpdateLinksProcessed);
?>