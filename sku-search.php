<?php
/**
NOTE:  You should not be entering this page without some feature parameters already being set.
**/

//Set defaults.
$btnSubmit="";
$frmApplyPriceRange=0;
$frmCategoryID=0;
$frmProductID=0;
$frmFeatureIDValues=array();
$frmSeriesIDValues=array();

$frmBrandValues=array();

$arrFeatures=array();
$arrFeatureIDs=array();
$arrFeatureID_sqlListWhere = array();

$frmSKUsPerPage = 20;
$frmChgSKUsPerPage = 0;
$frmSKUPage = 1;
$frmSKUListOrderBy="CurrentAvgRetailerPrice DESC";

$frmShowRetailers=0;

$SearchLimitMin=0;
$SearchLimitMax=20;

$frmSKU2Delete = "";

$arrCompareSKUs = array();
$cntCompareSKUs = 0;
$MaxCompareSKUs = 6;

$strLowPrice="";

$frmMinRetailerPrice=-1;
$frmMaxRetailerPrice=999999;

$frmCheckAllFeatureID = 0;
$frmUnCheckAllFeatureID = 0;

$strLGQParameters = "";
$arrLGQProds = array();
$arrLGQProds[] = 11;
$arrLGQProds[] = 10;
$arrLGQProds[] = 5;
$arrLGQProds[] = 12;
$arrLGQProds[] = 4;
$arrLGQProds[] = 3;
$arrLGQProds[] = 9;
$arrLGQProds[] = 7;
$arrLGQProds[] = 1;
$arrLGQProds[] = 2;

$frmSKU = "";
$frmLGQSKU = "";
$frmPinLGQSKU = 0;

$btnSubmit2 = "";

$frmShowFeatureID = "";

//include the generic config.
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/functions/DisplaySKUSummary.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/functions/GetCategoryProduct.php');

function form_select_selected($fcnVar, $fcnValue) {
	$str = '';
	if($fcnVar === $fcnValue) {
		$str = ' selected="selected"';
	} else {
		$str = '';
	}
	return $str;
}

//Set the reset string.  This is used to remove all check boxes and return to the first query for this category/product
if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],"sku-configuration-splash.php") >0) {
	$_SESSION["ResetQueryString"] = $_SERVER['QUERY_STRING'];
} else {
	if(!isset($_SESSION["arrFirstConfig"])) {
			$_SESSION["arrFirstConfig"] = array();
			$sqlCatProds = "select CategoryID, ProductID from lgq.vw_categories_products order by CategoryID, ProductID";
			GetResultSet($dbconn, $rsCatProds, $sqlCatProds);
			while($rowCatProds = mysqli_fetch_assoc($rsCatProds)) {
				$sqlFirstConfig = sprintf("select ConfigurationID from lgq.vw_sku_configuration
									where CategoryID=%s and ProductID=%s
									order by ConfigurationID limit 1"
									, $rowCatProds["CategoryID"], $rowCatProds["ProductID"]);
				GetResultSet($dbconn, $rsFirstConfig, $sqlFirstConfig);
				$rowFirstConfig = mysqli_fetch_assoc($rsFirstConfig);
				$_SESSION["arrFirstConfig"][ $rowCatProds["CategoryID"] ][ $rowCatProds["ProductID"] ] = $rowFirstConfig["ConfigurationID"];
			}
	}

	$strFirstConfig = "&frmFeatureIDValues[]=1200|".$_SESSION["arrFirstConfig"][$frmCategoryID][$frmProductID];
	$_SESSION["ResetQueryString"] = sprintf("btnSubmit=Search&frmCategoryID=%s&frmProductID=%s&frmFeatureIDValues[]=1300|1".$strFirstConfig."&frmShowRetailers=0&frmPinLGQSKU=0&btnSubmit=Search"
									, $frmCategoryID, $frmProductID);
}

//Set qPrev=qCur in tblBrandSeries
$tblBrandSeriesTbl = "tblBrandSeries_".$_COOKIE["PHPSESSID"];
$tblBrandSeries = "lgq_sessions.".$tblBrandSeriesTbl;
$sqlUpdtBrandSeries = sprintf("update %s set qPrev=qCurr where CategoryID=%s and ProductID=%s", $tblBrandSeries, $frmCategoryID, $frmProductID);
GetResultSet($dbconn, $rsUpdtBrandSeries, $sqlUpdtBrandSeries);
//qPrev and qCurr will be set to 0 when the LG&Q is pressed.

$QryStr = $_SERVER['QUERY_STRING'];

