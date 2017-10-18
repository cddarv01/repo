<?php
$location="lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0); 
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");                                         
echo "Start\n";
$date = date('Y-m-d H:i:s');
$dateMinusWeekProcess=strtotime("$date -1 week");
$dateMinusWeek = date("Y-m-d H:i:s", $dateMinusWeekProcess);

$SQLAllLinksToProcess = "SELECT * FROM lgqtest.tblLinksForWalmart_copy WHERE qProcessed = 0";
GetResultSet($con32, $rsAllLinksToProcess, $SQLAllLinksToProcess);
$SQLLinkToProcess = "SELECT Link, SiteProductID FROM lgqtest.tblLinksForWalmart_copy WHERE qProcessed = 0";
GetResultSet($con32, $rsLinkToProcess, $SQLLinkToProcess);
$SQLSKUCount0 = "SELECT count(*) as Num FROM lgqtest.tblLinksForWalmart_copy WHERE qProcessed = 0";
GetResultSet($con32, $rsSKUCount0, $SQLSKUCount0);
$linkToProcess = mysqli_fetch_assoc($rsLinkToProcess);
$allLinksToProcess = mysqli_fetch_assoc($rsAllLinksToProcess);
$skuCount0 = mysqli_fetch_assoc($rsSKUCount0);

if($skuCount0['Num'] == 0){
	echo "All SKUs have been processed";
	$sqlUpdateqProcessed = "UPDATE lgqtest.tblLinksForWalmart_copy SET qProcessed = 0 WHERE qProcessed = 5";
	GetResultSet($con32, $rsSQLUpdateqProcessed, $sqlUpdateqProcessed);
	echo "All SKUs qProcessed value has been set to 0. Please run program again.";
}
echo "Processing SKUs\n";
while($linkToProcess = mysqli_fetch_assoc($rsAllLinksToProcess)){ 
	sleep(3);
	$link = $linkToProcess['Link'];
	$siteProductID = $linkToProcess['SiteProductID'];
	echo $siteProductID."\n";
	/*This shell_exec uses lynx instead of curl. Nothing else is different.
	*[-dump] flag for lynx dumps formatted output to text file
	[-source] flag for lynx is almost the same as dump except it formats HTML output*/
	shell_exec("lynx -dump -source ".$link." > /opt/web/lgq/BananaPants/retailers/pagesDownloaded/walmart/".$siteProductID.".txt");
	$SQLUpdateqProcessed = "UPDATE lgqtest.tblLinksForWalmart_copy SET qProcessed = 5 WHERE SiteProductID = '$siteProductID'";
	GetResultSet($con32, $rsUpdateqProcessed, $SQLUpdateqProcessed);
}
$date = date('Y-m-d H:i:s');
echo "Page downlading process complete - ".$date."\n";
echo "No SKUs To Process\n";
$SQLUpdateqProcessed = "UPDATE lgqtest.tblLinksForWalmart_copy SET qProcessed = 0 WHERE qProcessed = 5";
GetResultSet($con32, $rsUpdateqProcessed, $SQLUpdateqProcessed);
echo "All SKUs qProcessed Value Has Been Set To 0\n";
echo "Please Run Program again\n";
?>
