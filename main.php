<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/arrUsers.php');
//Put the first possible configuration (FeatureID = 1200) for each Category/Product combination
//Use for building the reset string in sku-search.php
//if(!isset($_COOKIE["arrFirstConfig"])) {
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
//}

//Create lgq_sessions.tblBrandSeries, include the Brand with SeriesID=0
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/create-brand-series.php');

$strProdSub = '<span style="color: black; font-weight: bold;">***</span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="ISO-8859-1">
	<link rel="icon" type="image/png" href="/favicon.png" />
	<title>Stevenson LG&amp;Q&trade; Administration</title>
	<meta name="description" content="Stevenson Like, Grade & Quality (LG&amp;Q&trade;) Application">
	<meta name="author" content="The Stevenson Company">
	<meta http-equiv="X-UA-Compatible" content="IE=8">

	<script src="/scripts/jquery/jquery-1.8.3.min.js"></script>
	<script src="/scripts/nouislider/Link.js"></script>
	<script src="/scripts/nouislider/jquery.nouislider.js"></script>
	<link href="/scripts/nouislider/jquery.nouislider.css" rel="stylesheet">
	<link href="/scripts/jquery/jquery-ui-1.10.4.custom/css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">

	<script src="/scripts/jquery/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
	<script>
		$(document).ready(function(){
			$("#frmSKU").autocomplete({
				source: "/sku_search/ajaxsearch.php",
				minLength: 2
			});
		});
	</script>

	<link rel="stylesheet" href="/styles/simplegrid.css">
	<link rel="stylesheet" href="/styles/main.css">
	<style>
	.external {
	    background-position: right center;
	    background-repeat: no-repeat;
	    background-image: linear-gradient(transparent, transparent), url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwIiBoZWlnaHQ9IjEwIj48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtODI2LjQyOSAtNjk4Ljc5MSkiPjxyZWN0IHdpZHRoPSI1Ljk4MiIgaGVpZ2h0PSI1Ljk4MiIgeD0iODI2LjkyOSIgeT0iNzAyLjMwOSIgZmlsbD0iI2ZmZiIgc3Ryb2tlPSIjMDZjIi8+PGc+PHBhdGggZD0iTTgzMS4xOTQgNjk4Ljc5MWg1LjIzNHY1LjM5MWwtMS41NzEgMS41NDUtMS4zMS0xLjMxLTIuNzI1IDIuNzI1LTIuNjg5LTIuNjg5IDIuODA4LTIuODA4LTEuMzExLTEuMzExeiIgZmlsbD0iIzA2ZiIvPjxwYXRoIGQ9Ik04MzUuNDI0IDY5OS43OTVsLjAyMiA0Ljg4NS0xLjgxNy0xLjgxNy0yLjg4MSAyLjg4MS0xLjIyOC0xLjIyOCAyLjg4MS0yLjg4MS0xLjg1MS0xLjg1MXoiIGZpbGw9IiNmZmYiLz48L2c+PC9nPjwvc3ZnPg==');
	    padding-right: 17px;
	}
	</style>
	<!--[if IE]>
		<script src="/common/<?=COMMONVERSION?>/scripts/html5shiv/dist/html5shiv-printshiv.js"></script>
	<![endif]-->
</head>

<body id="home">
	<?
