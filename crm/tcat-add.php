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

require("settings.php");

if(isset($_POST["key"])) {
	switch($_POST["key"]) {
		case "confirm":
			$OUTPUT = confirm($_POST);
			break;
		case "write":
			$OUTPUT = write($_POST);
			break;
		default:
			$OUTPUT = "Invalid use of script";
	}
} else {
	$OUTPUT = enter();
}

$OUTPUT.="<p>
	<table border=0 cellpadding='2' cellspacing='1'>
	<tr><th>Quick Links</th></tr>
	<tr class='bg-odd'><td><a href='tcat-add.php'>Add Query Category</a></td></tr>
	<tr class='bg-odd'><td><a href='tcat-list.php'>View Query Categories</a></td></tr>
	<script>document.write(getQuicklinkSpecial());</script>
	<tr class='bg-odd'><td><a href='index.php'>My Business</a></td></tr>
	</table>";

require("template.php");

function enter() {

	$out="<h3>Add Query Category</h3>
	<form action='".SELF."' method=post>
	<input type=hidden name=key value='confirm'>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th colspan=2>Category Details</th></tr>
	<tr class='bg-odd'><td>Category Name</td><td><input type=text size=20 name=name value=''></td></tr>
	<tr class='bg-even'><td>Description</td><td><input type=text size=20 name=des value=''></td></tr>
	<tr><td colspan=2 align=right><input type=submit value='Confirm &raquo;'></td></tr>
	</form>
	</table>";

	return $out;
}

function entererr($_POST,$errors="") {

	extract($_POST);

	$out="<h3>Add Query Category</h3>
	$errors
	<form action='".SELF."' method=post>
	<input type=hidden name=key value='confirm'>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th colspan=2>Category Details</th></tr>
	<tr class='bg-odd'><td>Category Name</td><td><input type=text size=20 name=name value='$name'></td></tr>
	<tr class='bg-even'><td>Description</td><td><input type=text size=20 name=des value='$des'></td></tr>
	<tr><td colspan=2 align=right><input type=submit value='Confirm &raquo;'></td></tr>
	</form>
	</table>";

	return $out;
}

function confirm($_POST,$errors="") {

	extract($_POST);

	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($name, "string", 1, 100, "Invalid Category name.");
	$v->isOk ($des, "string", 0, 100, "Invalid category description.");

	# display errors, if any
	if ($v->isError ()) {
		$confirm = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirm .= "<li class=err>".$e["msg"];
		}
		return entererr($_POST, $confirm."</li>");
	}

	$out="<h3>Add Query Category</h3>
	<form action='".SELF."' method=post>
	<input type=hidden name=key value='write'>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th colspan=2>Category Details</th></tr>
	<tr class='bg-odd'><td>Category Name</td><td><input type=hidden name=name value='$name'>$name</td></tr>
	<tr class='bg-even'><td>Description</td><td><input type=hidden name=des value='$des'>$des</td></tr>
	<tr><td colspan=2 align=right><input type=submit value='Write &raquo;'></td></tr>
	</form>
	</table>";

	return $out;
}

function write($_POST) {

	extract($_POST);

	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($name, "string", 1, 100, "Invalid category name.");
	$v->isOk ($des, "string", 0, 100, "Invalid category description.");

	# display errors, if any
	if ($v->isError ()) {
		$confirm = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirm .= "<li class=err>".$e["msg"];
		}
		return entererr($_POST, $confirm."</li>");
	}

	db_conn('crm');
	$Sl="INSERT INTO tcats (name,des,div) VALUES ('$name','$des','".USER_DIV."')";
	$Ry=db_exec($Sl) or errDie("Unable to insert category into db.");

	$out="<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width='50%'>
	<tr><th>Category added to the system</th></tr>
	<tr class=datacell><td>New Category, has been successfully added to the system.</td></tr>
	</table>";

	return $out;
}

?>
