<?
require ("../settings.php");
require ("../core-settings.php");
require_lib("docman");
# decide what to do
# Decide what to do
if (isset ($_POST["key"])) {
	switch ($_POST["key"]) {
		case "confirm":
			if(!isset($_POST["conf"])){
				$OUTPUT = get_data ($_POST);
			}else{
				$OUTPUT = con_data ($_POST);
			}
			break;
		case "write":
			$OUTPUT = write_data ($_POST);
			break;
		default:
			if(isset($_GET['docid'])){
				$OUTPUT = get_data ($_GET);
			}else{
				$OUTPUT = "<li> - Invalid use of module";
			}
	}
} else {
	if(isset($_GET['docid'])){
		$OUTPUT = get_data ($_GET);
	}else{
		$OUTPUT = "<li> - Invalid use of module";
	}
}


# display output
require ("../template.php");
# enter new data
function get_data ($VARS = array(), $errors = "")
{

	# Get vars
	global $DOCLIB_DOCTYPES;
	foreach ($VARS as $key => $value) {
		$$key = $value;
	}
	if(!isset($typeid)){
		$xin = "";
		$xins = $xin;
		$typeid = "";
		$docref = "";
		$docname = "";
		$day = date("d");
		$mon = date("m");
		$year = date("Y");
		$descrip = "";
	}else{
  		$xin = (isset($xin)) ? $xin : "";
		$xins = $xin;
		$xin = xin($typeid, $xin);
 	}
	//New
	// DataBase
	db_conn("cubit");
	$S1 = "SELECT * FROM documents ORDER BY docname";
	$Ri = db_exec($S1) or errDie("Unable to enter data.");
	
	while($data = pg_fetch_array($Ri))  {
	}
	//End New
	
	# Select Type
	db_conn('cubit');
	$typs= "<select name='typeid' onchange='document.form1.submit();'>";
	# User types
	$sql = "SELECT * FROM doctypes WHERE div = '".USER_DIV."' ORDER BY typename ASC";
	$typRslt = db_exec($sql);
	if(pg_numrows($typRslt) < 1){
		if(strlen($typeid) < 1)
			$typeid = "inv";
		$xin = xin($typeid, $xins);
	}else{
		while($typ = pg_fetch_array($typRslt)){
			$sel = "";
			if($typ['typeid'] == $typeid)
				$sel = "selected";
			$typs .= "<option value='$typ[typeid]' $sel>($typ[typeref]) $typ[typename]</option>";
		}
	}
	# Built-in types
	foreach($DOCLIB_DOCTYPES as $tkey => $val){
		$sel = "";
		if($tkey == $typeid)
			$sel = "selected";
		$typs .= "<option value='$tkey' $sel>$DOCLIB_DOCTYPES[$tkey]</option>";
	}
	$typs .="</select>";
	
  db_conn('cubit');
   # write to db
  $S1 = "SELECT * FROM document WHERE docid='$docid' AND docname = docname";
  $Ri = db_exec($S1) or errDie ("Unable to access database.");
  if(pg_numrows($Ri)<1){return "Document not Found";
  }
  $Data = pg_fetch_array($Ri);

	$get_data =
"

<h3>Modify Document</h3>
<br>
<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
<form action='".SELF."' method=post>
<input type=hidden name=key value=confirm>
<input type=hidden name=docid value='$docid'>
<tr><td colspan=2>$errors</td></tr>
	<tr><th>Field</th><th>Value</th></tr>
	<tr class='bg-odd'><td>Type</td><td>$typs</td></tr>
	$xin
	<tr class='bg-even'><td>Ref</td><td><input type=text size=10 name=docref value='$Data[docref]'></td></tr>
	<tr class='bg-odd'><td>Document Name</td><td><input type=text size=20 name=docname value='$Data[docname]'></td></tr>
	<tr class='bg-even'><td>Date</td><td><input type=text size=2 name=day maxlength=2  value='$day'>-<input type=text size=2 name=mon maxlength=2  value='$mon'>-<input type=text size=4 name=year maxlength=4 value='$year'></td></tr>
	
	<tr class='bg-even'><td>Decription</td><td><textarea name=descrip rows=4 cols=18>$Data[descrip]</textarea></td></tr>
	<tr><td><br></td></tr>
	<tr><td colspan=2 align=right><input type=submit name=conf value='Confirm &raquo;'></td></tr>
	</table></form>
	<p>
	<table border=0 cellpadding='2' cellspacing='1'>
		<tr><th>Quick Links</th></tr>
		<tr class='bg-odd'><td><a href='tlist-docview.php'>List Removed Documents</a></td></tr>
		<tr class='bg-odd'><td><a href='../main.php'>Main Menu</a></td></tr>
	</table>";
        return $get_data;
}

