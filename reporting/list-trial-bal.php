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

require ("../settings.php");          // Get global variables & functions
require("../core-settings.php");

# decide what to do
if (isset($_POST["key"])) {
	switch ($_POST["key"]) {
			case "print":
				$OUTPUT = printacc($_POST);
				break;

			case "printsave":
				$OUTPUT = print_saveacc($_POST);
				break;

			default:
				$OUTPUT = view();
	}
} else {
        # Display default output
        $OUTPUT = view();
}

require ("../template.php");

# Default View
function view(){

	core_connect();
	$sql = "SELECT batchid FROM batch WHERE proc = 'no'";
	$Rs = db_exec($sql) or errdie("Batch file unreachable.");
	if(pg_numrows($Rs) > 0){
		$sum = pg_numrows($Rs);
		$out = pg_fetch_array($Rs);
		$note = "<tr class='bg-even'><td colspan=2 class=err><li>Note : There are $sum unprocessed batch entries.</td></tr><tr><td><br></td></tr>";
	}else{
		$note = "";
	}

	$view = "
	<h3>Trial Balance</h3>
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width=350>
	<form action='".SELF."' method=post name=form>
	<input type=hidden name=key value=print>
	$note
	<tr><th>Field</th><th>Value</th></tr>
	<tr class='bg-odd'><td>Include Accounts with Zero balances</td><td valign=center>
	<input type=radio name=zero value=yes>Yes | <input type=radio name=zero value=no checked=yes>No</td></tr>
	<tr><td><br></td></tr>
	<tr class='bg-even'><td>List Debit & Credit</td><td valign=center>
        <input type=radio name=work value=no checked=yes>Yes | <input type=radio name=work value=Yes >No</td></tr>
        <tr><td><br></td></tr>
	<tr><td><input type=button value='< Cancel' onClick='javascript:history.back();'></td><td valign=center><input type=submit value='Continue >'></td></tr>
	</table>";

	return $view;
}


