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

# get settings
require("settings.php");
require("core-settings.php");
require("libs/ext.lib.php");

# decide what to do
if (isset($_GET["quoid"]) && isset($_GET["cont"])) {
	$_GET["stkerr"] = '0,0';
	$OUTPUT = details($_GET);
} else if (isset($_GET["quoid"])) {
	$OUTPUT = details($_GET);
}else{
	if (isset($_POST["key"])) {
		switch ($_POST["key"]) {
			case "update":
				$OUTPUT = write($_POST);
				break;
			default:
			case "details":
				$OUTPUT = details($_POST);
				break;
			}
	} else {
		$OUTPUT = details($_POST);
	}
}

# get templete
require("template.php");



# Default view
function view()
{

	# Query server for depts
	db_conn("exten");

	$sql = "SELECT * FROM departments WHERE div = '".USER_DIV."' ORDER BY deptname ASC";
	$deptRslt = db_exec ($sql) or errDie ("Unable to view customers");
	if (pg_numrows ($deptRslt) < 1) {
		return "<li class='err'>There are no Departments found in Cubit.</li>";
	}else{
		$depts = "<select name='deptid'>";
		while($dept = pg_fetch_array($deptRslt)){
			$depts .= "<option value='$dept[deptid]'>$dept[deptname]</option>";
		}
		$depts .= "</select>";
	}

	//layout
	$view = "
		<br><br>
		<form action='".SELF."' method='POST' name='form'>
		<table ".TMPL_tblDflts." width='400'>
			<input type='hidden' name='key' value='details'>
			<input type='hidden' name='cussel' value='cussel'>
			<tr>
				<th colspan='2'>New Quote</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>".REQ."Select Department</td>
				<td valign='center'>$depts</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>First Letters of customer</td>
				<td valign='center'><input type='text' size='5' name='letters' maxlength='5'></td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td></td>
				<td valign='center'><input type='submit' value='Continue &raquo'></td>
			</tr>
		</table>
		</form>"
		.mkQuickLinks(
			ql("quote-view.php", "View Quotes"),
			ql("customers-new.php", "New Customer")
		);
    return $view;

}



# Default view
function view_err($_POST, $err = "")
{

	# get vars
	extract ($_POST);

	# Query server for depts
	db_conn("exten");

	$sql = "SELECT * FROM departments WHERE div = '".USER_DIV."' ORDER BY deptname ASC";
	$deptRslt = db_exec ($sql) or errDie ("Unable to view customers");
	if (pg_numrows ($deptRslt) < 1) {
		return "<li class='err'>There are no Departments found in Cubit.</li>";
	}else{
		$depts = "<select name='deptid'>";
		while($dept = pg_fetch_array($deptRslt)){
			if(isset($deptid) && $dept['deptid'] == $deptid){
				$sel = "selected";
			}else{
				$sel = "";
			}
			$depts .= "<option value='$dept[deptid]' $sel>$dept[deptname]</option>";
		}
		$depts .= "</select>";
	}

	//layout
	$view = "
		<br><br>
		<form action='".SELF."' method='POST' name='form'>
		<table ".TMPL_tblDflts." width='400'>
			<input type='hidden' name='key' value='details'>
			<input type='hidden' name='cussel' value='cussel'>
			<tr>
				<th colspan='2'>New Quote</th>
			</tr>
			<tr>
				<td colspan='2'>$err</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Select Department</td>
				<td valign='center'>$depts</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>First Letters of customer</td>
				<td valign='center'><input type='text' size='5' name='letters' value='$letters' maxlength=5></td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td></td>
				<td valign='center'><input type='submit' value='Continue &raquo'></td>
			</tr>
		</table>
		</form>"
		.mkQuickLinks(
			ql("quote-view.php", "View Quotes"),
			ql("customers-new.php", "New Customer")
		);
	return $view;

}



# create a dummy quote
function create_dummy($deptid)
{

	db_connect();

	# Dummy Vars
	$cusnum = 0;
	$salespn = "";
	$comm = "";
	$salespn = "";
	$chrgvat = getSetting("SELAMT_VAT");
//	$odate = date("Y-m-d");
	$followondate = date("Y-m-d");
	$ordno = "";
	$delchrg = "0.00";
	$cordno = "";
	$terms = 0;
	$traddisc = 0;
	$SUBTOT = 0;
	$vat = 0;
	$total = 0;

	$trans_date_setting = getCSetting ("USE_TRANSACTION_DATE");
	if (isset ($trans_date_setting) AND $trans_date_setting == "yes"){
		$trans_date_value = getCSetting ("TRANSACTION_DATE");
		$date_arr = explode ("-", $trans_date_value);
		$date_year = $date_arr[0];
		$date_month = $date_arr[1];
		$date_day = $date_arr[2];
	}else {
		$date_year = date("Y");
		$date_month = date("m");
		$date_day = date("d");
	}
	$odate = "$date_year-$date_month-$date_day";

	// $quoid = divlastid('quo', USER_DIV);

	# insert quote to DB
	$sql = "
		INSERT INTO quotes (
			deptid, cusnum, cordno, ordno, chrgvat, terms, traddisc, salespn, odate, delchrg, 
			subtot, vat, total, balance, comm, username, accepted, done, div, ncdate
		) VALUES (
			'$deptid', '$cusnum',  '$cordno', '$ordno', '$chrgvat', '$terms', '$traddisc', '$salespn', '$odate', '$delchrg', 
			'$SUBTOT', '$vat' , '$total', '$total', '$comm', '".USER_NAME."', 'n', 'n', '".USER_DIV."', '$followondate'
		)";
	$rslt = db_exec($sql) or errDie("Unable to insert quote to Cubit.",SELF);

	# get next ordnum
	$quoid = pglib_lastid ("quotes", "quoid");

	return $quoid;

}



# details
function details($_POST, $error="")
{

	extract($_POST);

	# validate input
	include("libs/validate.lib.php");
	$v = new validate ();
	if (isset($quoid)) {
		$v->isOk($quoid, "num", 1, 20, "Invalid quote number.");
	}

	if (isset($deptid)) {
		$v->isOk($deptid, "num", 1, 20, "Invalid department number.");
	}

	if (isset($letters)) {
		$v->isOk($letters, "string", 0, 5, "Invalid First 3 Letters.");
	}

	# display errors, if any
	if ($v->isError ()) {
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$error .= "<li class='err'>$e[msg]</li>";
		}
		$confirm .= "$error<p><input type='button' onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $confirm;
	}



	if (!isset($deptid)) {
		$deptid = 0;
	} else if (isset($quoid)) {
		db_conn("cubit");
		$sql = "UPDATE quotes SET deptid='$deptid' WHERE quoid='$quoid' AND deptid<>'$deptid'";
		db_exec($sql) or errDie("Error updating invoice department.");
	}

	if(!isset($quoid)){
		$quoid = create_dummy($deptid);
	}

	if (!isset($stkerr)) {
		$stkerr = "0,0";
	}

	if(!isset($done)){
		$done = "";
	}

	if (!isset($sel_frm)) {
		$sel_frm = "stkcod";
	}



	# Get quote info
	db_connect();

	$sql = "SELECT * FROM quotes WHERE quoid = '$quoid' AND div = '".USER_DIV."'";
	$quoRslt = db_exec ($sql) or errDie ("Unable to get quote information");
	if (pg_numrows ($quoRslt) < 1) {
		return "<li class='err'>Quote Not Found</li>";
	}
	$quo = pg_fetch_array($quoRslt);

	# check if quote has been printed
	if($quo['accepted'] == "y"){
		$error = "<li class='err'> Error : Quote number <b>$quoid</b> has already been printed.</li>";
		$error .= "<p><input type='button' onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $error;
	}

	if(!isset($lead)){
		$lead = $quo["lead"];
	}

	//manual error handling
	if(!isset($quo['ncdate'])) $quo['ncdate'] = "";

	if(strlen($quo['ncdate']) < 1){
		$ncdate_year = date("Y");
		$ncdate_month = date("m",mktime(0,0,0,date("m"),date("d")+5,date("Y")));
		$ncdate_day = date("d",mktime(0,0,0,date("m"),date("d")+5,date("Y")));
	}else {
		$darr = explode ("-",$quo['ncdate']);
		$ncdate_year = $darr['0'];
		$ncdate_month = $darr['1'];
		$ncdate_day = $darr['2'];
	}

	# get department
	db_conn("exten");

	$sql = "SELECT * FROM departments WHERE deptid = '$quo[deptid]' AND div = '".USER_DIV."'";
	$deptRslt = db_exec($sql);
	if(pg_numrows($deptRslt) < 1){
		$dept['deptname'] = "<li class='err'>Department not Found.</li>";
	}else{
		$dept = pg_fetch_array($deptRslt);
	}

	# Get selected customer info
	if (isset($letters)) {
		db_connect();
		$sql = "SELECT * FROM customers WHERE cusnum = '$quo[cusnum]' AND div = '".USER_DIV."'";
		$custRslt = db_exec ($sql) or errDie ("Unable to view customer");
		if (pg_numrows ($custRslt) < 1) {

			db_connect();

			if ($inv['deptid'] == 0){
				$searchdept = "";
			}else {
				$searchdept = "deptid = '$quo[deptid]' AND ";
			}

			# Query server for customer info
			$sql = "SELECT cusnum,cusname,surname FROM customers WHERE $searchdept location != 'int' AND lower(surname) LIKE lower('$letters%') AND div = '".USER_DIV."' ORDER BY surname";
			$custRslt = db_exec ($sql) or errDie ("Unable to view customers");
			if (pg_numrows ($custRslt) < 1) {
				$ajax_err = "<li class='err'>No customer names starting with <b>$letters</b> in database.</li>";
				//return view_err($_POST, $err);
			}else{
				$customers = "<select name='cusnum' onChange='javascript:document.form.submit();'>";
				$customers .= "<option value='-S' selected>Select Customer</option>";
				while($cust = pg_fetch_array($custRslt)){
					$customers .= "<option value='$cust[cusnum]'>$cust[cusname] $cust[surname]</option>";
				}
				$customers .= "</select>";
			}
			# take care of the unset vars
			$cust['addr1'] = "";
			$cust['cusnum'] = "";
			$cust['vatnum'] = "";
			$cust['accno'] = "";
		}else{
			$cust = pg_fetch_array($custRslt);
			# moarn if customer account has been blocked
			if($cust['blocked'] == 'yes'){
				return "<li class='err'>Error : Selected customer account has been blocked.</li>";
			}
			$customers = "<input type='hidden' name=cusnum value='$cust[cusnum]'>$cust[cusname]  $cust[surname]";
			$cusnum = $cust['cusnum'];
		}
	}

