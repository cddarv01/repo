<? /*********************/ ?>
<div id="pg-nav-container" style="margin: 10px 0px -10px 0px;">
	<div style="float: left;">
<?
/*Possible ways to order:
Price
Brand
SKU 
*/
//If you have a SKU pinned to the top, that is also part of the criteria above, then remove that from the total, else do the total
if($frmPinLGQSKU == 1) {
	//Does the frmLGQSKU show up in the results.
	GetResultSet($dbconn, $rsCntSKUs, "select count(*) as cntSKUs, sum(if(SKU='".$frmLGQSKU."', 0, 1)) as chkSKUs from ".$tblSKUList);
	$rowCntSKUs = mysqli_fetch_assoc($rsCntSKUs);
	$cntSKUs=$rowCntSKUs["chkSKUs"];		
} else {
	GetResultSet($dbconn, $rsCntSKUs, "select count(*) as cntSKUs from ".$tblSKUList);
	$rowCntSKUs = mysqli_fetch_assoc($rsCntSKUs);
	$cntSKUs=$rowCntSKUs["cntSKUs"];	
}


if($frmSKUsPerPage=="ALL") {
	$SKUPages = 1;
} else {
	$SKUPages = ceil($cntSKUs/$frmSKUsPerPage);
}

if ($frmChgSKUsPerPage == 1) { 
	$frmSKUPage = 1;
} 

//echobr($cntSKUs." ".$frmSKUsPerPage." ".$SKUPages);

/*
if($cntSKUs < $frmSKUsPerPage) {
	$strTotalSKUs = $cntSKUs;	
} else {
	$strTotalSKUs = ($frmSKUsPerPage*$frmSKUPage)." of ".$cntSKUs;
}
*/
$strTotalSKUs = $cntSKUs;
?>
	<span style="font-weight: bold;">SKUs:</span> <?=$strTotalSKUs?>&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight: bold;">Page:</span>
<?
$SKUsPerPageSet = 5;
$SKUPageSets = ceil($SKUPages/$SKUsPerPageSet);
/*Which PageSet am I on*/
$SKUPageSet = ceil($frmSKUPage/$SKUsPerPageSet);

/*Given the page, which SKUPageSet am I on*/
/*Create the list of pages.  Show up to 5 pages, moving as you go down the line*/
/*What pages do I show, well $frmSKUPage+4*/
$strNavPages = "";
$i=0;
for($s = 1; $s <= $SKUsPerPageSet; $s++) {
	$p = $s + ($SKUsPerPageSet*($SKUPageSet-1));
	if($p <= $SKUPages) {
		$i++;		
		if($frmSKUPage == $p) {
			$page_style = " style='background-color: #000; color: #fff; padding: 3px;'";	
		} else {
			$page_style = "";
		}
		$strNavPages .= sprintf("<span><a class=\"pg-nav\" href=\"sku-search.php?%s&frmSKUsPerPage=%s&frmSKUPage=%s&frmSKUListOrderBy=%s&btnSubmit=Search\" %s>%s</a><span>"
							, $BaseQueryString, $frmSKUsPerPage, $p, $frmSKUListOrderBy, $page_style, $p);
	}
}
if($i < $SKUsPerPageSet) {
	for ($b = 1; $b < ($i-$SKUsPerPageSet); $b++) {
		$strNavPages .= "<span>&nbsp;</span>";	
	}
}
if($SKUPageSet == 1) {
	$strPgSetNavFirst = sprintf("<span>&le;</span>");
	$strPgSetNavPrev = sprintf("<span>&lt;</span>");
} else {
	$strPgSetNavFirst = sprintf("<span><a class=\"pg-nav\" href=\"sku-search.php?%s&frmSKUsPerPage=%s&frmSKUPage=1&frmSKUListOrderBy=%s&btnSubmit=Search\" title=\"First Page\">&le;</a></span>"
						, $BaseQueryString, $frmSKUsPerPage, $frmSKUListOrderBy);
	$strPgSetNavPrev = sprintf("<span><a class=\"pg-nav\" href=\"sku-search.php?%s&frmSKUsPerPage=%s&frmSKUPage=%s&frmSKUListOrderBy=%s&btnSubmit=Search\" title=\"Previous 5 Pages\">&lt</a></span>"
						, $BaseQueryString, $frmSKUsPerPage, $SKUsPerPageSet*($SKUPageSet-1), $frmSKUListOrderBy);
} 
/**/

