<?
#This program is copyright by Andre Coetzee email: ac@main.me
#and is licensed under the GPL v3
#
#
#
#
#Please add yourself to: http://www.accounting-123.com
#Developers, Software Vendors, Support, Accountants, Users
#
#
#The full software license can be found here:
#http://www.accounting-123.com/a.php?a=153/GPLv3
#
#
#
#
#
#
#
#
#
#
#


# trans-new.php :: debit-credit Transaction
#
##

# get settings
require("../settings.php");
require("../core-settings.php");

# decide what to do
if (isset($_POST["key"])) {
	switch ($_POST["key"]) {
		case "details":
			$OUTPUT = details($_POST);
			break;
		case "confirm":
			$OUTPUT = confirm($_POST);
			break;
		case "write":
			$OUTPUT = write($_POST);
			break;
		default:
			if(isset($_GET['ledgid'])){
				$OUTPUT = edit($_GET);
			}else{
				$OUTPUT = "<li class='err'> Invalid use of module.</li>";
			}
	}
} else {
	if(isset($_GET['ledgid'])){
		$OUTPUT = edit($_GET);
	}else{
		$OUTPUT = "<li class='err'> Invalid use of module.</li>";
	}
}

# get templete
require("../template.php");



# Select Accounts
function edit($_GET)
{

	# Get vars
	extract ($_GET);

	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($ledgid, "num", 1, 20, "Invalid Input Ledger Number.");

	# display errors, if any
	if ($v->isError ()) {
		$confirm = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirm .= "<li class='err'>".$e["msg"]."</li>";
		}
		$confirm .= "<p><input type='button' onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $confirm;
	}

	# Get ledger settings
	core_connect();

	$sql = "SELECT * FROM in_ledgers WHERE ledgid='$ledgid'  AND div = '".USER_DIV."'";
	$ledRslt = db_exec($sql);
	if(pg_numrows($ledRslt) < 1){
		return "<li>Invalid Input Ledger Number.</li>";
	}
	$led = pg_fetch_array($ledRslt);

	# accounts drop downs
	core_connect();

	# keep the charge vat option stable
	if($led['chrgvat'] == 'yes'){
		$chrgy = "checked=yes";
		$chrgn = "";
	}else{
		$chrgy = "";
		$chrgn = "checked=yes";
	}

	# keep the date option stable
	if($led['dateopt'] == 'system'){
		$dates = "checked=yes";
		$dateu = "";
	}else{
		$dates = "";
		$dateu = "checked=yes";
	}

	# keep the description option stable
	$num= "";$emp = ""; $once = ""; $edit = "";
	$$led['desopt'] = "checked=yes";

	# keep the refnum option stable
	$numr= "";$empr = ""; $oncer = ""; $editr = "";
	$led['refopt'] .= "r";
	$$led['refopt'] = "checked=yes";

	/* End Toggle Options */

	// Options Layout
	$Opts = "
		<center>
		<h3> High Speed Input Ledger Edit </h3>
		<form action='".SELF."' method='POST' name='form'>
			<input type='hidden' name='key' value='details'>
			<input type='hidden' name='ledgid' value='$ledgid'>
		<table ".TMPL_tblDflts." align='center'>
			<tr>
				<th colspan='3'>Details</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Ledger Name</td>
				<td><input type='text' size='30' name='lname' value='$led[lname]'></td>
			</tr>
			<tr>
				<th><h4>Debit</h4></th>
				<th><h4>Credit</h4></th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'>".mkAccSelect ("dtaccid", $led['dtaccid'])."</td>
				<td align='center'>".mkAccSelect ("ctaccid", $led['ctaccid'])."</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<th colspan='3'>Options</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Number of Entries</td>
				<td><input type='text' size='10' name='numtran' value='$led[numtran]'></td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Date Entry</td>
				<td><input type='radio' name='dateopt' value='system' $dates>System &nbsp;&nbsp; <input type='radio' name='dateopt' value='user' $dateu>User Input</td>
			<tr class='".bg_class()."'>
				<td>Charge Vat </td>
				<td><input type='radio' name='chrgvat' value='yes' $chrgy>Yes &nbsp;&nbsp; <input type='radio' name='chrgvat' value='no' $chrgn>No</td>
			</tr>
			<tr>
				<th colspan='3'>Description</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Description</td>
				<td><input type='text' size='30' name='descript' value='$led[descript]'></td>
			</tr>
			<tr class='".bg_class()."'>
				<td rowspan='3' valign='top'>Options</td>
				<td><input type='radio' name='desopt' value='emp' $emp> Empty Input Box</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='desopt' value='once' $once> Once Only Setting</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='desopt' value='edit' $edit> Default Editable</td>
			</tr>
			<tr>
				<th colspan='3'>Reference Number</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Reference Number</td>
				<td><input type='text' size='10' name='refnum' value='$led[refnum]'></td>
			</tr>
			<tr class='".bg_class()."'>
				<td rowspan='4' valign='top'>Options</td>
				<td><input type='radio' name='refopt' value='num' $numr> Auto Number</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='refopt' value='emp' $empr> Empty Input Box</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='refopt' value='once' $oncer> Once Only Setting</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='refopt' value='edit' $editr> Default Editable Input</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td align='right'><input type='button' value='&laquo Back' onClick='javascript:history.back()'></td>
				<td align='right'><input type='submit' value='Continue &raquo'></td>
			</tr>
		</table>
		</form>
		<p>
		<table border=0 cellpadding='2' cellspacing='1' width=15%>
			<tr>
				<th>Quick Links</th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='ledger-view.php'>View High Speed Input Ledgers</td>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='../main.php'>Main Menu</td>
			</tr>
		</table>";
	return $Opts;

}



