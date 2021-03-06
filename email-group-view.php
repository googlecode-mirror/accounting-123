<?php

require ("settings.php");

	$OUTPUT = display($_POST);

	$OUTPUT .= "<p>".
				mkQuickLinks(
					ql("email-groups.php", "Send Email To Group"),
					ql("email-group-new.php", "Add Email Group"),
					ql("email-group-view.php", "View Email Groups")
				);

require ("template.php");

function display($_POST)
{

	db_connect ();

	// Retrieve teams
	$sql = "SELECT * FROM egroups";
	$group_rslt = db_exec($sql) or errDie("Unable to retrieve groups.");

	$listing = "";
	while ($garr = pg_fetch_array($group_rslt)) {
		$listing .= "
						<tr class='".bg_class()."'>
							<td>$garr[grouptitle]</td>
							<td>$garr[groupname]</td>
							<td><a href='email-group-remove.php?id=$garr[id]'>Remove</a></td>
						</tr>";
	}

	if (empty($listing)) {
		$team_out = "
		<tr class='".bg_class()."'>
			<td colspan='3'><li>No groups found</li></td>
		</tr>";
	}

	$OUTPUT = "
					<h3>View Groups</h3>
					<table ".TMPL_tblDflts.">
						<tr>
							<th>Group Title</th>
							<th>Group Name</th>
							<th>Options</th>
						</tr>
						$listing
					</table>";
	return $OUTPUT;

}

?>