/*
	echo("<div style=\"width: 700px; float: left; padding: 5px; background-color: #999; overflow: hidden;\">");
		echobr($QryStr);
		echobr('$_SESSION["arrNormalizedFeatureIDs"]');
		print_r($_SESSION["arrNormalizedFeatureIDs"]);
		echoarr(get_defined_vars());
	echo ("</div>");
*/
	?>
		<!-- Grid 1/1-->
		<div class="grid">
			<div class="col-1-1">
				<div class="content">
					<a href="index.php"><img style="margin: 10px 0px 0px 340px; border: none; outline: none;" src="/images/stevenson-like-grade-and-quality.png" /></a><br />
					<p style="width: 100%; padding: 10px; margin: 0px 0px 0px 0px; background-color: #3498db; color: #FFFFFF; font-size: 36px; font-family: Arial, Helvetica, sans-serif; text-align: center;">Major Appliances</p>
					<p style="font-style: italic; font-size: 24px; text-align: center;  padding: 0px; margin: 0px 0px 0px 0px; border-bottom: 1px solid black;">The industry's most comprehensive, timely and user-friendly access for competitive product information.</p>
				</div>
			</div>
		</div>
		<!-- Grid 1/3 -->
		<div class="grid grid-pad">
			<div class="col-1-3">
				<div class="content">
					<h2>Laundry</h2>
					<div id="navcontainer">
						<ul id="navlist">
						<?
						$frmCategoryID=1;
						$sqlProds = "select a.ProductID, b.Product, count(SKU) as cntSKUs from lgq.tblSKUs as a, lgq.tblProducts as b
									where a.CategoryID=".$frmCategoryID." and a.CategoryID=b.CategoryID and a.ProductID=b.ProductID and a.qActive=1
									group by a.CategoryID, a.ProductID, b.Product order by b.ProductSort";
						GetResultSet($dbconn, $rsProds, $sqlProds);
						while($rowProds = mysqli_fetch_assoc($rsProds)) {
							if(in_array($_COOKIE['UserID'], $arrDemoUsers)) {
								if(in_array($rowProds["ProductID"], $arrDemoProds[ $_COOKIE['UserID'] ] ) ) {
									?>
									<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$strProdSub?>&nbsp;<?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)&nbsp;<?=$strProdSub?></a></li>
									<?
								} else {
									?>
									<li><a href="main.php"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
									<?
								}
							} else {
								?>
								<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
								<?
							}
						}
						?>
						</ul>
					</div>
					<h2>Kitchen Cleaning</h2>
					<div id="navcontainer">
						<ul id="navlist">
						<?
						$frmCategoryID=4;
						$sqlProds = "select a.ProductID, b.Product, count(SKU) as cntSKUs from lgq.tblSKUs as a, lgq.tblProducts as b
									where a.CategoryID=".$frmCategoryID." and a.CategoryID=b.CategoryID and a.ProductID=b.ProductID and a.qActive=1
									group by a.CategoryID, a.ProductID, b.Product order by b.ProductSort";
						GetResultSet($dbconn, $rsProds, $sqlProds);
						while($rowProds = mysqli_fetch_assoc($rsProds)) {
							if(in_array($_COOKIE['UserID'], $arrDemoUsers)) {
								if(in_array($rowProds["ProductID"], $arrDemoProds[ $_COOKIE['UserID'] ] ) ) {
									?>
									<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$strProdSub?>&nbsp;<?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)&nbsp;<?=$strProdSub?></a></li>
									<?
								} else {
									?>
									<li><a href="main.php"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
									<?
								}
							} else {
								?>
								<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
								<?
							}
						}
						?>
						</ul>
					</div>
					<h2>Home Comfort</h2>
					<div id="navcontainer">
						<ul id="navlist">
						<?
						$frmCategoryID=5;
						$sqlProds = "select a.ProductID, b.Product, count(SKU) as cntSKUs from lgq.tblSKUs as a, lgq.tblProducts as b
									where a.CategoryID=".$frmCategoryID." and a.CategoryID=b.CategoryID and a.ProductID=b.ProductID and a.qActive=1
									group by a.CategoryID, a.ProductID, b.Product order by b.ProductSort";
						GetResultSet($dbconn, $rsProds, $sqlProds);
						while($rowProds = mysqli_fetch_assoc($rsProds)) {
							if(in_array($_COOKIE['UserID'], $arrDemoUsers)) {
								if(in_array($rowProds["ProductID"], $arrDemoProds[ $_COOKIE['UserID'] ] ) ) {
									?>
									<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$strProdSub?>&nbsp;<?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)&nbsp;<?=$strProdSub?></a></li>
									<?
								} else {
									?>
									<li><a href="main.php"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
									<?
								}
							} else {
								?>
								<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
								<?
							}
						}
						?>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-1-3">
				<div class="content">
					<h2>Refrigeration</h2>
					<div id="navcontainer">
						<ul id="navlist">
						<?
						$frmCategoryID=2;
						$sqlProds = "select a.ProductID, b.Product, count(SKU) as cntSKUs from lgq.tblSKUs as a, lgq.tblProducts as b
									where a.CategoryID=".$frmCategoryID." and a.CategoryID=b.CategoryID and a.ProductID=b.ProductID and a.qActive=1
									group by a.CategoryID, a.ProductID, b.Product order by b.ProductSort";
						GetResultSet($dbconn, $rsProds, $sqlProds);
						while($rowProds = mysqli_fetch_assoc($rsProds)) {
							if(in_array($_COOKIE['UserID'], $arrDemoUsers)) {
								if(in_array($rowProds["ProductID"], $arrDemoProds[ $_COOKIE['UserID'] ] ) ) {
									?>
									<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$strProdSub?>&nbsp;<?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)&nbsp;<?=$strProdSub?></a></li>
									<?
								} else {
									?>
									<li><a href="main.php"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
									<?
								}
							} else {
								?>
								<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
								<?
							}
						}
						?>
						</ul>
					</div>
					<h2>Cooking</h2>
					<div id="navcontainer">
						<ul id="navlist">
						<?
						$frmCategoryID=3;
						$sqlProds = "select a.ProductID, b.Product, count(SKU) as cntSKUs from lgq.tblSKUs as a, lgq.tblProducts as b
									where a.CategoryID=".$frmCategoryID." and a.CategoryID=b.CategoryID and a.ProductID=b.ProductID and a.qActive=1
									group by a.CategoryID, a.ProductID, b.Product order by b.ProductSort";
						GetResultSet($dbconn, $rsProds, $sqlProds);
						while($rowProds = mysqli_fetch_assoc($rsProds)) {
							if(in_array($_COOKIE['UserID'], $arrDemoUsers)) {
								if(in_array($rowProds["ProductID"], $arrDemoProds[ $_COOKIE['UserID'] ] ) ) {
									?>
									<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$strProdSub?>&nbsp;<?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)&nbsp;<?=$strProdSub?></a></li>
									<?
								} else {
									?>
									<li><a href="main.php"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
									<?
								}
							} else {
								?>
								<li><a href="sku-configuration-splash.php?frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$rowProds["ProductID"]?>&frmFeatureIDValues[]=1300|1&frmFeatureIDValues[]=1508|1"><?=$rowProds["Product"]?>&nbsp;(<?=number_format($rowProds["cntSKUs"],0)?> SKUs)</a></li>
								<?
							}
						}
						?>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-1-3">
				<div class="content">
					<div style="text-align: center; margin: 10px 0px 0px 0px; padding: 10px 10px 20px 10px; background-color: #CCC;">
						<h2>Quick Search</h2>
						<fieldset style="background-color: #F4F4F4;">
						<form name="specific-sku" id="specific-sku" action="sku-detail.php" method="get">
							<p style="padding: 3px; margin: 0px; font-weight: bold;">SPECIFIC SKU:</p>
							<input name="frmSKU" id="frmSKU" type="text" style="width: 150px;"  placeholder="SKU"/>
							<input name="frmShowRetailers" id="frmShowRetailers" type="hidden" value="0"/>
							<input type="submit" name="btnSubmit" id="btnSubmit" value="Go To SKU" />
							<p style="text-align: left; font-size: 12px;">This is an auto-complete form. Start typing and the dropdown will change as you type.</p>
						</form>
						</fieldset>