# Get Data Errors
function enter_err($_POST, $err="")
{
  # Get vars
	global $DOCLIB_DOCTYPES;
	foreach ($VARS as $key => $value) {
		$$key = $value;
	}
	if(!isset($typeid)){
		$xin = "";
		$xins = $xin;
		$typeid = "";
		$docref = "";
		$docname = "";
		$day = date("d");
		$mon = date("m");
		$year = date("Y");
		$descrip = "";
	}else{
  		$xin = (isset($xin)) ? $xin : "";
		$xins = $xin;
		$xin = xin($typeid, $xin);
 	}
	$get_data =
"

<h3>Modify Document</h3>
<br>
<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
<form action='".SELF."' method=post>
<tr><td>$err<br></td><tr>
<input type=hidden name=key value=confirm>
<input type=hidden name=docid value='$docid'>
	<tr><td colspan=2>$errors</td></tr>
	<tr><th>Field</th><th>Value</th></tr>
	<tr class='bg-odd'><td>Type</td><td>$typs</td></tr>
	$xin
	<tr class='bg-even'><td>Ref</td><td><input type=text size=10 name=docref value='$docref'></td></tr>
	<tr class='bg-odd'><td>Document Name</td><td><input type=text size=20 name=docname value='$docname'></td></tr>
	<tr class='bg-even'><td>Date</td><td><input type=text size=2 name=day maxlength=2  value='$day'>-<input type=text size=2 name=mon maxlength=2  value='$mon'>-<input type=text size=4 name=year maxlength=4 value='$year'></td></tr>
	
	<tr class='bg-even'><td>Decription</td><td><textarea name=descrip rows=4 cols=18>$descrip</textarea></td></tr>
	<tr><td><br></td></tr>
	<tr><td colspan=2 align=right><input type=submit name=conf value='Confirm &raquo;'></td></tr>
	</table></form>
	<p>
	<table border=0 cellpadding='2' cellspacing='1'>
		<tr><th>Quick Links</th></tr>
		<tr class='bg-odd'><td><a href='tlist-docview.php'>View Removed Documents</a></td></tr>
	</table>";
        return $get_data;
}
# confirm new data
function con_data ($_POST)
{
	# Get vars
	global $_FILES, $DOCLIB_DOCTYPES;
	foreach ($_POST as $key => $value) {
		$$key = $value;
	}
	
	# Validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($docid, "string", 1, 20, "Invalid document number.");
	$v->isOk ($typeid, "string", 1, 20, "Invalid type code.");
	if(isset($xin)){
		$v->isOk ($xin, "num", 1, 20, "Invalid $DOCLIB_DOCTYPES[$typeid] number.");
	}
	$v->isOk ($docname, "string", 1, 255, "Invalid Document name.");
	$v->isOk ($docref, "string", 0, 255, "Invalid Document reference.");
	$date = $day."-".$mon."-".$year;
	if(!checkdate($mon, $day, $year)){
		$v->isOk ($date, "num", 1, 1, "Invalid date.");
	}
	// $v->isOk ($docname, "string", 1, 255, "Invalid Document name.");
	$v->isOk ($descrip, "string", 0, 255, "Invalid Document Description.");

	# Display errors, if any
	if ($v->isError ()) {
		$confirm = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirm .= "<li class=err>".$e["msg"];
		}
		// $confirm .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return get_data($_POST, $confirm);
	}

	if(!isset($xin)){
		$typRs = get("cubit", "*", "doctypes", "typeid", $typeid);
		$typ = pg_fetch_array($typRs);
		$typename = "($typ[typeref]) $typ[typename]";
		$xinc = "";
	}else{
		$typename = $DOCLIB_DOCTYPES[$typeid];
		$xinc = xinc($typeid, $xin);
	}


	$con_data ="<h3>Confirm Document</h3>
	<form action='".SELF."' method=post>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<input type=hidden name=key value=write>
	<input type=hidden name=docid value='$docid'>
	<input type=hidden name=typeid value='$typeid'>
	<input type=hidden name=docname value='$docname'>
	<input type=hidden name=docref value='$docref'>
	<input type=hidden name=day value='$day'>
	<input type=hidden name=mon value='$mon'>
	<input type=hidden name=year value='$year'>
	<input type=hidden name=descrip value='$descrip'>
 	
	<tr><th>Field</th><th>Value</th></tr>
	<tr class='bg-odd'><td>Type</td><td>$typename</td></tr>
	$xinc
	<tr class='bg-even'><td>Document Name</td><td>$docname</td></tr>
	<tr class='bg-odd'><td>Ref</td><td>$docref</td></tr>
	<tr class='bg-even'><td>Date</td><td align=center>$date</td></tr>
	<tr class='bg-even'><td>Description</td><td>$descrip</td></tr>
	<tr><td><br></td></tr>
	<tr><td><br></td></tr>
	<tr><td align=right></td><td valign=left><input type=submit value='Write &raquo;'></td></tr>
	</table></form>
	<p>
	<table border=0 cellpadding='2' cellspacing='1'>
		<tr><th>Quick Links</th></tr>
		<tr class='bg-odd'><td><a href='tlist-docview.php'>View Removed Documents</a></td></tr>
		<tr class='bg-odd'><td><a href='../main.php'>Main Menu</a></td></tr>
	</table>";
        return $con_data;
}
# write new data
function write_data ($_POST)
{
	# Get vars
	global $DOCLIB_DOCTYPES;
	foreach ($_POST as $key => $value) {
		$$key = $value;
	}
	# Validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($docid, "string", 1, 20, "Invalid document number.");
	$v->isOk ($typeid, "string", 1, 20, "Invalid type code.");
	if(isset($xin)){
		$v->isOk ($xin, "num", 1, 20, "Invalid $DOCLIB_DOCTYPES[$typeid] number.");
	}
	$v->isOk ($docname, "string", 1, 255, "Invalid Document name.");
	$v->isOk ($docref, "string", 0, 255, "Invalid Document reference.");
	$date = $year."-".$mon."-".$day;
	if(!checkdate($mon, $day, $year)){
		$v->isOk ($date, "num", 1, 1, "Invalid date.");
	}
	$v->isOk ($descrip, "string", 0, 255, "Invalid Document Description.");

	# Display errors, if any
	if ($v->isError ()) {
		$confirmCust = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirmCust .= "<li class=err>".$e["msg"];
		}
		$confirmCust .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $confirmCust;
	}

	if(!isset($xin)){
		$typRs = get("cubit", "*", "doctypes", "typeid", $typeid);
		$typ = pg_fetch_array($typRs);
		$typename = $typ['typename'];
		$xin = 0;
	}else{
		$typename = $DOCLIB_DOCTYPES[$typeid];
	}
		
	db_conn('cubit');

	if ( ! pglib_transaction("BEGIN") ) {
		return "<li class=err>Unable to get_data document(TB)</li>";
	}

	$S1="SELECT * FROM document WHERE docid='$docid'";
	$Ri=db_exec($S1) or errDie("Unable to get document details.");

	if(pg_num_rows($Ri)<1) {
		return "Invalid document.";
	}

	$cdata=pg_fetch_array($Ri);

	# write to db
	$S1 = "UPDATE document SET typeid='$typeid',typename='$typename', xin='$xin',docref='$docref',docdate='$date',docname='$docname',descrip='$descrip', div='".USER_DIV."' WHERE docid='$docid'";
	$Ri = db_exec($S1) or errDie ("Unable to access database.");
	$Data = pg_fetch_array($Ri);

	

	if (!pglib_transaction("COMMIT")) {
		return "<li class=err>Unable to get_data document. (TC)</li>";
	}
	
	/*# Write to db
	$sql = "UPDATE documents SET typeid = '$typeid', docref = '$docref', docname = '$docname', typename = '$typename', xin = '$xin', docdate = '$date', descrip = '$descrip' WHERE docid = '$docid' AND div = '".USER_DIV."'";
	$docRslt = db_exec ($sql) or errDie ("Unable to edit $docname.", SELF);
	if (pg_cmdtuples ($docRslt) < 1) {
		return "<li class=err>Unable to edit $docname to database.";
	}*/

	$write_data =
	"<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width='50%'>
		<tr><th>Document Details</th></tr>
		<tr class=datacell><td>Document <b>$docname</b>, has been successfully added to the system.</td></tr>
	</table>
	<p>
	<table border=0 cellpadding='2' cellspacing='1'>
		<tr><th>Quick Links</th></tr>
		<tr class='bg-odd'><td><a href='tlist-docview.php'>View Removed Documents</a></td></tr>
		<tr class='bg-odd'><td><a href='../main.php'>Main Menu</a></td></tr>
	</table>";
	return $write_data;
}
?>
