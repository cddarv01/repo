<?php
//Set defaults.
$btnSubmit="";
$frmApplyPriceRange=0;
$frmFeatureIDValues=array();

$arrFeatures=array();
$arrFeatureIDs=array();
$arrFeatureID_sqlListWhere = array();

$frmSKUsPerPage = 20;
$frmSKUPage = 1;
$arrOrderSKUListBy[0]="SKU";

$BrandSeries="";
$Description="";
$SKUExternalLink="";

$qShowRetailers=0;

$arrCompareSKUs = array();

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php');

//Get the name of the Category and the Product
//TO DO: Add check so that if there is no frmCategoryID or frmProductID then go back to the main page, because at this point nothing has been sent to the header
GetResultSet($dbconn, $rsCategory, "select Category, CategoryFileAbbr from lgq.vw_categories_products where CategoryID=".$frmCategoryID);
$rowCategory = mysqli_fetch_assoc($rsCategory);
$Category = $rowCategory["Category"];
$CategoryFileAbbr = $rowCategory["CategoryFileAbbr"];

GetResultSet($dbconn, $rsProduct, "select Product, ProductFileAbbr from lgq.vw_categories_products where ProductID=".$frmProductID);
$rowProduct = mysqli_fetch_assoc($rsProduct);
$Product = $rowProduct["Product"];
$ProductFileAbbr = $rowProduct["ProductFileAbbr"];

$tblSKUCompareTbl = "tblSKUCompare_".$_COOKIE["PHPSESSID"];
$tblSKUCompare = "lgq_sessions.".$tblSKUCompareTbl;

//Fill out arrCompareSKUs
$sqlCompares = sprintf("select SKU from %s where CategoryID=%s and ProductID=%s order by SKUOrd" 
					, $tblSKUCompare, $frmCategoryID, $frmProductID);	