/* --- Start Drop Downs --- */

	# Select warehouse
	db_conn("exten");

	//no good ... breaks the search feature because it doesnt send search with
	// onChange='javascript:document.form.submit();'
	$whs = "<select name='whidss[]'>";
	$sql = "SELECT * FROM warehouses WHERE div = '".USER_DIV."' ORDER BY whname ASC";
	$whRslt = db_exec($sql);
	if(pg_numrows($whRslt) < 1){
		return "<li class='err'> There are no Stores found in Cubit.</li>";
	}else{
		$whs .= "<option value='-S' disabled selected>Select Store</option>";
		while($wh = pg_fetch_array($whRslt)){
			if (!user_in_store_team($wh["whid"], USER_ID)) continue;
			$whs .= "<option value='$wh[whid]'>($wh[whno]) $wh[whname]</option>";
		}
	}
	$whs .= "</select>";

	# get sales people
	db_conn("exten");

	$sql = "SELECT * FROM salespeople WHERE div = '".USER_DIV."' ORDER BY salesp ASC";
	$salespRslt = db_exec ($sql) or errDie ("Unable to get sales people.");
	if (pg_numrows ($salespRslt) < 1) {
		return "<li class='err'> There are no Sales People found in Cubit.</li>";
	}else{
		$salesps = "<select name='salespn'>";
		while($salesp = pg_fetch_array($salespRslt)){
			if($salesp['salesp'] == $quo['salespn']){
				$sel = "selected";
			}else{
				$sel = "";
			}
			$salesps .= "<option value='$salesp[salesp]' $sel>$salesp[salesp]</option>";
		}
		$salesps .= "</select>";
	}

	# days drop downs
	$days = array("0"=>"0","7"=>"7","14"=>"14","30"=>"30","60"=>"60","90"=>"90","120"=>"120");
	$termssel = extlib_cpsel("terms", $days, $quo['terms']);

	# Keep the charge vat option stable
	if($quo['chrgvat'] == "inc"){
		$chin = "checked=yes";
		$chex = "";
		$chno = "";
	}elseif($quo['chrgvat'] == "exc"){
		$chin = "";
		$chex = "checked=yes";
		$chno = "";
	}else{
		$chin = "";
		$chex = "";
		$chno = "checked=yes";
	}

	# format date
	list($quote_year, $quote_month, $quote_day) = explode("-", $quo['odate']);
//	list($followon_year, $followon_month, $followon_day) = explode("-", $quo['ncdate']);

/* --- End Drop Downs --- */
	// get the ID of the first warehouse
	db_conn("exten");

	$sql = "SELECT whid FROM warehouses ORDER BY whid ASC LIMIT 1";
	$rslt = db_exec($sql) or errDie("Error reading warehouses (FWH).");

	if ( pg_num_rows($rslt) > 0 ) {
		$FIRST_WH = pg_fetch_result($rslt, 0, 0);
	} else {
		$FIRST_WH = "-S";
	}
