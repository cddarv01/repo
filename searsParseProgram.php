<?php
$location = "lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0);
$date = date('Y-m-d H:i:s'); 
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");                                         
echo "Start\n";
$dateMinusWeekProcess=strtotime("$date -1 week");
$dateMinusWeek = date("Y-m-d H:i:s", $dateMinusWeekProcess);
$SQLAllSKUsToProcess = "select * from lgqtest.tblSKUsForSears";
GetResultSet($con32, $rsAllSKUsToProcess, $SQLAllSKUsToProcess);
/*
//FOR iMACROS PARSING
$rowToProcess = mysqli_fetch_assoc($rsSKUToProcess);
*/
//This shell_exec is creating a list of all the files (webpages) to parse from '/opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears', to get Link, SKU, and Price data. 
shell_exec("ls -t /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears | sed 's/$/,/' | tr -d \" \t\n\r\" | sed -e 's/\.txt//g' > /opt/web/lgq/BananaPants/retailers/parseLists/SearsPagesToProcess.txt");
$linkPagesToProcess = file_get_contents("/opt/web/lgq/BananaPants/retailers/parseLists/SearsPagesToProcess.txt");
$arrayPagesToProcess = (explode(',', $linkPagesToProcess));
$popEmptyElement=array_pop($arrayPagesToProcess);
/*
//FOR iMACROS PARSING
while($rowToProcess = mysqli_fetch_assoc($rsAllSKUsToProcess)){
*/
foreach($arrayPagesToProcess as $pageLinkID){
	$pageIDVar = "$pageLinkID";
	echo $pageIDVar."\n";
	//regexVar1 is used to find the Link, SKU, and Price from the webpage that has been downloaded. It uses [grep -e 'canonical'] as a keyword to find the Link, [-e 'Model'] to find SKU, and [-e 'price-wrapper'] for prices.
	$regexVar1 = "| grep -e 'canonical' -e 'Model #' -e 'price-wrapper'"; 
	/*regexVar2 is used to clean up the remaining text (html tags, css, etc.) that is left after regexVar1 has pulled the lines that contain the keywords "canonical", "Model", and "price-wrapper". 
	*[sed 's/<\/span>//'] removes all </span> tags
	*[sed 's/<\/small>//'] removes all </small> tags
	*[sed 's/.*l\">//'] removes any text, special characters, or spaces that come before l"
	*[sed 's/.*href=\"//'] removes any text, special characters, or spaces that come before href="
	*[sed 's/\">//'] removes all ">
	*[sed 's/.*\\\$//'] removes any text, special characters, or spaces that come before $
	*[sed 's/<script.*\//'"] removes any text, special characters, or spaces that come after <script*/
	$regexVar2 = "| sed 's/<\/span>//' | sed 's/<\/small>//' | sed 's/.*l\">//' | sed 's/.*href=\"//' | sed 's/\">//' | sed 's/.*\\\$//' | sed 's/<script.*//'";
	/*
	//FOR iMACROS PARSING
	$SKUToProcess = $rowToProcess['SKU'];
	//echo "<br>".$SKUToProcess;
	//echo $SKUToProcess."\n";
	
	shell_exec("cat /opt/web/lgq/BananaPants/webDownloads/".$rowToProcess['SKU'].".htm ".$regexVar1." ".$regexVar2." > /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_temp.txt");
	shell_exec("head -2 /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_temp.txt > /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_temp2.txt");
	shell_exec("tail -2 /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_temp.txt >> /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_temp2.txt");
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_temp2.txt | awk '!seen[$0]++' > /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_priceData.txt");
	shell_exec("rm /opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_temp*");
	$priceSKULink = file_get_contents("/opt/web/lgq/BananaPants/retailers/priceData/sears/".$rowToProcess['SKU']."_priceData.txt");
	*/
	//The 1st shell_exec uses regexVar1 and regexVar2 to parse the webpage that has been download and put the results in '/opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar_temp.txt'
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/'$pageIDVar'.txt ".$regexVar1." ".$regexVar2." > /opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar"."_temp.txt");
	//The 2nd shell_exec takes the top 2 lines of '/opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar_temp.txt' and inserts them in '/opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar_temp2.txt'
	shell_exec("head -2 /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp.txt > /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp2.txt");
	//The 3rd shell_exec takes the last 2 lines of '/opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar_temp.txt' and inserts them in '/opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar_temp2.txt' 
	shell_exec("tail -2 /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp.txt >> /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp2.txt");
	/*The 4th shell_exec places a ',' at the end of each line with [sed s/$/,/], checks for duplicates with [awk '!seen[$0]++'], and removes all whitespace with [tr -d \" \t\n\r\"], then inserts the results in
	'/opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar_priceData.txt'
	The results will be made into an array and delimited by ',' */
	shell_exec("cat /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp2.txt | sed s/$/,/ | awk '!seen[$0]++' | tr -d \" \t\n\r\" > /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_priceData.txt");
	//The 5th shell_exec deletes parsing temp files $pageIDVar_temp in '/opt/web/lgq/BananaPants/retailers/priceData/sears/'
	shell_exec("rm /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp*");
	
	$priceSKULink = file_get_contents("/opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_priceData.txt");
	$arrayPriceSKULink = (explode(",",$priceSKULink));
	$link=@$arrayPriceSKULink[0];
	$SKU=@$arrayPriceSKULink[1];
	$price=@$arrayPriceSKULink[2];
	$maxPrice=@$arrayPriceSKULink[3];
	
	/*
	//IN PROGRESS: TO REMOVE PHP NOTICE - UNDEFINED OFFSET - EMPTY ARRAY ISSUE FROM $arrayPriceSKULink explode PROCESS
	$arrayCounter = count($arrayPriceSKULink);
	switch($arrayCounter){
		case ($arrayCounter == 0):
			$link=$arrayPriceSKULink[0];
		case ($arrayCounter == 1):
			$link=$arrayPriceSKULink[0];
			$SKU=$arrayPriceSKULink[1];
		case ($arrayCounter >= 2):	
			$link=$arrayPriceSKULink[0];
			$SKU=$arrayPriceSKULink[1];
			$price=$arrayPriceSKULink[2];
	}
	*/
	
	if(preg_match('/[a-zA-Z]/', $price) == 1){
		/*The 4 shell_exec commands below is similar to the shell_exec commands above, except the 2nd shell_exec here takes the first 3 lines and there is no shell_exec with 'tail'. 
		This is because the page that is being parsed does not have have multiple prices. Therefore, the results from using regexVar1 and regexVar2, along with 'head -3', give a single link, SKU, and price*/
		shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/'$pageIDVar'.txt ".$regexVar1." ".$regexVar2." > /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp.txt");
		shell_exec("head -3 /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp.txt > /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp2.txt");
		shell_exec("cat /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp2.txt | sed s/$/,/ | awk '!seen[$0]++' | tr -d \" \t\n\r\" > /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_priceData.txt");
		shell_exec("rm /opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_temp*");
		$priceSKULink = file_get_contents("/opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_priceData.txt");
		$arrayPriceSKULink = (explode(",",$priceSKULink));
		$price = $arrayPriceSKULink[2];
		if(preg_match('/[a-zA-Z]/', $price) == 1){
			/*regexVar3 is used to capture red (color) sales prices on sears.com. Basically, if the first two series of shell_exec commands (lines 52-63 and 91-94) return letters in the $price variable, regexVar3 looks for red sales prices.
			*[sed -n '/redSale/,/h4/p'] grabs a block of text, starting with the keyword 'redSale' and ending at the second keyword 'h4'
			*[grep \\\$] looks for '$' in the resulting block of text.
			*[sed 's/.*\$//'] deletes all text and whitespace before '$' (including '$')
			*[sed 's/<\/span>//'] deletes all </span> 
			*[sed 's/$/,/'] adds ',' to the end of the remain line(s) */
			$regexVar3 = "| sed -n '/redSale/,/h4/p' | grep \\\$ | sed 's/.*\$//' | sed 's/<\/span>//' | sed 's/$/,/'";
			/*This shell_exec takes regexVar3 and parses the page '/opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/$pageIDVar.txt' and inserts the result in to 
			'/opt/web/lgq/BananaPants/retailers/priceData/sears/$pageIDVar_priceData2.txt'*/
			shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/'$pageIDVar'.txt ".$regexVar3." > "."$pageIDVar"."_priceData2.txt");
			$priceAttempt2 = file_get_contents("/opt/web/lgq/BananaPants/retailers/priceData/sears/"."$pageIDVar"."_priceData2.txt");
			$arrayPriceAttempt2 = (explode(",",$priceAttempt2));
			$price = $arrayPriceAttempt2[0];
			if(preg_match('/[a-zA-Z]/', $price) == 1){
				$price = '';	
			}
		}
	}
	if(preg_match('/[a-zA-Z]/', $price) == 1 or empty($price)){
		/*regexVar4 is used if the previous attempts of retrieving a price is unsuccessful. 
		*[sed -n '/price-big/,/<\//p'] grabs block of text, starting with 'price-big' and end with '//p'
		*[sed 's/.*\\\$//'] delete all whitespace and text before '$'
		*[sed 's/<.*\//'] delete all whitespace and text after '<'
		*[sed 's/,//'] remove all ','
		*[tr -d \" \t\n\r\"] remove all whitespace (tabs, newlines, spaces, etc.) */
		$regexVar4 = "| sed -n '/price-big/,/<\//p' | sed 's/.*\\\$//' | sed 's/<.*//' | sed 's/,//' | tr -d \" \t\n\r\"";
		$price = shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/'$pageIDVar'.txt ".$regexVar4);
	}
	if(empty($price)){
		if(!empty($maxPrice)){
			$price = $maxPrice;
			$maxPrice = NULL;
		}
	}
	if(empty($maxPrice)){
		/*regexVar5 is used to capture a Max Price if $maxPrice is empty after the first attempt.
		*[grep -e 'Regular price'] searches for keyword 'Regular price'
		*[sed 's/.*\\\$//'] removes all text and whitespace before '$' (including '$')
		*[sed 's/<.*\//'] removes all text and whitespace after '<' (including '<')
		*[sed 's/,//'] removes all ',' */
		$regexVar5 = "| grep -e 'Regular price' | sed 's/.*\\\$//' | sed 's/<.*//' | sed 's/,//'";
		$maxPrice = shell_exec("cat /opt/web/lgq/BananaPants/retailers/pagesDownloaded/sears/'$pageIDVar'.txt ".$regexVar5);
	}
	if(preg_match('/[a-zA-Z]/', $maxPrice) == 1){
		$maxPrice = '';	
	}
	if(preg_match('/[a-zA-Z]/', $price) == 1){
		$price = '';	
	}
	/*$SQLInsertRetailerLink = "INSERT INTO lgqtest.tblRetailertestlink (Date, Link, RetailerID, qProcessed) VALUES ('$date', '$link', 3, 1)";
	GetResultSet($con31, $rsInsertRetailerLink, $SQLInsertRetailerLink);
	$SQLSelectLinkID = "SELECT LinkID FROM lgqtest.tblRetailertestlink WHERE Link = '$link'";
	GetResultSet($con31, $rsSelectLinkID, $SQLSelectLinkID);
	$selectedLinkID = mysqli_fetch_assoc($rsSelectLinkID);
	$linkID = $selectedLinkID['LinkID'];
	if(isset($link) && isset($SKU) && isset($price)){
		$SQLInsertGoodLink = "INSERT INTO lgqtest.tblRetailer_goodLink (Date, Link, RetailerID, LinkID) VALUES ('$date', '$link', 3, '$linkID')";
		GetResultSet($con31, $rsInsertGoodLink, $SQLInsertGoodLink);
	}
	*/
	//this shell_exec removes all file ending with '_priceData2.txt' from /opt/web/lgq/BananaPants/retailers/Programs/parsePrograms/
	shell_exec("rm /opt/web/lgq/BananaPants/retailers/Programs/parsePrograms/*_priceData2.txt");
	$SQLSKUCheck = "SELECT SKU FROM lgqtest.tblSKUsForSears WHERE SKU like '%$SKU%'";
	GetResultSet($con32, $rsSQLSKUCheck, $SQLSKUCheck);
	$SKUisGood = "Sku is good";
	$SKUCheck = mysqli_fetch_assoc($rsSQLSKUCheck);
	if($SKUCheck > 0){
		$SQLInsertPriceData = "INSERT INTO lgqtest.tblRetailerPrices (LinkID, Date, Price, MaxPrice, RetailerID, SKU, Link, qStatus, note) VALUES ('$pageIDVar', '$date', '$price', '$maxPrice', 3, '$SKU', '$link', 3, '$SKUisGood')";
		GetResultSet($con32, $rsInsertPriceData, $SQLInsertPriceData);
	} else {
		$SQLInsertPriceData = "INSERT INTO lgqtest.tblRetailerPrices (LinkID, Date, Price, MaxPrice, RetailerID, SKU, Link) VALUES ('$pageIDVar', '$date', '$price', '$maxPrice', 3, '$SKU', '$link')";
		GetResultSet($con32, $rsInsertPriceData, $SQLInsertPriceData);
	}
	echo $SQLInsertPriceData."\n";
	
	/*$sqlGrabSkuAttributes = "SELECT * FROM tblSKUs WHERE SKU = '$SKU'";
	GetResultSet($con32,$rsGrabSkuAttributes,$sqlGrabSkuAttributes);
	$rowSkuAttributes =  mysqli_fetch_assoc($rsGrabSkuAttributes);
	$categoryIDAttribute = $rowSkuAttributes['CategoryID'];
	$productIDAttribute = $rowSkuAttributes['ProductID'];
	$brandIDAttribute = $rowSkuAttributes['BrandID'];
	
	if($link != ""){
		$sqlBrandLinkDuplicateCheck = "SELECT * FROM lgqtest.BrandLinks WHERE SKU = '$SKU'";
		GetResultSet($con32,$rsBrandLinkDuplicateCheck,$sqlBrandLinkDuplicateCheck);
		$rowBrandLinkDuplicateCheck = mysqli_fetch_assoc($rsBrandLinkDuplicateCheck);
		$categoryIDDuplicateCheck = $rowBrandLinkDuplicateCheck['CategoryID'];
		$productIDDuplicateCheck = $rowBrandLinkDuplicateCheck['ProductID'];
		$skuDuplicateCheck = $rowBrandLinkDuplicateCheck['SKU'];
		$brandIDDuplicateCheck = $rowBrandLinkDuplicateCheck['BrandID'];
		$brandLinkDuplicateCheck = $rowBrandLinkDuplicateCheck['BrandLink'];
		
		if($SKU == $skuDuplicateCheck){
			echo "Found duplicate entry for SKU - ".$SKU."\n";
		} else {				
			if($price != ""){
				$sqlInsertManufactureLink = "INSERT INTO lgqtest.tblBrandLinks (CategoryID, ProductID, SKU, BrandID, BrandLink, BrandUpdateTime, Note) VALUES('$categoryIDAttribute', '$productIDAttribute', '$SKU', '$brandIDAttribute', '$link', '$date', '$price')";
				GetResultSet($con32, $rsInsertManufactureLink, $sqlInsertManufactureLink);
			} else {
				$sqlInsertManufactureLink = "INSERT INTO lgqtest.tblBrandLinks (CategoryID, ProductID, SKU, BrandID, BrandLink, BrandUpdateTime, Note) VALUES('$categoryIDAttribute', '$productIDAttribute', '$SKU', '$brandIDAttribute', '$link', '$date', 'N/A')";
				GetResultSet($con32, $rsInsertManufactureLink, $sqlInsertManufactureLink);
			}   
			echo "SKU - ".$SKU." - Inserted into tblBrandLinks\n";
		}
	}*/
	
}
?>