GetResultSet($dbconn, $rsCompares, $sqlCompares);
while($rowCompares = mysqli_fetch_assoc($rsCompares)) {
	$arrCompareSKUs[] = $rowCompares["SKU"];		
}
$cntCompareSKUs = count($arrCompareSKUs);
$WidthPerSKU = round(810/$cntCompareSKUs,0);
$strCompareSKUs = implode_quot(", ", $arrCompareSKUs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="ISO-8859-1">
	<link rel="icon" type="image/png" href="/favicon.png" />
	<title>Stevenson LG&amp;Q&trade; - SKU Search</title>
	<meta name="description" content="Stevenson Like, Grade & Quality (LG&Q&trade;) Application">
	<meta name="author" content="The Stevenson Company">
	<meta http-equiv="X-UA-Compatible" content="IE=8">

	<link rel="stylesheet" href="/styles/simplegrid.css">
	<link rel="stylesheet" href="/styles/main.css">
	<style>
		
	</style>
	<!--[if IE]>
		<script src="/common/<?=COMMONVERSION?>/scripts/html5shiv/dist/html5shiv-printshiv.js"></script>
	<![endif]-->
</head>

<body id="home">
	<!-- Grid 1/2-->
	<div class="grid grid-pad">
		<div class="col-3-1">
			<div class="content">
				<a href="main.php"><img style="margin: 10px 10px 10px 10px;" src="/images/stevenson-like-grade-and-quality-226x76.png" /></a>
				<div id="sku-compare-lhs-print">
					<p style="margin: 0px 0px 0px 0px; padding: 0px; text-align: center; font-size: 14px; font-weight: bold;"><a href="sku-search.php?<?=$_SERVER['QUERY_STRING']?>" style="color: blue;">RETURN TO SKU SEARCH</a></p>
					<p style="margin: 10px auto; padding: 0px; text-align:center;">
					<a href="export-skus.php?frmTbl=<?=$tblSKUCompare?>&frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$frmProductID?>" target="_blank"  title="Click to export to .xls" alt="Click to export to .xls">
						<img src="/images/export_to_excel.png" />
					</a>
					</p>	
					<p style="margin: 10px auto; padding: 0px; text-align:center; font-size: 14px;">
					<a href="export-skus.php?frmTbl=<?=$tblSKUCompare?>&frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$frmProductID?>&frmPhotos=<?=2?>" target="_blank"  title="Click to export to .xls" alt="Click to export to .xls">
						With Photos
					</a>
					</p>	
				</div>			
			</div>
		</div>
		<div class="col-9-1">
			<div class="content">
				<p style="width: 830px; padding: 4px 20px 4px 10px; margin: 6px 0px; background-color: #3498db; color: #FFFFFF; 
					font-size: 32px; font-family: Arial, Helvetica, sans-serif; text-align: right;"><?=$Category?> - <?=$Product?></p>
				<p style="margin: 20px 0px 0px 0px; padding: 3px; text-align: center; font-size: 14px; font-weight: bold; color: #FFF; background-color: #000;">SKU Comparison</p>
			</div>
		</div>
	</div>	
	<!-- Grid 1/1-->
	<div class="grid">
		<div class="col-1-1">
			<div class="content">
<table>
<?
//Loop through each SKU and get the main pieces for the title
//Get the list of features
?>
	<tr>
		<td style="text-align: right; padding: 1px; background-color: #fff; color: #fff; font-size: 18px; font-weight: bold; width: 300px;">&nbsp;</td>
<?
//Loop through the SKUs
for($i = 0; $i < $cntCompareSKUs; $i++) {
		$CurrentSKU = $arrCompareSKUs[$i];
		//Let's get the SKU Brand, etc
		$sqlSKUTitle = sprintf("select a.CategoryID, a.ProductID, a.SKU, a.ImagesProcessedTime, if(c.FeatureLabel IS NOT NULL
							, concat(b.FeatureLabel,\" \",c.FeatureLabel,\" Series\"), b.FeatureLabel) as BrandSeries, b.BrandFileAbbr
							, d.FeatureLabel as Description, a.qActive as qActive
							from lgq.tblSKUs  as a
							LEFT JOIN (select CategoryID, ProductID, SKU, FeatureLabel, BrandFileAbbr 
										from lgq.vw_sku_feature_values as a 
										, lgq.tblBrands as b
										where a.CategoryID=%s and a.ProductID=%s and a.SKU=\"%s\" and a.FeatureID=5
										and a.FeatureValue=b.BrandID) as b
							USING (CategoryID, ProductID, SKU)
							LEFT JOIN (select CategoryID, ProductID, SKU, FeatureLabel from lgq.vw_sku_feature_values 
										where CategoryID=%s and ProductID=%s and SKU=\"%s\" and FeatureID=73) as c
							USING(CategoryID, ProductID, SKU)
							LEFT JOIN (select CategoryID, ProductID, SKU, FeatureLabel from lgq.vw_sku_feature_values 
										where CategoryID=%s and ProductID=%s and SKU=\"%s\" and FeatureID=31) as d
							USING(CategoryID, ProductID, SKU)
							where a.CategoryID=%s and a.ProductID=%s and a.SKU=\"%s\""
							, $frmCategoryID, $frmProductID, $CurrentSKU
							, $frmCategoryID, $frmProductID, $CurrentSKU
							, $frmCategoryID, $frmProductID, $CurrentSKU
							, $frmCategoryID, $frmProductID, $CurrentSKU);
		GetResultSet($dbconn, $rsSKUTitle, $sqlSKUTitle);	
		$rowSKUTitle=mysqli_fetch_assoc($rsSKUTitle);
		$SKU = $rowSKUTitle["SKU"];
		if($rowSKUTitle["qActive"] == 0) {
			$strSKUDiscontinued = " <span style='color: #900000;'>(Discontinued)</span>";
		} else {
			$strSKUDiscontinued = "";
		}
		$BrandSeries = $rowSKUTitle["BrandSeries"];
		$BrandFileAbbr = $rowSKUTitle["BrandFileAbbr"];
		$Description = $rowSKUTitle["Description"];
		
		if(is_null($rowSKUTitle["ImagesProcessedTime"])) {
			$ImageSrc = "/assets/processed/image-not-available-350.png"; 
		} else {
			$ImageSrc = "/assets/processed/".$CategoryFileAbbr."/".$ProductFileAbbr."/".$SKU."/photo.jpg";
		}
$SKUExternalLink="";
?>
		<td style="width: <?=$WidthPerSKU?>px; vertical-align: bottom;">
			<p style="margin: 0px; padding: 2px; font-weight: bold;"><?=$BrandSeries."<br />".$Description.$SKUExternalLink?></p><hr />
			<img src="<?=$ImageSrc?>" />
			<p style="margin: 0px; padding: 2px; font-weight: bold; color: #333;"><?=$SKU.$strSKUDiscontinued?></p>
		</td>
<?	
} //end loop through arrCompareSKUs
?>	
	</tr>
<?
$strFeatures="";
$sqlFeatures = sprintf("Select FeatureID, Feature, min(FeatureValueSortBy) as FeatureValueSortBy from lgq.vw_sku_feature_values
					where CategoryID=%s and ProductID=%s and SKU IN(%s) 
					and qNormalized = 0 and FeatureID NOT IN(72,15,57,31,1400,1350)  
					Group By FeatureID, Feature order by FeatureSort"
					, $frmCategoryID, $frmProductID, $strCompareSKUs);
GetResultSet($dbconn, $rsFeatures, $sqlFeatures);
while($rowFeatures = mysqli_fetch_assoc($rsFeatures)) {
	$FeatureID = $rowFeatures["FeatureID"];
	$Feature = $rowFeatures["Feature"];
	?>
	<tr>
		<td style="text-align: right; padding: 5px; background-color: #7f7f7f; color: #fff; font-size: 18px; font-weight: bold; width: 300px;"><?=$Feature?></td>
		<?
		for($i = 0; $i < $cntCompareSKUs; $i++) {
		?>
		<td style="text-align: right; background-color: #7f7f7f; color: #fff;">&nbsp;</td>
		<?
		}
		?>
	</tr>
	<?
	switch($rowFeatures["FeatureValueSortBy"]) {
		case "Label":
			$sqlOrderBy = "FeatureLabel";
			break;
			
		case "Value":
			$sqlOrderBy = "FeatureValue";
			break;
		
		case "Sort":
			$sqlOrderBy = "FeatureValueSort";
			break;
			
		default:
			$sqlOrderBy = "FeatureLabel";
			
	}
	//Now, get the data for each feature for each SKU 
	?>
	<tr>
		<td>&nbsp;</td>
	<?
	for($i = 0; $i < $cntCompareSKUs; $i++) {
		$strFeatures="";
		$CurrentSKU = $arrCompareSKUs[$i];
		$sqlFeatureValues = sprintf("select FeatureValue, FeatureLabel from lgq.vw_sku_feature_values 
								where CategoryID=%s and ProductID=%s and SKU=\"%s\" and FeatureID=%s 
								Order by %s" 
								, $frmCategoryID, $frmProductID, $CurrentSKU, $FeatureID, $sqlOrderBy);	
		GetResultSet($dbconn, $rsFeatureValues, $sqlFeatureValues);
/*
		if(mysqli_num_rows($rsFeatureValues) > 1) {
			$strFeatures.= "<ul>";
		}
		while($rowFeatureValues = mysqli_fetch_assoc($rsFeatureValues)) {
			if(mysqli_num_rows($rsFeatureValues) > 1) {
				$strFeatures.= "<li>".$rowFeatureValues["FeatureLabel"]."</li>";
			} else {
				$strFeatures.= "<p style='margin: 3px; padding; 0px;'>".$rowFeatureValues["FeatureLabel"]."</p>";				
			}
		}
		if(mysqli_num_rows($rsFeatureValues) > 1) {
			$strFeatures.= "</ul>";
		}
*/
		while($rowFeatureValues = mysqli_fetch_assoc($rsFeatureValues)) {
			$strFeatures.= "<p style='margin: 3px; padding; 0px;'>".$rowFeatureValues["FeatureLabel"]."</p>";				
		}
	?>
		<td style="text-align: right; vertical-align: top;"><?=$strFeatures?></td>
	<?
	}
	?>
	</tr>
	<?

}
?>
<?
//Now grab the Retail prices
?>
	<tr>
		<td style="text-align: right; padding: 5px; background-color: #7f7f7f; color: #fff; font-size: 18px; font-weight: bold; width: 300px;">Avg. Retailer Price</td>
		<?
		for($i = 0; $i < $cntCompareSKUs; $i++) {
		?>
		<td style="text-align: right; background-color: #7f7f7f; color: #fff;">&nbsp;</td>
		<?
		}
		?>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<?
		for($i = 0; $i < $cntCompareSKUs; $i++) {
			$sqlRetailPrice = sprintf("select if(CurrentAvgRetailerPrice IS NULL, \"Not Available\", concat(\"$\",format(CurrentAvgRetailerPrice,0))) as RetailPrice
								from lgq.tblSKUs where CategoryID=%s and ProductID=%s and SKU='%s'"
								, $frmCategoryID, $frmProductID, $arrCompareSKUs[$i] );
			GetResultSet($dbconn, $rsRetailPrice, $sqlRetailPrice);
			$rowRetailPrice = mysqli_fetch_assoc($rsRetailPrice);
		?>
			<td style="text-align: right; "><?=$rowRetailPrice["RetailPrice"]?></td>
		<?
		}
		?>
	</tr>		
<?
//Now grab the Brand prices
?>
	<tr>  
		<td style="text-align: right; padding: 5px; background-color: #7f7f7f; color: #fff; font-size: 18px; font-weight: bold; width: 300px;">MSRP</td>
		<?
		for($i = 0; $i < $cntCompareSKUs; $i++) {
		?>
		<td style="text-align: right; background-color: #7f7f7f; color: #fff;">&nbsp;</td>
		<?
		}
		?>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<?
		for($i = 0; $i < $cntCompareSKUs; $i++) {
			$sqlBrandPrice = sprintf("select if(CurrentBrandPrice IS NULL, \"Not Available\", concat(\"$\",format(CurrentBrandPrice,0))) as BrandPrice
								from lgq.tblSKUs where CategoryID=%s and ProductID=%s and SKU='%s'"
								, $frmCategoryID, $frmProductID, $arrCompareSKUs[$i] );
			GetResultSet($dbconn, $rsBrandPrice, $sqlBrandPrice);
			$rowBrandPrice = mysqli_fetch_assoc($rsBrandPrice);
		?>
			<td style="text-align: right; "><?=$rowBrandPrice["BrandPrice"]?></td>
		<?
		}
		?>
	</tr>	
</table>	
<?	
//echoarr(get_defined_vars());
?>
			</div>
		</div>
	</div>
</body>
</html>