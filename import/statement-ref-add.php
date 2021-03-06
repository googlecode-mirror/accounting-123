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

require("../settings.php");
require("../core-settings.php");

if(isset($_POST["key"])) {
	switch($_POST["key"]) {
		case "enter2":
			$OUTPUT = enter2($_POST);
			break;
		case "confirm":
			$OUTPUT = confirm($_POST);
			break;
		case "write":
			$OUTPUT = write($_POST);
			break;
		default:
			$OUTPUT = "Invalid";
	}
} else {
	$OUTPUT = enter($_POST);
}

$OUTPUT .= "
	<p>
	<table ".TMPL_tblDflts.">
		<tr>
			<th>Quick Links</th>
		</tr>
		<tr class='".bg_class()."'>
			<td><a href='import-settings.php'>Statement Import Settings</a></td>
		</tr>
		<tr class='".bg_class()."'>
			<td><a href='../main.php'>Main Menu</a></td>
		</tr>
	</table>";

require("../template.php");



function enter($_POST)
{

	extract($_POST);
	
	if(!isset($pn)) {
		$pn = "+";
		$type = "In Description";
		$action = "Insert into cashbook";
		$description = "";
	}
	
	if($pn == "+") {
		$psel1 = "selected";
		$psel2 = "";
	} else {
		$psel1 = "";
		$psel2 = "selected";
	}

	$pns = "
		<select name='pn'>
			<option value='+' $psel1>+</option>
			<option value='-' $psel2>-</option>
		</select>";
	
	if($type == "Exact Description") {
		$tsel1 = "";
		$tsel2 = "selected";
	} else {
		$tsel1 = "selected";
		$tsel2 = "";
	}
	
	$types = "
		<select name='type'>
			<option value='In Description' $tsel1>In Description</option>
			<option value='Exact Description' $tsel2>Exact Description</option>
		</select>";
	
	$asel1 = "";
	$asel2 = "";
	$asel3 = "";
	$asel4 = "";
	$asel5 = "";
	
	if($action == "Insert into cashbook") {
		$asel1 = "selected";
	} elseif($action == "Customer Payment") {
		$asel2 = "selected";
	} elseif($action == "Supplier Payment") {
		$asel3 = "selected";
	} elseif($action == "Ignore") {
		$asel4 = "selected";
	} elseif($action == "Delete") {
		$asel5 = "selected";
	}
	
	$actions = "
		<select name='action'>
			<option value='Insert into cashbook'$asel1>Insert into cashbook</option>
			<option value='Customer Payment'$asel2>Customer Payment</option>
			<option value='Supplier Payment'$asel3>Supplier Payment</option>
			<option value='Ignore'$asel4>Ignore</option>
			<option value='Delete'$asel5>Delete</option>
		</select>";

	$out = "
		<h3>Add statement description</h3>
		<table ".TMPL_tblDflts.">
		<form action='".SELF."' method='POST'>
			<input type='hidden' name='key' value='enter2'>
			<tr>
				<th colspan='2'>Details</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Description</td>
				<td><input type='text' size='20' name='description' value='$description'></td>
			</tr>
			<tr class='".bg_class()."'>
				<td>+/-</td>
				<td>$pns</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Type</td>
				<td>$types</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Action</td>
				<td>$actions</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td colspan='2' align='right'><input type='submit' value='Continue &raquo;'></td>
			</tr>
		</form>
		</table>";
	return $out;

}



function enter2($_POST)
{

	extract($_POST);
	
	$description = safe($description);
	$pn = safe($pn);
	$type = safe($type);
	$action = safe($action);
	
	if($action == "Customer Payment" && $pn == "-") {
		return "<li class='err'>You cannot have a 'Payment from customer' on your statement for a negative amount.</li>".enter($_POST);
	}
	
	if($action == "Supplier Payment" && $pn == "+") {
		return "<li class='err'>You cannot have a 'Payment to supplier' on your statement for a positive amount.</li>".enter($_POST);
	}
	
	if($action == "Insert into cashbook") {
	
		$details = "
			<select name='account'>
				<option value=''>Select Account</option>";
		db_conn('core');
		
		$Sl = "SELECT * FROM accounts WHERE div = '".USER_DIV."' ORDER BY accname";
		$Rl = db_exec($Sl) or errDie("Unable to get account data.");
		if(pg_numrows($Rl) < 1){
			return "<li>There are No accounts in Cubit.";
		}
		
		while($ad = pg_fetch_array($Rl)) {
			if(isDisabled($ad['accid'])) {
				continue;
			}
			$details .= "<option value='$ad[accid]'>$ad[accname]</option>";
		}
		$details .="</select>";
		
	} elseif($action == "Customer Payment") {
		db_conn('cubit');
		
		$Sl = "SELECT cusnum,surname FROM customers WHERE div='".USER_DIV."' AND location='loc' ORDER BY surname";
		$Rl = db_exec($Sl) or errDie("Unable to get customers.");
		$details = "
			<select name='account'>
				<option value='0'>Select Customer</option>";
		while($cd = pg_fetch_array($Rl)) {
			$details .= "<option value='$cd[cusnum]'>$cd[surname]</option>";
		}
		$details.="</select>";
		
	} elseif($action == "Supplier Payment") {
	
		db_conn('cubit');
		$Sl = "SELECT supid,supname FROM suppliers WHERE div='".USER_DIV."' AND location='loc'  ORDER BY supname";
		$Rl = db_exec($Sl) or errDie("Unable to get suppliers.");
		$details = "
			<select name='account'>
				<option value=0>Select Supplier</option>";
		while($cd = pg_fetch_array($Rl)) {
			$details .= "<option value='$cd[supid]'>$cd[supname]</option>";
		}
		$details .= "</select>";

	} elseif($action == "Ignore") {
		$details = "<input type=hidden name=account value=0>";
		
	} elseif($action == "Delete") {
		$details = "<input type=hidden name=account value=0>";
	}

	$out = "
		<h3>Add statement description details</h3>
		<table ".TMPL_tblDflts.">
		<form action='".SELF."' method='POST'>
			<input type='hidden' name='key' value='confirm'>
			<input type='hidden' name='description' value='$description'>
			<input type='hidden' name='pn' value='$pn'>
			<input type='hidden' name='type' value='$type'>
			<input type='hidden' name='action' value='$action'>
			<tr>
				<th colspan='2'>Details</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Description</td>
				<td>$description</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>+/-</td>
				<td>$pn</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Type</td>
				<td>$type</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Action</td>
				<td>$action</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Action Details</td>
				<td>$details</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td colspan='2' align='right'><input type='submit' value='Confirm &raquo;'></td>
			</tr>
		</form>
		</table>";
	return $out;

}