function printacc($_POST)
{
		# get vars
		foreach ($_POST as $key => $value) {
			$$key = $value;
		}

		// Set up table to display in
		$OUTPUT = "
        <center>
        <h3>Trial Balance</h3>

		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width=450>
        <tr><th>Account Number</th><th>Account Name</th><th>Debit</th><th>Credit</th></tr>";

		// Connect to database
		core_connect();
        $sql = "SELECT * FROM trial_bal ORDER BY topacc, accnum ASC";
        $accRslt = db_exec ($sql) or errDie ("ERROR: Unable to retrieve account details from database.", SELF);
		$numrows = pg_numrows ($accRslt);

        if ($numrows < 1) {
			$OUTPUT = "There are no Accounts yet in Cubit.";
			require ("../template.php");
		}

		# display all Accounts
        $i=0;
        $tldebit = 0;
        $tlcredit = 0;

		if($zero == "no"){
			while($acc = pg_fetch_array ($accRslt)){
				$acc['debit'] = sprint($acc['debit']);
				$acc['credit'] = sprint($acc['credit']);

				$i++;

				if(floatval($acc['debit']) == 0 && floatval($acc['credit']) == 0){
					$i++;
					continue;
				}
				$branname = branname($acc['div']);
				$OUTPUT .= "<tr class='".bg_class()."'><td>$acc[div] - $acc[topacc]/$acc[accnum]</td><td>$acc[accname] - $branname</td>";

				if($work=="Yes")
				{
					if($acc['debit']>$acc['credit'])
					{
						$acc['debit']=$acc['debit']-$acc['credit'];
						$acc['credit']=0;
					}

					if($acc['credit']>$acc['debit'])
					{
						$acc['credit']=$acc['credit']-$acc['debit'];
						$acc['debit']=0;
					}

					if($acc['credit']==$acc['debit'])
					{
						$acc['credit']=0;
						$acc['debit']=0;
					}
				}

				if(floatval($acc['debit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[debit]</td>";
				}

				if(floatval($acc['credit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[credit]</td>";
				}

				$OUTPUT .="</tr>";

				$tldebit += $acc['debit'];
				$tlcredit += $acc['credit'];
			}
		}elseif($zero == "yes"){
			while($acc = pg_fetch_array ($accRslt)){
				$acc['debit'] = sprint($acc['debit']);
				$acc['credit'] = sprint($acc['credit']);

				$i++;
				$branname = branname($acc['div']);
				$OUTPUT .= "<tr class='".bg_class()."'><td>$acc[div] - $acc[topacc]/$acc[accnum]</td><td>$acc[accname] - $branname</td>";

				if($work=="Yes")
					{
						if($acc['debit']>$acc['credit'])
						{
							$acc['debit']=$acc['debit']-$acc['credit'];
							$acc['credit']=0;
						}

						if($acc['credit']>$acc['debit'])
						{
							$acc['credit']=$acc['credit']-$acc['debit'];
							$acc['debit']=0;
						}

						if($acc['credit']==$acc['debit'])
						{
							$acc['credit']=0;
							$acc['debit']=0;
						}
					}

				if(floatval($acc['debit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[debit]</td>";
				}

				if(floatval($acc['credit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[credit]</td>";
				}

				$OUTPUT .="</tr>";

				$tldebit += $acc['debit'];
				$tlcredit += $acc['credit'];
			}
		}
        $OUTPUT .= "<tr class='".bg_class()."'><td colspan=2><b>Total</b></td><td align=center><b>".CUR." $tldebit</b></td><td align=center><b>".CUR." $tlcredit</b></td></tr>
		<tr><td><br></td></tr>

		<!--
		<tr><td align=center><form action='".SELF."' method=post name=form><input type=hidden name=key value=printsave><input type=hidden name=zero value='$zero'><input type=submit value='Save'></form></td>
		<td><form action='../pdf/trial-bal-pdf.php' method=post name=form><input type=hidden name=key value=print><input type=hidden name=work value='$work'><input type=hidden name=zero value='$zero'><input type=submit name=pdf value='View PDF'></form></td>
		<td colspan=2><form action='../xls/trial-bal-xls.php' method=post name=form><input type=hidden name=key value=print><input type=hidden name=work value='$work'><input type=hidden name=zero value='$zero'><input type=submit name=xls value='Export to spreadsheet'></form></td></tr>
		-->

		</table>
		<p>
		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width=25%>
			<tr><th>Quick Links</th></tr>
			<script>document.write(getQuicklinkSpecial());</script>
		</table>";

		return $OUTPUT;
}

function print_saveacc($_POST)
{
		# get vars
		foreach ($_POST as $key => $value) {
			$$key = $value;
		}

		// Set up table to display in
		$OUTPUT = "
        <center>
        <h3>Trial Balance as at : ".date("d M Y")."</h3>
		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width=450>
        <tr><th>Account Number</th><th>Account Name</th><th>Debit</th><th>Credit</th></tr>";

		// Connect to database
		core_connect();
        $sql = "SELECT * FROM trial_bal ORDER BY topacc, accnum ASC";
        $accRslt = db_exec ($sql) or errDie ("ERROR: Unable to retrieve account details from database.", SELF);
		$numrows = pg_numrows ($accRslt);

        if ($numrows < 1) {
			$OUTPUT = "There are no Accounts yet in Cubit.";
			require ("../template.php");
		}

		# display all Accounts
        $i=0;
        $tldebit = 0;
        $tlcredit = 0;

		if($zero == "no"){
			while($acc = pg_fetch_array ($accRslt)){
				$i++;

				if(intval($acc['debit']) == 0 && intval($acc['credit']) == 0){
					$i++;
					continue;
				}
				$branname = branname($acc['div']);

				$OUTPUT .= "<tr class='".bg_class()."'><td>$acc[div] - $acc[topacc]/$acc[accnum]</td><td>$branname - $acc[accname]</td>";

				if(intval($acc['debit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[debit]</td>";
				}

				if(intval($acc['credit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[credit]</td>";
				}

				$OUTPUT .="</tr>";

				$tldebit += $acc['debit'];
				$tlcredit += $acc['credit'];
			}
		}elseif($zero == "yes"){
			while($acc = pg_fetch_array ($accRslt)){
				$i++;
				$branname = branname($acc['div']);
				$OUTPUT .= "<tr class='".bg_class()."'><td>$acc[div] - $acc[topacc]/$acc[accnum]</td><td>$branname - $acc[accname]</td>";

				if(intval($acc['debit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[debit]</td>";
				}

				if(intval($acc['credit']) == 0){
					$OUTPUT .="<td align=center> - </td>";
				}else{
					$OUTPUT .="<td align=center>".CUR." $acc[credit]</td>";
				}

				$OUTPUT .="</tr>";

				$tldebit += $acc['debit'];
				$tlcredit += $acc['credit'];
			}
		}
        $OUTPUT .= "<tr class='".bg_class()."'><td colspan=2><b>Total</b></td><td align=center><b>".CUR." $tldebit</b></td><td align=center><b>".CUR." $tlcredit</b></td></tr>
		</table><br>";

		$output = base64_encode($OUTPUT);
		core_connect();
		$sql = "INSERT INTO save_trial_bal(gendate, output, div) VALUES('".date("Y-m-d")."', '$output', '".USER_DIV."')";
		$Rs = db_exec($sql) or errdie("Unable to save the Trial Balance.");

		$OUTPUT .= "
		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width=25%>
			<tr><th>Quick Links</th></tr>
			<script>document.write(getQuicklinkSpecial());</script>
		</table>";

		return $OUTPUT;
}
?>
