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
# admin-usradd.php :: Module to add users to the system
##

require ("settings.php");

if ($_POST) {
	switch ($_POST["key"]) {
		case "confirm":
			$OUTPUT = confirmUser ($_POST);
			break;
		case "write":
			$OUTPUT = writeUser ($_POST);
			break;
		default:
			$OUTPUT = enterUser ();
        }
}
else {
	$OUTPUT = enterUser ();
}

require ("template.php");

##
# functions
##

# enter new users details
function enterUser ($username="", $postype='P', $manager=false, $err="")
{
		# Connect to db
		db_connect ();
		$brans = "<select name='div'>";
		$sql = "SELECT * FROM branches ORDER BY branname ASC";
		$branRslt = db_exec($sql);
		if(pg_numrows($branRslt) < 1){
			return "<li>There are no branches in Cubit.";
		}else{
			while($bran = pg_fetch_array($branRslt)){
				$brans .= "<option value='$bran[div]'>($bran[brancod]) $bran[branname]</option>";
			}
		}
		$brans .= "</select>";
		$posman_hgh = "";
		if ( $postype == 'S' ) {
			$posman_vis = "visible";
			$posman_hgh = "";
		} else {
			$posman_vis = "hidden";
			//$posman_hgh = "height: 0px;";
		}

		$enterUser = "
        <h3>Add new user to database</h3>
        <script>
        function showPosMan() {
        	x = document.getElementById('div_posman');

        	x.style.visibility = 'visible';
        }
        function hidePosMan() {
        	x = document.getElementById('div_posman');

        	x.style.visibility = 'hidden';
        }
        </script>
        <table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
        <form action='".SELF."' method=post>
        <input type=hidden name=key value=confirm>
		<input type=hidden name=f1 value='0'>
        $err
		<tr>
			<th colspan='2'>Note</th>
		</tr>
		<tr class='bg-odd'>
			<td colspan='2'>This user will only be able to create POS invoices.</td>
		</tr>
        <tr><th>Field</th><th>Value</th></tr>
		<tr class='bg-odd'><td>".REQ."Branch</td><td>$brans</td></tr>
        <tr class='bg-even'><td>".REQ."Username</td><td><input type=text size=20 name=username value='$username'> must not contain spaces</td></tr>
        <tr class='bg-odd'><td>".REQ."Password</td><td><input type=password size=20 name=password></td></tr>
        <tr class='bg-even'><td>".REQ."Confirm password</td><td><input type=password size=20 name=password2></td></tr>
        <tr class='bg-odd'>
        	<td rowspan=3>POS Type</td>
        	<td>
			<table><tr>
				<td><input type=radio name=postype value='P' ".($postype=='P'?"checked":"").">POS User</td>
			</tr></table>
        	</td>
        </tr>
        <tr class='bg-odd'>
        	<td>
        	<table><tr>
        		<td><input type=radio name=postype value='S' ".($postype=='S'?"checked":"")." >Speed POS User</td>
        	</tr></table>
        	</td>
        </tr>
	<tr class='bg-odd'>
        	<td>
        	<table><tr>
        		<td><input type=radio name=postype value='M' ".($postype=='S'?"checked":"")." >POS Supervisor</td>
        	</tr></table>
        	</td>
        </tr>

        <tr><td align=right colspan=2><input type=submit value='Confirm &raquo'></td></tr>
        </form>
        </table>
        <p>
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th>Quick Links</th></tr>
	<tr class='bg-odd'><td><a href='main.php'>Main Menu</a></td></tr>
	</table>";

	return $enterUser;
}

# confirm entered info
function confirmUser ($_POST)
{
	# Get vars
	foreach ($_POST as $key => $value) {
		$$key = $value;
	}
	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($username, "string", 1, 20, "Invalid user name.");
	$username2 = str_replace(" ", "", $username);
	if(strlen($username) > strlen($username2))
		$v->isOk ($username, "num", 0, 0, "Error : user name must not contain spaces.");
	$v->isOk ($div, "num", 1, 20, "Invalid Branch.");
	$v->isOk ($password, "string", 1, 20, "Invalid password.");
	if ( $postype != 'P' && $postype != 'S' && $postype != 'M' ) {
		$v->addError("", "Invalid POS user.");
	}
	if(isset($f1))
	{
		$v->isOk ($password2, "string", 1, 20, "Invalid password 2.");
		$v->pwMatch ($password, $password2, "Passwords do not match.");
	}


	if($postype == "M") {
		$manager="Yes";
	} else {
		$manager="No";
	}

        # display errors, if any
		if ($v->isError ()) {
			$theseErrors = "";

			$errors = $v->getErrors();
			foreach ($errors as $e) {
				$theseErrors .= "-".$e["msg"]."<br>";
			}
			$theseErrors = "<tr><td class=err colspan=2>$theseErrors</td></tr>
			<tr><td colspan=2><br></td></tr>";
			return enterUser($username,$postype,$manager=="Yes"?true:false,$theseErrors);
			exit;
		}

		# Get branch name
		db_connect();
		$sql = "SELECT branname FROM branches WHERE div = '$div'";
		$branRslt = db_exec($sql);
		$bran = pg_fetch_array($branRslt);

		if(isset($f1))
		{
			$ex="<input type=hidden name=f2 value=''>";
			# exit if user exists
			$sql = "SELECT username FROM users WHERE username = '$username'";
			$usrRslt = db_exec ($sql) or errDie ("Unable to check database for existing username.");
			if (pg_numrows ($usrRslt) > 0) {
				return "User, $username, already exists in database.";
			}

		} else {$ex="";}




		$confirmUser ="
        <h3>Add user to database</h3>
        <h4>Confirm entry</h4>
        <table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
        <form action='".SELF."' method=post name=form>
        <input type=hidden name=key value=write>
        <input type=hidden name=username value='$username'>
		<input type=hidden name=div value='$div'>
        <input type=hidden name=password value='$password'>
        <input type=hidden name=manager value='$manager'>
        <input type=hidden name=postype value='$postype'>

        $ex
        <tr><th>Field</th><th>Value</th></tr>
		<tr class='bg-odd'><td>Branch</td><td>$bran[branname]</td>
        <tr class='bg-even'><td>Username</td><td>$username</td>
        <tr class='bg-odd'><td>Password</td><td>********</td>";

        if ( $postype == "P" ) {
			$confirmUser .= "
			<tr class='bg-even'><td>POS Type</td><td>POS User</td>";
		} else if ( $postype == "S" ) {
			$confirmUser .= "
			<tr class='bg-even'><td>POS Type</td><td>Speed POS User</td>
			";
		}else if ( $postype == "M" ) {
			$confirmUser .= "
			<tr class='bg-even'><td>POS Type</td><td>POS Supervisor</td>
			<tr class='bg-odd'><td>POS Supervisor</td><td>$manager</td>";
		}

        $confirmUser .= "
        <tr><td><br></td></tr></table>";


        $confirmUser .="</table></td></tr>
        <tr><td align=right colspan=3><input type=submit name=doneBtn value='Write &raquo'></td></tr>
        </form>
        </table>
        <p>
		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
			<tr><th>Quick Links</th></tr>
			<tr class='bg-odd'><td><a href='main.php'>Main Menu</a></td></tr>
		</table>";

		return $confirmUser;
}

