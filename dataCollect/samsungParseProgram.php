<?php
$location="lgq.lgq3-prices.internal.stevensoncompany.com";
set_time_limit(0);
ini_set('include_path', '/opt/web/lgq/BananaPants');
include("include/config.php");                                         
echo "Start\n";
//This shell_exec is building a new parsing list (names of web pages to parse) 
shell_exec("ls -t /opt/web/lgq/BananaPants/brands/pagesDownloaded/samsung | sed 's/$/,/' | tr -d \" \t\n\r\" | sed -e 's/\.txt//g' > /opt/web/lgq/BananaPants/brands/parseList/samsungParseList.txt");
$linkPagesToProcess = file_get_contents("/opt/web/lgq/BananaPants/brands/parseList/samsungParseList.txt");
$arrayPagesToProcess = (explode(',', $linkPagesToProcess));
$popEmptyElement=array_pop($arrayPagesToProcess);
//shell_exec to remove existing price data pages before doing a new run
shell_exec("rm /opt/web/lgq/BananaPants/brands/priceData/samsung/*");

foreach($arrayPagesToProcess as $pageToProcess){
	//regexVar1 searches for link using keyword 'canonical', then removing any other text or whitespace.
	$regexVar1 = "| grep 'canonical' | sed 's/.*canonical\" href=\"//' | sed 's/\">.*//' | head -1";
	/*regexVar2 searches for SKU and price by capturing a block of text (starting with 'product-details__info epp-product' and ending with 'product-details__info-saved'), then uses grep to grab the lines that contain
	*'after epp-price' (for price data) and 'info-sku' (for SKU data). then deletes any other whitespace or text.*/
	$regexVar2 = "| sed -n '/product-details__info epp-product/,/product-details__info-saved/p' | grep -e \"after epp-price\" -e \"info-sku\" | sed 's/.* content=\"//' | sed 's/.*sku\">//' | sed 's/<\/h2>//' | sed 's/.*\"price\">//' | sed 's/\">.*//' | sed 's/,//' | sed 's/<\/span>//'";
	$date = date('Y-m-d H:i:s');
	//shell_exec parses the downloaded web page with the criteria in regexVar1 and stores the results in a 'temp_' file
	shell_exec("cat /opt/web/lgq/BananaPants/brands/pagesDownloaded/samsung/".$pageToProcess.".txt ".$regexVar1." > /opt/web/lgq/BananaPants/brands/priceData/samsung/".$pageToProcess."_temp.txt");
	//shell_exec parses the downloaded web page with the criteria in regexVar2 and stores the results in a 'temp_' file
	shell_exec("cat /opt/web/lgq/BananaPants/brands/pagesDownloaded/samsung/".$pageToProcess.".txt ".$regexVar2." >> /opt/web/lgq/BananaPants/brands/priceData/samsung/".$pageToProcess."_temp.txt");
	//This shell_exec takes all of the text that is in the 'temp_' file and places it in another '.txt' file (data in file is used in an array later)
	shell_exec("cat /opt/web/lgq/BananaPants/brands/priceData/samsung/".$pageToProcess."_temp.txt | sed 's/$/,/' | tr -d \" \t\n\r\" > /opt/web/lgq/BananaPants/brands/priceData/samsung/".$pageToProcess.".txt");
	//This shell_exec deletes 'temp_' file(s).
	shell_exec("rm /opt/web/lgq/BananaPants/brands/priceData/samsung/".$pageToProcess."_temp.txt");
	$priceSKULink = file_get_contents("/opt/web/lgq/BananaPants/brands/priceData/samsung/".$pageToProcess.".txt");
	$arrayPriceSKULink = (explode(",",$priceSKULink));
	$link=$arrayPriceSKULink[0];
	$SKU=@$arrayPriceSKULink[1];
	$SKU=substr($SKU,0,strpos($SKU,'/'));
	$price=@$arrayPriceSKULink[2];
	if(preg_match('/[a-zA-Z]/', $price) > 0){
		$price = '';	
	}
	if($price != "" && $SKU != ""){
		$SQLInsertPriceData = "INSERT INTO lgqtest.tblBrandPrices (LinkID, Date, MinPrice, BrandID, SKU, Link, qStatus, note) VALUES ('$pageToProcess', '$date', '$price', 620, '$SKU', '$link', 3, 'Sku is Good')";
		GetResultSet($con32,$rsInsertPriceData,$SQLInsertPriceData);
	} elseif($price != "" || $SKU != "") {
		$SQLInsertPriceData = "INSERT INTO lgqtest.tblBrandPrices (LinkID, Date, MinPrice, BrandID, SKU, Link, qStatus) VALUES ('$pageToProcess', '$date', '$price', 620, '$SKU', '$link', 3)";
		GetResultSet($con32,$rsInsertPriceData,$SQLInsertPriceData);
	} else {
		echo "No Price or SKU data found for LinkID - ".$pageToProcess."\n";
	}
	
	$sqlGrabSkuAttributes = "SELECT * FROM lgqtest.tblSKUs WHERE SKU = '$SKU'";
	GetResultSet($con32,$rsGrabSkuAttributes,$sqlGrabSkuAttributes);
	$rowSkuAttributes =  mysqli_fetch_assoc($rsGrabSkuAttributes);
	$categoryIDAttribute = $rowSkuAttributes['CategoryID'];
	$productIDAttribute = $rowSkuAttributes['ProductID'];
	$brandIDAttribute = $rowSkuAttributes['BrandID'];
	if($brandIDAttribute != "620"){
		$brandIDAttribute = "620";
	}
	if($link != ""){
		$sqlBrandLinkDuplicateCheck = "SELECT * FROM lgqtest.tblBrandLinks WHERE SKU = '$SKU'";
		GetResultSet($con32,$rsBrandLinkDuplicateCheck,$sqlBrandLinkDuplicateCheck);
		$rowBrandLinkDuplicateCheck = mysqli_fetch_assoc($rsBrandLinkDuplicateCheck);
		$categoryIDDuplicateCheck = $rowBrandLinkDuplicateCheck['CategoryID'];
		$productIDDuplicateCheck = $rowBrandLinkDuplicateCheck['ProductID'];
		$skuDuplicateCheck = $rowBrandLinkDuplicateCheck['SKU'];
		$brandIDDuplicateCheck = $rowBrandLinkDuplicateCheck['BrandID'];
		$brandLinkDuplicateCheck = $rowBrandLinkDuplicateCheck['BrandLink'];
		
		if($SKU == $skuDuplicateCheck){
			echo "Found duplicate entry for SKU - ".$SKU."\n";
			if($price != ""){
				$sqlUpdateManufactureLink = "UPDATE lgqtest.tblBrandLinks SET BrandLink='$link', BrandUpdateTime='$date', MinPrice='$price' WHERE SKU='$skuDuplicateCheck' and BrandID='$brandIDAttribute'";
				GetResultSet($con32, $rsUpdateManufactureLink, $sqlUpdateManufactureLink);
			} else {
					$sqlUpdateManufactureLink = "UPDATE lgqtest.tblBrandLinks SET BrandLink='$link', BrandUpdateTime='$date', MinPrice='N/A' WHERE SKU='$skuDuplicateCheck' and BrandID='$brandIDAttribute'";
					GetResultSet($con32, $rsUpdateManufactureLink, $sqlUpdateManufactureLink);
			}
		} else {				
			if($price != ""){
				$sqlInsertManufactureLink = "INSERT INTO lgqtest.tblBrandLinks (CategoryID, ProductID, SKU, BrandID, BrandLink, BrandUpdateTime, MinPrice) VALUES('$categoryIDAttribute', '$productIDAttribute', '$SKU', '$brandIDAttribute', '$link', '$date', '$price')";
				GetResultSet($con32, $rsInsertManufactureLink, $sqlInsertManufactureLink);
			} else {
				$sqlInsertManufactureLink = "INSERT INTO lgqtest.tblBrandLinks (CategoryID, ProductID, SKU, BrandID, BrandLink, BrandUpdateTime, MinPrice) VALUES('$categoryIDAttribute', '$productIDAttribute', '$SKU', '$brandIDAttribute', '$link', '$date', 'N/A')";
				GetResultSet($con32, $rsInsertManufactureLink, $sqlInsertManufactureLink);
			}   
			echo "SKU - ".$SKU." - Inserted into tblBrandLinks\n";
		}
	}
} 
$SQLResetSamsungLinksqProcessed = "UPDATE lgqtest.tblLinksForSamsung SET qProcessed = 0 WHERE qProcessed = 5";
GetResultSet($con32,$ResetSamsungLinksqProcessed,$SQLResetSamsungLinksqProcessed);

?>