/* --- Start Products Display --- */

	# select all products
	$products = "
		<table ".TMPL_tblDflts." width='100%'>
			<tr>
				<th>STORE</th>
				<th>ITEM NUMBER</th>
				<th>VAT CODE</th>
				<th>DESCRIPTION</th>
				<th>QTY</th>
				<th>UNIT PRICE</th>
				<th>UNIT DISCOUNT</th>
				<th>AMOUNT</th>
				<th>Remove</th>
			<tr>";

	# get selected stock in this quote
	db_connect();

	$sql = "SELECT * FROM quote_items  WHERE quoid = '$quoid' AND div = '".USER_DIV."'";
	$stkdRslt = db_exec($sql);
	$i = 0;
	$key = 0;
	while ($stkd = pg_fetch_array($stkdRslt)) {
		$stkd['account'] += 0;
		if($stkd['account'] != 0) {

			# Keep track of selected stock amounts
			$amts[$i] = $stkd['amt'];
			$i++;

			db_conn('core');

			$Sl = "SELECT accid,topacc,accnum,accname FROM accounts WHERE acctype='I' ORDER BY accname";
			$Ri = db_exec($Sl) or errDie("Unable to get accounts.");

			$Accounts = "
				<select name='accounts[]'>
					<option value='0'>Select Account</option>";
			while($ad = pg_fetch_array($Ri)) {
				if(isb($ad['accid'])) {
					continue;
				}
				if($ad['accid'] == $stkd['account']) {
					$sel = "selected";
				} else {
					$sel = "";
				}
				$Accounts .= "<option value='$ad[accid]' $sel>$ad[accname]</option>";
			}
			$Accounts .= "</select>";

			$sernos = "";

			$stkd['unitcost'] = sprint($stkd['unitcost']);
			$stkd['amt'] = sprint($stkd['amt']);

			# Input qty if not serialised
			$qtyin = "<input type='text' size='3' name='qtys[]' value='$stkd[qty]'>";

			$viewcost = "<input type='text' size='8' name='unitcost[]' value='$stkd[unitcost]'>";

			db_conn('cubit');

			$Sl = "SELECT * FROM vatcodes ORDER BY code";
			$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

			$Vatcodes = "
				<select name='vatcodes[]'>
					<option value='0'>Select</option>";
			while($vd = pg_fetch_array($Ri)) {
				if($stkd['vatcode'] == $vd['id']) {
					$sel = "selected";
				} else {
					$sel = "";
				}
				$Vatcodes .= "<option value='$vd[id]' $sel>$vd[code]</option>";
			}
			$Vatcodes.="</select>";

			//print "fo";

			# Put in product
			$products .= "
				<tr class='".bg_class()."'>
					<td colspan='2'>$Accounts<input type='hidden' name='whids[]' value='$stkd[whid]'></td>
					<td><input type='hidden' name='stkids[]' value='$stkd[stkid]'>$Vatcodes</td>
					<td><input type='text' size='20' name='descriptions[]' value='$stkd[description]'> $sernos</td>
					<td>$qtyin</td>
					<td>$viewcost</td>
					<td><input type='hidden' name='disc[]' value='$stkd[disc]'><input type='hidden' name='discp[]' value='$stkd[discp]'></td>
					<td nowrap><input type='hidden' name='amt[]' value='$stkd[amt]'> ".CUR." $stkd[amt]</td>
					<td><input type='checkbox' name='remprod[]' value='$key'><input type='hidden' name='SCROLL' value='yes'></td>
				</tr>";
			$key++;

		} else {

			# keep track of selected stock amounts
			$amts[$i] = $stkd['amt'];
			$i++;

			# get selected stock in this warehouse
			db_connect();

			$sql = "SELECT * FROM stock WHERE stkid = '$stkd[stkid]' AND div = '".USER_DIV."'";
			$stkRslt = db_exec($sql);
			$stk = pg_fetch_array($stkRslt);

			# get warehouse name
			db_conn("exten");

			$sql = "SELECT whname FROM warehouses WHERE whid = '$stk[whid]' AND div = '".USER_DIV."'";
			$whRslt = db_exec($sql);
			$wh = pg_fetch_array($whRslt);

			db_conn('cubit');

			$Sl = "SELECT * FROM vatcodes ORDER BY code";
			$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

			$Vatcodes = "
				<select name='vatcodes[]'>
					<option value='0'>Select</option>";
			while($vd = pg_fetch_array($Ri)) {
				if($stkd['vatcode'] == $vd['id']) {
					$sel = "selected";
				} else {
					$sel = "";
				}
				$Vatcodes .= "<option value='$vd[id]' $sel>$vd[code]</option>";
			}
			$Vatcodes .= "</select>";

			$stkd['unitcost'] = sprint($stkd['unitcost']);
			$stkd['amt'] = sprint($stkd['amt']);

			# put in product
			$products .= "
				<input type='hidden' name='accounts[]' value='0'>
				<input type='hidden' name='descriptions[]' value=''>
				<tr class='".bg_class()."'>
					<td><input type='hidden' name='whids[]' value='$stkd[whid]'>$wh[whname]</td>
					<td><input type='hidden' name='stkids[]' value='$stkd[stkid]'><a href='#' onclick='openwindow(\"stock-amt-det.php?stkid=$stk[stkid]\")'>$stk[stkcod]</a></td>
					<td>$Vatcodes</td>
					<td>".extlib_rstr($stk['stkdes'], 30)."</td>
					<td><input type='text' size='3' name='qtys[]' value='$stkd[qty]'></td>
					<td><input type='text' size='8' name='unitcost[]' value='$stkd[unitcost]'></td>
					<td><input type='text' size='4' name='disc[]' value='$stkd[disc]'> OR <input type='text' size='4' name='discp[]' value='$stkd[discp]' maxlength=5>%</td>
					<td nowrap><input type='hidden' name='amt[]' value='$stkd[amt]'> ".CUR." $stkd[amt]</td>
					<td><input type='checkbox' name='remprod[]' value='$key'><input type='hidden' name='SCROLL' value='yes'></td>
				</tr>";
			$key++;
		}
	}

	# Look above(remprod keys)
	$keyy = $key;

	# look above(if i = 0 then there are no products)
	if($i == 0){
		$done = "";
	}

	# check if stock warehouse was selected
	if(isset($whidss)){
		foreach($whidss as $key => $whid){
			if(isset($stkidss[$key]) && $stkidss[$key] != "-S" && isset($cust['pricelist'])){
				# skip if not selected
				if($whid == "-S"){
					continue;
				}

				# get selected stock in this warehouse
				db_connect();

				$sql = "SELECT * FROM stock WHERE stkid = '$stkidss[$key]' AND div = '".USER_DIV."' ORDER BY stkcod ASC";
				$stkRslt = db_exec($sql);
				$stk = pg_fetch_array($stkRslt);

				# get selected warehouse name
				db_conn("exten");
				$sql = "SELECT whname FROM warehouses WHERE whid = '$stk[whid]' AND div = '".USER_DIV."'";
				$whRslt = db_exec($sql);
				$wh = pg_fetch_array($whRslt);

				# get price from price list if it is set
				if(isset($cust['pricelist'])){
					# get selected stock in this warehouse
					db_conn("exten");
					$sql = "SELECT price FROM plist_prices WHERE listid = '$cust[pricelist]' AND stkid = '$stk[stkid]' AND div = '".USER_DIV."'";
					$plRslt = db_exec($sql);
					if(pg_numrows($plRslt) > 0){
						$pl = pg_fetch_array($plRslt);
						$stk['selamt'] = $pl['price'];
					}
				}

				/* -- Start Some Checks -- */
				# check if they are selling too much
				if(($stk['units'] - $stk['alloc']) < $qtyss[$key]){
					if(!in_array($stk['stkid'], explode(",", $stkerr))){
						if($stk['type'] != 'lab'){
							$stkerr .= ",$stk[stkid]";
							$error .= "<li class='err'>Warning :  Item number <b>$stk[stkcod]</b> does not have enough items available.</li>";
						}
					}
				}
				/* -- End Some Checks -- */

				# Calculate the Discount discount
				if($discs[$key] < 1){
					if($discps[$key] > 0){
						$discs[$key] = round((($discps[$key]/100) * $stk['selamt']), 2);
					}
				}else{
					$discps[$key] = round((($discs[$key] * 100) / $stk['selamt']), 2);
				}

				# Calculate amount
				$amt[$key] = ($qtyss[$key] * ($stk['selamt'] - $discs[$key]));

				db_conn('cubit');

				$Sl = "SELECT * FROM vatcodes ORDER BY code";
				$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

				$Vatcodes = "
					<select name='vatcodes[]'>
						<option value='0'>Select</option>";
				while($vd = pg_fetch_array($Ri)) {
					if($stk['vatcode'] == $vd['id']) {
						$sel = "selected";
					} else {
						$sel = "";
					}
					$Vatcodes .= "<option value='$vd[id]' $sel>$vd[code]</option>";
				}
				$Vatcodes .= "</select>";

				$stk['selamt'] = sprint($stk['selamt']);
				$amt[$key] = sprint($amt[$key]);

				# put in selected warehouse and stock
				$products .= "
					<input type='hidden' name='accounts[]' value='0'>
					<input type='hidden' name='descriptions[]' value=''>
					<tr class='".bg_class()."'>
						<td><input type='hidden' name='whids[]' value='$whid'>$wh[whname]</td>
						<td><input type='hidden' name='stkids[]' value='$stk[stkid]'><a href='#' onclick='openwindow(\"stock-amt-det.php?stkid=$stk[stkid]\")'>$stk[stkcod]</a></td>
						<td>$Vatcodes</td>
						<td>".extlib_rstr($stk['stkdes'], 30)."</td>
						<td><input type='text' size='3' name='qtys[]' value='$qtyss[$key]'></td>
						<td><input type='text' size='8' name='unitcost[]'  value='$stk[selamt]'></td>
						<td><input type='text' size='4' name='disc[]' value='$discs[$key]'> OR <input type='text' size='4' name='discp[]' value='$discps[$key]' maxlength='5'>%</td>
						<td nowrap><input type='hidden' name='amt[]' value='$amt[$key]'> ".CUR." $amt[$key]</td>
						<td><input type='checkbox' name='remprod[]' value='$keyy'></td>
					</tr>";
				$keyy++;
			}elseif(isset($accountss[$key]) && $accountss[$key] != "0" && isset($cust['pricelist'])){

				db_conn('core');

				$Sl = "SELECT * FROM accounts WHERE accid='$accountss[$key]'";
				$Ri = db_exec($Sl) or errDie("Unable to get account data.");

				if(pg_num_rows($Ri) < 1) {
					return "invalid.";
				}

				$ad = pg_fetch_array($Ri);

				# Calculate amount
				$amt[$key] = sprint($qtyss[$key] * ($unitcosts[$key]));

				# Input qty if not serialised
				$qtyin = "<input type='text' size='3' name='qtys[]' value='$qtyss[$key]'>";

				$unitcosts[$key] = sprint($unitcosts[$key]);
				$amt[$key] = sprint($amt[$key]);

				# Check permissions
				$viewcost = "<input type='text' size='8' name='unitcost[]' value='$unitcosts[$key]'>";

				db_conn('cubit');

				$Sl = "SELECT * FROM vatcodes ORDER BY code";
				$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

				$Vatcodes = "
					<select name='vatcodes[]'>
						<option value='0'>Select</option>";
				while($vd = pg_fetch_array($Ri)) {
					if($vatcodess[$key] == $vd['id']) {
						$sel = "selected";
					} else {
						$sel = "";
					}
					$Vatcodes .= "<option value='$vd[id]' $sel>$vd[code]</option>";
				}
				$Vatcodes .= "</select>";

				# Put in selected warehouse and stock
				$products .= "
					<tr class='".bg_class()."'>
						<td colspan='2'>$ad[accname]<input type='hidden' name='accounts[]' value='$accountss[$key]'><input type='hidden' name='whids[]' value='0'></td>
						<td>$Vatcodes<input type='hidden' name='stkids[]' value='0'></td>
						<td><input type='text' size='20' name='descriptions[]' value='$descriptionss[$key]'></td>
						<td>$qtyin</td>
						<td>$viewcost</td>
						<td><input type='hidden' name='disc[]' value='0'><input type='hidden' name='discp[]' value='0'></td>
						<td nowrap><input type='hidden' name='amt[]' value='$amt[$key]'> ".CUR." $amt[$key]</td>
						<td><input type='checkbox' name='remprod[]' value='$keyy'></td>
					</tr>";
				$keyy++;
			}else{

				# skip if not selected
				if($whid == "-S"){
					continue;
				}

				if(!isset($addnon)) {

					if (isset ($filter_store) AND $filter_store != "0"){
						# get warehouse name
						db_conn("exten");
						$sql = "SELECT whname FROM warehouses WHERE whid = '$filter_store' AND div = '".USER_DIV."'";
						$whRslt = db_exec($sql);
						$wh = pg_fetch_array($whRslt);
					}

					if(isset($des) AND $des!="") {
						$len = strlen($des);
						if($des == "Show All"){
							$Wh = "";
							$des = "";
						}else {
							$Wh = "AND (lower(substr(stkdes,1,'$len'))=lower('$des') OR lower(substr(stkcod,1,'$len'))=lower('$des'))";
						}
					} else {
						$Wh = "AND FALSE";
						$des = "";
					}

					$check_setting = getCSetting ("OPTIONAL_STOCK_FILTERS");

					if (isset ($check_setting) AND $check_setting == "yes"){
						if (isset ($filter_class) AND $filter_class != "0"){
							$Wh .= " AND prdcls = '$filter_class'";
						}
						if (isset ($filter_cat) AND $filter_cat != "0"){
							$Wh .= " AND catid = '$filter_cat'";
						}
					}

					if (isset($filter_store) AND $filter_store != "0"){
						$Wh .= " AND whid = '$filter_store'";
					}

					# get stock on this warehouse
					db_connect();

					$sql = "SELECT * FROM stock WHERE blocked = 'n' AND div = '".USER_DIV."' $Wh ORDER BY stkcod ASC";
					$stkRslt = db_exec ($sql) or errDie ("Unable to retrieve stocks from database.");
					if (pg_numrows ($stkRslt) < 1) {
						$error .= "<li class='err'>There are no stock items in the selected store.</li>";
						continue;
					}

					if ($sel_frm == "stkcod") {
						$cods = "<select name='stkidss[]' onChange='javascript:document.form.submit();'>";
						$cods .= "<option value='-S' disabled selected>Select Number</option>";
						$count = 0;
						while($stk = pg_fetch_array($stkRslt)){
							$cods .= "<option value='$stk[stkid]'>$stk[stkcod] (".sprint3($stk['units'] - $stk['alloc']).")</option>";
						}
						$cods .= "</select> ";

						$descs = "";
					} else {
						$descs = "<select style='width:250px' name='stkidss[]' onChange='javascript:document.form.submit();'>";
						$descs .= "<option value='-S' disabled selected>Select Description</option>";
						$count = 0;
						while($stk = pg_fetch_array($stkRslt)){
							$descs .= "<option value='$stk[stkid]'>$stk[stkdes] (".sprint3($stk['units'] - $stk['alloc']).")</option>";
						}
						$descs .= "</select> ";

						$cods = "";
					}

					db_conn('cubit');

					$Sl = "SELECT * FROM vatcodes ORDER BY code";
					$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

					$Vatcodes = "
						<select name='vatcodess[]'>
							<option value='0'>Select</option>";
					while($vd = pg_fetch_array($Ri)) {
						if($vd['del'] == "Yes") {
							$sel = "selected";
						} else {
							$sel = "";
						}
						$Vatcodes .= "<option value='$vd[id]' $sel>$vd[code]</option>";
					}
					$Vatcodes .= "</select>";

					# put in drop down and warehouse
					$products .= "
						<input type='hidden' name='accountss[]' value='0'>
						<input type='hidden' name='descriptionss[]' value=''>
						<tr class='".bg_class()."'>
							<td><input type='hidden' name='whidss[]' value='$filter_store'></td>
							<td>$cods<input type='hidden' name='vatcodess' value='0'></td>
							<td></td>
							<td>$descs</td>
							<td><input type='text' size='3' name='qtyss[]'  value='1'></td>
							<td> </td>
							<td><input type='text' size='4' name='discs[]' value='0'> OR <input type='text' size='4' name='discps[]' value='0' maxlength='5'>%</td>
							<td><input type='hidden' name='amts[]' value='0.00'>".CUR." 0.00</td>
							<td></td>
						</tr>";
				}else{

					db_conn('core');

					$Sl = "SELECT accid,topacc,accnum,accname FROM accounts WHERE acctype='I' ORDER BY accname";
					$Ri = db_exec($Sl) or errDie("Unable to get accounts.");

					$Accounts = "
						<select name='accountss[]'>
							<option value='0'>Select Account</option>";
					while($ad = pg_fetch_array($Ri)) {
						if(isb($ad['accid'])) {
							continue;
						}
						$Accounts .= "<option value='$ad[accid]'>$ad[accname]</option>";
					}
					$Accounts .= "</select>";

					db_conn('cubit');

					$Sl = "SELECT * FROM vatcodes ORDER BY code";
					$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

					$Vatcodes = "
						<select name='vatcodess[]'>
							<option value='0'>Select</option>";
					while($vd = pg_fetch_array($Ri)) {
						if($vd['del'] == "Yes") {
							$sel = "selected";
						} else {
							$sel = "";
						}
						$Vatcodes .= "<option value='$vd[id]' $sel>$vd[code]</option>";
					}
					$Vatcodes .= "</select>";

					$products .= "
						<tr class='".bg_class()."'>
							<td colspan='2'>$Accounts<input type='hidden' name='whidss[]' value='$FIRST_WH'></td>
							<inpu type='hidden' name='stkidss[]' value=''>
							<td>$Vatcodes</td>
							<td><input type='text' size='20' name='descriptionss[]'></td>
							<td><input type='text' size='3' name='qtyss[]' value='1'></td>
							<td><input type='text' name='unitcosts[]' size='7'></td>
							<td></td>
							<td>".CUR." 0.00</td>
							<td><input type='hidden' name='discs[]' value='0'><input type='hidden' name='discps[]' value='0' ></td>
						</tr>";
				}
			}
		}
	}else{
		if(!isset($addnon) && !isset($upBtn)){

			if (isset ($filter_store) AND $filter_store != "0"){
				# get selected warehouse name
				db_conn("exten");

				$sql = "SELECT whname FROM warehouses WHERE whid = '$filter_store' AND div = '".USER_DIV."'";
				$whRslt = db_exec($sql);
				$wh = pg_fetch_array($whRslt);
			}

			if(isset($des) AND $des!="") {
				$len = strlen($des);
				if($des == "Show All"){
					$Wh = "";
					$des = "";
				}else {
					$Wh = "AND (lower(substr(stkdes,1,'$len'))=lower('$des') OR lower(substr(stkcod,1,'$len'))=lower('$des'))";
				}
			} else {
				$Wh = "AND FALSE";
				$des = "";
			}

			$check_setting = getCSetting ("OPTIONAL_STOCK_FILTERS");

			if (isset ($check_setting) AND $check_setting == "yes"){
				if (isset ($filter_class) AND $filter_class != "0"){
					$Wh .= " AND prdcls = '$filter_class'";
				}
				if (isset ($filter_cat) AND $filter_cat != "0"){
					$Wh .= " AND catid = '$filter_cat'";
				}
			}

			if (isset($filter_store) AND $filter_store != "0"){
				$Wh .= " AND whid = '$filter_store'";
			}

			# get stock on this warehouse
			db_connect();
			$sql = "SELECT * FROM stock WHERE blocked = 'n' AND div = '".USER_DIV."' $Wh ORDER BY stkcod ASC";
			$stkRslt = db_exec ($sql) or errDie ("Unable to retrieve stocks from database.");
			if (pg_numrows ($stkRslt) < 1) {
				if(!(isset($err))) {$err="";}
				$err .= "<li>There are no stock items in the selected warehouse.</li>";
			}
			$stks = "<select name='stkidss[]' onChange='javascript:document.form.submit();'>";
			$stks .= "<option value='-S' disabled selected>Select Number</option>";
			$count = 0;
			while($stk = pg_fetch_array($stkRslt)){
				$stks .= "<option value='$stk[stkid]'>$stk[stkcod] (".sprint3($stk['units'] - $stk['alloc']).")</option>";
			}
			$stks .= "</select> ";

			$products .= "
				<input type='hidden' name='descriptionss[]' value=''>
				<input type='hidden' name='vatcodess[]' value=''>
				<input type='hidden' name='accountss[]' value='0'>
				<tr class='".bg_class()."'>
					<td><input type='hidden' name='whidss[]' value='$filter_store'></td>
					<td>$stks</td>
					<td></td>
					<td></td>
					<td><input type='text' size='3' name='qtyss[]' value='1'></td>
					<td> </td>
					<td><input type='text' size='4' name='discs[]' value='0'> OR <input type='text' size='4' name='discps[]' value='0' maxlength='5'>%</td>
					<td>".CUR." 0.00</td>
					<td></td>
				</tr>";

		}else if ( isset($addnon) ) {
			db_conn('core');
			$Sl = "SELECT accid,topacc,accnum,accname FROM accounts WHERE acctype='I' ORDER BY accname";
			$Ri = db_exec($Sl) or errDie("Unable to get accounts.");

			$Accounts = "
				<select name='accountss[]'>
					<option value='0'>Select Account</option>";
			while($ad = pg_fetch_array($Ri)) {
				if(isb($ad['accid'])) {
					continue;
				}
				$Accounts .= "<option value='$ad[accid]'>$ad[accname]</option>";
			}
			$Accounts .= "</select>";

			db_conn('cubit');
			$Sl = "SELECT * FROM vatcodes ORDER BY code";
			$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

			$Vatcodes = "
				<select name='vatcodess[]'>
					<option value='0'>Select</option>";
			while($vd = pg_fetch_array($Ri)) {
				if($vd['del'] == "Yes") {
					$sel = "selected";
				} else {
					$sel = "";
				}
				$Vatcodes .= "<option value='$vd[id]' $sel>$vd[code]</option>";
			}

			$Vatcodes .= "</select>";


			$products .= "
				<tr class='".bg_class()."'>
					<td colspan='2'>$Accounts<input type='hidden' name='whidss[]' value='$FIRST_WH'></td>
					<inpu type='hidden' name='stkidss[]' value=''>
					<td>$Vatcodes</td>
					<td><input type='text' size='20' name='descriptionss[]'></td>
					<td><input type='text' size='3' name='qtyss[]' value='1'></td>
					<td><input type='text' name='unitcosts[]' size='7'></td>
					<td></td>
					<td>".CUR." 0.00</td>
					<td><input type='hidden' name='discs[]' value='0'><input type='hidden' name='discps[]' value='0'></td>
				</tr>";
		}
	}

	$products .= "</table>";