# Error Handler
function error($_POST, $error)
{

	# Get vars
	extract ($_POST);

	# accounts drop downs
	core_connect();
	$dtacc = "<select name='dtaccid'>";
	$sql = "SELECT * FROM accounts WHERE div = '".USER_DIV."' ORDER BY accname ASC";
	$accRslt = db_exec($sql);
	if(pg_numrows($accRslt) < 1){
		return "<li>There are No accounts in Cubit.</li>";
	}
	while($acc = pg_fetch_array($accRslt)){
		if($acc['accid'] == $led['dtaccid']){
			$sel = "selected";
		}else{
			$sel = "";
		}
		# Check Disable
		if(isDisabled($acc['accid']))
			continue;
		$dtacc .= "<option value='$acc[accid]' $sel>$acc[topacc]/$acc[accnum] - $acc[accname]</option>";
	}
	$dtacc .= "</select>";

	$ctacc = "<select name='ctaccid'>";
	$sql = "SELECT * FROM accounts WHERE div = '".USER_DIV."' ORDER BY accname ASC";
	$accRslt = db_exec($sql);
	if(pg_numrows($accRslt) < 1){
		return "<li>There are No accounts in Cubit.";
	}
	while($acc = pg_fetch_array($accRslt)){
		if($acc['accid'] == $led['ctaccid']){
			$sel = "selected";
		}else{
			$sel = "";
		}
		# Check Disable
		if(isDisabled($acc['accid']))
			continue;
		$ctacc .= "<option value='$acc[accid]' $sel>$acc[topacc]/$acc[accnum] - $acc[accname]</option>";
	}
	$ctacc .= "</select>";

	/* Toggle options */

	# keep the charge vat option stable
	if($led['chrgvat'] == 'yes'){
		$chrgy = "checked=yes";
		$chrgn = "";
	}else{
		$chrgy = "";
		$chrgn = "checked=yes";
	}

	# keep the date option stable
	if($led['dateopt'] == 'system'){
		$dates = "checked=yes";
		$dateu = "";
	}else{
		$dates = "";
		$dateu = "checked=yes";
	}

	# keep the description option stable
	$num = "";
	$emp = "";
	$once = "";
	$edit = "";
	$$led['desopt'] = "checked=yes";

	# keep the refnum option stable
	$numr= "";
	$empr = "";
	$oncer = "";
	$editr = "";
	$led['refopt'] .= "r";
	$$led['refopt'] = "checked=yes";

	/* Toggle Options */

	// Options Layout
	$error = "
		<center>
		<h3> Edit High Speed Input Ledger </h3>
		<form action='".SELF."' method='POST' name='form'>
			<input type='hidden' name='key' value='details'>
			<input type='hidden' name='ledgid' value='$led[ledgid]'>
		<table ".TMPL_tblDflts." align='center'>
			<tr>
				<td colspan='3'>$error</td>
			</tr>
			<tr>
				<th colspan='3'>Details</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Ledger Name</td>
				<td><input type='text' size='30' name='lname' value='$led[lname]'></td>
			</tr>
			<tr>
				<th><h4>Debit</h4></th>
				<th><h4>Credit</h4></th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'>$dtacc</td>
				<td align='center'>$ctacc</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<th colspan='3'>Options</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Number of Entries</td>
				<td><input type='text' size='10' name='numtran' value='$led[numtran]'></td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Date Entry</td>
				<td><input type='radio' name='dateopt' value='system' $dates>System &nbsp;&nbsp; <input type='radio' name='dateopt' value='user' $dateu>User Input</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Charge Vat </td>
				<td><input type='radio' name='chrgvat' value='yes' $chrgy>Yes &nbsp;&nbsp; <input type='radio' name='chrgvat' value='no' $chrgn>No</td>
			</tr>
			<tr>
				<th colspan='3'>Description</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Description</td>
				<td><input type='text' size='30' name='descript' value='$led[descript]'></td>
			</tr>
			<tr class='".bg_class()."'>
				<td rowspan='3' valign='top'>Options</td>
				<td><input type='radio' name='desopt' value='emp' $emp> Empty Input Box</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='desopt' value='once' $once> Once Only Setting</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='desopt' value='edit' $edit> Default Editable</td>
			</tr>
			<tr>
				<th colspan='3'>Reference Number</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Reference Number</td>
				<td><input type='text' size='10' name='refnum' value='$led[refnum]'></td>
			</tr>
			<tr class='".bg_class()."'>
				<td rowspan='4' valign='top'>Options</td>
				<td><input type='radio' name='refopt' value='num' $numr> Auto Number</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='refopt' value='emp' $empr> Empty Input Box</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='refopt' value='once' $oncer> Once Only Setting</td>
			</tr>
			<tr class='".bg_class()."'>
				<td><input type='radio' name='refopt' value='edit' $editr> Default Editable Input</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td align='right'><input type='button' value='&laquo Back' onClick='javascript:history.back()'></td>
				<td align='right'><input type='submit' value='Continue &raquo'></td>
			</tr>
		</table>
		</form>
		<p>
		<table border=0 cellpadding='2' cellspacing='1' width='15%'>
			<tr>
				<th>Quick Links</th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='ledger-view.php'>View High Speed Input Ledgers</td>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='../main.php'>Main Menu</td>
			</tr>
		</table>";
	return $error;

}