<!--
						<p style="padding: 3px; margin: 0px; font-weight: bold;">OR</p>
						<fieldset style="background-color: #F4F4F4;">
						<form name="keyword-search" id="keyword-search" action="sku-keyword.php" method="get">
							<p style="padding: 3px; margin: 0px; font-weight: bold;">KEYWORD:</p>
							<select name="frmCategoryIDProductID" id="frmCategoryIDProductID" style="width: 250px;">
								<option value="0">Pick a Category/Product</option>
							<?
								$sqlGetCatProd = "select CategoryID, Category, ProductID, Product from lgq.vw_categories_products order by CategorySort, ProductSort";
								GetResultSet($dbconn, $rsGetCatProd, $sqlGetCatProd);
								$tmpCategory="";
								while($rowGetCatProd = mysqli_fetch_assoc($rsGetCatProd)) {
									if($tmpCategory != $rowGetCatProd["Category"]) {
										?>
										<optgroup label="<?=$rowGetCatProd["Category"]?>">
										<?
									}
									?>
									<option value="<?=$rowGetCatProd["CategoryID"]."|".$rowGetCatProd["ProductID"]?>"><?=$rowGetCatProd["Product"]?></option>
									<?
									$tmpCategory = $rowGetCatProd["Category"];
								}
							?>
							</select>
							<input name="frmKeyword" id="frmKeyword" type="text" style="width: 250px;" placeholder="Enter Keyword"/><br />
							<input type="submit" name="btnSubmit" id="btnSubmit" value="Find Matches" /><br />
							<p style="text-align: left; font-size: 12px;">Keep the search to a single word or phrase. You can even use partial words (i.e. sani will find all words containing sani).
								Searches are not case sensitive.</p>
						</form>
