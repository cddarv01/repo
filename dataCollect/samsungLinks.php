<?php
$location="lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0);
ini_set('include_path', '/opt/web/lgq/BananaPants/brands');
include("include/config.php");                                         
echo "Start\n";
$date=date('Y-m-d H:i:s'); 
$datePlusWeekProcess=strtotime("$date +1 week");
$expirationDate = date("Y-m-d H:i:s", $datePlusWeekProcess);

$SQLTotalSKUsCount = "SELECT count(*) FROM lgqtest.tblSamsungSKUs";
GetResultSet($con32, $rsTotalSKUsCount, $SQLTotalSKUsCount);
$TotalNumOfSKUs = mysqli_fetch_assoc($rsTotalSKUsCount);
$SQLTotalSKUsProcessed = "SELECT count(*) FROM lgqtest.tblSamsungSKUs WHERE qProcessed != 0";
GetResultSet($con32, $rsTotalSKUsProcessed, $SQLTotalSKUsProcessed);
$TotalNumOfSKUsProcessed = mysqli_fetch_assoc($rsTotalSKUsProcessed);

$SQLMaxqProcessedCheck = "SELECT MAX(qProcessed) FROM lgqtest.tblSamsungSKUs";
GetResultSet($con32, $rsMaxqProcessedCheck, $SQLMaxqProcessedCheck);
$qProcessedMaxNum = mysqli_fetch_assoc($rsMaxqProcessedCheck);
$inProcessCheck = $qProcessedMaxNum;

$SQLSelectTestDate = "SELECT MAX(expirationDate) FROM lgqtest.tblSamsungSKUs";
GetResultSet($con32, $rsSelectTestDate, $SQLSelectTestDate);
$checkSkuDateForTableUpdate = mysqli_fetch_assoc($rsSelectTestDate);
$dateCheck = $checkSkuDateForTableUpdate;

if($date > $dateCheck){
	if($inProcessCheck == 0){
		$SQLDropTblSkusForSamsung = "DROP TABLE lgqtest.tblSamsungSKUs";
		GetResultSet($con32,$rsSQLDropTblSkusForSamsung,$SQLDropTblSkusForSamsung);
		$SQLCreateTblSkusForSamsung = "CREATE TABLE tblSamsungSKUs SELECT ProductID, BrandID, SKU FROM lgqtest.tblSKUs WHERE BrandID = 620";
		GetResultSet($con32,$rsSQLCreateTblSkusForSamsung,$SQLCreateTblSkusForSamsung);
		$SQLAddqProcessedAndTimeToTable = "ALTER TABLE lgqtest.tblSamsungSKUs ADD qProcessed INT DEFAULT 0, ADD expirationDate DATETIME DEFAULT '0000-00-00 00:00:00'";
		GetResultSet($con32,$rsSQLAddqProcessedAndTimeToTable,$SQLAddqProcessedAndTimeToTable);
		$SQLInsertDate = "INSERT INTO lgqtest.tblSamsungSKUs (DateAdded) VALUES ('$expirationDate')";
		GetResultSet($con32,$rsSQLInsertDate,$SQLInsertDate);
	} else {
		echo "\nSamsung Link retrieval has not been fully processed. Samsung SKUs table have not been updated. Please finish current Link retrieval";
	}
}

$SQLAllSKUsToProcess = "SELECT * FROM lgqtest.tblSamsungSKUs";
GetResultSet($con32, $rsAllSKUsToProcess, $SQLAllSKUsToProcess);
$SQLSKUToProcess = "SELECT SKU FROM lgqtest.tblSamsungSKUs WHERE qProcessed = 0";
GetResultSet($con32, $rsSKUToProcess, $SQLSKUToProcess);

$rowToProcess = mysqli_fetch_assoc($rsSKUToProcess);

