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

# Get settings
require("settings.php");
require("core-settings.php");
require("libs/ext.lib.php");

# decide what to do
if (isset($_GET["invid"])) {
	$OUTPUT = details($_GET);
} else {
	if (isset($_POST["key"])) {
		switch ($_POST["key"]) {
			case "accept":
				$OUTPUT = accept($_POST);
				break;

			default:
				$OUTPUT = "<li class=err>Invalid use of module.";
		}
	}else{
		$OUTPUT = "<li class=err>Invalid use of module.";
	}
}
# Get templete
require("template.php");

# Details
function details($_GET)
{

	# Get vars
	foreach ($_GET as $key => $value) {
		$$key = $value;
	}
	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($invid, "num", 1, 20, "Invalid Sales Order number.");

	# display errors, if any
	if ($v->isError ()) {
		$err = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$err .= "<li class=err>".$e["msg"];
		}
		$confirm .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $confirm;
	}

	# Get Sales Order info
	db_connect();
	$sql = "SELECT * FROM nons_invoices WHERE invid = '$invid' AND div = '".USER_DIV."'";
	$invRslt = db_exec ($sql) or errDie ("Unable to get invoices information");
	if (pg_numrows ($invRslt) < 1) {
		return "<i class=err>Not Found</i>";
	}
	$inv = pg_fetch_array($invRslt);

	/* --- Start Products Display --- */

	# Products layout
	$products = "
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' border=0 width=100%>
	<tr>
		<th width='5%'>#</th>
		<th width='65%'>DESCRIPTION</th>
		<th width='10%'>QTY</th>
		<th width='10%'>UNIT PRICE</th>
		<th width='10%'>AMOUNT</th>
	<tr>";

	# Get selected stock in this Sales Order
	db_connect();
	$sql = "SELECT * FROM nons_inv_items  WHERE invid = '$invid' AND div = '".USER_DIV."'";
	$stkdRslt = db_exec($sql);
	$i = 0;

	while($stkd = pg_fetch_array($stkdRslt)){
		$i++;

		# put in product
		$products .="<tr class='bg-odd'>
			<td align=center>$i</td>
			<td>$stkd[description]</td>
			<td>$stkd[qty]</td>
			<td>$stkd[unitcost]</td>
			<td>".CUR." $stkd[amt]</td>
		</tr>";
	}
	$products .= "</table>";

 	/* --- Start Some calculations --- */


	# Get subtotal
	$SUBTOT = sprint($inv['subtot']);

	# Get Total
	$TOTAL = sprint($inv['total']);

	# Get vat
	$VAT = sprint($inv['vat']);

	/* --- End Some calculations --- */

	if($inv['invnum']==0) {
		$inv['invnum']=$inv['invid'];
	}

	/* -- Final Layout -- */
	$details = "<center><h3>Non-Stock Sales Order Details</h3>
	<form action='".SELF."' method=post name=form>
	<input type=hidden name=key value=accept>
	<input type=hidden name=invid value='$invid'>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' border=0 width=95%>
	<tr><td valign=top>
		<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' border=0>
			<tr><th colspan=2> Customer Details </th></tr>
			<tr class='bg-odd'><td>Customer</td><td valign=center>$inv[cusname]</td></tr>
			<tr class='bg-even'><td>Customer Address</td><td valign=center><pre>$inv[cusaddr]</pre></td></tr>
			<tr class='bg-odd'><td>Customer Vat Number</td><td valign=center>$inv[cusvatno]</td></tr>
		</table>
	</td><td valign=top align=right>
		<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' border=0>
			<tr><th colspan=2> Non-Stock Sales Order Details </th></tr>
			<tr class='bg-odd'><td>Non-Stock Sales Order No.</td><td valign=center>$inv[invnum]</td></tr>
			<tr class='bg-odd'><td>Date</td><td valign=center>$inv[odate]</td></tr>
			<tr class='bg-even'><td>VAT Inclusive</td><td valign=center>$inv[chrgvat]</td></tr>
		</table>
	</td></tr>
	<tr><td><br></td></tr>
	<tr><td colspan=2>
	$products
	</td></tr>
	<tr><td>
		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
			<tr><th width=40%>Quick Links</th><th width=45%>Remarks</th><td rowspan=5 valign=top width=15%><br></td></tr>
			<tr><td class='bg-odd'><a href='nons-sorder-new.php'>New Non-Stock Sales Orders</a></td><td class='bg-odd' rowspan=4 align=center valign=top>".nl2br($inv['remarks'])."</td></tr>
			<tr class='bg-odd'><td><a href='nons-sorder-view.php'>View Non-Stock Sales Orders</a></td></tr>
			<script>document.write(getQuicklinkSpecial());</script>
		</table>
	</td><td align=right>
		<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' border=0 width=80%>
			<tr class='bg-odd'><td>SUBTOTAL</td><td align=right>".CUR." $inv[subtot]</td></tr>
			<tr class='bg-odd'><td>VAT @ ".TAX_VAT." %</td><td align=right>".CUR." $inv[vat]</td></tr>
			<tr class='bg-even'><th>GRAND TOTAL</th><td align=right>".CUR." $inv[total]</td></tr>
		</table>
	</td></tr>
	<tr><td align=right><input type=button value='&laquo Back' onClick='javascript:history.back()'> | </td><td><input type=submit value='Accept'></td></tr>
	</table></form>
	</center>";

	return $details;
}