# Select vat accounts
function slctacc($_POST)
{

	# Get vars
	extract ($_POST);

	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($ledgid, "num", 1, 20, "Invalid Input Ledger Number.");
	$v->isOk ($lname, "string", 1, 255, "Invalid Ledger Name.");
	$v->isOk ($dtaccid, "num", 1, 50, "Invalid Account to be Debited.");
	$v->isOk ($ctaccid, "num", 1, 50, "Invalid Account to be Credited.");
	$v->isOk ($chrgvat, "string", 1, 4, "Invalid charge vat option.");
	$v->isOk ($numtran, "num", 1, 20, "Invalid Number on entries.");
	$v->isOk ($dateopt, "string", 1, 10, "Invalid date entry option.");
	$v->isOk ($desopt, "string", 1, 5, "Invalid Description option.");
	if($desopt == 'once'){
		$v->isOk ($descript, "string", 1, 255, "Invalid Description.");
	}else{
		$v->isOk ($descript, "string", 0, 255, "Invalid Description.");
	}

	$v->isOk ($refopt, "string", 1, 5, "Invalid Reference number option.");
	if($refopt == 'once'){
		$v->isOk ($refnum, "string", 1, 255, "Invalid Reference number.");
	}else{
		$v->isOk ($refnum, "string", 0, 255, "Invalid Reference number.");
	}

	# display errors, if any
	if ($v->isError ()) {
		$confirm = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirm .= "<li class='err'>".$e["msg"]."</li>";
		}
		return error($_POST, $confirm);
		#$confirm .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		#return $confirm;
	}



	# Get ledger settings
	core_connect();

	$sql = "SELECT * FROM in_ledgers WHERE ledgid='$ledgid' AND div = '".USER_DIV."'";
	$ledRslt = db_exec($sql);
	if(pg_numrows($ledRslt) < 1){
		return "<li>Invalid Input Ledger Number.</li>";
	}
	$led = pg_fetch_array($ledRslt);

	# uppercase first letter of name
	$lname = ucfirst($lname);

	# Account numbers
	$dtaccRs = get("core","*","accounts","accid",$dtaccid);
	$dtacc  = pg_fetch_array($dtaccRs);
	$ctaccRs = get("core","*","accounts","accid",$ctaccid);
	$ctacc  = pg_fetch_array($ctaccRs);

	db_conn("core");

	$vatacc = "<select name='vataccid'>";
	$sql = "SELECT * FROM accounts WHERE div = '".USER_DIV."' ORDER BY accname ASC";
	$accRslt = db_exec($sql);
	if(pg_numrows($accRslt) < 1){
		return "<li>There are No accounts in Cubit.";
	}
	while($acc = pg_fetch_array($accRslt)){
		if($acc['accid'] == $led['vataccid']){
			$sel = "selected";
		}else{
			$sel = "";
		}
		# Check Disable
		if(isDisabled($acc['accid']))
			continue;
		$vatacc .= "<option value='$acc[accid]' $sel>$acc[topacc]/$acc[accnum] - $acc[accname]</option>";
	}
	$vatacc .= "</select>";

	# keep the vatded acc option stable
	if($led['vatdedacc'] == $ctaccid){
		$vatct = "checked=yes";
		$vatdt = "";
	}else{
		$vatct = "";
		$vatdt = "checked=yes";
	}

	# keep the vat inc option stable
	if($led['vatinc'] == 'no'){
		$vatincy = "";
		$vatincn = "checked=yes";
	}else{
		$vatincy = "checked=yes";
		$vatincn = "";
	}

	// Details
	$slctacc = "
		<center>
		<h3> High Speed Input Ledger Edit </h3>
		<h2>Select Vat Accounts</h2>
		<form action='".SELF."' method='POST' name='form'>
			<input type='hidden' name='key' value='details'>
			<input type='hidden' name='ledgid' value='$ledgid'>
			<input type='hidden' name='lname' value='$lname'>
			<input type='hidden' name='dtaccid' value='$dtaccid'>
			<input type='hidden' name='ctaccid' value='$ctaccid'>
			<input type='hidden' name='chrgvat' value='$chrgvat'>
			<input type='hidden' name='numtran' value='$numtran'>
			<input type='hidden' name='dateopt' value='$dateopt'>
			<input type='hidden' name='descript' value='$descript'>
			<input type='hidden' name='desopt' value='$desopt'>
			<input type='hidden' name='refnum' value='$refnum'>
			<input type='hidden' name='refopt' value='$refopt'>
		<table ".TMPL_tblDflts." align='center'>
			<tr>
				<th>Option</th>
				<th>Value</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Ledger Name</td>
				<td>$lname</td>
			</tr>
			<tr class='".bg_class()."'>
				<td valign='top'>Vat Deductable Account</td>
				<td><input type='radio' name='vatdedacc' value='$dtaccid' $vatdt>$dtacc[topacc]/$dtacc[accnum] - $dtacc[accname]<br><input type='radio' name='vatdedacc' value='$ctaccid' $vatct>$ctacc[topacc]/$ctacc[accnum] - $ctacc[accname]</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Vat Account</td>
				<td>$vatacc</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Vat Inclusive</td>
				<td><input type='radio' size='20' name='vatinc' value='yes' $vatincy>Yes &nbsp;&nbsp;<input type='radio' size='20' name='vatinc' value='no' $vatincn>No</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td align='right'><input type='button' value='&laquo Back' onClick='javascript:history.back()'></td><td align='right'><input type='submit' value='Continue &raquo'></td></tr>
		</table>
		</form>
		<p>
		<table border=0 cellpadding='2' cellspacing='1' width=15%>
			<tr>
				<th>Quick Links</th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='ledger-view.php'>View High Speed Input Ledgers</td>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='../main.php'>Main Menu</td>
			</tr>
		</table>";
	return $slctacc;

}



