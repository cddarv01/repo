<?php
$location="lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0);
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");                                         
echo "Start\n";
$SQLAllLinksToProcess = "SELECT * FROM lgqtest.tblLinksForSamsung WHERE Link NOT LIKE '%/support%' AND Link NOT LIKE '%.com' AND Link NOT LIKE '%@%' AND qProcessed = 0";
GetResultSet($con32,$rsAllLinksToProcess,$SQLAllLinksToProcess);
$SQLSKUToProcess = "SELECT SKU FROM lgqtest.tblSamsungSKUs WHERE qProcessed = 0";
GetResultSet($con32, $rsSKUToProcess, $SQLSKUToProcess);
shell_exec("rm /opt/web/lgq/BananaPants/brands/pagesDownloaded/samsung/*");

while($rowToProcess = mysqli_fetch_assoc($rsAllLinksToProcess)){
	$date = date('Y-m-d H:i:s');
	$linkToProcess = $rowToProcess['Link'];
	$SKUToProcess = $rowToProcess['SKU'];
	echo $linkToProcess."\n";
	$SQLInsertLinkInBrandTable = "INSERT INTO lgqtest.tblBrandtestlink (Date, Link, SKU, BrandID, qProcessed) VALUES ('$date', '$linkToProcess', '$SKUToProcess', 620, 0)";
	GetResultSet($con32,$rsInsertLinkInBrandTable,$SQLInsertLinkInBrandTable);
	$SQLLinkID = "SELECT LinkID, max(date) FROM lgqtest.tblBrandtestlink WHERE SKU = '$SKUToProcess' and Link = '$linkToProcess' limit 1";
	GetResultSet($con32,$rsLinkID,$SQLLinkID);
	$selectedLinkID = mysqli_fetch_assoc($rsLinkID);
	$linkID = $selectedLinkID['LinkID']; 
	/* This shell_exec downloads pages using links retreived from the link gathering process (stored in the retailer's link database on lgq3-prices). 
	The downloaded pages are saved as text files in '/opt/web/lgq/BananaPants/brands/pagesDownloaded/samsung/' */
	shell_exec('sudo curl -L "'.$linkToProcess.'" > /opt/web/lgq/BananaPants/brands/pagesDownloaded/samsung/'.$linkID.'.txt');
	$SQLUpdateSamsungLinksqProcessed = "UPDATE lgqtest.tblLinksForSamsung SET qProcessed = 5 WHERE SKU = '$SKUToProcess' AND qProcessed = 0";
	GetResultSet($con32,$rsUpdateSamsungLinksqProcessed,$SQLUpdateSamsungLinksqProcessed);
	sleep(5);
}
$date = date('Y-m-d H:i:s');
echo "Page downlading process complete - ".$date."\n";
$SQLResetSamsungLinksqProcessed = "UPDATE lgqtest.tblLinksForSamsung SET qProcessed = 0 WHERE qProcessed = 5";
GetResultSet($con32,$ResetSamsungLinksqProcessed,$SQLResetSamsungLinksqProcessed);

?>