/**/
if($SKUPageSet == $SKUPageSets) {
	$strPgSetNavLast = sprintf("<span>&ge;</span>");
	$strPgSetNavNext = sprintf("<span>&gt;</span>");
} else {
	$strPgSetNavLast = sprintf("<span><a class=\"pg-nav\" href=\"sku-search.php?%s&frmSKUsPerPage=%s&frmSKUPage=%s&frmSKUListOrderBy=%s&btnSubmit=Search\" title=\"Last Page\">&ge;</a></span>"
						, $BaseQueryString, $frmSKUsPerPage, $SKUPages, $frmSKUListOrderBy);
	$strPgSetNavNext = sprintf("<span><a class=\"pg-nav\" href=\"sku-search.php?%s&frmSKUsPerPage=%s&frmSKUPage=%s&frmSKUListOrderBy=%s&btnSubmit=Search\" title=\"Next 5 Pages\">&gt;</a></span>"
						, $BaseQueryString, $frmSKUsPerPage, (1+($SKUsPerPageSet*$SKUPageSet)), $frmSKUListOrderBy);
	$strNavPages .= sprintf("<span><a class=\"pg-nav\" href=\"sku-search.php?%s&frmSKUsPerPage=%s&frmSKUPage=%s&frmSKUListOrderBy=%s&btnSubmit=Search\" title=\"Next 5 Pages\">...</a></span>
						<span><a class=\"pg-nav\" href=\"sku-search.php?%s&frmSKUsPerPage=%s&frmSKUPage=%s&frmSKUListOrderBy=%s&btnSubmit=Search\">%s</a><span>"
						, $BaseQueryString, $frmSKUsPerPage, (1+($SKUsPerPageSet*$SKUPageSet)), $frmSKUListOrderBy
						, $BaseQueryString, $frmSKUsPerPage, $SKUPages, $frmSKUListOrderBy, $SKUPages);
} 
?>
	
	<?=$strPgSetNavFirst.$strPgSetNavPrev.$strNavPages.$strPgSetNavNext.$strPgSetNavLast?>
	&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight: bold;">SKUs Per Page:</span>
	<select name="frmSKUsPerPage">
	<option value="20" <?=($frmSKUsPerPage == 20 ? 'selected="selected"' : '')?> onclick="location.href='sku-search.php?<?=$BaseQueryString?>&frmSKUPage=1&frmSKUListOrderBy=<?=$frmSKUListOrderBy?>&frmSKUsPerPage=20&frmChgSKUsPerPage=1&btnSubmit=Search';">20</option>
	<option value="50" <?=($frmSKUsPerPage == 50 ? 'selected="selected"' : '')?> onclick="location.href='sku-search.php?<?=$BaseQueryString?>&frmSKUPage=1&frmSKUListOrderBy=<?=$frmSKUListOrderBy?>&frmSKUsPerPage=50&frmChgSKUsPerPage=1&btnSubmit=Search';">50</option>
	<option value="100" <?=($frmSKUsPerPage == 100 ? 'selected="selected"' : '')?> onclick="location.href='sku-search.php?<?=$BaseQueryString?>&frmSKUPage=1&frmSKUListOrderBy=<?=$frmSKUListOrderBy?>&frmSKUsPerPage=100&frmChgSKUsPerPage=1&btnSubmit=Search';">100</option>
	<!--<option value="ALL" disabled="disabled">ALL</option>-->
	</select>
</div>
	<?
	//Export
	if($cntSKUs <= 100) {
	?>
		<div style="float: right; margin: 0px 0px 0px 0px; padding: 0px; width: auto;">
			<div style="float: left;">
				<p style="margin: 0px auto; padding: 0px;" title="Sort By Avg. Retail Price">
					<strong>Sort By:</strong> 
					 Avg. Retail $: <a href="<?=$_SERVER['PHP_SELF']."?".$BaseQueryString."&frmSKUListOrderBy=CurrentAvgRetailerPrice ASC&btnSubmit=Search"?>" class="sort-arrows">&#x25B2;</a>
						&nbsp;<a href="<?=$_SERVER['PHP_SELF']."?".$BaseQueryString."&frmSKUListOrderBy=CurrentAvgRetailerPrice DESC&btnSubmit=Search"?>" class="sort-arrows">&#x25BC;</a>
				</p>	
			</div>
			<div style="float: right; margin-top:-5px;">
				<a href="export-skus.php?frmTbl=<?=$tblSKUList?>&frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$frmProductID?>&frmPhotos=0" target="_blank"  title="Click to export to .xls without photos" alt="Click to export to .xls without photos">
					<img src="/images/export_to_excel.png" style="margin: 0 auto; padding-right: 0px; display: block; "/>
				</a>
				<p style="color: #00007f; margin: 0 auto; font-size:9px;">
					 <a href="export-skus.php?frmTbl=<?=$tblSKUList?>&frmCategoryID=<?=$frmCategoryID?>&frmProductID=<?=$frmProductID?>&frmPhotos=1" target="_blank"  title="Click to export to .xls with photos" alt="Click to export to .xls with photos">
					 	<strong>With Photos</strong>
					</a>
				</p>
			</div>
			<div style="clear: both;"></div>
		</div>
	<?
	} else {
	?>
		<div style="float: right; margin: 0px 0px 0px 0px; padding: 0px; width: auto;">
			<div style="float: left;">
				<p style="margin: 0px auto; padding: 0px;" title="Sort By Avg. Retail Price">
					<strong>Sort By:</strong> 
					 Avg. Retail $: <a href="<?=$_SERVER['PHP_SELF']."?".$BaseQueryString."&frmSKUListOrderBy=CurrentAvgRetailerPrice ASC&btnSubmit=Search"?>" class="sort-arrows">&#x25B2;</a>
						&nbsp;<a href="<?=$_SERVER['PHP_SELF']."?".$BaseQueryString."&frmSKUListOrderBy=CurrentAvgRetailerPrice DESC&btnSubmit=Search"?>" class="sort-arrows">&#x25BC;</a>
				</p>	
			</div>
			<div style="float: right; margin-top:-5px;">
				<img src="/images/export_to_excel-disabled.png" style="margin: 0 auto; padding-right: 0px; display: block; "
				title="Narrow your search to <=100 SKUs to enable download" 
				alt="Narrow your search to <=100 SKUs to enable download" 
				onClick='alert("Narrow your search to <=100 SKUs to enable download")'/>
				<p style="color: #CCCCCC; margin: 0 auto; font-size:9px;">
					 	<strong>With Photos</strong>
				</p>
			</div>
			<div style="clear: both;"></div>
		</div>
	<?						
	}
	?>
	<div style="clear: both;"></div>
</div>
<? /*********************/ ?>