//If there is nothing in btnSubmit or frmCategoryID or frmProductID are not properly set, then go to the index page.
if($btnSubmit == "" || $frmCategoryID == 0 || $frmProductID == 0) {
	header('Location: /index.php');
} else {
	//Get the name of the Category and the Product
	$sqlCatProd = sprintf("select Category, CategoryFileAbbr, Product, ProductFileAbbr from lgq.vw_categories_products
						where CategoryID=%s and ProductID=%s"
						, $frmCategoryID, $frmProductID);
	GetResultSet($dbconn, $rsCatProd, $sqlCatProd);

	//If you get no records, return to index page.
	if(mysqli_num_rows($rsCatProd) == 0) {
		header('Location: /index.php');
	} else {
		$rowCatProd = mysqli_fetch_assoc($rsCatProd);
		$Category = $rowCatProd["Category"];
		$CategoryFileAbbr = $rowCatProd["CategoryFileAbbr"];
		$Product = $rowCatProd["Product"];
		$ProductFileAbbr = $rowCatProd["ProductFileAbbr"];
	}
}
//echobr($btnSubmit);
if($btnSubmit == "Apply Price Range") {
	if($frmMinRetailerPrice > $frmMaxRetailerPrice) {
		$tmpPrice = $frmMinRetailerPrice;
		$frmMinRetailerPrice = $frmMaxRetailerPrice;
		$frmMaxRetailerPrice = $tmpPrice;
		unset($tmpPrice);
	}

	//echobr($btnSubmit." : ".$frmMinRetailerPrice);
	if($frmMinRetailerPrice == 0) {
		$frmMinRetailerPrice = 1;
	}
	//echobr($btnSubmit." : ".$frmMinRetailerPrice);

	$frmApplyPriceRange = 1;
	//echobr($QryStr);
	$QryStr = str_replace("&btnSubmit=Apply+Price+Range", "", $QryStr);
	$QryStr = str_replace("&frmApplyPriceRange=0", "&frmApplyPriceRange=1", $QryStr);
	//echobr($QryStr);
	$btnSubmit = "Search";
}

if($frmApplyPriceRange == 1) {
	//Take the min and max retailer price and turn it into a ID|Value pair
	$frmFeatureIDValues[] = "1360|".$frmMinRetailerPrice;
	$frmFeatureIDValues[] = "1360|".$frmMaxRetailerPrice;
}



//Build a Base Query String
$BaseQueryString="frmCategoryID=".$frmCategoryID."&frmProductID=".$frmProductID."&frmShowRetailers=".$frmShowRetailers."&frmPinLGQSKU=".$frmPinLGQSKU."&frmLGQSKU=".$frmLGQSKU;

if($btnSubmit == "LGQ") {
	$frmFeatureIDValues=array();
	$sqlLGQFeatureValues = sprintf("select a.FeatureID, a.Feature, a.FeatureValue, a.FeatureLabel from lgq.vw_sku_feature_values as a
							, lgq.xrefLGQFeatures as b
							where a.CategoryID=%s and a.ProductID=%s and a.SKU='%s'
							and a.CategoryID=b.CategoryID and a.ProductID=b.ProductID and a.FeatureID=b.FeatureID order by a.Feature"
							, $frmCategoryID, $frmProductID, $frmLGQSKU);
	//echobr($sqlLGQFeatureValues);
	GetResultSet($dbconn, $rsLGQFeatureValues, $sqlLGQFeatureValues);
	$cntLGQFeatures = mysqli_num_rows($rsLGQFeatureValues);
	$p = 0;
	while($rowLGQFeatureValues = mysqli_fetch_assoc($rsLGQFeatureValues)) {
		$frmFeatureIDValues[] = $rowLGQFeatureValues["FeatureID"]."|".$rowLGQFeatureValues["FeatureValue"];
		if($p > 0) {
			$strLGQParameters .= "; ";
		}
		//If FeatureID = 1300 and FeatureValue = 2 then add the 1's as well
		if($rowLGQFeatureValues["FeatureID"] == 1300 && $rowLGQFeatureValues["FeatureValue"] == 2) {
			$frmFeatureIDValues[] = $rowLGQFeatureValues["FeatureID"]."|1";
			$strLGQParameters .= "<strong>Model Status</strong> - Active &amp; Discontinued Models";
		} else {
			$strLGQParameters .= "<strong>".$rowLGQFeatureValues["Feature"]."</strong> - ".$rowLGQFeatureValues["FeatureLabel"];
		}
		$p++;
	}
	$strLGQParameters .= " <br /><span style='font-style: italic; color: red'>(These are checked on the lefthand side menu.  
							Continue to refine your search by changing the parameters on the lefthand side menu.)</span>";
	
	$QryStr = str_replace("&frmSKU=".$frmSKU, "", $QryStr);
	//$QryStr = str_replace("&btnSubmit=LGQ", "&btnSubmit=Search", $QryStr);
	//echobr($QryStr);
	//$btnSubmit = "Search";
	
	//Set qPrev and qCurr = 0 when the LG&Q button is pressed.  This is to reset to default so that no previous settings are lingering.
	$sqlUpdtBrandSeries = sprintf("update %s set qPrev=0, qCurr=0 where CategoryID=%s and ProductID=%s", $tblBrandSeries, $frmCategoryID, $frmProductID);	 
}

//TO DO:  Need a check to make sure the price range entered is actually a number.  If not, send alert

//Go ahead and set the qCurr for BrandIDs where Series=0, e.g. if the individual Brand has been clicked.

