<?php
set_time_limit(0);
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");                                         
echo "Start\n";
$date = date('Y-m-d H:i:s');
echo $date."\n";
$dateMinusWeekProcess=strtotime("$date -1 week");
$dateMinusWeek = date("Y-m-d H:i:s", $dateMinusWeekProcess);
shell_exec("rm /opt/web/lgq/BananaPants/retailers/parseLists/walmartParseList*");
shell_exec("ls -t /opt/web/lgq/BananaPants/retailers/pagesDownloaded/walmart | sed 's/$/,/' | tr -d \" \t\n\r\" > /opt/web/lgq/BananaPants/retailers/parseLists/walmartParseList.txt");
$pagesToProcess=file_get_contents("/opt/web/lgq/BananaPants/retailers/parseLists/walmartParseList.txt");
$pagesToProcessArray=(explode(',',$pagesToProcess));
$popEmptyElement=array_pop($pagesToProcessArray);
$regexVar1 = "| grep -e 'canonical' | sed 's/.*canonical\" href=\"//' | sed 's/\"\/>.*//' | sed 's/ <td>//' | sed 's/<\/td>//' | sed 's/<\/td>.*//' | sed 's/$/,/' | tr -d \" \t\n\r\""; 
$regexVar2 = "| grep -e 'itemprop=\"model' | sed 's/.*itemprop=\"model\" content=\"//' | sed 's/\".*//' | head -1";
$regexVar3 = "| grep -e 'itemprop=\"price' | sed 's/.*itemprop=\"price\" content=\"//' | sed 's/\".*//' | head -1";
foreach($pagesToProcessArray as $page){
	echo "\n".$page."\n";
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/walmart/".$page." ".$regexVar1." > /opt/web/lgq/BananaPants/retailers/priceData/walmart/temp_".$page);
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/walmart/".$page." ".$regexVar2." >> /opt/web/lgq/BananaPants/retailers/priceData/walmart/temp_".$page);
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/walmart/".$page." ".$regexVar3." >> /opt/web/lgq/BananaPants/retailers/priceData/walmart/temp_".$page);
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/priceData/walmart/temp_".$page." | sed s/$/,/ | awk '!seen[$0]++' | tr -d \" \t\n\r\" > /opt/web/lgq/BananaPants/retailers/priceData/walmart/".$page);
	shell_exec("rm /opt/web/lgq/BananaPants/retailers/priceData/walmart/temp_".$page);
	$date = date('Y-m-d H:i:s');
	$pageIDStrippedTxt = substr($page,0,strpos($page,'.txt'));
	/*$SQLPageLinkMatch = "SELECT SiteProductID, Link FROM lgqtest.tblLinksForWalmart_copy WHERE SiteProductID = '$pageIDStrippedTxt'";
	GetResultSet($con32, $rsSQLPageLinkMatch, $SQLPageLinkMatch);
	$linkSelected = mysqli_fetch_assoc($rsSQLPageLinkMatch);
	$linkCompare = $linkSelected['Link'];*/
	
	$priceSKULink = file_get_contents("/opt/web/lgq/BananaPants/retailers/priceData/walmart/".$page);
	$arrayPriceSKULink = (explode(",",$priceSKULink));
	$linkParced=@$arrayPriceSKULink[0];
	echo $linkParced."\n";
	$SKU=@$arrayPriceSKULink[1];
	echo $SKU."\n";
	$price=@$arrayPriceSKULink[2];
	echo $price."\n";
	$link=$linkParced;
	if(strpos($linkParced, "<") !== FALSE){
		$SKU='';
	}
	if(strpos($link, "https://") === FALSE){
		$fullLink='https://www.walmart.com'.$link;
		echo $fullLink."\n";
		$link=$fullLink;
	}
	if(strpos($link, "FFRS0822S1") !== FALSE){
	   $SKU="FFRS0822S1";
	}
	if(preg_match('/[a-zA-Z]/', $price) == 1){
		$price='';	
	}
	       
	$SQLInsertRetailerLink = "INSERT INTO lgqtest.tblRetailertestlink (Date, Link, RetailerID, qProcessed) VALUES ('$date', '$link', 6, 1)";
	GetResultSet($con32, $rsInsertRetailerLink, $SQLInsertRetailerLink);
	$SQLSelectLinkID = "SELECT LinkID FROM lgqtest.tblRetailertestlink WHERE Link = '$link'";
	GetResultSet($con32, $rsSelectLinkID, $SQLSelectLinkID);
	$selectedLinkID = mysqli_fetch_assoc($rsSelectLinkID);
	$linkID = $selectedLinkID['LinkID'];
	$SKUisGood = "Sku is good";
	
	$SQLInsertPriceData = "INSERT INTO lgqtest.tblRetailerPrices (LinkID, Date, Price, RetailerID, SKU, Link, qStatus, note) VALUES ('$linkID', '$date', '$price', 6, '$SKU', '$link', '3', '$SKUisGood')";
	GetResultSet($con32, $rsInsertPriceData, $SQLInsertPriceData);				
}
echo "\nWalmart Parsing finished - \n".$date;
						
?>