echo "Processing SKUs\n";
while($rowToProcess = mysqli_fetch_assoc($rsAllSKUsToProcess)){
	$SKUToProcess = $rowToProcess['SKU'];
	
	$SQLCheckSKUsFromLinkTable="select SKU, Link from lgqtest.tblLinksForSamsung where SKU = '$SKUToProcess'";
	GetResultSet($con32, $rsCheckSKUsFromLinkTable, $SQLCheckSKUsFromLinkTable);
	$rowToCheck = mysqli_fetch_assoc($rsCheckSKUsFromLinkTable);
	$linkToCheck = $rowToCheck['Link'];
	//regexVar1 captures block of text (starting from 'product-title' and ending with 'search-results-rating-row'), uses grep 'href' to grab the Link from the block of text, then uses the other expressions to clear away all other text.
	$regexVar1 = "| sed -n '/product-title/,/search-results-rating-row/p' | sed -n '/href=\"/,/\" /p' | sed 's/\"//' | sed 's/\".*//' | head -1 | sed 's/.*a href=//'";
	echo $SKUToProcess."\n";
	//this shell_exec collects link using the criteria from regexVar1
	shell_exec('curl -L "http://www.samsung.com/us/search/searchMain?Dy=1&Nty=1&Ntt='.$SKUToProcess.'" '.$regexVar1.' > /opt/web/lgq/BananaPants/brands/links/samsung/'.$SKUToProcess.'.txt');
	sleep(3);	
	 
	$linksToProcess=file_get_contents("/opt/web/lgq/BananaPants/brands/links/samsung/".$SKUToProcess.".txt");
	$linksToProcessArray=(explode('**',$linksToProcess));

	foreach($linksToProcessArray as $link){
		if(preg_match('/<>@/',$link) !== FALSE){
			echo "Link result for SKU - ".$SKUToProcess." - violates expected form\n";
			$link = "";
			if(empty($link)){
				echo "Link capture 1st pass failed. Attempting 2nd pass\n";
				//regexVar2 searches for Link by using keyword 'canonical', then removes any other text or whitespace.
				$regexVar2 = "| grep 'canonical' | sed 's/.*href=\"//' | sed 's/\".*//'";
				//this shell_exec collects link using the criteria from regexVar2
				$link=shell_exec('curl -L "http://www.samsung.com/us/search/searchMain?Dy=1&Nty=1&Ntt='.$SKUToProcess.'" '.$regexVar2);
			}
			if(strpos($link, "gnb.css") !== FALSE){
				$link = "";
			}
			if(strpos($link, "https") !== FALSE){
				$link = "";
			}
			if(strpos($link, "us/") !== FALSE){
				if(strpos($link, "http://www.samsung.com") === FALSE){
					$fullLink='http://www.samsung.com'.$link;
					$fullLink=preg_replace('/\s+/', '', $fullLink);
			}
				$fullLink = $link;
				echo "Final Link - ".$fullLink."\n";
				$date=date('Y-m-d H:i:s'); 
				if(mysqli_num_rows($rsCheckSKUsFromLinkTable) > 0){
					$sqlInsertLinks = "UPDATE lgqtest.tblLinksForSamsung SET DateUpdated='$date', Link='$fullLink' WHERE SKU = '".$SKUToProcess."'";
					GetResultSet($con32, $rsInsertLinks, $sqlInsertLinks);
				} else {
					$sqlInsertLinks = "INSERT INTO lgqtest.tblLinksForSamsung (DateAdded, DateUpdated, SKU, Link) VALUES ('$date', '$date', '$SKUToProcess', '$fullLink')";
					GetResultSet($con32, $rsInsertLinks, $sqlInsertLinks);
				}
				$SQLSelectLinkFromBrandTable = "SELECT count(*) FROM lgqtest.tblBrandtestlink WHERE Link = '$fullLink' and SKU = '".$SKUToProcess."'";
				GetResultSet($con32,$rsSelectLinkFromBrandTable,$SQLSelectLinkFromBrandTable);
				$brandTableLink = mysqli_fetch_assoc($rsSelectLinkFromBrandTable);
				if($brandTableLink > 0){
					$SQLInsertLinkInBrandTable = "UPDATE lgqtest.tblBrandtestlink SET Date = '$date' WHERE Link = '$fullLink' and SKU = '".$SKUToProcess."' and BrandID = 620";
					GetResultSet($con32,$rsInsertLinkInBrandTable,$SQLInsertLinkInBrandTable);
				} else {
					$SQLUpdateLinkInBrandTable = "INSERT INTO lgqtest.tblBrandtestlink (Date, Link, SKU, BrandID) VALUES ($date, $fullLink, '$SKUToProcess', 620))";
					GetResultSet($con32,$rsUpdateLinkInBrandTable,$SQLUpdateLinkInBrandTable);
				}
			}else{
				echo "No Link available for - ".$SKUToProcess."\n\n";
			}
			$SQLUpdateqProcessed = "UPDATE tblSKUsForSamsung SET qProcessed = 5  WHERE SKU = '".$SKUToProcess."' and qProcessed = 0";
			GetResultSet($con32,$rsUpdateqProcessed,$SQLUpdateqProcessed);
		}
	}
}							

?>