/* --- End Products Display --- */


/* --- Start Some calculations --- */

	# Calculate subtotal
	$SUBTOT = sprint($quo['subtot']);

	$VATP = TAX_VAT;

	# Calculate subtotal
	$SUBTOT = sprint($quo['subtot']);
 	$VAT = sprint($quo['vat']);
	$TOTAL = sprint($quo['total']);

/* --- End Some calculations --- */

/*--- Start checks --- */
	# check only if the customer is selected
	if(isset($cusnum) && $cusnum != "-S"){
		#check againg credit limit
		if($cust['credlimit'] != 0 && ($TOTAL + $cust['balance']) > $cust['credlimit']){
			$error .= "<li class='err'>Warning : Customers Credit limit of <b>".CUR." $cust[credlimit]</b> has been exceeded</li>";
		}
		$avcred = ($cust['credlimit'] - $cust['balance']);
	}else{
		$avcred = "0.00";
	}

	$quo['delvat'] += 0;

	if($quo['delvat'] == 0) {
		$Sl = "SELECT * FROM vatcodes WHERE del='Yes'";
		$Ri = db_exec($Sl) or errDie("Unable to get data.");

		$vd = pg_fetch_array($Ri);

		$quo['delvat'] = $vd['id'];
	}

	db_conn('cubit');
	$Sl = "SELECT * FROM vatcodes ORDER BY code";
	$Ri = db_exec($Sl) or errDie("Unable to get vat codes");

	$Vatcodes = "
		<select name='delvat'>
			<option value='0'>Select</option>";
	while($vd = pg_fetch_array($Ri)) {
		if($vd['id'] == $quo['delvat']) {
			$sel = "selected";
		} else {
			$sel = "";
		}
		$Vatcodes.="<option value='$vd[id]' $sel>$vd[code]</option>";
	}
	$Vatcodes.="</select>";

	if (!isset($showvat))
		$showvat = TRUE;

	if($showvat == TRUE){
		$vat14 = AT14;
	}else {
		$vat14 = "";
	}

	// Which display method was selected
	if (isset($sel_frm) && $sel_frm == "stkdes") {
		$sel_frm_cod = "";
		$sel_frm_des = "checked";
	} else {
		$sel_frm_cod = "checked";
		$sel_frm_des = "";
	}

	$sel = "";
	if(isset($lead) AND (strlen($lead) > 0))
		$sel = "checked=yes";