# write user to db
function writeUser ($_POST)
{
	# get vars
	foreach ($_POST as $key => $value) {
		$$key = $value;
	}
	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($div, "num", 1, 20, "Invalid Branch.");
	$v->isOk ($username, "string", 1, 20, "Invalid user name.");
	$v->isOk ($password, "string", 1, 20, "Invalid password.");
	//$v->isOk ($tool, "string", 1, 3, "Invalid tooltips selection.");
	if ( $postype != 'P' && $postype != 'S'  && $postype != 'M' ) {
		$v->addError("", "Invalid POS user.");
	}
	$v->isOk ($username, "string", 1, 20, "Invalid user name.");
	$username2 = str_replace(" ", "", $username);
	if(strlen($username) > strlen($username2)) {
		$v->addError("", "Error : user name must not contain spaces.");
	}
	$v->isOk ($div, "num", 1, 20, "Invalid Branch.");
	$v->isOk ($password, "string", 1, 20, "Invalid password.");
	if ( $postype != 'P' && $postype != 'S' && $postype != 'M' ) {
		$v->addError("", "Invalid POS user.");
	}
	if(isset($f1))
	{
		$v->isOk ($password2, "string", 1, 20, "Invalid password 2.");
		$v->pwMatch ($password, $password2, "Passwords do not match.");
	}

	# display errors, if any
	if ($v->isError ()) {
		$theseErrors = "";

		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$theseErrors .= "-".$e["msg"]."<br>";
		}
		$theseErrors = "<tr><td class=err colspan=2>$theseErrors</td></tr>
		<tr><td colspan=2><br></td></tr>";
		return enterUser($username,$postype,$manager=="Yes"?true:false,$theseErrors);
		exit;
	}

	# connect to db
	db_connect ();
	if ( ! isset($admin)) $admin=0;

	$Sql = "DELETE FROM userscripts WHERE username='$username'";
        $Ex = db_exec($Sql);

	if(isset($f2))
	{
		# exit if user exists
		$sql = "SELECT username FROM users WHERE username='$username'";
		$usrRslt = db_exec ($sql) or errDie ("Unable to check database for existing username.");
		if (pg_numrows ($usrRslt) > 0) {
			return "User, $username, already exists in database.";
		}

		# get md5 hash of password
		$password = md5 ($password);

		if($manager=="Yes"){
			$abo=1000;

			$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'pos-report-user.php')";
			$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
		} else {
			$abo=0;
		}

		$sql = "INSERT INTO users (username, password, services_menu, admin,div, usertype,abo)
		VALUES ('$username', '$password', 'L', $admin, '$div', '$postype','$abo')";
		$nwUsrRslt = db_exec ($sql) or errDie ("Unable to add user to database.");
	} else {
		// update the admin variable
		//db_exec("UPDATE users SET admin=$admin WHERE username='$username'");
	}



        $Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'top_menu.php')";
        $Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'diary.php')";
	$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'diary-day.php')";
	$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'glodiary.php')";
	$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'glodiary-day.php')";
	$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'todo.php')";
	$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'index_die.php')";
	$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'index-services.php')";
        $Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");

	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'pos-invoice-new.php')";
        $Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'pos-slip.php')";
        $Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'pos-invoice-print.php')";
        $Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
	$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'index-sales.php')";
        $Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");
        
		$Sql = "INSERT INTO userscripts (username, script) VALUES ('$username', 'cust-stmnt.php')";
		$Ex = db_exec ($Sql) or errDie ("Unable to add user to database.");    


       # status report
	$writeUser ="
        <table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width='50%'>
        <tr><th>New user added to database</th></tr>
        <tr class=datacell><td>New user, $username, was successfully added to Cubit.</td></tr>
        </table>
        <p>
        <tr>
        <table border=0 cellpadding='2' cellspacing='1'>
        <tr><th>Quick Links</th></tr>
        <tr bgcolor='#88BBFF'><td><a href='".SELF."'>Add another user</a></td></tr>
        <tr bgcolor='#88BBFF'><td><a href='main.php'>Main Menu</a></td></tr>
        </tr>";

        return $writeUser;
}
?>
