<?php
$location = "lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0);
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");                                         
echo "Start\n";
$date=date('Y-m-d H:i:s'); 

$SQLAllSKUsToProcess = "select * from lgqtest.tblSKUsForSears where qProcessed = 0";
GetResultSet($con32, $rsAllSKUsToProcess, $SQLAllSKUsToProcess);
$SQLSKUToProcess = "select SKU from lgqtest.tblSKUsForSears where qProcessed = 0";
GetResultSet($con32, $rsSKUToProcess, $SQLSKUToProcess);
$rowToProcess = mysqli_fetch_assoc($rsSKUToProcess);
$SKUToProcess=$rowToProcess['SKU'];
$newline = "\n";
//Set all Links qProcessed value to zero before new link fetch.
$SQLUpdateProcessed = "UPDATE lgqtest.tblRetailertestlink SET qProcessed = 0";
GetResultSet($con32, $rsUpdateProcessed, $SQLUpdateProcessed);

$sqlLinkGrab = "select * from tblSKUsForSears where qProcessed = 0 order by RAND() limit 1" ;
GetResultSet($con32, $rsLinkGrab, $sqlLinkGrab);
$rowLinkGrab=mysqli_fetch_assoc($rsLinkGrab);
if(isset($rowLinkGrab)){
   echo '<meta http-equiv="refresh" content="0; url=http://'.$location.'BananaPants/SearsRetailerMacro.php"/>';
}else{ 
    $SQLDropTBLSKUsForSears = "drop table tblSKUsForSears";
    GetResultSet($con32, $rsDrop, $SQLDropTBLSKUsForSears);
    $SQLCreateTBLSKUsForSears = "create table tblSKUsForSears 
                                select a.BrandID, concat(b.ProductFileAbbr, '+', a.SKU) as SKU, 0 as qProcessed 
                                from tblSKUs as a left join vw_categories_products as b on a.ProductID = b.ProductID 
                                where BrandID in (20, 700, 948, 874, 655, 4620, 850, 150, 160, 180, 5006, 240, 260, 290, 5037, 6333, 330, 946, 620, 480, 580, 390, 281) and a.qActive = 1 group by SKU having count(*) < 2";
    echo $SQLCreateTBLSKUsForSears;
    GetResultSet($con32, $rsCreate, $SQLCreateTBLSKUsForSears);
    echobr("<h1>Links Reset at:".date('Y-m-d H:i:s')."</h1>");
    echo '<meta http-equiv="refresh" content="0; url=http://'.$location.'/BananaPants/SearsRetailerMacro.php"/>';      
}

while($rowToProcess = mysqli_fetch_assoc($rsAllSKUsToProcess)){
	$SKUToProcess = $rowToProcess['SKU'];
	$date=date('Y-m-d H:i:s');
	sleep(3);
	echo $SKUToProcess."\n";
	//regexVar1 is variable created to be used within the shell_exec function below. See '/opt/web/lgq/BananaPants/retailers/Programs/linksPrograms/snippets/sears/links' for parsing examples.
	$regexVar1 = "| grep -e 'card-title\" itemprop=' | sed -n '/card-title/,/card-review-container/p' | sed 's/.*_self\" href=\"//' | sed 's/?.*//' | tr -d \" \t\n\r\"";
	/*this shell_exce has curl with -s and -D flags. '-s' = silent (no progress or error message output will be shown in linux putty session). '-D' = dump header (into text document) 
	*shell_exec is storing the page result from a sears.com search for a SKU, in /opt/web/lgq/BananaPants/retailers/links/sears/SKU_temp.txt*/
	shell_exec('sudo curl -s -D - "www.sears.com/search='.$SKUToProcess.'?storeOrigin=Sears&filterList=storeOrigin" > /opt/web/lgq/BananaPants/retailers/links/sears/'.$SKUToProcess.'_temp.txt');
	//*The 2nd shell_exec is parsing the page that was downloaded, in the first shell_exec, using regexVar1; then stores the results in '/opt/web/lgq/BananaPants/retailers/links/sears/SKU_linksTemp.txt'
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/links/sears/".$SKUToProcess."_temp.txt ".$regexVar1." > /opt/web/lgq/BananaPants/retailers/links/sears/".$SKUToProcess."_linksTemp.txt");
	//*The 3rd shell_exec moves the results from the '/opt/web/lgq/BananaPants/retailers/links/sears/SKU_linksTemp.txt' to '/opt/web/lgq/BananaPants/retailers/links/sears/SKU_links.txt'
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/links/sears/".$SKUToProcess."_linksTemp.txt > /opt/web/lgq/BananaPants/retailers/links/sears/".$SKUToProcess."_links.txt");
    //*The 4th shell_exec deletes the text 'temp' text documents (i.e. text documents that contain the SKU and the work temp).
	shell_exec("rm /opt/web/lgq/BananaPants/retailers/links/sears/".$SKUToProcess."_*emp.txt");

	$SQLUpdateProcessed = "UPDATE lgqtest.tblSKUsForSears SET qProcessed = 5 WHERE SKU = '$SKUToProcess'";
	GetResultSet($con32, $rsUpdateProcessed, $SQLUpdateProcessed);
	$processedLinks=file_get_contents("/opt/web/lgq/BananaPants/retailers/links/sears/".$SKUToProcess."_links.txt");
	$processedLinksArray=(explode(',',$processedLinks));
	
	foreach($processedLinksArray as $link){
		$fullLink='http://www.sears.com'.$link;
		echo $fullLink."\n";
		if(strpos($fullLink, "http://www.sears.com/") === FALSE){
		} else {
			if(strpos($fullLink, "/p-") === FALSE){
			} else {
				$SQLInsertLinks = "INSERT INTO lgqtest.tblRetailertestlink (Date, Link, RetailerID, qProcessed) VALUES ('$date', '$fullLink', 3, 3)";
				GetResultSet($con32, $rsInsertLinks, $SQLInsertLinks);
			}
		}
	}
}
/*$SQLUpdateProcessed = "UPDATE lgqtest.tblSKUsForSears SET qProcessed = 0 WHERE qProcessed = 5";
GetResultSet($con31, $rsUpdateProcessed, $SQLUpdateProcessed);*/
?>