/*--- Start checks --- */

/* -- Final Layout --No VAT<input type=radio size=7 name=chrgvat value='nov' $chno> */
	$details_begin = "
		<center>
		<h3>New Quote</h3>
		<form action='".SELF."' method='POST' name='form'>
			<input type='hidden' name='key' value='update'>
			<input type='hidden' name='quoid' value='$quoid'>
		<table ".TMPL_tblDflts." width='95%'>
			<tr>
				<td valign='top'>
					<div id='cust_selection'>";

	if (empty($ajax_err) && (isset($cusnum) || AJAX)) {
		if (isset($cusnum)) {
			$OTS_OPT = onthespot_encode(
				SELF,
				"cust_selection",
				"deptid=$quo[deptid]&letters=$letters&cusnum=$cusnum&quoid=$quoid"
			);
			$custedit = "
				<td nowrap>
					<a href='javascript: popupSized(\"cust-edit.php?cusnum=$cusnum&onthespot=$OTS_OPT\", \"edit_cust\", 700, 630);'>Edit Customer Details</a>
				</td>";
		} else {
			$custedit = "";
		}

		$optional_filter_setting = getCSetting ("OPTIONAL_STOCK_FILTERS");

		if (isset ($optional_filter_setting) AND $optional_filter_setting == "yes"){

			db_connect ();

			$catsql = "SELECT catid, cat, catcod FROM stockcat WHERE div = '".USER_DIV."' ORDER BY cat ASC";
			$catRslt = db_exec($catsql);
			if(pg_numrows($catRslt) < 1){
				$cat_drop = "<input type='hidden' name='filter_cat' value='0'>";
			}else{
				$cat_drop = "<select name='filter_cat'>";
				$cat_drop .= "<option value='0'>All Categories</option>";
				while($cat = pg_fetch_array($catRslt)){
					if (isset ($filter_cat) AND $filter_cat == $cat['catid']){
						$cat_drop .= "<option value='$cat[catid]' selected>($cat[catcod]) $cat[cat]</option>";
					}else {
						$cat_drop .= "<option value='$cat[catid]'>($cat[catcod]) $cat[cat]</option>";
					}
				}
				$cat_drop .= "</select>";
			}

			# Select classification
			$classsql = "SELECT * FROM stockclass WHERE div = '".USER_DIV."' ORDER BY classname ASC";
			$clasRslt = db_exec($classsql);
			if(pg_numrows($clasRslt) < 1){
				$class_drop = "<input type='hidden' name='filter_class' value='0'>";
			}else{
				$class_drop = "<select name='filter_class' style='width: 167'>";
				$class_drop .= "<option value='0'>All Classifications</option>";
				while($clas = pg_fetch_array($clasRslt)){
					if (isset ($filter_class) AND $filter_class == $clas['clasid']){
						$class_drop .= "<option value='$clas[clasid]' selected>$clas[classname]</option>";
					}else {
						$class_drop .= "<option value='$clas[clasid]'>$clas[classname]</option>";
					}
				}
				$class_drop .= "</select>";
			}

			$display_optional_filters = "
				<tr class='".bg_class()."'>
					<td>Select Category</td>
					<td>$cat_drop</td>
				</tr>
				<tr class='".bg_class()."'>
					<td>Select Classification</td>
					<td>$class_drop</td>
				</tr>";
		}

		db_conn("exten");

		$sql = "SELECT whid, whname, whno FROM warehouses WHERE div = '".USER_DIV."' ORDER BY whname ASC";
		$whRslt = db_exec($sql);
		if(pg_numrows($whRslt) < 1){
			$store_drop = "<input type='hidden' name='filter_store' value='0'>";
		}else{

			if (!isset ($filter_store)){
				# check if setting exists
				db_connect();
				$sql = "SELECT value FROM set WHERE label = 'DEF_WH' AND div = '".USER_DIV."'";
				$Rslt = db_exec ($sql) or errDie ("Unable to check database for existing settings.");
				if (pg_numrows ($Rslt) > 0) {
					$set = pg_fetch_array($Rslt);
					$filter_store = $set['value'];
				}
			}
			$store_drop = "<select name='filter_store'>";
			$store_drop .= "<option value='0'>All Stores</option>";
			while($wh = pg_fetch_array($whRslt)){
				if (isset ($filter_store) AND $filter_store == $wh['whid']){
					$store_drop .= "<option value='$wh[whid]' selected>($wh[whno]) $wh[whname]</option>";
				}else {
					$store_drop .= "<option value='$wh[whid]'>($wh[whno]) $wh[whname]</option>";
				}
			}
			$store_drop .= "</select>";
		}

		$ajaxOut = "
			<input type='hidden' name='letters' value='$letters'>
			<input type='hidden' name='stkerr' value='$stkerr'>
			<table ".TMPL_tblDflts.">
				<tr>
					<th colspan='2'> Customer Details </th>
				</tr>
				<tr class='".bg_class()."'>
					<td>Department</td>
					<td valign='center'>$dept[deptname]</td>
				</tr>
				<tr class='".bg_class()."'>
					<td>Account No.</td>
					<td valign='center'>$cust[accno]</td>
				</tr>
				<tr class='".bg_class()."'>
					<td>Customer</td>
					<td valign='center'>$customers</td>
					$custedit
					<td><input type='button' onClick=\"javascript:document.location=('pos-quote-new.php')\" value='Quote Non Customer'></td>
				</tr>
				<tr class='".bg_class()."'>
					<td valign='top'>Customer Address</td>
					<td valign='center'>".nl2br($cust['addr1'])."</td>
				</tr>
				<tr class='".bg_class()."'>
					<td>Customer Req number</td>
					<td valign='center'><input type='text' size='10' name='cordno' value='$quo[cordno]'></td>
				</tr>
				<tr class='".bg_class()."'>
					<td>Customer VAT Number</td>
					<td>$cust[vatnum]</td>
				</tr>
				<tr class='".bg_class()."'>
					<td valign='top'>Next Contact Date</td>
					<td valign='center'>".mkDateSelect("ncdate",$ncdate_year,$ncdate_month,$ncdate_day)."</td>
				</tr>
				<tr class='".bg_class()."'>
					<td valign='top'>Add As Lead</td>
					<td valign='center'><input type='checkbox' name='lead' $sel value='yes'></td>
				</tr>
				<tr class='".bg_class()."'>
					<td>Select Using</td>
					<td>Stock Code<input type='radio' name='sel_frm' value='stkcod' onChange='javascript:document.form.submit();' $sel_frm_cod><br>Stock Description<input type='radio' name='sel_frm' value='stkdes' onChange='javascript:document.form.submit();' $sel_frm_des></td>
				</tr>
				<tr><td><br></td></tr>
				<tr>
					<th colspan='2'>Additional Filters</th>
				</tr>
				<tr class='".bg_class()."'>
					<td>Select Store</td>
					<td>$store_drop</td>
				</tr>
				$display_optional_filters
				<tr class='".bg_class()."'>
					<td>Stock Filter</td>
					<td><input type='text' size='13' name='des' value='$des'> <input type='submit' value='Search'> <input type='submit' name='des' value='Show All'></td>
				</tr>
			</table>";
	} else {
		db_conn("exten");
		$sql = "SELECT * FROM departments WHERE div = '".USER_DIV."' ORDER BY deptname ASC";
		$deptRslt = db_exec($sql) or errDie("Unable to view customers");
		if (pg_numrows($deptRslt) < 1) {
			return "<li class='err'>There are no Departments found in Cubit.</li>";
		} else {
			$depts = "<select id='deptid'>";
			$depts .= "<option value='0'>All Departments</option>";
			while ($dept = pg_fetch_array($deptRslt)){
				$depts .= "<option value='$dept[deptid]'>$dept[deptname]</option>";
			}
			$depts .= "</select>";
		}

		if (!isset($ajax_err)) $ajax_err = "";

		$ajaxOut = "
			<script>
				function updateCustSelection() {
					deptid = getObject('deptid').value;
					letters = getObject('letters').value;
					ajaxRequest('".SELF."', 'cust_selection', AJAX_SET, 'letters='+letters+'&deptid='+deptid+'&quoid=$quoid');
				}
			</script>
			$ajax_err
			<table ".TMPL_tblDflts." width='400'>
			<tr>
				<th colspan='2'>New Quote</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>".REQ."Select Department</td>
				<td valign='center'>$depts</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>First Letters of customer</td>
				<td valign='center'><input type='text' size='5' id='letters' maxlength='5'></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td valign='center'><input type='button' value='Update &raquo' onClick='updateCustSelection();'></td>
			</tr>
		</table>";
	}

	$avcred = sprint ($avcred);


	if (isset ($addprodBtn) OR isset ($addnon) OR isset ($saveBtn) OR isset ($upBtn) OR isset ($doneBtn) OR isset ($donePrnt) OR isset ($des)){
		$jump_bot = "
			<script>
				window.location.hash='bottom';
			</script>";
	}else {
		$jump_bot = "";
	}

	$details_end = "
					</div>
				</td>
				<td valign='top' align='right'>
					<table ".TMPL_tblDflts.">
						<tr>
							<th colspan='2'> Quote Details </th>
						</tr>
						<tr class='".bg_class()."'>
							<td>Quote No.</td>
							<td valign='center'>$quo[quoid]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Order No.</td>
							<td valign='center'><input type='text' size='5' name='ordno' value='$quo[ordno]'></td>
						</tr>
						<tr class='".bg_class()."'>
							<td>VAT Inclusive</td>
							<td valign='center'>Yes <input type='radio' size='7' name='chrgvat' value='inc' $chin> No<input type='radio' size='7' name='chrgvat' value='exc' $chex> </td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Terms</td>
							<td valign='center'>$termssel Days</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Sales Person</td>
							<td valign='center'>$salesps</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Quote Date</td>
							<td valign='center'>".mkDateSelect("quote",$quote_year,$quote_month,$quote_day)."</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Available Credit</td>
							<td>".CUR." $avcred</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Trade Discount</td>
							<td valign='center'><input type='text' size='5' name='traddisc' value='$quo[traddisc]'>%</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Delivery Charge</td>
							<td valign='center'><input type='text' size='7' name='delchrg' value='$quo[delchrg]'>$Vatcodes</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td colspan='2'>$products</td>
			</tr>
			<tr>
				<td>
					<table ".TMPL_tblDflts.">
						<tr>
							<td rowspan='2'>"
								.mkQuickLinks(
									ql("quote-view.php", "View Quotes"),
									ql("customers-new.php", "New Customer")
								)."
							</td>
							<th width='25%'>Comments</th>
							<td rowspan='5' valign='top' width='50%'>$error</td>
						</tr>
						<tr>
							<td class='".bg_class()."' rowspan='4' align='center' valign='top'>
								<textarea name='comm' rows='4' cols='20'>$quo[comm]</textarea>
							</td>
						</tr>
					</table>
				</td>
				<td align='right'>
					<table ".TMPL_tblDflts." width='80%'>
						<tr class='".bg_class()."'>
							<td>SUBTOTAL</td>
							<td align='right' nowrap>".CUR." <input type='hidden' name='SUBTOT' value='$SUBTOT'>$SUBTOT</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Trade Discount</td>
							<td align='right' nowrap>".CUR." $quo[discount]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Delivery Charge</td>
							<td align='right' nowrap>".CUR." $quo[delivery]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td><b>VAT $vat14</b></td>
							<td align='right' nowrap>".CUR." $VAT</td>
						</tr>
						<tr class='".bg_class()."'>
							<th>GRAND TOTAL</th>
							<td align='right' nowrap>".CUR." $TOTAL</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align='center' colspan='2' nowrap><input name='addprodBtn' type='submit' value='Add Product'> | <input name='addnon' type='submit' value='Add Non stock Product'> | <input type='submit' name='saveBtn' value='Save'> | <input type='submit' name='upBtn' value='Update'>$done</td>
			</tr>
		</table>
		<a name='bottom'>
		</form>
		</center>
		$jump_bot";

	if (AJAX) {
		return $ajaxOut;
	} else {
		return "$details_begin$ajaxOut$details_end";
	}

}



