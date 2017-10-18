<?php
$location="lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0);
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");                                         
echo "Start\n";
$date=date('Y-m-d H:i:s'); 

$SQLAllSKUsToProcess = "SELECT * FROM tblSKUsForWalmart";
GetResultSet($con32,$rsAllSKUsToProcess,$SQLAllSKUsToProcess);
while($rowToProcess = mysqli_fetch_assoc($rsAllSKUsToProcess)){
	$SKUToProcess = $rowToProcess['SKU'];
	echo "Processing SKU - ".$SKUToProcess."\n";
	$SQLCheckSKUsFromLinkTable="select SKU from lgqtest.tblLinksForWalmart_copy where SKU = '$SKUToProcess'";
	GetResultSet($con32, $rsCheckSKUsFromLinkTable, $SQLCheckSKUsFromLinkTable);
	$sqlUpdateqProcessed = "UPDATE lgqtest.tblSKUsForWalmart SET qProcessed = 0 WHERE SKU = '$SKUToProcess'";
	GetResultSet($con32, $rsSQLUpdateqProcessed, $sqlUpdateqProcessed);
	$SQLCheckDates = "select DateAdded, DateUpdated from lgqtest.tblLinksForWalmart_copy where SKU = '$SKUToProcess'";
	GetResultSet($con32, $rsCheckDates, $SQLCheckDates);
	$rowDates = mysqli_fetch_assoc($rsCheckDates);
	$date=date('Y-m-d H:i:s');
	$dateVar = $rowDates['DateUpdated'];

	$datePlusWeek = strtotime("$dateVar +1 week");
	$regexVar1 = " | grep -e 'js-product-title' | sed 's/.*js-product-title href=\"//' | sed 's/\">.*//' | head -3";
	$regexVar2 = " | sed s/$/,/ | tr -d \" \t\n\r\"";
	
	echo $SKUToProcess."\n";
	shell_exec('curl "https://www.walmart.com/search/?query='.$SKUToProcess.'&facet=retailer:Walmart.com"'.$regexVar1.' > /opt/web/lgq/BananaPants/retailers/links/walmart/'.$SKUToProcess.'_temp.txt');
	sleep(3);				
	
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/links/walmart/".$SKUToProcess."_temp.txt".$regexVar2." > /opt/web/lgq/BananaPants/retailers/links/walmart/".$SKUToProcess."_links.txt");
	shell_exec("rm /opt/web/lgq/BananaPants/retailers/links/walmart/".$SKUToProcess."_temp.txt"); 
   
	$linksToProcess=file_get_contents("/opt/web/lgq/BananaPants/retailers/links/walmart/".$SKUToProcess."_links.txt");
	$linksToProcessArray=(explode(',',$linksToProcess));
	$popEmptyElement=array_pop($linksToProcessArray);
	
	foreach($linksToProcessArray as $link){
		$fullLink='http://www.walmart.com'.$link;
		$fullLink=preg_replace('/\s+/', '', $fullLink);
		$siteProductID=substr($link,strrpos($link,'/')+1);
		$siteProductID=preg_replace('/\s+/', '', $siteProductID);
		echo $fullLink."\n";
		
		$regexVar3 = " | grep -e 'Specifications' | sed 's/.*Model:<\/td> <td> //' | sed 's/ <\/td>.*//' | head -1";

		$SKUCheck=shell_exec('curl "https://www.walmart.com'.$link.'"'.$regexVar3.'');
		$SKUCheck=preg_replace('/\s+/', '', $SKUCheck);
		echo "SKU Returned from Check - ".$SKUCheck."\n";
		
		$SQLSKUCheck = "SELECT * FROM lgqtest.tblSKUsForWalmart WHERE SKU = '$SKUCheck'";
		GetResultSet($con32,$rsSKUCheck,$SQLSKUCheck);
		
		if(mysqli_num_rows($rsSKUCheck) > 0){
			echo "VERIFING SKU\n";
			$SQLCheckSiteProductID="select SiteProductID from lgqtest.tblLinksForWalmart_copy where SiteProductID = '$siteProductID'";
			GetResultSet($con32, $rsCheckSiteProductID, $SQLCheckSiteProductID);
			if(mysqli_num_rows($rsCheckSiteProductID) > 0){
				$sqlInsertLinks = "UPDATE lgqtest.tblLinksForWalmart_copy SET DateUpdated='$date', SKU='$SKUToProcess', SiteProductID='$siteProductID', Link='$fullLink'  WHERE SiteProductID  = '".$siteProductID."'";
				GetResultSet($con32, $rsInsertLinks, $sqlInsertLinks);
			} else {
				$sqlInsertLinks = "INSERT INTO lgqtest.tblLinksForWalmart_copy (DateAdded, DateUpdated, SKU, SiteProductID, Link) VALUES ('$date', '$date', '$SKUToProcess', '$siteProductID', '$fullLink')";
				GetResultSet($con32, $rsInsertLinks, $sqlInsertLinks);
			}
				
		}
		
	}			
}				
echo "Walmart Link Collection Completed at - ".$date."\n";
?>