function accept($_POST)
{
	extract($_POST);

	# Validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($invid, "num", 1, 20, "Invalid Sales Order number.");

	# display errors, if any
	if ($v->isError ()) {
		$err = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$err .= "<li class=err>".$e["msg"];
		}
		$confirm .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $confirm;
	}

	# Get Sales Order info
	db_connect();
	$sql = "SELECT * FROM nons_invoices WHERE invid = '$invid' AND div = '".USER_DIV."'";
	$invRslt = db_exec ($sql) or errDie ("Unable to get -sorder- information");
	if (pg_numrows ($invRslt) < 1) {
		return "<i class=err>Not Found</i>";
	}
	$inv = pg_fetch_array($invRslt);

	db_connect();
/* - Start Copying - */
pglib_transaction ("BEGIN") or errDie("Unable to start a database transaction.",SELF);

	$sql = "INSERT INTO nons_invoices(cusname,cusaddr,cusvatno,chrgvat,sdate,odate,done,username,
				prd,invnum,div,remarks,cusid,age,typ,subtot,balance,vat,total,descrip,ctyp,
				accid,tval,docref,jobid,jobnum,labid,location,fcid,currency,xrate,fbalance,
				fsubtot)
	VALUES('$inv[cusname]','$inv[cusaddr]','$inv[cusvatno]','$inv[chrgvat]','$inv[sdate]',
			'$inv[odate]', '$inv[done]','$inv[username]','$inv[prd]','$inv[invnum]','$inv[div]',
			'$inv[remarks]','$inv[cusid]','$inv[age]','inv','$inv[subtot]','$inv[balance]',
			'$inv[vat]','$inv[total]','$inv[descrip]', '$inv[ctyp]','$inv[accid]','$inv[tval]',
			'$inv[docref]','$inv[jobid]','$inv[jobnum]','$inv[labid]','$inv[location]',
			'$inv[fcid]','$inv[currency]','$inv[xrate]','$inv[fbalance]', '$inv[fsubtot]')";
	$upRslt = db_exec ($sql) or errDie ("Unable to update -sorder- information");

	# get next ordnum
	$ninvid = lastinvid();

	# Get selected stock in this Sales Order
	db_connect();
	$sql = "SELECT * FROM nons_inv_items  WHERE invid = '$invid' AND div = '".USER_DIV."'";
	$stkdRslt = db_exec($sql);

	while($stkd = pg_fetch_array($stkdRslt)){
		$stkd['cunitcost'] += 0;
		$sql = "INSERT INTO nons_inv_items(invid, qty, description, div, amt, unitcost, accid, rqty, vatex, cunitcost)
		VALUES('$ninvid', '$stkd[qty]', '$stkd[description]', '$stkd[div]', '$stkd[amt]', '$stkd[unitcost]', '$stkd[accid]', '$stkd[rqty]', '$stkd[vatex]', '$stkd[cunitcost]')";
		$upRslt = db_exec ($sql) or errDie ("Unable to update -sorder- information");
	}

	# Set to not serialised
	$sql = "UPDATE nons_invoices SET done = 'y' WHERE invid = '$invid' AND div = '".USER_DIV."'";
	$rslt = db_exec($sql) or errDie("Unable to update -sorder-s in Cubit.",SELF);


/* - End Copying - */
pglib_transaction ("COMMIT") or errDie("Unable to commit a database transaction.",SELF);

	header("Location: nons-invoice-new.php?invid=$ninvid&cont=1");
	exit;

	# Final Laytout
	$write = "
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
		<tr><th>Non-Stock Sales Orders accepted</th></tr>
		<tr class='bg-even'><td>Non-Stock Sales Orders for Customer <b>$inv[cusname]</b> has been accepted.</td></tr>
	</table>
	<p>
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
		<tr><th>Quick Links</th></tr>
		<tr class='bg-odd'><td><a href='nons-sorder-view.php'>View Non-Stock Sales Orders</a></td></tr>
		<script>document.write(getQuicklinkSpecial());</script>
	</table>";

	return $write;
}
?>