function confirm($_POST)
{

	extract($_POST);
	
	$description = safe($description);
	$pn = safe($pn);
	$type = safe($type);
	$action = safe($action);
	
	$account += 0;
	
	if($action == "Insert into cashbook") {
	
		db_conn('core');
		
		$Sl = "SELECT * FROM accounts WHERE accid='$account'";
		$Rl = db_exec($Sl) or errDie("Unable to get account data.");
		if(pg_numrows($Rl) < 1){
			return "<li>There are No accounts in Cubit.</li>";
		}
		
		$ad = pg_fetch_array($Rl);
		
		$details = $ad['accname'];;
	
	} elseif($action == "Customer Payment") {
		db_conn('cubit');
		
		$Sl = "SELECT cusnum,surname FROM customers WHERE cusnum='$account'";
		$Rl = db_exec($Sl) or errDie("Unable to get customers.");
		
		$cd = pg_fetch_array($Rl);
		
		$details = $cd['surname'];
		
	} elseif($action == "Supplier Payment") {
	
		db_conn('cubit');
			
		$Sl = "SELECT supid,supname FROM suppliers WHERE supid='$account'";
		$Rl = db_exec($Sl) or errDie("Unable to get suppliers.");
		
		$cd = pg_fetch_array($Rl);
		
		$details = $cd['supname'];
		
	} elseif($action == "Ignore") {
		$details = "";
		
	} elseif($action == "Delete") {
		$details = "";
	}

	$out = "
		<h3>Confirm statement description</h3>
		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
		<form action='".SELF."' method=post>
			<input type='hidden' name='key' value='write'>
			<input type='hidden' name='description' value='$description'>
			<input type='hidden' name='pn' value='$pn'>
			<input type='hidden' name='type' value='$type'>
			<input type='hidden' name='action' value='$action'>
			<input type='hidden' name='account' value='$account'>
			<tr>
				<th colspan='2'>Details</th>
			<tr>
			<tr class='".bg_class()."'>
				<td>Description</td>
				<td>$description</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>+/-</td>
				<td>$pn</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Type</td>
				<td>$type</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Action</td>
				<td>$action</td>
			</tr>
			<tr class='".bg_class()."'>
				<td>Action Details</td>
				<td>$details</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td colspan='2' align='right'><input type='submit' value='Write &raquo;'></td>
			</tr>
		</form>
		</table>";
	return $out;

}



function write($_POST)
{

	extract($_POST);
	
	$description = safe($description);
	$pn = safe($pn);
	$type = safe($type);
	$action = safe($action);
	
	$account += 0;
	
	db_conn('cubit');
	
	if($type == "In Description") {
		$type = "i";
	} else {
		$type = "e";
	}
	
	if($action == "Insert into cashbook") {
		$action = "c";
	} elseif($action == "Customer Payment") {
		$action = "cp";
	} elseif($action == "Supplier Payment") {
		$action = "sp";
	} elseif($action == "Ignore") {
		$action = "i";
	} elseif($action == "Delete") {
		$action = "d";
	}
	
	$Sl = "
		INSERT INTO statement_refs (
			ref, dets, pn, action, account, by
		) VALUES (
			'$description', '$type', '$pn', '$action', '$account', '".USER_DIV."'
		)";
	$Ri = db_exec($Sl) or errDie("Unable to insert data.");
	
	$out = "
		<table ".TMPL_tblDflts.">
			<tr>
				<th>Description added</th>
			</tr>
			<tr class='".bg_class()."'>
				<td>Description added to system.</td>
			</tr>
		</table>";
	return $out;

}

function safe ($value) {
	if(!isset($value)) {return "Invalid use of function";}
	$value = str_replace("$","",$value);
	$value = str_replace("'","",$value);
	$value = str_replace("\\","",$value);
	$value = str_replace("\"","",$value);
	$value = str_replace("\"","",$value);
	$value = str_replace(";","",$value);
	return $value;
}



?>