-->
						</fieldset>
					</div>
					<div style="text-align: center; margin: 10px 0px 0px 0px; padding: 10px 10px 20px 10px; background-color: #CCC;">
						<p style="margin: 0px 0px 10px 0px; padding: 0px; text-decoration: underline; font-weight: bold; font-size: 16px;">SKUs Recently Added</p>
							<?
							$sqlRecentSKUs = "select FeatureValue, FeatureLabel, count(*) as cntSKUs from lgq.vw_sku_feature_values 
											where FeatureID=1514 group by FeatureValue, FeatureLabel order by FeatureValue DESC limit 4";
							GetResultSet($dbconn, $rsRecentSKUs, $sqlRecentSKUs);
							while($rowRecentSKUs = mysqli_fetch_assoc($rsRecentSKUs)) {
								?>
								<p style="margin: 0px; padding: 0px;"><a href="sku-list-recent.php?frmLastUpdt=<?=$rowRecentSKUs["FeatureValue"]?>&frmMass=10">
								<?=$rowRecentSKUs["FeatureLabel"]?>  - <?=$rowRecentSKUs["cntSKUs"]?></a>
								</p>
								<?
							}
							/*
							<p style="margin: 0px; padding: 0px;"> 5/27/15  - 239</p>
							<p style="margin: 0px; padding: 0px;"> 5/20/15  - 109</p>
							<p style="margin: 0px; padding: 0px;"> 4/28/15  - 162</p>
							<p style="margin: 0px; padding: 0px;"> 4/8/15  - 178</p>
							<p style="margin: 0px; padding: 0px;"> 3/24/15  - 158</p>
							<p style="margin: 0px; padding: 0px;"> 2/27/15  - 239</p>
							<p style="margin: 0px; padding: 0px;"> 2/16/15  - 68</p>
							<p style="margin: 0px; padding: 0px;"> 2/4/15  - 173</p>
							<p style="margin: 0px; padding: 0px;"> 2/2/15  - 47</p>
							<p style="margin: 0px; padding: 0px;"> 1/20/15  - 115</p>
							<p style="margin: 0px; padding: 0px;"> 1/8/15  - 105</p>
							<p style="margin: 0px; padding: 0px;">12/11/14 - 127</p>
							<p style="margin: 0px; padding: 0px;">11/26/14 - 419</p>
							<p style="margin: 0px; padding: 0px;">10/29/14 - 132</p>
							<p style="margin: 0px; padding: 0px;">10/20/14 -  82</p>
							<p style="margin: 0px; padding: 0px;">10/10/14 - 207</p>
							*/
							$CompanyID=0;
							$sqlCompanyID = "select CompanyID from lgq.tblCompanyUsers where UserID=".$_COOKIE["UserID"];
							GetResultSet($dbconn, $rsCompanyID, $sqlCompanyID);
							$rowCompanyID = mysqli_fetch_assoc($rsCompanyID);
							$CompanyID = $rowCompanyID["CompanyID"];
							
							if($CompanyID == 180) {
								?>
								<p style="text-align: center; margin:20px 0px 10px 0px; font-weight: bold;"><a href="Stevenson LGQ Walk Through for GE 20160315.pptx" target="_blank"><i>Stevenson LGQ&trade;</i> <br />Walk-Through for GE.pptx</a></p>
								<?
							} else {
								?>
								<p style="text-align: center; margin:20px 0px 10px 0px; font-weight: bold;"><a href="Stevenson LGQ Walk Through 20160510.pptx" target="_blank"><i>Stevenson LGQ&trade;</i> Walk-Through.pptx</a></p>
								<?
							}
							?>
							<p style="text-align: center; margin:20px 0px 3px 0px;">Contact: <a href="mailto:jack.cruse@stevensoncompany.com">Jack Cruse</a>, 502-271-5272</p>
					</div>
				</div>
			</div>
		</div>
	<div class="grid">
		<div class="col-1-1">
			<div class="content" style="padding: 5px 5px 5px 50px;">
						<?
						if( ($_COOKIE["UserID"] == 2 || $_COOKIE["UserID"] == 3 || $_COOKIE["UserID"] == 10 || $_COOKIE["UserID"] >= 10000) 
							&& $_COOKIE["UserID"] != 10002) {
								//<h3><a href="true-modus.php">LG&Q To TrueModus Mapping</a></h3>
							?>
							
							<?
						}
						?>
						<?
						/**/
						$arrDemoUsers = array(7,2000,2001,2002,2003,2004,2005,2006,2007,2008,2009,2010,2011,2012,2013,4200,4201,4202,4203,4204,4205,4206,4207,4208,4209
											,3000,3001,3002,3003,3004,3005,3006,3007,3008,3009,3010,3011,3012,3013,3014,3015,3016,3017,3018,3019,3020,3021
											,3022,3023,3024
											, 58001, 58002, 58003, 58004, 58005, 58006, 58007, 58008, 58009);
						/**/
						/*
						$arrDemoUsers = array(2000,2001,2002,2003,2004,2005,2006,2007,2008,2009,2010,2011,2012,2013,4200,4201,4202,4203,4204,4205,4206,4207,4208,4209);
						*/
						if(in_array($_COOKIE["UserID"], $arrDemoUsers)) {
						?>
						<div style="text-align: center; margin: 5px 50px 0px 0px; padding: 10px 5px 5px 5px; background-color: #FCF0AD;">
							<p style="margin: 0px; padding: 3px; font-weight: bold; text-decoration: underline;">LG&Q&trade; Trial Demo Survey</p>
							<p style="margin: 0px; padding: 3px;">Thank you for trying out Stevenson LG&Q&trade;.<br />We want to hear from you. Click the link below to take a short survey.</p>
							<?
							$sqlProceed = "select HTMLPg from lgq.tblUserLog where UserID=".$_COOKIE["UserID"]." and HTMLPg='/sku-search.php' limit 1"; 
							GetResultSet($dbconn, $rsProceed, $sqlProceed);
							if(mysqli_num_rows($rsProceed) == 1) {
							?>
							<p style="margin: 0px; padding: 3px; font-size: 20px;"><a href="http://tscres.com/start.php?SID=LGQPOST&SRC=40&PID=<?=$_COOKIE["UserID"]?>">Trial Demo Survey</a></p>	
							<?
							} else {
							?>
							<p style="margin: 0px; padding: 3px; font-size: 20px; text-decoration: underline;" title="You must try the site for the link to become active">Trial Demo Survey</p>
							<p style="margin: 0px; padding: 3px; font-size: 16px; color: red; font-weight: bold;">NOTE: The link will become active once you have actually used the site.</p>
							<p style="margin: 0px; padding: 3px; font-size: 16px; color: red; font-weight: bold;">Try out the site, then come back to this page to take the survey.</p>
							<?
							}
							?>
						</div>
						<?
						}
						?>
			</div>
			<div class="content" style="padding: 5px 5px 5px 90px;">
				<?
				$sqlBrandImages = "select BrandID, BrandFileAbbr from lgq.tblBrands order by Brand";
				GetResultSet($dbconn, $rsBrandImages, $sqlBrandImages);
				while($rowBrandImages = mysqli_fetch_assoc($rsBrandImages)) {
					?>
					<a href="sku-list-brand.php?frmBrandID=<?=$rowBrandImages["BrandID"]?>&frmMass=10"><img src="/images/logos/<?=$rowBrandImages["BrandFileAbbr"]?>.png" style="width: 100px; height: 43px;" /></a>&nbsp;&nbsp;&nbsp;&nbsp;
					<?
				}
				?>
			</div>
		</div>
	</div>
</body>
</html>