# write
function write($_POST)
{

	#get vars
	extract ($_POST);

	if (!isset($cusnum)) {
		return details($_POST, "<li class='err'>Please select customer/department first.</li>");
	}

	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($cusnum, "num", 1, 20, "Invalid Customer, Please select a customer.");
	$v->isOk ($quoid, "num", 1, 20, "Invalid Quote Number.");
	$v->isOk ($cordno, "string", 0, 20, "Invalid Customer Order Number.");
	$v->isOk ($comm, "string", 0, 255, "Invalid Comments.");
	$v->isOk ($ordno, "string", 0, 20, "Invalid order number.");
	$v->isOk ($chrgvat, "string", 1, 4, "Invalid charge vat option.");
	$v->isOk ($terms, "num", 1, 20, "Invalid terms.");
	$v->isOk ($salespn, "string", 1, 255, "Invalid sales person.");
	$v->isOk ($quote_day, "num", 1, 2, "Invalid Quote Date day.");
	$v->isOk ($quote_month, "num", 1, 2, "Invalid Quote Date month.");
	$v->isOk ($quote_year, "num", 1, 5, "Invalid Quote Date year.");
	$odate = $quote_year."-".$quote_month."-".$quote_day;
	if(!checkdate($quote_month, $quote_day, $quote_year)){
		$v->isOk ($odate, "num", 1, 1, "Invalid Quote Date.");
	}
	$v->isOk ($ncdate_day, "num", 1, 2, "Invalid Next Contact Date day.");
	$v->isOk ($ncdate_month, "num", 1, 2, "Invalid Next Contact Date month.");
	$v->isOk ($ncdate_year, "num", 1, 5, "Invalid Next Contact Date year.");
	$ncdate = $ncdate_year."-".$ncdate_month."-".$ncdate_day;
	if(!checkdate($ncdate_month, $ncdate_day, $ncdate_year)){
		$v->isOk ($ncdate, "num", 1, 1, "Invalid Followon Date.");
	}
	$v->isOk ($traddisc, "float", 0, 20, "Invalid Trade Discount.");
	if($traddisc > 100){
		$v->isOk ($traddisc, "float", 0, 0, "Error : Trade Discount cannot be more than 100 %.");
	}
	$v->isOk ($delchrg, "float", 0, 20, "Invalid Delivery Charge.");
	$v->isOk ($SUBTOT, "float", 0, 20, "Invalid Delivery Charge.");

	# used to generate errors
	$error = "asa@";

	# check quantities
	if(isset($qtys)){
		foreach($qtys as $keys => $qty){
			$discp[$keys] += 0;
			$disc[$keys] += 0;

			$v->isOk ($qty, "float", 1, 15, "Invalid Quantity for product number : <b>".($keys+1)."</b>");
			$v->isOk ($disc[$keys], "float", 0, 20, "Invalid Discount for product number : <b>".($keys+1)."</b>.");
			if($disc[$keys] > $unitcost[$keys]){
				$v->isOk ($disc[$keys], "float", 0, 0, "Error : Discount for product number : <b>".($keys+1)."</b> is more than the unitcost.");
			}
			$v->isOk ($discp[$keys], "float", 0, 20, "Invalid Discount Percentage for product number : <b>".($keys+1)."</b>.");
			if($discp[$keys] > 100){
				$v->isOk ($discp[$keys], "float", 0, 0, "Error : Discount for product number : <b>".($keys+1)."</b> is more than 100 %.");
			}
			$v->isOk ($unitcost[$keys], "float", 1, 20, "Invalid Unit Price for product number : <b>".($keys+1)."</b>.");
			if($qty < 1){
				$v->isOk ($qty, "num", 0, 0, "Error : Item Quantity must be at least one. Product number : <b>".($keys+1)."</b>");
			}
		}
	}
	# check whids
	if(isset($whids)){
		foreach($whids as $keys => $whid){
			$v->isOk ($whid, "num", 1, 10, "Invalid Store number, please enter all details.");
		}
	}

	# check stkids
	if(isset($stkids)){
		foreach($stkids as $keys => $stkid){
			$v->isOk ($stkid, "num", 1, 10, "Invalid Stock number, please enter all details.");
		}
	}
	# check amt
	if(isset($amt)){
		foreach($amt as $keys => $amount){
			$v->isOk ($amount, "float", 1, 20, "Invalid Amount, please enter all details.");
		}
	}

	# display errors, if any
	$err = "";
	if ($v->isError ()) {
		$errors = $v->getErrors();
			foreach ($errors as $e) {
			$err .= "<li class='err'>$e[msg]</li>";
		}
		return details($_POST, $err);
	}



// 	# insert quote to DB
// 	$sql = "UPDATE quotes SET delvat='$delvat',cusnum = '$cusnum', deptname = '$dept[deptname]', cusacc = '$cust[accno]', cusname = '$cust[cusname]', surname = '$cust[surname]', cusaddr = '$cust[addr1]', cusvatno = '$cust[vatnum]', cordno = '$cordno', ordno = '$ordno', chrgvat = '$chrgvat', terms = '$terms', salespn = '$salespn',
// 	odate = '$odate', traddisc = '$traddisc', delchrg = '$delchrg', subtot = '$SUBTOT', vat = '$VAT', total = '$TOTAL', balance = '$TOTAL', comm = '$comm', discount='$traddiscmt', delivery='$delexvat' WHERE quoid = '$quoid'";
// 	$rslt = db_exec($sql) or errDie("Unable to update quote in Cubit.",SELF);

	# Get quote info
	db_connect();

	$sql = "SELECT * FROM quotes WHERE quoid = '$quoid' AND div = '".USER_DIV."'";
	$quoRslt = db_exec ($sql) or errDie ("Unable to get quote information");
	if (pg_numrows ($quoRslt) < 1) {
		return "<li>- Quote Not Found</li>";
	}

	$quo = pg_fetch_array($quoRslt);

	$quo['traddisc'] = $traddisc;

	# check if quote has been printed
	if($quo['accepted'] == "y"){
		$error = "<li class='err'>Error : Quote number <b>$quoid</b> has already been printed.</li>";
		$error .= "<p><input type='button' onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $error;
	}

	# Get selected customer info
	db_connect();
	$sql = "SELECT * FROM customers WHERE cusnum = '$cusnum' AND div = '".USER_DIV."'";
	$custRslt = db_exec ($sql) or errDie ("Unable to get customer information");
	if (pg_numrows ($custRslt) < 1) {
		$sql = "SELECT * FROM quote_data WHERE quoid = '$quoid' AND div = '".USER_DIV."'";
		$custRslt = db_exec ($sql) or errDie ("Unable to get customer information data");
		$cust = pg_fetch_array($custRslt);
		$cust['cusname'] = $cust['customer'];
		$cust['surname'] = "";
		$cust['addr1'] = "";
	}else{
		$cust = pg_fetch_array($custRslt);

		$quo['deptid'] = $cust['deptid'];

		# If customer was just selected, get the following
		if($quo['cusnum'] == 0){
			$traddisc = $cust['traddisc'];
			$terms = $cust['credterm'];
		}
	}

	# get department
	db_conn("exten");

	$sql = "SELECT * FROM departments WHERE deptid = '$quo[deptid]' AND div = '".USER_DIV."'";
	$deptRslt = db_exec($sql);
	if(pg_numrows($deptRslt) < 1){
		$dept['deptname'] = "<i class='err'>Not Found</i>";
	}else{
		$dept = pg_fetch_array($deptRslt);
	}

	# fix those nasty zeros
	$traddisc += 0;
	$delchrg += 0;

	$vatamount = 0;
	$showvat = TRUE;

	# insert quote to DB
	db_connect();

# begin updating
pglib_transaction ("BEGIN") or errDie("Unable to start a database transaction.",SELF);

		/* -- Start remove old items -- */

			# get selected stock in this quote
			db_connect();

			$sql = "SELECT * FROM quote_items  WHERE quoid = '$quoid' AND div = '".USER_DIV."'";
			$stktRslt = db_exec($sql);


			#while($stkt = pg_fetch_array($stktRslt)){
			#	update stock(alloc + qty)
			#	$sql = "UPDATE stock SET alloc = (alloc - '$stkt[qty]')  WHERE stkid = '$stkt[stkid]'";
			#	$rslt = db_exec($sql) or errDie("Unable to update stock to Cubit.",SELF);
			#}


			# remove old items
			$sql = "DELETE FROM quote_items WHERE quoid='$quoid' AND div = '".USER_DIV."'";
			$rslt = db_exec($sql) or errDie("Unable to update quote items in Cubit.",SELF);

		/* -- End remove old items -- */
		$taxex = 0;
		if(isset($qtys)){
			foreach($qtys as $keys => $value){
				if(isset($remprod)&&in_array($keys, $remprod)){
// 					if(in_array($keys, $remprod)){
// 						# skip product (wonder if $keys still align)
// 						$amt[$keys] = 0;
// 						continue;
// 					}else{
// 						# get selamt from selected stock
// 						$sql = "SELECT * FROM stock WHERE stkid = '$stkids[$keys]' AND div = '".USER_DIV."'";
// 						$stkRslt = db_exec($sql);
// 						$stk = pg_fetch_array($stkRslt);
//
// 						# Calculate the Discount discount
// 						if($disc[$keys] < 1){
// 							if($discp[$keys] > 0){
// 								$disc[$keys] = (($discp[$keys]/100) * $unitcost[$keys]);
// 							}
// 						}else{
// 							$discp[$keys] = (($disc[$keys] * 100) / $unitcost[$keys]);
// 						}
//
// 						# Calculate amount
// 						$amt[$keys] = ($qtys[$keys] * ($unitcost[$keys] - $disc[$keys]));
//
// 						# Check Tax Excempt
// 						if($stk['exvat'] == 'yes'){
// 							$taxex += $amt[$keys];
// 						}
//
// 						$wtd = $whids[$keys];
// 						# insert quote items
// 						$sql = "INSERT INTO quote_items(quoid, whid, stkid, qty, unitcost, amt, disc, discp, div) VALUES('$quoid', '$whids[$keys]', '$stkids[$keys]', '$qtys[$keys]', '$unitcost[$keys]', '$amt[$keys]', '$disc[$keys]', '$discp[$keys]', '".USER_DIV."')";
// 						$rslt = db_exec($sql) or errDie("Unable to insert quote items to Cubit.",SELF);
//
// 						# update stock(alloc + qty)
// 						# $sql = "UPDATE stock SET alloc = (alloc + '$qtys[$keys]') WHERE stkid = '$stkids[$keys]'";
// 						# $rslt = db_exec($sql) or errDie("Unable to update stock to Cubit.",SELF);
// 					}
				}elseif(isset($accounts[$keys])&&$accounts[$keys]!=0){
					$accounts[$keys] += 0;
					# Get selamt from selected stock
					db_conn('core');

					$Sl = "SELECT * FROM accounts WHERE accid='$accounts[$keys]'";
					$Ri = db_exec($Sl) or errDie("Unable to get account data.");

					$ad = pg_fetch_array($Ri);

					# Calculate amount
					$amt[$keys] = ($qtys[$keys] * ($unitcost[$keys]));

					db_conn('cubit');
					$Sl = "SELECT * FROM vatcodes WHERE id='$vatcodes[$keys]'";
					$Ri = db_exec($Sl);

					if(pg_num_rows($Ri) < 1) {
						return details($_POST, "<li class='err'>Please select the vatcode for all your items.</li>");
					}

					$vd = pg_fetch_array($Ri);

					if($vd['zero'] == "Yes") {
						$excluding = "y";
					} else {
						$excluding = "";
					}

					if((TAX_VAT != $vd['vat_amount']) AND ($vd['vat_amount'] != "0.00")){
						$showvat = FALSE;
					}

					$vr = vatcalc($amt[$keys],$quo['chrgvat'],$excluding,$quo['traddisc'],$vd['vat_amount']);
					$vrs = explode("|",$vr);
					$ivat = $vrs[0];
					$iamount = $vrs[1];

					$vatamount += $ivat;

					# Check Tax Excempt
					if($vd['zero'] == "Yes"){
						$taxex += $amt[$keys];
						$exvat = "y";
					} else {
						$exvat = "n";
					}

					//$newvat+=vatcalc($amt[$keys],$chrgvat,$exvat,$traddisc);
					$vatcodes[$keys] += 0;
					$accounts[$keys] += 0;
					$descriptions[$keys] = remval($descriptions[$keys]);
					$wtd = $whids[$keys];
					# insert invoice items
					$sql = "
						INSERT INTO quote_items (
							quoid, whid, stkid, qty, unitcost, amt, 
							disc, discp,  div, vatcode, description, 
							account
						) VALUES (
							'$quoid', '$whids[$keys]', '$stkids[$keys]', '$qtys[$keys]', '$unitcost[$keys]', '$amt[$keys]', 
							'$disc[$keys]', '$discp[$keys]', '".USER_DIV."', '$vatcodes[$keys]', '$descriptions[$keys]', 
							'$accounts[$keys]'
						)";
					$rslt = db_exec($sql) or errDie("Unable to insert invoice items to Cubit.",SELF);

				}else{
					# get selamt from selected stock
					$sql = "SELECT * FROM stock WHERE stkid = '$stkids[$keys]' AND div = '".USER_DIV."'";
					$stkRslt = db_exec($sql);
					$stk = pg_fetch_array($stkRslt);

					# Calculate the Discount discount
					if($disc[$keys] < 1){
						if($discp[$keys] > 0){
							$disc[$keys] = (($discp[$keys]/100) * $unitcost[$keys]);
						}
					}else{
						$discp[$keys] = (($disc[$keys] * 100) / $unitcost[$keys]);
					}

					# Calculate amount
					$amt[$keys] = ($qtys[$keys] * ($unitcost[$keys] - $disc[$keys]));

					$Sl = "SELECT * FROM vatcodes WHERE id='$vatcodes[$keys]'";
					$Ri = db_exec($Sl);

					if(pg_num_rows($Ri)<1) {
						return details($_POST, "<li class='err'>Please select the vatcode for all your items.</li>");
					}
					$vd = pg_fetch_array($Ri);

					if($vd['zero'] == "Yes") {
						$excluding = "y";
					} else {
						$excluding = "";
					}

					if((TAX_VAT != $vd['vat_amount']) AND ($vd['vat_amount'] != "0.00")){
						$showvat = FALSE;
					}

					$vr = vatcalc($amt[$keys],$quo['chrgvat'],$excluding,$quo['traddisc'],$vd['vat_amount']);
					$vrs = explode("|",$vr);
					$ivat = $vrs[0];
					$iamount = $vrs[1];

					$vatamount += $ivat;

					# Check Tax Excempt
					if($stk['exvat'] == 'yes'||$vd['zero'] == "Yes"){
						$taxex += $amt[$keys];
						$exvat = "y";
					} else {
						$exvat = "n";
					}
					$wtd = $whids[$keys];
					# insert quote items
					$sql = "
						INSERT INTO quote_items (
							quoid, whid, stkid, qty, unitcost, 
							amt, disc, discp, div, vatcode
						) VALUES (
							'$quoid', '$whids[$keys]', '$stkids[$keys]', '$qtys[$keys]', '$unitcost[$keys]', 
							'$amt[$keys]', '$disc[$keys]', '$discp[$keys]', '".USER_DIV."','$vatcodes[$keys]'
						)";
					$rslt = db_exec($sql) or errDie("Unable to insert quote items to Cubit.",SELF);

					# update stock(alloc + qty)
					# $sql = "UPDATE stock SET alloc = (alloc + '$qtys[$keys]') WHERE stkid = '$stkids[$keys]'";
					# $rslt = db_exec($sql) or errDie("Unable to update stock to Cubit.",SELF);
				}
				# everything is set place done button
				$_POST["done"] = "&nbsp; | &nbsp;<input name='doneBtn' type='submit' value='Done'>";
				//&nbsp; | &nbsp;<input type='submit' name='donePrnt' value='Done, Print and make another'>";
			}
		}else{
			$_POST["done"] = "";
		}

		db_conn('cubit');

		$Sl = "SELECT * FROM vatcodes WHERE id='$delvat'";
		$Ri = db_exec($Sl);

		$vd = pg_fetch_array($Ri);

		if($vd['zero'] == "Yes") {
			$excluding = "y";
		} else {
			$excluding = "";
		}

		if((TAX_VAT != $vd['vat_amount']) AND ($vd['vat_amount'] != "0.00")){
			$showvat = FALSE;
		}

		$_POST['showvat'] = $showvat;

		$vr = vatcalc($delchrg,$quo['chrgvat'],$excluding,$quo['traddisc'],$vd['vat_amount']);

		$vrs = explode("|",$vr);
		$ivat = $vrs[0];
		$iamount = $vrs[1];

		$vatamount += $ivat;

		/* --- ----------- Clac --------------------- */
		##----------------------NEW----------------------

		$sub = 0.00;
		if(isset($amt)) {
			$sub = sprint(array_sum($amt));
		}

		$VATP = TAX_VAT;

		if($chrgvat == "exc"){
			$taxex = sprint($taxex - ($taxex * $traddisc/100));
			$subtotal = sprint($sub + $delchrg);
			$traddiscmt = sprint($subtotal * $traddisc/100);
			$subtotal = sprint($subtotal - $traddiscmt);
//			$VAT=sprint(($subtotal-$taxex)*$VATP/100);
			$VAT = $vatamount;
			$SUBTOT = $sub;
			$TOTAL = sprint($subtotal + $VAT);
			$delexvat = sprint($delchrg);

		}elseif($chrgvat == "inc"){
			$ot = $taxex;
			$taxex = sprint($taxex - ($taxex * $traddisc/100));
			$subtotal = sprint($sub + $delchrg);
			$traddiscmt = sprint($subtotal * $traddisc/100);
			$subtotal = sprint($subtotal - $traddiscmt);
// 			$VAT=sprint(($subtotal-$taxex)*$VATP/(100+$VATP));
			$VAT = $vatamount;
			$SUBTOT = sprint($sub);
			$TOTAL = sprint($subtotal);
			$delexvat = sprint(($delchrg));
			$traddiscmt = sprint($traddiscmt);

		} else {
			$subtotal = sprint($sub + $delchrg);
			$traddiscmt = sprint($subtotal * $traddisc/100);
			$subtotal = sprint($subtotal - $traddiscmt);
			$VAT = sprint(0);
			$SUBTOT = $sub;
			$TOTAL = $subtotal;
			$delexvat = sprint($delchrg);
		}

		/* --- ----------- Clac --------------------- */
		##----------------------END----------------------
/* --- ----------- Clac ---------------------

		# calculate subtot
		$SUBTOT = 0.00;
		if(isset($amt))
			$SUBTOT = array_sum($amt);

		$SUBTOT -= $taxex;

		# duplicate
		$SUBTOTAL = $SUBTOT;

		$VATP = TAX_VAT;
		if($chrgvat == "exc"){
			$SUBTOTAL = $SUBTOTAL;
			$delexvat= ($delchrg);
		}elseif($chrgvat == "inc"){
			$SUBTOTAL = sprint(($SUBTOTAL * 100)/(100 + $VATP));
			$delexvat = sprint(($delchrg * 100)/($VATP + 100));
		}else{
			$SUBTOTAL = ($SUBTOTAL);
			$delexvat = ($delchrg);
		}

		$SUBTOT = $SUBTOTAL;
		$EXVATTOT = $SUBTOT;
		$EXVATTOT += $delexvat;

		# Minus trade discount from taxex
		if($traddisc > 0){
			$traddiscmtt = (($traddisc/100) * $taxex);
		}else{
			$traddiscmtt = 0;
		}
		$taxext = ($taxex - $traddiscmtt);

		if($traddisc > 0) {
			$traddiscmt = ($EXVATTOT * ($traddisc/100));
		}else{
			$traddiscmt = 0;
		}
		$EXVATTOT -= $traddiscmt;
		// $EXVATTOT -= $taxex;

		$traddiscmt = sprint($traddiscmt  + $traddiscmtt);

		if($chrgvat != "nov"){
			$VAT = sprint($EXVATTOT * ($VATP/100));
		}else{
			$VAT = 0;
		}

		$TOTAL = sprint($EXVATTOT + $VAT + $taxext);
		$SUBTOT += $taxex;

/* --- ----------- Clac --------------------- */

		$delvat += 0;

		//manual error handling
		if(!isset($lead)) $lead = "";
		# insert quote to DB
		$sql = "
			UPDATE quotes 
			SET delvat='$delvat',cusnum = '$cusnum', deptid = '$dept[deptid]', deptname = '$dept[deptname]', 
				cusacc = '$cust[accno]', cusname = '$cust[cusname]', surname = '$cust[surname]', cusaddr = '$cust[addr1]', 
				cusvatno = '$cust[vatnum]', cordno = '$cordno', ordno = '$ordno', chrgvat = '$chrgvat', terms = '$terms', 
				salespn = '$salespn', odate = '$odate', ncdate = '$ncdate', traddisc = '$traddisc', delchrg = '$delchrg', 
				subtot = '$SUBTOT', vat = '$VAT', total = '$TOTAL', balance = '$TOTAL', comm = '$comm', discount='$traddiscmt', 
				delivery='$delexvat', lead = '$lead' 
			WHERE quoid = '$quoid'";
		$rslt = db_exec($sql) or errDie("Unable to update quote in Cubit.",SELF);

		# remove old data
		$sql = "DELETE FROM quote_data WHERE quoid='$quoid' AND div = '".USER_DIV."'";
		$rslt = db_exec($sql) or errDie("Unable to update quote data in Cubit.",SELF);

		# pu in new data
		$sql = "
			INSERT INTO quote_data (
				quoid, dept, customer, addr1, div
			) VALUES (
				'$quoid', '$dept[deptname]', '$cust[cusname] $cust[surname]', '$cust[addr1]', '".USER_DIV."'
			)";
		$rslt = db_exec($sql) or errDie("Unable to insert quote data to Cubit.",SELF);


		$ncdate = "$ncdate_year-$ncdate_month-$ncdate_day";

/* --- Start button Listeners --- */
	if (isset($donePrnt)) {
		$sql = "UPDATE quotes SET done='y' WHERE quoid='$quoid' AND div='".USER_DIV."'";
		$rslt = db_exec($sql) or errDie("Unable to update quote status in Cubit.");

		$OUTPUT = "
			<script>
				printer('pdf/pdf-quote.php?quoid=$quoid');
				move('quote-new.php');
			</script>";
		return $OUTPUT;
	}


	if(isset($doneBtn)){
		# insert quote to DB
		$sql = "UPDATE quotes SET done = 'y' WHERE quoid = '$quoid' AND div = '".USER_DIV."'";
		$rslt = db_exec($sql) or errDie("Unable to update quote status in Cubit.",SELF);

		#add lead
		if(isset($lead) AND ($lead == "yes")){
			db_conn("crm");
			$sql = "
				INSERT INTO leads (
					surname, date, by, con, div, supp_id, cust_id, lead_source, birthdate, reports_to_id, 
					assigned_to, assigned_to_id, account_id, gender, website, salespid, ncdate, team_id, dept_id, tell, 
					hadd, ref
				) VALUES (
					'$cust[surname]', 'now', '".USER_NAME."', 'No', '".USER_DIV."', '0', '0', '0', 'now', '0', 
					'".USER_NAME."', '2', '0', 'Male', 'http://', '0', '$ncdate', '0', '0', '$cust[cellno]', 
					'$cust[addr1]', ''
				)";
			$rslt = db_exec($sql) or errDie ("Unable to add lead to database.");
			$lead_id = pglib_lastid("leads", "id");
		}
	}

	# commit updating
	pglib_transaction ("COMMIT") or errDie("Unable to commit a database transaction.",SELF);



	if(isset($doneBtn)){

//old <a target='_blank' href='quote-print.php?quoid=$quoid'>Print Quote</a>

		// Final Laytout
// 		$write = "
// 			<table ".TMPL_tblDflts.">
// 				<tr>
// 					<th colspan='2'>New Quote</th>
// 				</tr>
// 				<tr class='".bg_class()."'>
// 					<td>Quote for customer <b>$cust[cusname] $cust[surname]</b> has been recorded.</td>
// 					<td><input type='button' onClick=\"javascript:printer('pdf/quote-pdf-print.php?quoid=$quoid')\" value='Print Quote'></td>
// 					<td><input type='button' onclick='javascript:move(\"quote-email.php?evs=$quoid\")' value='Email'></td>
// 				</tr>
// 			</table>"
// 			.mkQuickLinks(
// 				ql("quote-view.php", "View Quotes"),
// 				ql("customers-new.php", "New Customer")
// 			);
// 		return $write;
		return "
			<script>
				printer('quote-print.php?quoid=$quoid');
				document.location='quote-new.php';
			</script>";

	}elseif(isset($saveBtn)){

		// Final Laytout
		$write = "
			<table ".TMPL_tblDflts.">
				<tr>
					<th>New Quote Saved</th>
				</tr>
				<tr class='".bg_class()."'>
					<td>Quote for customer <b>$cust[cusname] $cust[surname]</b> has been saved.</td>
				</tr>
			</table>"
			.mkQuickLinks(
				ql("quote-view.php", "View Quotes"),
				ql("customers-new.php", "New Customer")
			);
		return $write;
	}else{
		if(isset($wtd)){$_POST['wtd']=$wtd;}
		return details($_POST);
	}

/* --- End button Listeners --- */

}


?>