# Enter Details of Transaction
function details($_POST)
{

	# Get vars
	extract ($_POST);

	#redirect if must chrgvat
	if($chrgvat == 'yes' && !isset($vataccid)){
		return slctacc($_POST);
	}

	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($ledgid, "num", 1, 20, "Invalid Input Ledger Number.");
	$v->isOk ($lname, "string", 1, 255, "Invalid Ledger Name.");
	$v->isOk ($dtaccid, "num", 1, 50, "Invalid Account to be Debited.");
	$v->isOk ($ctaccid, "num", 1, 50, "Invalid Account to be Credited.");
	$v->isOk ($chrgvat, "string", 1, 4, "Invalid charge vat option.");
	$v->isOk ($numtran, "num", 1, 20, "Invalid Number on entries.");
	$v->isOk ($dateopt, "string", 1, 10, "Invalid date entry option.");
	$v->isOk ($desopt, "string", 1, 5, "Invalid Description option.");
	if($desopt == 'once'){
		$v->isOk ($descript, "string", 1, 255, "Invalid Description.");
	}else{
		$v->isOk ($descript, "string", 0, 255, "Invalid Description.");
	}

	$v->isOk ($refopt, "string", 1, 5, "Invalid Reference number option.");
	if($refopt == 'once'){
		$v->isOk ($refnum, "string", 1, 255, "Invalid Reference number.");
	}else{
		$v->isOk ($refnum, "string", 0, 255, "Invalid Reference number.");
	}
	if($chrgvat == 'yes'){
		$v->isOk ($vatdedacc, "num", 1, 50, "Invalid Vat Deductable Account number.");
		$v->isOk ($vataccid, "num", 1, 50, "Invalid Vat Account number.");
		$v->isOk ($vatinc, "string", 1, 3, "Invalid vat inclusive selection.");
	}

	# display errors, if any
	if ($v->isError ()) {
		$confirm = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirm .= "<li class='err'>".$e["msg"]."</li>";
		}
		return error($_POST, $confirm);
		# $confirm .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		# return $confirm;
	}

	# Account numbers
	$dtaccRs = get("core","*","accounts","accid",$dtaccid);
	$dtacc  = pg_fetch_array($dtaccRs);
	$ctaccRs = get("core","*","accounts","accid",$ctaccid);
	$ctacc  = pg_fetch_array($ctaccRs);
	if($chrgvat == 'yes'){
		$vataccRs = get("core","*","accounts","accid",$vataccid);
		$vatacc  = pg_fetch_array($vataccRs);
		$vatin = ucwords($vatinc);
		$vataccnum = "
			<tr class='".bg_class()."'>
				<td>Vat Account</td>
				<td><input type='hidden' name='vataccid' value='$vataccid'><input type='hidden' name='vatdedacc' value='$vatdedacc'>$vatacc[topacc]/$vatacc[accnum] - $vatacc[accname]</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Vat Inclusive</td>
				<td><input type='hidden' name='vatinc' value='$vatinc'>$vatin</td>
			</tr>";
	}else{
		$vataccnum = "";
	}

	/* Toggle Options */

	# Charge Vat Option
	$vat = ucwords($chrgvat);

	# Date Option
	if($dateopt == 'system'){
		$date = 'System Date';
	}elseif($dateopt == 'user'){
		$date = 'User Input Date';
	}

	# Description and Refnum Option
	$options = array("num"=>"Auto Number", "emp"=>"Empty Input Box", "once"=>"Once Only Setting", "edit"=>"Default Editable Input");
	$descriptopt = $options[$desopt];
	$refnumopt = $options[$refopt];

	# put auto number if its auto number
	if($refopt == 'num'){
		$refnums = $options[$refopt];
	}else{
		$refnums = $refnum;
	}

	/* Toggle Options */

	# uppercase first letter of name
	$lname = ucfirst($lname);

	// Details
	$details = "
		<center>
		<h3> High Speed Input Ledger Edit </h3>
		<form action='".SELF."' method='POST' name='form'>
			<input type='hidden' name='key' value='write'>
			<input type='hidden' name='ledgid' value='$ledgid'>
			<input type='hidden' name='lname' value='$lname'>
			<input type='hidden' name='dtaccid' value='$dtaccid'>
			<input type='hidden' name='ctaccid' value='$ctaccid'>
			<input type='hidden' name='chrgvat' value='$chrgvat'>
			<input type='hidden' name='numtran' value='$numtran'>
			<input type='hidden' name='dateopt' value='$dateopt'>
			<input type='hidden' name='descript' value='$descript'>
			<input type='hidden' name='desopt' value='$desopt'>
			<input type='hidden' name='refnum' value='$refnum'>
			<input type='hidden' name='refopt' value='$refopt'>
		<table ".TMPL_tblDflts." align='center'>
			<tr>
				<th>Option</th>
				<th>Value</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Ledger Name</td>
				<td>$lname</td>
			</tr>
			<tr>
				<th><h4>Debit</h4></th>
				<th><h4>Credit</h4></th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'>$dtacc[topacc]/$dtacc[accnum] - $dtacc[accname]</td>
				<td align='center'>$ctacc[topacc]/$ctacc[accnum] - $ctacc[accname]</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<th colspan='3'>Options</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Number of Entries</td>
				<td>$numtran</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Date Entry</td>
				<td>$date</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Charge Vat </td>
				<td>$vat</td>
			</tr>
			$vataccnum
			<tr>
				<th colspan='3'>Description</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Description</td>
				<td>$descript</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Option</td>
				<td>$descriptopt</td>
			</tr>
			<tr>
				<th colspan='3'>Reference Number</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Reference Number</td>
				<td>$refnums</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Option</td>
				<td>$refnumopt</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td align='right'><input type='button' value='&laquo Back' onClick='javascript:history.back()'></td>
				<td align='right'><input type='submit' value='Continue &raquo'></td>
			</tr>
		</table>
		</form>
		<p>
		<table border=0 cellpadding='2' cellspacing='1' width='15%'>
			<tr>
				<th>Quick Links</th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='ledger-new.php'>New High Speed Input Ledger</td>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='ledger-view.php'>View High Speed Input Ledgers</td>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='../main.php'>Main Menu</td>
			</tr>
		</table>";
	return $details;

}