//Convert the frmFeatureIDValues to arrFeatures
foreach($frmFeatureIDValues as $strFeatureIDValue) {
	$arrTemp=array();
	$arrTemp = explode("|",$strFeatureIDValue);
	//if frmUnCheckAllFeatureID = $arrTemp[0] then don't add it to the array
	if($frmUnCheckAllFeatureID != $arrTemp[0]) {
		$arrFeatures[$arrTemp[0]][] = $arrTemp[1];
		$BaseQueryString .= "&frmFeatureIDValues[]=".$strFeatureIDValue;
	}
}

//TO DO: How to handle this, $frmUnCheckAllFeatureID != $arrTemp[0], below???
//echobr(urldecode($QryStr));

//Check the status of the brands that were checked qPrev=1.
$sqlBrands = sprintf("select BrandID from %s where CategoryID=%s and ProductID=%s and SeriesID=0 and qPrev=1" 
					, $tblBrandSeries, $frmCategoryID, $frmProductID);
GetResultSet($dbconn, $rsBrands, $sqlBrands);
$arrFeatures[1509] = array();
//echoarr($arrFeatures[1509]);
while($rowBrands = mysqli_fetch_assoc($rsBrands)) {
	//See if the Brand IS NOT in the frmBrandValues array NOW, since qPrev=1 it must have been before
	if(!in_array($rowBrands["BrandID"], $frmBrandValues) || count($frmBrandValues) == 0) {
		//set the qCurr=0 for all BrandIDs, and then remove the Series from the BrandIDs
		$sqlUpdtBrand = sprintf("Update %s set qCurr=0 where CategoryID=%s and ProductID=%s and BrandID=%s and SeriesID=0" 
							, $tblBrandSeries, $frmCategoryID, $frmProductID, $rowBrands["BrandID"]);
		GetResultSet($dbconn, $rsUpdtBrand, $sqlUpdtBrand);
		$sqlSeries = sprintf("select SeriesID from %s where CategoryID=%s and ProductID=%s and BrandID=%s and SeriesID>0"
							, $tblBrandSeries, $frmCategoryID, $frmProductID, $rowBrands["BrandID"]);
		GetResultSet($dbconn, $rsSeries, $sqlSeries);
		while($rowSeries = mysqli_fetch_assoc($rsSeries)) {
			$sqlUpdtSeries = sprintf("Update %s set qCurr=0 where CategoryID=%s and ProductID=%s and BrandID=%s and SeriesID=%s" 
								, $tblBrandSeries, $frmCategoryID, $frmProductID, $rowBrands["BrandID"], $rowSeries["SeriesID"]);
			GetResultSet($dbconn, $rsUpdtSeries, $sqlUpdtSeries);
			//echobr($rowSeries["SeriesID"]);
			if(array_key_exists(1509, $arrFeatures)) {
				if(in_array($rowSeries["SeriesID"], $arrFeatures[1509])) {
					$key = array_search($rowSeries["SeriesID"], $arrFeatures[1509]);
					//echobr($key);
					unset($arrFeatures[1509][$key]);
					$QryStr = str_replace("&frmFeatureIDValues[]=1509|".$rowSeries["SeriesID"], "", $QryStr);	
					$BaseQueryString = str_replace("&frmFeatureIDValues[]=1509|".$rowSeries["SeriesID"], "", $BaseQueryString);
				}
			}
		}	
	}
}
//echoarr($arrFeatures[1509]);

