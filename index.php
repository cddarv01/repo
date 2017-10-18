<?php
//
$btnSubmit = "";
$frmUserName = "";
$frmUserPassword = "";
$ErrorMsg = "";

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config-without-login.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/arrUsers.php');

if($btnSubmit == "") {
	//Check to see if you are logged in and you have a UserName and UserPassword that matches
	if(isset($_COOKIE["qLoggedIn"]) && $_COOKIE["qLoggedIn"] == 1 && in_array($_COOKIE["UserName"],$arrUserName) ) {
			//echo "Go to main";
			//header('Location: testlogin.php');
				
			header('Location: main.php');
	}
}

if($btnSubmit == "Login") {
	$frmUserName = trim($frmUserName);
	$frmUserPassword = trim($frmUserPassword);

	if(in_array(strtolower($frmUserName), $arrUserName)) {
		//Now is the password right
		$UserKey = array_search(strtolower($frmUserName), $arrUserName);
		//echo( $UserKey );	
		if(!is_null($UserKey) && strtolower($frmUserPassword) == strtolower($arrUserPassword[$UserKey]) ) {
			//Check to see if the User is active and has confirmed
			$sqlUser = "select * from lgq.tblCompanyUsers where UserID=".$UserKey;
			GetResultSet($dbconn, $rsUser, $sqlUser);
			$rowUser = mysqli_fetch_assoc($rsUser);
			if(mysqli_num_rows($rsUser) == 1 && $rowUser["qActive"] == 1) {
				/**/
				setcookie('UserID', $UserKey, $cookie_expire);
				setcookie('UserName', strtolower($frmUserName), $cookie_expire);
				setcookie('UserPassword', $frmUserPassword,$cookie_expire);
				setcookie('qLoggedIn', 1, $cookie_expire);
				/**/
				//echo "Go to main";
				header('Location: main.php');	
			} elseif(mysqli_num_rows($rsUser) == 1 && $rowUser["qActive"] == 0 && is_null($rowUser["ConfirmationTime"])) {
				//Inactive, awaiting confirmation
				$ErrorMsg = "<div style=\"margin: 20px; padding: 10px; background-color: #FCF0AD;\">
							<p>Your account has not been confirmed.  Please click the link in the <strong>Stevenson LG&Q Account Confirmation</strong> email we sent you.</p>
							<p>For questions, contact Jack Cruse at <a href=\"mailto:jack.cruse@stevensoncompany.com\">jack.cruse@stevensoncompany.com</a> or 502-271-5272.</p>
							</div>";
			} else {
				$ErrorMsg = "<p style=\"text-align: center; font-weight: bold; font-size: 22px; color: red;\">Your Email or Password information is incorrect, please try again.</p>";
			}
		} else {
			$btnSubmit == "";
			setcookie('qLoggedIn', 0, $cookie_expire);
			$ErrorMsg = "<p style=\"text-align: center; font-weight: bold; font-size: 22px; color: red;\">Your Email or Password information is incorrect, please try again.</p>";			
		}	

	} else {
		//Check to see if the username and password is in the generic login
		$sqlGenericLogin = sprintf("select GenericCompanyLogin, GenericCompanyPassword from tblCompanies 
								where GenericCompanyLogin='%s' and GenericCompanyPassword='%s'"
								, strtolower($frmUserName), strtolower($frmUserPassword));
		GetResultSet($dbconn, $rsGenericLogin, $sqlGenericLogin);
		if(mysqli_num_rows($rsGenericLogin) == 1) {
			header('Location: create-account.php?btnSubmit=Initial&frmGenericCompanyLogin='.strtolower($frmUserName).'&frmGenericCompanyPassword='.strtolower($frmUserPassword));	
		} 
		$btnSubmit == "";
		setcookie('qLoggedIn', 0, $cookie_expire);
		$ErrorMsg = "<p style=\"text-align: center; font-weight: bold; font-size: 22px; color: red;\">Your User or Password information is incorrect, please try again.</p>";	
	}	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<link rel="icon" type="image/png" href="/favicon.png" />
	<title>Stevenson LG&amp;Q&trade; Administration</title>
	<meta name="description" content="Stevenson Like, Grade & Quality (LGQ&trade;) Application">
	<meta name="author" content="The Stevenson Company">

	<link rel="stylesheet" href="/styles/simplegrid.css">
	<link rel="stylesheet" href="/styles/main.css">
	<!--[if IE]>
		<script src="/common/<?=COMMONVERSION?>/scripts/html5shiv/dist/html5shiv-printshiv.js"></script>
	<![endif]-->
</head>

<body id="home">
		<!-- Grid 1/1-->
		<div class="grid">
			<div class="col-1-1">
				<div class="content">
					<a href="index.php"><img style="margin: 10px 0px 0px 200px;" src="/images/stevenson-like-grade-and-quality-alt.png" /></a>			
				</div>
			</div>
		</div>
		<div class="grid">
			<div class="col-4-12">
				<div class="content">&nbsp;
				</div>
			</div>
			<div class="col-4-12">
				<div class="content">
					<form name="login" id="login" action="<?=$_SERVER['PHP_SELF']?>" method="post">
						<table style="margin: 30px 0px 30px 50px;">
							<tr>
								<td>User:</td>
								<td><input name="frmUserName" id="frmUserName" type="text" /></td>
							</tr>
							<tr>
								<td>Password:</td>
								<td><input name="frmUserPassword" id="frmUserPassword" type="text" /></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td style="text-align: center;"><input  style="text-align: center; margin: 20px 0px 0px 0px;" type="submit" name="btnSubmit" id="btnSubmit" value="Login" /></td>
							</tr>
						</table>
						<?=$ErrorMsg?>
					</form>
				</div>
			</div>
			<div class="col-4-12">
				<div class="content">&nbsp;
				</div>
			</div>


		</div>
<?
//echo $_SERVER['SERVER_NAME'];
//echoarr(get_defined_vars());
?>
</body>
</html>