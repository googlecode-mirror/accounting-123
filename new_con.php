<?
# This program is copyright by Cubit Accounting Software CC
# Reg no 2002/099579/23
# Full e-mail support is available
# by sending an e-mail to andre@andre.co.za
#
# Rights to use, modify, change and all conditions related
# thereto can be found in the license.html file that is
# distributed along with this program.
# You may not use this program in any way or form without
# consenting to the terms and conditions contained in the
# license. If this program did not include the license.html
# file please contact us at +27834433455 or via email
# andre@andre.co.za (In South Africa: Tel. 0834433455)
#
# Our website is at http://www.cubit.co.za
# comments. suggestions and applications for free coding
# could be made via email to andre@andre.co.za
#
# Our banking details as follows:
# Banker: Nedbank
# Account Name: Cubit Accounting Software
# Account Number: 1357 082517
# Swift Code: NEDSZAJJ
# Branch Code: 135705
# Branch Name: Manager Direct
# Banker Address: 3rd Floor Nedcor Park, 6 Press Avenue, Johanesburg
#
#
# Fees due to integrators, will be paid into your account within 30 days
# of receipt of the relevant license fee.
#
# Please ensure that we have your correct banking details.
require ("settings.php");
require ("libs/ext.lib.php");

// remove all '
if ( isset($_GET) ) {
	foreach ( $_GET as $key => $value ) {
		$_GET[$key] = str_replace("'", "", $value);
	}
}

if ( isset($_POST) ) {
	foreach ( $_POST as $key => $value ) {
		$_GET[$key] = str_replace("'", "", $value);
	}
}

# decide what to do
if (isset ($_POST["key"])) {
	switch ($_POST["key"]) {
		case "confirm":
			$OUTPUT = con_data ($_POST);
			break;
		case "write":
			$OUTPUT = write_data ($_POST);
			break;
		default:
			$OUTPUT = get_data ("");
	}
} else {
	$OUTPUT = get_data ("");
}