//If frmBrandValues is not empty, then loop through and set the Series for that entire brand
//If frmBrandValues DID have stuff, but a series was checked, then the Brand would have been removed from frmBrandValues above
$arrUncheckedBrands = array();
//echoarr($arrFeatures[1509]);
if(count($frmBrandValues) > 0) {
	//loop through and set tblBrandSeries
	for($i=0; $i < count($frmBrandValues); $i++) {
		//Check the qPrev against the current to see if all are checked or still in the header
		//If the Brand is checked and the Prev count != arrFeatures count for the brand, then 
		//Does the sum qPrev = count(*), then check to see if the features are in the array
		//When sum(qPrev) = count(*) then I must have previously checked the Brand, now see if a Series has been unchecked.
		$sqlUpdtBrandSeries = sprintf("update %s set qCurr=(CASE 
													WHEN SeriesID = 0 THEN 1
													ELSE 9 END) 
													where CategoryID=%s and ProductID=%s and BrandID=%s"
								, $tblBrandSeries, $frmCategoryID, $frmProductID, $frmBrandValues[$i]);	
		GetResultSet($dbconn, $rsUpdtBrandSeries, $sqlUpdtBrandSeries);
		
		//Get an array of all possible SeriesIDs
		$sqlSeriesList = sprintf("select SeriesID from %s where CategoryID=%s and ProductID=%s and BrandID=%s and SeriesID > 0 and qCurr = 9" 
								, $tblBrandSeries, $frmCategoryID, $frmProductID, $frmBrandValues[$i]);
		//echobr($sqlSeriesList);
		GetResultSet($dbconn, $rsSeriesList, $sqlSeriesList);
		$arrSeries = array();
		while($rowSeriesList = mysqli_fetch_assoc($rsSeriesList)) {
			$SeriesValue = $rowSeriesList["SeriesID"];
			if(!in_array($SeriesValue,$arrFeatures[1509])) {
				$QryStr .= "&frmFeatureIDValues[]=1509|".$SeriesValue;	
				$BaseQueryString .= "&frmFeatureIDValues[]=1509|".$SeriesValue;	
				$arrFeatures[1509][] = $SeriesValue;
			} else {
				//The series is in the array, so change the qCurr to 1
				$sqlUpdtSeriesStatus = sprintf("update %s set qCurr=1 where CategoryID=%s and ProductID=%s and BrandID=%s and SeriesID=%s"
										, $tblBrandSeries, $frmCategoryID, $frmProductID, $frmBrandValues[$i], $SeriesValue);	
				//echobr($sqlUpdtSeriesStatus);
				GetResultSet($dbconn, $rsUpdtSeriesStatus, $sqlUpdtSeriesStatus);				
			}
		}
		
		//See if there is a series that is now unchecked.
		$sqlSeriesUnChecked = sprintf("select SeriesID from %s where CategoryID=%s and ProductID=%s and BrandID=%s and SeriesID > 0 and qPrev=1 and qCurr=9"
								, $tblBrandSeries, $frmCategoryID, $frmProductID, $frmBrandValues[$i]);
		GetResultSet($dbconn, $rsSeriesUnChecked, $sqlSeriesUnChecked);
		if(mysqli_num_rows($rsSeriesUnChecked) > 0) {
			$arrUncheckedBrands[] = $frmBrandValues[$i];		
		}
		while($rowSeriesUnchecked = mysqli_fetch_assoc($rsSeriesUnChecked)) {
			$SeriesUnchecked = $rowSeriesUnchecked["SeriesID"];
			if( in_array($SeriesUnchecked,$arrFeatures[1509]) ) {
				$key = array_search($SeriesUnchecked, $arrFeatures[1509]);
				unset($arrFeatures[1509][$key]);					
				$QryStr = str_replace("&frmFeatureIDValues[]=1509|".$SeriesUnchecked, "", $QryStr);	
				$BaseQueryString = str_replace("&frmFeatureIDValues[]=1509|".$SeriesUnchecked, "", $BaseQueryString);
			}	
		}	
	}		
}
//echoarr($arrFeatures[1509]);
for($i = 0; $i < count($arrUncheckedBrands); $i++) {
	//Change qCurr for the Brand and the Unchecked Series 
	$sqlUpdtBrandSeries = sprintf("Update %s set qCurr=0 where CategoryID=%s and ProductID=%s and BrandID=%s 
								and (SeriesID=0 or (SeriesID > 0 and qCurr != 1) )" 
								, $tblBrandSeries, $frmCategoryID, $frmProductID, $arrUncheckedBrands[$i]);
	GetResultSet($dbconn, $rsUpdtBrandSeries, $sqlUpdtBrandSeries);
	if(in_array($arrUncheckedBrands[$i], $frmBrandValues)) {
		$key = array_search($arrUncheckedBrands[$i], $frmBrandValues);
		unset($frmBrandValues[$key]);	
		$QryStr = str_replace("&frmBrandValues[]=5|".$arrUncheckedBrands[$i], "", $QryStr);	
		$BaseQueryString = str_replace("&frmBrandValues[]=5|".$arrUncheckedBrands[$i], "", $BaseQueryString);
	}
}

if(count($frmBrandValues) == 0) {
	$frmBrandValues=array();	
} else {
	sort($frmBrandValues);	
}

if(array_key_exists(1509, $arrFeatures)) {
	if(count($arrFeatures[1509]) == 0) {
		unset($arrFeatures[1509]);
	} else {
		sort($arrFeatures[1509]);
	}	
}

//echobr(urldecode($QryStr));

//See if 1360, the price, is in frmUnCheckAllFeatureID
if($frmUnCheckAllFeatureID == 1360) {
	$frmApplyPriceRange = 0;
	$frmMinRetailerPrice=-1;
	$frmMaxRetailerPrice=999999;
}

//unset($arrTemp);
$arrFeatureIDs=array_keys($arrFeatures);

