<?

require ("settings.php");

$OUTPUT = show_message ();

$OUTPUT .= "<br> <center>" .mkQuickLinks() . "</center>";

require ("template.php");




function show_message ()
{

	$display = "
					<center>
					<table ".TMPL_tblDflts."
						<tr>
							<th>Version Mismatch</th>
						</tr>
						<tr class='".bg_class()."'>
							<td>Cubit has detect an unsupported browser.</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Please ensure you are using the latest version of Mozilla Firefox.</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>The latest version can always be downloaded from <a target='_blank' href='http://www.getfirefox.com'>here</a></td>
						</tr>
					</table>
					</center>
				";
	return $display;

}



?>