# display output
require ("template.php");
# enter new data
function get_data ($err)
{
        global $_GET;
	extract($_GET);

	$fields["surname"] = "";
	$fields["name"] = "";
	$fields["accountname"] = "";
	$fields["account_id"] = 0;
	$fields["account_type"] = "";
	$fields["lead_source"] = 0;
	$fields["title"] = "";
	$fields["department"] = "";
	$fields["birthdate"] = date("Y-m-d");
	$fields["reports_to_id"] = 0;
	//$fields["assigned_to_id"] = "";
	$fields["tell"] = "";
	$fields["cell"] = "";
	$fields["fax"] = "";
	$fields["tell_office"] = "";
	$fields["tell_other"] = "";
	$fields["email"] = "";
	$fields["email_other"] = "";
	$fields["assistant"] = "";
	$fields["assistant_phone"] = "";
	$fields["padd"] = "";
	$fields["padd_city"] = "";
	$fields["padd_state"] = "";
	$fields["padd_code"] = "";
	$fields["padd_country" ] ="";
	$fields["hadd"] = "";
	$fields["hadd_city"] = "";
	$fields["hadd_state"] = "";
	$fields["hadd_code"] = "";
	$fields["hadd_country"] = "";
	$fields["description"] = "";
	$fields["upload_img"] = "no";
	$fields["team_id"] = 0;

	foreach ( $fields as $key => $value ) {
		if ( ! isset($$key) )
			$$key = $value;
	}

	list($bf_year, $bf_month, $bf_day) = explode("-", $birthdate);

// 	$select_bfday = "<select name=bf_day>";
// 	for ( $i = 1; $i <= 31; $i++ ) {
// 		if ( $bf_day == $i )
// 			$sel = "selected";
// 		else
// 			$sel = "";
//
// 		$select_bfday .= "<option $sel value='$i'>$i</option>";
// 	}
// 	$select_bfday .= "</select>";

// 	$select_bfmonth = "<select name=bf_month>";
// 	for ( $i = 1; $i <= 12; $i++ ) {
// 		if ( $bf_month == $i )
// 			$sel = "selected";
// 		else
// 			$sel = "";
//
// 		$select_bfmonth .= "<option $sel value='$i'>".date("F", mktime(0, 0, 0, $i, 1, 2000))."</option>";
// 	}
// 	$select_bfmonth .= "</select>";

// 	$select_bfyear = "<select name=bf_year>";
// 	for ( $i = 1971; $i <= 2027; $i++ ) {
// 		if ( $bf_year == $i )
// 			$sel = "selected";
// 		else
// 			$sel = "";
//
// 		$select_bfyear .= "<option $sel value='$i'>$i</option>";
// 	}
// 	$select_bfyear .= "</select>";

	// reports to name
	$reports_to = "";
	if ( ! empty($reports_to_id) ) {
		$reports_to_id += 0;

		db_conn("cubit");
		$sql = "SELECT * FROM cons WHERE id='$reports_to_id' LIMIT 1";
		$rslt = db_exec($sql) or errDie("Error retrieving 'Reports to' value.");

		$dat = pg_fetch_array($rslt);

		if ( ! empty($dat["name"]) ) {
			$reports_to .= "$dat[name] ";
		}

		$reports_to .= "$dat[surname]";
	}

	// crm value
	if ( isset($crm) ) {
		$ex = "<input type=hidden name=crm value=''>";
	} else {
		$ex = "";
	}

	$Cons ="<select size=1 name=Con>
	<option selected value='No'>No</option>
	<option value='Yes'>Yes</option>
	</select>";

	$select_source = extlib_cpsel("lead_source", crm_get_leadsrc(-1), $lead_source);

	if ($upload_img == "yes") {
		$img_yes = "checked";
		$img_no = "";
	} else {
		$img_yes = "";
		$img_no = "checked";
	}

	// Create the teams dropdown
	$sql = "SELECT * FROM crm.teams ORDER BY name ASC";
	$team_rslt = db_exec($sql) or errDie("Unable to retrieve teams.");

	$teams_sel = "<select name='team_id'>";
	$teams_sel .= "<option value='0'>[None]</option>";
	while ($team_data = pg_fetch_array($team_rslt)) {
		if ($team_id == $team_data["id"]) {
			$sel = "selected";
		} else {
			$sel = "";
		}

		$teams_sel .= "<option value='$team_data[id]'>$team_data[name]</option>";
	}
	$teams_sel .= "</select>";

	$get_data = "<h3>New Main Contact</h3>
	$err
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<form action='".SELF."' method=post name=frm_con>
	<input type=hidden name=key value=confirm>
	$ex
	<tr><th colspan=4>Contact Information</th></tr>
	<tr class='bg-even'>
		<td width=120>First Name</td>
		<td width=210><input type=text size=27 name=name value='$name'></td>

		<td width=120>Office Phone</td>
		<td width=210><input type=text size=27 name=tell_office value='$tell_office'></td>
	</tr>
	<tr class='bg-odd'>
		<td>".REQ."Company/Last Name</td>
		<td><input type=text size=27 name=surname value='$surname'></td>

		<td>Mobile</td>
		<td><input type=text size=27 name=cell value='$cell'></td>
	</tr>
	<tr class='bg-even'>
		<td>Reports To</td>
		<td>
			<input readonly=yes type=text size=27 name='reports_to' value='$reports_to'>
			<input type=hidden name='reports_to_id' value='$reports_to_id'>
			<input type=button value='Select' onClick='popupSized(\"list_cons.php?action=reportsto\", \"reportsto\", 700, 300, \"\");'>
		</td>

		<td>Home Phone</td>
		<td><input type=text size=27 name=tell value='$tell'></td>
	</tr>
	<tr class='bg-odd'>
		<td>Lead Source</td>
		<td>$select_source</td>

		<td>Other Phone</td>
		<td><input type=text size=27 name=tell_other value='$tell_other'></td>
	</tr>
	<tr class='bg-even'>
		<td>Title</td>
		<td><input type=text size=27 name=title value='$title'></td>

		<td>Fax</td>
		<td><input type=text size=27 name=fax value='$fax'></td>
	</tr>
	<tr class='bg-odd'>
		<td>Department</td>
		<td><input type=text size=27 name=department value='$department'></td>

		<td>E-mail</td>
		<td><input type=text size=27 name=email value='$email'></td>
	</tr>
	<tr class='bg-even'>
		<td>".REQ."Birthdate</td>
		<td>".mkDateSelect("bf")."</td>

		<td>Other E-mail</td>
		<td><input type=text size=27 name=email_other value='$email_other'></td>
	</tr>
	<tr class='bg-odd'>
		<td rowspan=2>Account Name</td>
		<td>
			<table><tr>
			<td>
				<input type=text readonly=yes size=27 name=accountname value='$accountname'>
				<input type=hidden name=account_id value='$account_id'>
				<input type=hidden name=account_type value='$account_type'>
			</td>
			<td align=center>
				<input type=button value='Customer' onClick='popupSized(\"customers-view.php?action=contact_acc\", \"contactacc\", 700, 450, \"\");'><br>
				<input type=button value='Supplier' onClick='popupSized(\"supp-view.php?action=contact_acc\", \"contactacc\", 700, 300, \"\");'>
			</td>
			</tr></table>
		</td>

		<td>Assistant</td>
		<td><input type=text size=27 name=assistant value='$assistant'></td>
	</tr>
	<tr class='bg-even'>

		<td align=center>
			Add Customer <input type=checkbox name=cust>
			Add Supplier <input type=checkbox name=supp><br>
		</td>

		<td>Assistant Phone</td>
		<td><input type=text size=27 name=assistant_phone value='$assistant_phone'></td>
	</tr>
	<tr class='bg-odd'>
		<td>Upload contact image</td>
		<td align='center'>
			Yes <input type='radio' name='upload_img' value='yes' $img_yes />
			No <input type='radio' name='upload_img' value='no' $img_no />
		</td>

		<td>Team Permissions</td>
		<td>$teams_sel</td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr>
		<th colspan=2>Physical Address</th>
		<th colspan=2>Postal Address</th>
	</tr>
	<tr class='bg-even'>
		<td colspan=2 align=center><textarea name=hadd rows=4 cols=35>$hadd</textarea></td>

		<td colspan=2 align=center><textarea name=padd rows=4 cols=35>$padd</textarea></td>
	</tr>
	<tr class='bg-odd'>
		<td>City</td>
		<td><input type=text size=27 name=padd_city value='$padd_city'></td>
		<td>City</td>
		<td><input type=text size=27 name=hadd_city value='$hadd_city'></td>
	</tr>
	<tr class='bg-even'>
		<td>State/Province</td>
		<td><input type=text size=27 name=padd_state value='$padd_state'></td>
		<td>State/Province</td>
		<td><input type=text size=27 name=hadd_state value='$hadd_state'></td>
	</tr>
	<tr class='bg-odd'>
		<td>Postal Code</td>
		<td><input type=text size=27 name=padd_code value='$padd_code'></td>
		<td>Postal Code</td>
		<td><input type=text size=27 name=hadd_code value='$hadd_code'></td>
	</tr>
	<tr class='bg-even'>
		<td>Country</td>
		<td><input type=text size=27 name=padd_country value='$padd_country'></td>
		<td>Country</td>
		<td><input type=text size=27 name=hadd_country value='$hadd_country'></td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr>
		<th colspan=2>Description</th>
		<th colspan=2>Options</th>
	</tr>
	<tr class='bg-odd'>
		<td colspan=2 align=center><textarea name=description rows=4 cols=35>$description</textarea></td>
		<td>Private</td>
		<td align=center>$Cons</td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr class='bg-even'>

	</tr>

	<tr>
		<td colspan=2 align=right><input type=submit value='Confirm &raquo;'></td>
	</tr>
	</form>
	</table>
	<p>
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th>Quick Links</th></tr>
	<tr class='bg-odd'><td><a href='list_cons.php'>List contacts</a></td></tr>
	<tr class='bg-odd'><td><a href='index_cons.php'>Contacts</a></td></tr>
	<tr class='bg-odd'><td><a href='main.php'>Main Menu</a></td></tr>
	</table>";

        return $get_data;
}