//If check all has been clicked, then fill out an array of all possible values
if($frmCheckAllFeatureID > 0) {
	$sqlCheckFeatureValues = "select FeatureValue from lgq.tblFeatureValues where FeatureID =".$frmCheckAllFeatureID;
	GetResultSet($dbconn, $rsCheckFeatureValues, $sqlCheckFeatureValues);
	while($rowCheckFeatureValues = mysqli_fetch_assoc($rsCheckFeatureValues)) {
		//frmCheckAllFeatureID is already in the arrFeatureIDs
		if(in_array($frmCheckAllFeatureID, $arrFeatureIDs)) {
			if(!in_array($rowCheckFeatureValues["FeatureValue"], $arrFeatures[$frmCheckAllFeatureID]) ) {
				$arrFeatures[$frmCheckAllFeatureID][] = $rowCheckFeatureValues["FeatureValue"];
				$BaseQueryString .= "&frmFeatureIDValues[]=".$frmCheckAllFeatureID."|".$rowCheckFeatureValues["FeatureValue"];
			}
		//add the frmCheckAllFeatureID to arrFeatureIDs and arrFeatures
		} else {
			$arrFeatureIDs[] = $frmCheckAllFeatureID;
			$arrFeatures[$frmCheckAllFeatureID][] = $rowCheckFeatureValues["FeatureValue"];
			$BaseQueryString .= "&frmFeatureIDValues[]=".$frmCheckAllFeatureID."|".$rowCheckFeatureValues["FeatureValue"];
		}
	}
}

$BaseQueryString .= "&frmMinRetailerPrice=".$frmMinRetailerPrice."&frmMaxRetailerPrice=".$frmMaxRetailerPrice."&frmApplyPriceRange=".$frmApplyPriceRange;

//Do we have a Min and Max Price set
/*
if( ($frmMinRetailerPrice == -1 && $frmMaxRetailerPrice == 999999) || ($frmMinRetailerPrice=="" && $frmMaxRetailerPrice=="")) {

} else {
	$arrFeatureIDs[]=1360;
}
*/
$cntFeatureIDs = count($arrFeatureIDs);

//Create these variables for use later
	$tblSKUListTbl = "tblSKUs_".$_COOKIE["PHPSESSID"];
	$tblSKUList = "lgq_sessions.".$tblSKUListTbl;

	$tblSKUCompareTbl = "tblSKUCompare_".$_COOKIE["PHPSESSID"];
	$tblSKUCompare = "lgq_sessions.".$tblSKUCompareTbl;
	//Now check to see if the the lgq_settions.tblSKUCompare_session exists, if not create it