# Write
function write($_POST)
{

	# Get vars
	extract ($_POST);

	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($ledgid, "num", 1, 20, "Invalid Input Ledger Number.");
	$v->isOk ($lname, "string", 1, 255, "Invalid Ledger Name.");
	$v->isOk ($dtaccid, "num", 1, 50, "Invalid Account to be Debited.");
	$v->isOk ($ctaccid, "num", 1, 50, "Invalid Account to be Credited.");
	$v->isOk ($chrgvat, "string", 1, 4, "Invalid charge vat option.");
	$v->isOk ($numtran, "num", 1, 20, "Invalid Number on entries.");
	$v->isOk ($dateopt, "string", 1, 10, "Invalid date entry option.");
	$v->isOk ($desopt, "string", 1, 5, "Invalid Description option.");
	if($desopt == 'once'){
		$v->isOk ($descript, "string", 1, 255, "Invalid Description.");
	}else{
		$v->isOk ($descript, "string", 0, 255, "Invalid Description.");
	}

	$v->isOk ($refopt, "string", 1, 5, "Invalid Reference number option.");
	if($refopt == 'once'){
		$v->isOk ($refnum, "string", 1, 255, "Invalid Reference number.");
	}else{
		$v->isOk ($refnum, "string", 0, 255, "Invalid Reference number.");
	}
	if($chrgvat == 'yes'){
		$v->isOk ($vatdedacc, "num", 1, 50, "Invalid Vat Deductable Account number.");
		$v->isOk ($vataccid, "num", 1, 50, "Invalid Vat Account number.");
		$v->isOk ($vatinc, "string", 1, 3, "Invalid vat inclusive selection.");
	}

	# display errors, if any
	if ($v->isError ()) {
		$confirm = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirm .= "<li class='err'>".$e["msg"]."</li>";
		}
		return error($_POST, $confirm);
		# $confirm .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		# return $confirm;
	}



	// Accounts details
	$dtaccRs = get("core","*","accounts","accid",$dtaccid);
	$dtacc  = pg_fetch_array($dtaccRs);
	$ctaccRs = get("core","*","accounts","accid",$ctaccid);
	$ctacc  = pg_fetch_array($ctaccRs);

	# insert the ledger into the DB
	core_connect();
	if($chrgvat == 'yes'){
		$sql = "
			UPDATE in_ledgers 
			SET lname ='$lname', dtaccid ='$dtaccid', ctaccid ='$ctaccid', chrgvat ='$chrgvat', 
				numtran ='$numtran', dateopt ='$dateopt', descript ='$descript', desopt ='$desopt', 
				refnum ='$refnum', refopt ='$refopt', vatdedacc ='$vatdedacc', vataccid ='$vataccid', 
				vatinc ='$vatinc' 
			WHERE ledgid = '$ledgid' AND div = '".USER_DIV."'";
	}else{
		$sql = "
			UPDATE in_ledgers 
			SET lname ='$lname', dtaccid ='$dtaccid', ctaccid ='$ctaccid', chrgvat ='$chrgvat', 
				numtran ='$numtran', dateopt ='$dateopt', descript ='$descript', desopt ='$desopt', 
				refnum ='$refnum', refopt ='$refopt' 
			WHERE ledgid = '$ledgid' AND div = '".USER_DIV."'";
	}
	$Rslt = db_exec($sql) or errDie("Unable to insert new input legder to Cubit", SELF);

	// Start layout
	$write = "
		<center>
		<table ".TMPL_tblDflts." width='500'>
			<tr>
				<th colspan='2'>High Speed Input Ledger Edit</th>
			</tr>
			<tr>
				<td class='".bg_class()."' colspan='2'>High Speed Input Ledger : <b>$lname</b> has been edited.</td>
			</tr>
			<tr>
				<td width='50%'><h3>Debit</h3></td>
				<td width='50%'><h3>Credit</h3></td>
			</tr>
			<tr class='".bg_class()."'>
				<td>$dtacc[topacc]/$dtacc[accnum] - $dtacc[accname]</td>
				<td>$ctacc[topacc]/$ctacc[accnum] - $ctacc[accname]</td>
			</tr>
		</table>
		<P>
		<table ".TMPL_tblDflts." width='25%'>
			<tr>
				<th>Quick Links</th>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='ledger-new.php'>New High Speed Input Ledger</td>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='ledger-view.php'>View High Speed Input Ledgers</td>
			</tr>
			<tr class='".bg_class()."'>
				<td align='center'><a href='../main.php'>Main Menu</td>
			</tr>
		</table>";
	return $write;

}



?>