# confirm new data
function con_data ($_POST)
{
	# get vars
	foreach ($_POST as $key => $value) {
		$$key = $value;
	}
	# validate input
	require_lib("validate");
	$v = new  validate ();

	$v->isOk($surname, "string", 1, 100, "Last name");
	$v->isOk($name, "string", 0, 100, "First name");
	$v->isOk($accountname, "string", 0, 1024, "Account");
	$v->isOk($account_id, "num", 0, 9, "Account ID (hidden)");
	$v->isOk($account_type, "string", 0, 100, "Account type (hidden)");
	$v->isOk($reports_to, "string", 0, 100, "Reports to");
	$v->isOk($reports_to_id, "num",0, 9, "Reports to ID (hidden)");
	$v->isOk($lead_source, "string", 0, 100, "Lead Source");
	$v->isOk($title, "string", 0, 100, "Title");
	$v->isOk($department, "string", 0, 100, "Department");
	$v->isOk($tell, "string", 0, 100, "Home Phone");
	$v->isOk($cell, "string", 0, 100, "Mobile Phone");
	$v->isOk($fax, "string", 0, 100, "Fax");
	$v->isOk($tell_office, "string", 0, 100, "Office Phone");
	$v->isOk($tell_other, "string", 0, 100, "Other Phone");
	$v->isOk($email, "string", 0, 100, "Email");
	$v->isOk($email_other, "string", 0, 100, "Other Email");
	$v->isOk($assistant, "string", 0, 100, "Assistant");
	$v->isOk($assistant_phone, "string", 0, 100, "Assistant Phone");
	$v->isOk($padd, "string", 0, 100, "Physical Address");
	$v->isOk($padd_city, "string", 0, 100, "Physical Address: City");
	$v->isOk($padd_state, "string", 0, 100, "Physical Address: State/Province");
	$v->isOk($padd_code, "string", 0, 100, "Physical Address: Postal Code");
	$v->isOk($padd_country, "string", 0, 100, "Physical Address: Country");
	$v->isOk($hadd, "string", 0, 100, "Postal Address");
	$v->isOk($hadd_city, "string", 0, 100, "Postal Address: City");
	$v->isOk($hadd_state, "string", 0, 100, "Postal Address: State/Province");
	$v->isOk($hadd_code, "string", 0, 100, "Postal Address: Postal Code");
	$v->isOk($hadd_country, "string", 0, 100, "Postal Address: Country");
	$v->isOk($description, "string", 0, 100, "Description");
	$v->isOk($Con,"string",2 ,3, "Invalid private.");
	$v->isOk($team_id, "num", 1, 9, "Team.");

        $birthdate = "$bf_year-$bf_month-$bf_day";
	if ( $v->isOk($birthdate, "string", 1, 100, "Birthdate") ) {
		if ( ! checkdate($bf_month, $bf_day, $bf_year) ) {
			$v->addError("_OTHER", "Invalid birthdate. No such date exists.");
		}
	}

	$birthdate_description = date("d F Y", mktime(0, 0, 0, $bf_day, $bf_month, $bf_year));

	# display errors, if any
	if ($v->isError ()) {
		$err = "The following field value errors occured:<br>";

		$errors = $v->getErrors();

		foreach ($errors as $e) {
			if ( $e["value"] == "_OTHER" )
				$err .= "<li class=err>$e[msg]</li>";
			else
				$err .= "<li class=err>Invalid characters: $e[msg]</li>";
		}
		return get_data($err);
	}

	db_connect();
	$lastid = pglib_lastid("customers","cusnum");

	# Get last account number
	$sql = "SELECT accno FROM customers WHERE cusnum = '$lastid' AND div = '".USER_DIV."'";
	$accRslt = db_exec($sql);
	if(pg_numrows($accRslt) < 1){
		do{
			$lastid--;
			# get last account number
			$sql = "SELECT accno FROM customers WHERE cusnum = '$lastid' AND div = '".USER_DIV."'";
			$accRslt = db_exec($sql);
			if(pg_numrows($accRslt) < 1){
				$accno = "";
				$naccno= "";
			}else{
				$acc = pg_fetch_array($accRslt);
				$accno = $acc['accno'];
			}
		}while(strlen($accno) < 1 && $lastid > 1);
	}else{
		$acc = pg_fetch_array($accRslt);
		$accno = $acc['accno'];
	}

	# Check if we got $accno(if not skip this)
	if(strlen($accno) > 0){
		// get the next account number
		$num = preg_replace ("/[^\d]+/", "", $accno);
		$num++;
		$chars = preg_replace("/[\d]/", "", $accno);
		$naccno = $chars.$num;
	}

	db_connect();
	$lastid = pglib_lastid("suppliers","supid");

	# get last account number
	$sql = "SELECT supno FROM suppliers WHERE supid = '$lastid' AND div = '".USER_DIV."'";
	$accRslt = db_exec($sql);
	if(pg_numrows($accRslt) < 1){
		do{
			$lastid--;
			# get last account number
			$sql = "SELECT supno FROM suppliers WHERE supid = '$lastid' AND div = '".USER_DIV."'";
			$accRslt = db_exec($sql);
			if(pg_numrows($accRslt) < 1){
				$supno = "";
				$nsupno= "";
			}else{
				$acc = pg_fetch_array($accRslt);
				$supno = $acc['supno'];
			}
		}while(strlen($supno) < 1 && $lastid > 1);
	}else{
		$acc = pg_fetch_array($accRslt);
		$supno = $acc['supno'];
	}

	# Check if we got $supno(if not skip this)
	if(strlen($supno) > 0){
		# Get the next account number
		$num = preg_replace ("/[^\d]+/", "", $supno);
		$num++;
		$chars = preg_replace("/[\d]/", "", $supno);
		$nsupno = $chars.$num;
	}


	if(isset($cust)) {
		$custext="
		<tr>
			<th colspan=2>Customer Details</th>
		</tr>
		<tr class='bg-odd'>
			<td>Acc No</td>
			<td><input type=text size=20 name=cusacc value='$naccno'></td>
		</tr>";
	} else {
		$custext="";
	}

	if(isset($supp)) {
		$suptext="
		<tr>
			<th colspan=2>Supplier Details</th>
		</tr>
		<tr class='bg-odd'>
			<td>Sup No</td>
			<td><input type=text size=20 name=supacc value='$nsupno'></td>
		</tr>";
	} else {
		$suptext="";
	}

	if ( ! empty($custext) || ! empty($suptext) ) {
		$account_id = 0;
		$accountname = "
			<table width=100% cellpadding=0 cellspacing=0>
				<td>$custext $suptext</td>
			</table>";
	}

	if(isset($crm)) {
		$ex="<input type=hidden name=crm value=''>";
	} else {
		$ex="";
	}

	if ($upload_img == "yes") {
		$upload_box = "
			<td>Contact Image</td>
			<td><input type='file' name='img_file' /></td>";
	} else {
		$upload_box = "<td colspan='2'>&nbsp;</td>";
	}

	// Retrieve the team name
	if ($team_id) {
		$sql = "SELECT name FROM crm.teams WHERE id='$team_id'";
		$team_rslt = db_exec($sql) or errDie("Unable to retrieve team name.");
		$team_name = pg_fetch_result($team_rslt, 0);
	} else {
		$team_name = "[None]";
	}

	$con_data =
	"<h3>Confirm contact details</h3>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<form action='".SELF."' method='post' enctype='multipart/form-data'>
	<input type=hidden name=key      value=write>
	<input type=hidden name='surname' value='$surname'>
	<input type=hidden name='name' value='$name'>
	<input type=hidden name='account_id' value='$account_id'>
	<input type=hidden name='accountname' value='".base64_encode($accountname)."'>
	<input type=hidden name='account_type' value='$account_type'>
	<input type=hidden name='lead_source' value='$lead_source'>
	<input type=hidden name='title' value='$title'>
	<input type=hidden name='department' value='$department'>
	<input type=hidden name='bf_day' value='$bf_day'>
	<input type=hidden name='bf_month' value='$bf_month'>
	<input type=hidden name='bf_year' value='$bf_year'>
	<input type=hidden name='reports_to_id' value='$reports_to_id'>
	<input type=hidden name='reports_to' value='$reports_to'>
	<input type=hidden name='tell' value='$tell'>
	<input type=hidden name='cell' value='$cell'>
	<input type=hidden name='fax' value='$fax'>
	<input type=hidden name='tell_office' value='$tell_office'>
	<input type=hidden name='tell_other' value='$tell_other'>
	<input type=hidden name='email' value='$email'>
	<input type=hidden name='email_other' value='$email_other'>
	<input type=hidden name='assistant' value='$assistant'>
	<input type=hidden name='assistant_phone' value='$assistant_phone'>
	<input type=hidden name='padd' value='$padd'>
	<input type=hidden name='padd_city' value='$padd_city'>
	<input type=hidden name='padd_state' value='$padd_state'>
	<input type=hidden name='padd_code' value='$padd_code'>
	<input type=hidden name='padd_country' value='$padd_country'>
	<input type=hidden name='hadd' value='$hadd'>
	<input type=hidden name='hadd_city' value='$hadd_city'>
	<input type=hidden name='hadd_state' value='$hadd_state'>
	<input type=hidden name='hadd_code' value='$hadd_code'>
	<input type=hidden name='hadd_country' value='$hadd_country'>
	<input type=hidden name='description' value='$description'>
	<input type=hidden name='Con' value='$Con'>
	<input type='hidden' name='upload_img' value='$upload_img' />
	<input type='hidden' name='team_id' value='$team_id' />
	$ex
	<tr><th colspan=4>Contact Information</th></tr>
	<tr class='bg-even'>
		<td width=120>First Name</td>
		<td width=210>$name</td>

		<td width=120>Office Phone</td>
		<td width=210>$tell_office</td>
	</tr>
	<tr class='bg-odd'>
		<td>Company/Last Name</td>
		<td>$surname</td>

		<td>Mobile</td>
		<td>$cell</td>
	</tr>
	<tr class='bg-even'>
		<td>Reports To</td>
		<td>$reports_to</td>

		<td>Home Phone</td>
		<td>$tell</td>
	</tr>
	<tr class='bg-odd'>
		<td>Lead Source</td>
		<td>".crm_get_leadsrc($lead_source)."</td>

		<td>Other Phone</td>
		<td>$tell_other</td>
	</tr>
	<tr class='bg-even'>
		<td>Title</td>
		<td>$title</td>

		<td>Fax</td>
		<td>$fax</td>
	</tr>
	<tr class='bg-odd'>
		<td>Department</td>
		<td>$department</td>

		<td>E-mail</td>
		<td>$email</td>
	</tr>
	<tr class='bg-even'>
		<td>Birthdate</td>
		<td>$birthdate_description</td>

		<td>Other E-mail</td>
		<td>$email_other</td>
	</tr>
	<tr class='bg-odd'>
		<td rowspan=2>Account Name</td>
		<td rowspan=2>$accountname</td>

		<td>Assistant</td>
		<td>$assistant</td>
	</tr>
	<tr class='bg-even'>
		<td>Assistant Phone</td>
		<td>$assistant_phone</td>
	</tr>

	<tr class='bg-odd'>
		$upload_box

		<td>Team Permissions</td>
		<td>$team_name</td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr>
		<th colspan=2>Physical Address</th>
		<th colspan=2>Postal Address</th>
	</tr>
	<tr class='bg-even'>
		<td colspan=2 align=left valign=top><xmp>$hadd</xmp></td>

		<td colspan=2 align=left><xmp>$padd</xmp></td>
	</tr>
	<tr class='bg-odd'>
		<td>City</td>
		<td>$padd_city</td>
		<td>City</td>
		<td>$hadd_city</td>
	</tr>
	<tr class='bg-even'>
		<td>State/Province</td>
		<td>$padd_state</td>
		<td>State/Province</td>
		<td>$hadd_state</td>
	</tr>
	<tr class='bg-odd'>
		<td>Postal Code</td>
		<td>$padd_code</td>
		<td>Postal Code</td>
		<td>$hadd_code</td>
	</tr>
	<tr class='bg-even'>
		<td>Country</td>
		<td>$padd_country</td>
		<td>Country</td>
		<td>$hadd_country</td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr>
		<th colspan=2>Description</th>
	</tr>
	<tr class='bg-odd'>
		<td colspan=2 align=left><xmp>$description</xmp></td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr><th colspan=2>Options</th></tr>
	<tr class='bg-even'>
		<td>Private</td>
		<td align=center>$Con</td>
	</tr>

	<tr><td><input type=submit name=back value='&laquo; Correction'></td><td align=right><input type=submit value='Write &raquo;'></td></tr>
	</form>
	</table>
	<p>
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th>Quick Links</th></tr>
	<tr class='bg-odd'><td><a href='list_cons.php'>List contacts</a></td></tr>
	<tr class='bg-odd'><td><a href='index_cons.php'>Contacts</a></td></tr>
	<tr class='bg-odd'><td><a href='main.php'>Main Menu</a></td></tr>
	</table>";

        return $con_data;
}
# write new data
function write_data ($_POST)
{
	$date=date("Y-m-d");
	# get vars
	foreach ($_POST as $key => $value) {
		$$key = $value;
	}

	if( isset($back) ) {
		return get_data("");
	}

	# validate input
	require_lib("validate");
	$v = new  validate ();

	$v->isOk($surname, "string", 1, 100, "Last name");
	$v->isOk($name, "string", 0, 100, "First name");
	$v->isOk($accountname, "string", 0, 1024, "Account");
	$v->isOk($account_id, "num", 0, 9, "Account ID (hidden)");
	$v->isOk($account_type, "string", 0, 100, "Account type (hidden)");
	$v->isOk($reports_to, "string", 0, 100, "Reports to");
	$v->isOk($reports_to_id, "num",0, 9, "Reports to ID (hidden)");
	$v->isOk($lead_source, "string", 0, 100, "Lead Source");
	$v->isOk($title, "string", 0, 100, "Title");
	$v->isOk($department, "string", 0, 100, "Department");
	$v->isOk($tell, "string", 0, 100, "Home Phone");
	$v->isOk($cell, "string", 0, 100, "Mobile Phone");
	$v->isOk($fax, "string", 0, 100, "Fax");
	$v->isOk($tell_office, "string", 0, 100, "Office Phone");
	$v->isOk($tell_other, "string", 0, 100, "Other Phone");
	$v->isOk($email, "string", 0, 100, "Email");
	$v->isOk($email_other, "string", 0, 100, "Other Email");
	$v->isOk($assistant, "string", 0, 100, "Assistant");
	$v->isOk($assistant_phone, "string", 0, 100, "Assistant Phone");
	$v->isOk($padd, "string", 0, 100, "Physical Address");
	$v->isOk($padd_city, "string", 0, 100, "Physical Address: City");
	$v->isOk($padd_state, "string", 0, 100, "Physical Address: State/Province");
	$v->isOk($padd_code, "string", 0, 100, "Physical Address: Postal Code");
	$v->isOk($padd_country, "string", 0, 100, "Physical Address: Country");
	$v->isOk($hadd, "string", 0, 100, "Postal Address");
	$v->isOk($hadd_city, "string", 0, 100, "Postal Address: City");
	$v->isOk($hadd_state, "string", 0, 100, "Postal Address: State/Province");
	$v->isOk($hadd_code, "string", 0, 100, "Postal Address: Postal Code");
	$v->isOk($hadd_country, "string", 0, 100, "Postal Address: Country");
	$v->isOk($description, "string", 0, 100, "Description");
	$v->isOk($upload_img, "string", 0, 3, "Upload Image");
	$v->isOk($team_id, "num", 1, 9, "Team");

        $v->isOk($Con,"string",2 ,3, "Invalid private.");

        $birthdate = "$bf_year-$bf_month-$bf_day";
	if ( $v->isOk($birthdate, "string", 1, 100, "Birthdate") ) {
		if ( ! checkdate($bf_month, $bf_day, $bf_year) ) {
			$v->addError("_OTHER", "Invalid birthdate. No such date exists.");
		}
	}

	$birthdate_description = date("d F Y", mktime(0, 0, 0, $bf_day, $bf_month, $bf_year));

	$assigned_to = USER_NAME;
	$assigned_to_id = USER_ID;

	// read the reports to name
	$reports_to = "";
	if ( ! empty($reports_to_id) ) {
		$reports_to_id += 0;

		db_conn("cubit");
		$sql = "SELECT * FROM cons WHERE id='$reports_to_id' LIMIT 1";
		$rslt = db_exec($sql) or errDie("Error retrieving 'Reports to' value.");

		$dat = pg_fetch_array($rslt);

		if ( ! empty($dat["name"]) ) {
			$reports_to .= "$dat[name] ";
		}

		$reports_to .= "$dat[surname]";
	}

	# display errors, if any
	if ($v->isError ()) {
		$err = "The following field value errors occured:<br>";

		$errors = $v->getErrors();

		foreach ($errors as $e) {
			if ( $e["value"] == "_OTHER" )
				$err .= "<li class=err>$e[msg]</li>";
			else
				$err .= "<li class=err>Invalid characters: $e[msg]</li>";
		}
		return get_data($err);
	}

        db_conn('cubit');

	if ( ! pglib_transaction("BEGIN") ) {
		return "<li class=err>Unable to add contact to database. (TB)</li>";
	}

	if(isset($supacc)) {
		$supacc=remval($supacc);
		$sql = "INSERT INTO  suppliers(deptid, supno, supname, location, fcid,
			currency, vatnum, supaddr, contname, tel, fax, email, url, listid,
			bankname, branname, brancode, bankaccno, balance, fbalance, div)
		VALUES ('2', '$supacc', '$surname', 'loc', '2', 'R', '',
			'$hadd \n $padd', '', '$tell', '$fax', '$email', '', '2', '', '',
			'', '', 0, 0, '".USER_DIV."')";
		$supRslt = db_exec ($sql) or errDie ("Unable to add supplier to the system.", SELF);
		if (pg_cmdtuples ($supRslt) < 1) {
			return "<li class=err>Unable to add supplier to database.</li>";
		}

		if ( ($supp_id = pglib_lastid("suppliers", "supid")) == 0 ) {
			return "<li class=err>Unable to add supplier to contact list.</li>";
		}

		$accountname = $surname;
		$account_type = "Supplier";
		$account_id = $supp_id;
	} else {
		$supp_id=0;
	}

	if(isset($cusacc)) {
		$cusacc=remval($cusacc);
		$sql = "INSERT INTO customers(deptid, accno, surname, title, init,
			location, fcid, currency, category, class, addr1, paddr1, vatnum,
			contname, bustel, tel, cellno, fax, email, url, traddisc, setdisc,
			pricelist, chrgint, overdue, intrate, chrgvat, credterm, odate,
			credlimit, blocked, balance, div,deptname,classname,catname)
		VALUES ('2', '$cusacc', '$surname', '', '', 'loc', '2', 'R', '2', '2',
			'$hadd', '$padd', '', '', '', '$tell', '$cell', '$fax', '$email',
			'', '0', '0', '2', 'yes', '0', '0', 'yes', '0', '$date', '0', 'no',
			'0', '".USER_DIV."','Ledger 1','General','General')";
		$custRslt = db_exec ($sql) or errDie ("Unable to add customer to system.", SELF);
		if (pg_cmdtuples ($custRslt) < 1) {
			return "<li class=err>Unable to add customer to database.</li>";
		}

			if (($cust_id = pglib_lastid("customers", "cusnum")) == 0) {
			return "<li class=err>Unable to add customer to contact list.</li>";
		}

		$accountname = $surname;
		$account_type = "Customer";
		$account_id = $cust_id;
	} else {
		$cust_id=0;
	}

	# write to db
	db_conn("cubit");
	$sql = "INSERT INTO cons (surname, name, accountname, account_id, account_type,
			lead_source, title, department, birthdate, reports_to, reports_to_id,
			tell, cell, fax, tell_office, tell_other, email, email_other, assistant,
			assistant_phone, padd, padd_city, padd_state, padd_code,
			padd_country, hadd, hadd_city, hadd_state, hadd_code,
			hadd_country, description, ref, date, con, by, div, supp_id,
			cust_id, assigned_to, assigned_to_id, team_id)
		VALUES('$surname', '$name', '$accountname', '$account_id', '$account_type',
			'$lead_source', '$title', '$department', '$birthdate', '$reports_to',
			'$reports_to_id', '$tell', '$cell', '$fax', '$tell_office',
			'$tell_other', '$email', '$email_other', '$assistant',
			'$assistant_phone', '$padd', '$padd_city', '$padd_state',
			'$padd_code', '$padd_country', '$hadd', '$hadd_city', '$hadd_state',
			'$hadd_code', '$hadd_country', '$description', '$account_type', CURRENT_DATE,
			'$Con', '".USER_NAME."', '".USER_DIV."', '$supp_id',
			'$cust_id', '$assigned_to', '$assigned_to_id', '$team_id')";
	$rslt = db_exec($sql) or errDie ("Unable to add contact to database.");

	$con_id = pglib_lastid("cons", "id");

	// Write the image (if any)
	if ($upload_img == "yes") {
		if (preg_match("/(image\/jpeg|image\/png|image\/gif)/",
			$_FILES["img_file"]["type"], $extension)) {
			$img = "";
			$fp = fopen ($_FILES["img_file"]["tmp_name"], "rb");
			while (!feof($fp)) {
				$img .= fread($fp, 1024);
			}
			fclose($fp);
			$img = base64_encode($img);

			$sql = "INSERT INTO cubit.cons_img (con_id, type, file, size)
			VALUES ('$con_id', '".$_FILES["img_file"]["type"]."', '$img',
				'".$_FILES["img_file"]["size"]."')";
			$ci_rslt = db_exec($sql) or errDie("Unable to add contact image.");
		} else {
			return "<li class='err'>
				Please note we only accept PNG, GIF and JPEG images.
			</li>";
		}
	}

	if (!pglib_transaction("COMMIT")) {
		return "<li class=err>Unable to add contact to database. (TC)</li>";
	}

	if(isset($crm)) {
		header("Location: crm/tokens-new.php?value=$surname");
		exit;
	}

	$write_data ="<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width='50%'>
	<tr><th colspan='2'>Contact added</th></tr>
	<tr class=datacell>
		<td>$surname has been added to Cubit.</td>
		<td align='center'>
			<a href='cons_perm_alloc.php?con_id=$con_id'>Allocate Permissions</a>
		</td>
	</tr>
	</table>
	<p>
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th>Quick Links</th></tr>
	<tr class='bg-odd'><td><a href='".SELF."'>Add another contact</a></td></tr>
        <tr class='bg-odd'><td><a href='index_cons.php'>Contacts</a></td></tr>
	<tr class='bg-odd'><td><a href='main.php'>Main Menu</a></td></tr>
	</table>";

	return $write_data;
}
?>