/*	if(CheckIfTableExists("lgq_sessions", $tblSKUCompareTbl) == 0) {
		//does not exist so add it.
		echobr("Create compare table".CheckIfTableExists("lgq", $tblSKUCompareTbl));
		CreateTable($dbconn, 0, $tblSKUCompare, "CategoryID int, ProductID int, SKU varchar(32), SKUOrd int default 0
							, PRIMARY KEY(CategoryID, ProductID, SKU, SKUOrd) USING BTREE", "MEMORY", "");
	}
*/
	$sqlShow = "show tables from lgq_sessions like '".$tblSKUCompareTbl."'";
	//echobr($sqlShow);
	GetResultSet($dbconn, $rsShow, $sqlShow);
	//echobr("Rows: ".mysqli_num_rows($rsShow));
	if(mysqli_num_rows($rsShow) == 1) {
		//echobr("DO NOT");
	} else {
		//echobr("DO");
		CreateTable($dbconn, 0, $tblSKUCompare, "CategoryID int, ProductID int, SKU varchar(32), SKUOrd int default 0
							, PRIMARY KEY(CategoryID, ProductID, SKU, SKUOrd) USING BTREE", "MEMORY", "");
	}

//Create somme arrays used below tihin btnSubmit == Search as well as sku-search-left-nav
	$sqlSKUListWhere=sprintf(" where CategoryID=%s and ProductID=%s", $frmCategoryID, $frmProductID);
	$sqlHavingListWhere = "";

	for($i = 0; $i < $cntFeatureIDs; $i++) {
		if($i == 0) {
			$sqlSKUListWhere .= " and ( ";
		} else {
			$sqlSKUListWhere .= " OR ";
		}
		if($arrFeatureIDs[$i] == 1360) {
			//This will be used later to show how many skus there are for a feature, GIVEN, the other feature choices
			//Need the if in case they have put in a price range, then only do SKUs with prices
			$arrFeatureID_sqlListWhere[ $arrFeatureIDs[$i] ] = sprintf(" (FeatureID = %s and (FeatureValue BETWEEN %s and %s) ) "
									, $arrFeatureIDs[$i], $frmMinRetailerPrice, $frmMaxRetailerPrice );
			/*
			if($frmApplyPriceRange == 1) {
				$arrFeatureID_sqlListWhere[ $arrFeatureIDs[$i] ] = sprintf(" (FeatureID = %s and (FeatureValue BETWEEN %s and %s) ) "
										, $arrFeatureIDs[$i], $frmMinRetailerPrice, $frmMaxRetailerPrice );
			} else {
				$arrFeatureID_sqlListWhere[ $arrFeatureIDs[$i] ] = sprintf(" (FeatureID = %s and (FeatureValue BETWEEN %s and %s or FeatureValue=0) ) "
										, $arrFeatureIDs[$i], $frmMinRetailerPrice, $frmMaxRetailerPrice );
			}
			*/
			$sqlSKUListWhere .= $arrFeatureID_sqlListWhere[ $arrFeatureIDs[$i] ];
		} else {
			//This will be used later to show how many skus there are for a feature, GIVEN, the other feature choices
			$arrFeatureID_sqlListWhere[ $arrFeatureIDs[$i] ] = sprintf(" (FeatureID = %s and FeatureValue IN(%s)) "
									, $arrFeatureIDs[$i], implode(",",$arrFeatures[ $arrFeatureIDs[$i] ]) );
			$sqlSKUListWhere .= $arrFeatureID_sqlListWhere[ $arrFeatureIDs[$i] ];
		}

	}

	//Note: $sqlHavingListWhere = "" above, so no else clause is put here
	if($cntFeatureIDs > 0) {
		$sqlSKUListWhere .= ") ";
		if($cntFeatureIDs > 1) {
			$sqlHavingListWhere = "	having count(FeatureID)=".count($arrFeatureIDs);
		}
	}

	//If the $cntFeatureIDs IN(0,1), then there is no need to do a sub-select and no need to include FeatureID in the group by.
	//This complication is done purely for SQL performance reasons
	if($cntFeatureIDs <= 1) {
		$sqlSKUList = sprintf("select CategoryID, ProductID, SKU from lgq.xrefSKUFeatures %s group by CategoryID, ProductID, SKU"
							, $sqlSKUListWhere);
	} else {
		$sqlSKUList = sprintf("select CategoryID, ProductID, SKU from
					(
						select CategoryID, ProductID, SKU, FeatureID from
						lgq.xrefSKUFeatures
						%s
						group by CategoryID, ProductID, SKU, FeatureID
					) as a
					group by CategoryID, ProductID, SKU%s"

					, $sqlSKUListWhere
					, $sqlHavingListWhere);
	}

	//echobr($sqlSKUList);
//Only want to create the table if the actual criteria has changed.  If it hasn't don't spend the overhead
switch($btnSubmit) {
	case "Search":
	case "Apply Price Range":
	case "LGQ":
		//Get the SKUList from the criteris, plus create some other arrays/variables for later use.
		$starttime=microtime(true);
		/*
			select CategoryID, ProductID, SKU from
			(
				select CategoryID, ProductID, SKU, FeatureID from
				lgq.xrefSKUFeatures
				where CategoryID=2 and ProductID=11
				and ( (FeatureID = 1200 and FeatureValue IN(1033,1034))
					OR (FeatureID = 5 and FeatureValue IN(180,580))
					OR (FeatureID = 51 and FeatureValue IN(1))
					OR (FeatureID = 59 and FeatureValue IN(1))
					)
				group by CategoryID, ProductID, SKU, FeatureID
			) as a
			group by CategoryID, ProductID, SKU having count(FeatureID)=4
		*/

			CreateTable($dbconn, 0, $tblSKUList, "CategoryID int, ProductID int, SKU varchar(32)
								, PRIMARY KEY(CategoryID, ProductID, SKU) USING BTREE", "MEMORY", $sqlSKUList);

			GetResultSet($dbconn, $rsAlter, "alter table ".$tblSKUList." add column CurrentBrandPrice decimal(8,2) default NULL
										, add column CurrentAvgRetailerPrice decimal(8,2) default NULL");
			GetResultSet($dbconn, $rsUpdtPrice, "update ".$tblSKUList." as a, lgq.tblSKUs as b
										set a.CurrentBrandPrice=b.CurrentBrandPrice
										, a.CurrentAvgRetailerPrice=b.CurrentAvgRetailerPrice
										where a.CategoryID=b.CategoryID and a.ProductID=b.ProductID and a.SKU=b.SKU");
		$endtime=microtime(true);
		//echobr(1000*($endtime - $starttime));
		//end Get SKUList
	break; //btnSubmit == Search
	case "Add2Compare":
		//How many skus you got right now
		$sqlCnt = sprintf("select Max(SKUOrd) as MaxSKUOrd from %s where CategoryID=%s and ProductID=%s"
						, $tblSKUCompare, $frmCategoryID, $frmProductID);
		GetResultSet($dbconn, $rsCnt, $sqlCnt);
		$rowCnt = mysqli_fetch_assoc($rsCnt);
		if(is_null($rowCnt["MaxSKUOrd"])) {
			$SKUOrd = 0;
		} else {
			$SKUOrd = $rowCnt["MaxSKUOrd"]+1;
		}
		$sqlInsSKU = sprintf("insert into %s(CategoryID, ProductID, SKU, SKUOrd)
							VALUES (%s, %s, '%s', %s)"
						, $tblSKUCompare
						, $frmCategoryID, $frmProductID, $frmSKU2Compare, $SKUOrd);
		GetResultSet($dbconn, $rsInsSKU, $sqlInsSKU);
		$QryStr = str_replace("&btnSubmit=Add2Compare","&btnSubmit=Search",$QryStr);
	break; //btnSubmit == Add2Compare
	case "Delete Compare SKU":
		$sqlDel = sprintf("delete from %s where CategoryID=%s and ProductID=%s and SKU='%s'"
						, $tblSKUCompare, $frmCategoryID, $frmProductID, $frmSKU2Delete);
		GetResultSet($dbconn, $rsDel, $sqlDel);
		$QryStr = str_replace("&btnSubmit=Delete+Compare+SKU","&btnSubmit=Search",$QryStr);
	break;
}

switch($btnSubmit2) {
	case "Clear All Compare SKUs":
		$sqlDel = sprintf("delete from %s where CategoryID=%s and ProductID=%s"
						, $tblSKUCompare, $frmCategoryID, $frmProductID);
		GetResultSet($dbconn, $rsDel, $sqlDel);
		$QryStr = str_replace("&btnSubmit2=Clear+All+Compare SKUs","",$QryStr);
	break;
}

//Fill out arrCompareSKUs
$sqlCompares = sprintf("select SKU from %s where CategoryID=%s and ProductID=%s order by SKUOrd"
					, $tblSKUCompare, $frmCategoryID, $frmProductID);
GetResultSet($dbconn, $rsCompares, $sqlCompares);
while($rowCompares = mysqli_fetch_assoc($rsCompares)) {
	$arrCompareSKUs[] = $rowCompares["SKU"];
}
$cntCompareSKUs = count($arrCompareSKUs);
//echoarr($arrCompareSKUs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="ISO-8859-1">
	<link rel="icon" type="image/png" href="/favicon.png" />
	<title>Stevenson LG&amp;Q&trade; - SKU Search</title>
	<meta name="description" content="Stevenson Like, Grade & Quality (LG&amp;Q&trade;) Application">
	<meta name="author" content="The Stevenson Company">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<script src="/scripts/jquery/jquery-1.8.3.min.js"></script>
	<script src="/scripts/nouislider/Link.js"></script>
	<script src="/scripts/nouislider/jquery.nouislider.js"></script>
	<link href="/scripts/nouislider/jquery.nouislider.css" rel="stylesheet">
	<link href="/scripts/jquery/jquery-ui-1.10.4.custom/css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">

	<script src="/scripts/jquery/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
	<script>
		$(document).ready(function(){
			$("#frmSKU").autocomplete({
				source: "/sku_search/ajaxsearch.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$frmProductID?>",
				minLength: 2
			});
		});
		//http://sleeplesscoding.blogspot.ro/2010/01/fixing-ie-onchange-event-for-checkboxes.html
	    if ($.browser.msie) {
	      $(function() {
	        $('input:radio, input:checkbox').click(function() {
	          this.blur();
	          this.focus();
	        });
	      });
	    }
	</script>
	<link rel="stylesheet" href="/styles/simplegrid.css">
	<link rel="stylesheet" href="/styles/main.css">
	<style>

	</style>
	<!--[if IE]>
		<script src="/common/<?=COMMONVERSION?>/scripts/html5shiv/dist/html5shiv-printshiv.js"></script>
	<![endif]-->
</head>
<body id="home">
	<?
/*
	echo("<div style=\"width: 700px; float: left; padding: 5px; background-color: #999; overflow: hidden;\">");
		print_r($frmFeatureIDValues);
		echobr($frmMinRetailerPrice." - ".$frmMaxRetailerPrice);
		echobr($QryStr);
		echobr('$_SESSION["arrNormalizedFeatureIDs"]');
		print_r($_SESSION["arrNormalizedFeatureIDs"]);
		//echoarr(get_defined_vars());
		echoarr($_GET);
	echo ("</div>");
*/
	?>
	<!-- Grid 1/2-->
	<div class="grid grid-pad">
		<div class="col-3-12">
			<div class="content">
				<a href="main.php"><img style="margin: 0px 10px 0px 10px;" src="/images/stevenson-like-grade-and-quality-226x76.png" /></a>
			</div>
			<div class="content" id="sku-search-left-nav">
			<?
				require_once("sku-search-left-nav.php");
			?>
			</div>
		</div>
		<div class="col-9-12">

			<div class="content" style="text-align: right;">
				<div style="float: left;">
						Contact: <a href="mailto:jack.cruse@stevensoncompany.com">Jack Cruse</a>, 502-271-5272
				</div>
				<div style="float: right;">
					<a href="main.php">Home</a>&nbsp;|&nbsp;
					<a href="sku-search.php?<?=$_SESSION["ResetQueryString"]?>">Reset SKU Search</a>
				</div>
				<div style="clear: both;"></div>
				<!--&nbsp;|&nbsp;<a href="help.php" target="_blank">Help</a>-->
			</div>
			<div class="content">
				<p style="width: 100%; padding: 4px 20px 4px 10px; margin: 6px 0px; background-color: #3498db; color: #FFFFFF;
					font-size: 32px; font-family: Arial, Helvetica, sans-serif; text-align: right;">
					<?=$Category?> - <?=$Product?>
				</p>
				<div style="font-size: 12px; text-align: right; padding: 5px; margin: 15px 0px; height: auto; font-family: Arial;">
					<? require_once("sku-search-compare.php"); ?>
				</div>
				<?
				if($frmPinLGQSKU == 1) {
				?>
				<div style="margin-top: 5px;">
					<div style="background-color: #b9cddb; padding: 10px;">
						<h3 style="margin: 0px; padding: 0px; float: left;">Refine Stevenson LG&amp;Q&trade; Search for <span style="color: #545454;"><?=$frmLGQSKU?></span></h3>
						<?
						//echobr("<p style='clear: both;'>".$QryStr."</p>");
						$QryStrNoPin = str_replace("frmPinLGQSKU=1", "frmPinLGQSKU=0", $QryStr);
						//echobr($QryStrNoPin);
						?>
						<a href="<?=$_SERVER['PHP_SELF']?>?<?=$QryStrNoPin?>">
							<img src="/images/red-x.png" style="display: inline; float: right; padding-right: 5px; height: 24px; width: 24px;" title="Remove <?=$frmLGQSKU?>" />
						</a>
						<div style="clear: both;"></div>
					</div>
					<div style="border: 2px solid #b9cddb; padding: 2px;">
						<?=DisplaySKUSummary($frmCategoryID, $frmProductID, $frmLGQSKU)?>
					</div>
					<?
					if($btnSubmit == "LGQ") {
					?>
					<div style="background-color: #EBEBEB; padding: 10px; border: 5px solid #b9cddb;">
						<p style="margin: 0px; padding: 0px; color: #666;"><span style="font-weight: bold; font-style: italic; text-decoration: underline;">SKUs Matching the Following Parameters:</span> <?=$strLGQParameters?></p>
					</div>
					<?
					} else {
						if($frmLGQSKU != "") {
							//Check to see if the SKU is in the current search.
							$sqlSKUCheck = sprintf("select SKU from %s where CategoryID=%s and ProductID=%s and SKU='%s'"
												, $tblSKUList, $frmCategoryID, $frmProductID, $frmLGQSKU);
							GetResultSet($dbconn, $rsSKUCheck, $sqlSKUCheck);
							if(mysqli_num_rows($rsSKUCheck) == 0) {
								$strLGQInSearch = "<p style='margin: 0px; padding: 0px; color: red;'><strong>NOTE</strong>: ".$frmLGQSKU." no longer matches the search parameters to the left.";	
							} else {
								$strLGQInSearch = "";
							}
						}
					?>
					<div style="background-color: #EBEBEB; padding: 10px; border: 5px solid #b9cddb;">
						<p style="margin: 0px; padding: 0px; color: red; font-style: italic;">Continue to refine your search by changing the parameters on the lefthand side menu.</p>
						<?=$strLGQInSearch?>
					</div>
					<?
					}
					?>
				</div>
				<?
				}
				?>
				<div style="padding: 5px; margin: 15px 0px 5px 10px; height: auto;">
				 <? include("sku-search-page-sort.php"); ?>
				</div>
				<?
				$sqlSKUListOrdered = sprintf("select * from %s order by %s %s "
										, $tblSKUList
				 						, $frmSKUListOrderBy
				 						, ($frmSKUsPerPage=="ALL" ? "" : "limit ".($frmSKUPage-1)*$frmSKUsPerPage.",".$frmSKUsPerPage));
				//echobr($sqlSKUListOrdered);
				GetResultSet($dbconn, $rsSKUListOrdered, $sqlSKUListOrdered);
				while($rowSKUListOrdered = mysqli_fetch_assoc($rsSKUListOrdered)) {
					if($frmPinLGQSKU == 1) {
					 	//Loop through each SKU and display the SKU Summary, skipping the LGQSKU
					 	if($frmLGQSKU != $rowSKUListOrdered["SKU"]) {
					 		?>
					 		<div style="border-bottom: 1px solid black; padding: 2px;">
					 			<? //echobr($rowSKUListOrdered["SKU"]); ?>
								<?=DisplaySKUSummary($rowSKUListOrdered["CategoryID"], $rowSKUListOrdered["ProductID"], $rowSKUListOrdered["SKU"])?>
							</div>
							<?
						}
					} else {
							?>
					 		<div style="border-bottom: 1px solid black; padding: 2px;">
					 			<? //echobr($rowSKUListOrdered["SKU"]); ?>
								<?=DisplaySKUSummary($rowSKUListOrdered["CategoryID"], $rowSKUListOrdered["ProductID"], $rowSKUListOrdered["SKU"])?>
							</div>
							<?						
					}
				}  // end while($rowSKUListOrdered = mysqli_fetch_assoc($rsSKUListOrdered)) {  
				?>
				<div style="border-top: 2px solid #666; padding-top: 10px;">
					<? include("sku-search-page-sort.php"); ?>
				<div>
			</div>
		</div>
	</div>
	<!-- 9/12 and 3/12 layout -->
	<div class="grid grid-pad" style="vertical-align: top;">
		<div class="col-3-12">

		</div>
		<div class="col-9-12">
			<div class="content">

			</div>
		</div>
	</div>
	<!-- Grid 1/1-->
	<div class="grid">
		<div class="col-1-1">
			<div class="content">

			</div>
		</div>
	</div>
</body>
</html>