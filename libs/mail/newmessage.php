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

# get settings

require ("../settings.php");
require_lib("validate");

// remove all '
if ( isset($_POST) ) {
	foreach ( $_POST as $key => $value ) {
		$_POST[$key] = str_replace("'", "", $value);
	}
}
if ( isset($_GET) ) {
	foreach ( $_GET as $key => $value ) {
		$_GET[$key] = str_replace("'", "", $value);
	}
}

require ("object_smtpmail.php");

// overwrite the GET VARS with POST VARS (so both can be access at any times)
if ( isset($_POST) ) {
	foreach ( $_POST as $arr => $arrval ) {
		$_GET[$arr] = $arrval;
	}
}

// make sure something is being done
if ( ! isset($_GET["key"]) ) $_GET["key"] = "create";

switch ( $_GET["key"] ) {
	case "send": // send the form
		$OUTPUT = sendMsg();
		break;

	case "create": // the new msg form
	default:
		$OUTPUT = writeMsg();
		break;
}

require ("../template.php");

// creates the form of the new message
function writeMsg() {
	global $_GET;

	$OUTPUT = "";

	// create the list of accounts to choose from (ones you may send from)
	$sql = "SELECT account_id,account_name,smtp_from
			 FROM mail_accounts WHERE ( username='".USER_NAME."' OR \"public\"='1' ) AND enable_smtp = '1'

		UNION
		SELECT mail_accounts.account_id,account_name,smtp_from
			FROM mail_accounts,mail_priv_accounts
			WHERE mail_accounts.account_id = mail_priv_accounts.account_id
				AND priv_owner = '".USER_NAME."' AND enable_smtp = '1'";

	$rslt = db_exec($sql);

	if ( pg_num_rows($rslt) <= 0 )
		return "You have no accounts from which you may send email.";

	// restore the previous entries if any (on errors)
	extract($_GET);
	extract($_FILES);

	if ( ! isset($_GET["send_to"]) ) $send_to = "";
	if ( ! isset($_GET["send_bcc"]) ) $send_bcc = "";
	if ( ! isset($_GET["send_cc"]) ) $send_cc = "";
	if ( ! isset($_GET["subject"]) ) $subject = "";
	if ( ! isset($_FILES["attachment"]) ) $attachment = "";
	if ( ! isset($_GET["body"]) ) $body = "";

	// creates the acocunts selection list
	$select_accounts = "<select name='aid'>";
	while ( $row = pg_fetch_array($rslt) ) {
		$select_accounts .= "<option value='$row[account_id]'>$row[account_name] ($row[smtp_from])</option>";
	}
	$select_accounts .= "</select>";

	// create the default values (with forward and reply)
	if ( isset($msg_id) ) {
		// read the message
		$rslt = db_exec("SELECT folder_id,subject,add_from,add_to,add_cc,add_bcc,priority,attachments,msgbody_id,date
				FROM mail_messages
				WHERE message_id = '$msg_id';");

		if ( pg_num_rows($rslt) <=0 ) {
			exit("No such message.");
		}

		// fetch the message data
		$msg_data = pg_fetch_array($rslt);

		// check to see if user has access  to this message's folder, giving him access to the message
		$sql = "
		SELECT folder_id FROM mail_folders
			WHERE ( username = '".USER_NAME."' OR \"public\" = '1' ) AND folder_id='$msg_data[folder_id]'
		UNION
		SELECT folder_id FROM mail_priv_folders WHERE priv_owner='".USER_NAME."' AND folder_id = '$msg_data[folder_id]'
		UNION
		SELECT mail_priv_accounts.account_id FROM mail_priv_accounts,mail_folders
			WHERE mail_folders.account_id = mail_priv_accounts.account_id";
		$rslt = db_exec($sql);

		if ( pg_num_rows($rslt) <= 0 ) {
			exit("You dont have sufficient privileges to read this message.");
		}

		// read the message body now that we know we are safe
		$rslt = db_exec("SELECT name,data FROM mail_msgbodies,mail_datatypes
			WHERE msgbody_id=$msg_data[msgbody_id] AND mail_datatypes.type_id = mail_msgbodies.type_id");
		if ( pg_num_rows($rslt) <= 0 ) {
			exit("Message body not found. Please contact Cubit.");
		}

		$msgbody = pg_fetch_array($rslt);

		// decode
		$msgbody["data"] = base64_decode($msgbody["data"]);

		// process
		require ("object_mailmsg.php");
		$msg = & new clsMailMsg;
		$msg->processMessage($msgbody["data"]);
		$attachments = "";

		// if it is a multipart message, create attachment list for all attachments, and fill the body with the rest
		if ( $msg->maintype == "multipart" ) {
			if ( ! is_array($msg->parts) ) {
				$msgbody["data"] = implode("", $msg->body);
			} else {
				// ok start splitting body from attachment
				$pmsg = & new clsMailMsg;
				$msgbody["data"] = "";

				foreach ( $msg->parts as $pnum => $partdata ) {
					$partdata = implode ("\n", $partdata);
					$pmsg->processMessage($partdata);

					if ( ! ($filename = $pmsg->getAttachmentFilename()) ) {
						$pmsg_data = implode("\n",$pmsg->body);

						// decode the message if shoudl
						if ( isset($pmsg->headers["Content-Transfer-Encoding"]) ) {
							// base64 encoding
							if ( strtolower($pmsg->headers["Content-Transfer-Encoding"]) == "base64" ) {
								$pmsg_data = base64_decode($pmsg_data);
							}
						}

						$msgbody["data"] .=  "$pmsg_data\n-----\n";
					}
				}
			}
		} else {
			if ( is_array($msg->body) )
				$msgbody["data"] = implode("\n", $msg->body);
			else
				$msgbody["data"] = $msg->body;
		}

		// if there were attachments, create the listing
		if ( is_array($attachments) ) {
			$attachments = implode("<br>", $attachments);
		}

		$showdoc_html_arr = explode("\n", $msgbody["data"]);
		$showdoc_html = "''\n";
		foreach($showdoc_html_arr as $value) {
			$value = str_replace("\r", "", $value);
			$value = str_replace("'","\\'", $value);
			if ( $msg->maintype == 'text' && $msg->subtype == 'plain' )
				$showdoc_html .= "+ '$value<br>'\n";
			else
				$showdoc_html .= "+ '$value'\n";
		}
	} else {
		$showdoc_html = "''";
	}

	// start of the body
	$OUTPUT .= "<form method=POST action='newmessage.php' name=editForm enctype='multipart/form-data'>
			<input type=hidden name=key value=send>
			".(isset($msg_id)?"<input type=hidden name=msg_id value='$msg_id'>":"")."
		<table width=100%>";

	// the body
	$OUTPUT .= "<tr>
			<td width=25%>
				Account:
			</td>
			<td width=75%>
				$select_accounts
			</td>
		</tr>
		<tr>
			<td width=25%>
				To:
			</td>
			<td width=75%>
				<input type=text name=send_to size=50 value='$send_to'>
			</td>
		</tr>
		<tr>
			<td width=25%>
				Cc:
			</td>
			<td width=75%>
				<input type=text name=send_cc size=50 value='$send_cc'>
			</td>
		</tr>
		<tr>
			<td width=25%>
				Bcc:
			</td>
			<td width=75%>
				<input type=text name=send_bcc size=50 value='$send_bcc'>
			</td>
		</tr>
		<tr>
			<td width=25%>
				Subject:
			</td>
			<td width=75%>
				<input type=text name=subject size=50 value='$subject'>
			</td>
		</tr>
		<tr>
			<td width=25%>
				Attachment:
			</td>
			<td width=75%>
				<input type=file name=attachment size=50 value='$attachment'>
			</td>
		</tr>
		<tr>
			<td width=100% colspan=2>
				<script language='JavaScript'>

				function update() {
					document.editForm.bodydata.value = editArea.document.body.innerHTML;
					document.editForm.submit();
				}

				function Init() {
					editArea.document.designMode = 'On';
					editArea.document.body.innerHTML = $showdoc_html;
				}

				function controlSelOn(ctrl) {
					ctrl.style.borderColor = '#000000';
					ctrl.style.backgroundColor = '#B5BED6';
					ctrl.style.cursor = 'hand';
				}

				function controlSelOff(ctrl) {
					ctrl.style.borderColor = '#D6D3CE';
					ctrl.style.backgroundColor = '#D6D3CE';
				}

				function controlSelDown(ctrl) {
					ctrl.style.backgroundColor = '#8492B5';
				}

				function controlSelUp(ctrl) {
				ctrl.style.backgroundColor = '#B5BED6';
				}

				function doBold() {
					editArea.document.execCommand('bold', false, null);
				}

				function doItalic() {
					editArea.document.execCommand('italic', false, null);
				}

				function doUnderline() {
					editArea.document.execCommand('underline', false, null);
				}

				function doLeft() {
					editArea.document.execCommand('justifyleft', false, null);
				}

				function doCenter() {
					editArea.document.execCommand('justifycenter', false, null);
				}

				function doRight() {
					editArea.document.execCommand('justifyright', false, null);
				}

				function doOrdList() {
					editArea.document.execCommand('insertorderedlist', false, null);
				}

				function doBulList() {
					editArea.document.execCommand('insertunorderedlist', false, null);
				}

				function doRule() {
					editArea.document.execCommand('inserthorizontalrule', false, null);
				}

				function doSize(fSize) {
					if(fSize != '')
						editArea.document.execCommand('fontsize', false, fSize);
				}

				window.onload = Init;

				</script>

				<table id='tblCtrls' width='700px' height='30px' border='0' cellspacing='0' cellpadding='0' bgcolor='#D6D3CE'>
				<tr>
				<td class='tdClass'>
					<img alt='Bold' class='buttonClass' src='../images/bold.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doBold()'>

					<img alt='Italic' class='buttonClass' src='../images/italic.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doItalic()'>
					<img alt='Underline' class='buttonClass' src='../images/underline.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doUnderline()'>

					<img alt='Left' class='buttonClass' src='../images/left.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doLeft()'>
					<img alt='Center' class='buttonClass' src='../images/center.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doCenter()'>
					<img alt='Right' class='buttonClass' src='../images/right.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doRight()'>

					<img alt='Ordered List' class='buttonClass' src='../images/ordlist.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doOrdList()'>
					<img alt='Bulleted List' class='buttonClass' src='../images/bullist.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doBulList()'>

					<img alt='Horizontal Rule' class='buttonClass' src='../images/rule.gif' onMouseOver='controlSelOn(this)' onMouseOut='controlSelOff(this)' onMouseDown='controlSelDown(this)' onMouseUp='controlSelUp(this)' onClick='doRule()'>
				</td>
				<td class='tdClass' align=right>
					<select name='selSize' onChange='doSize(this.options[this.selectedIndex].value)'>
					<option value=''>-- Font Size --</option>
					<option value='1'>Very Small</option>
					<option value='2'>Small</option>
					<option value='3'>Medium</option>
					<option value='4'>Large</option>
					<option value='5'>Larger</option>
					<option value='6'>Very Large</option>
					</select>
				</td>
				</tr>
				</table>

				<iframe name='editArea' id='editArea' style='width: 700px; height:405px; background: #FFFFFF;'></iframe>
				<input type=hidden name=bodydata value=''>
			</td>
		</tr>
		<tr>
			<td width=100% colspan=2>
				<input type=button onClick='update();' value='Send'> &nbsp; &nbsp;
				<input type=reset value='Clear'>
			</td>
		</tr>
		";

	// ends the body output
	$OUTPUT .= "</table>
	</form>";

	return $OUTPUT;
}

// verifies the message and sends it, the store it in database under sent items
function sendMsg() {
	global $_GET;

	$v = & new validate;

	$OUTPUT = "";

	// restore the variables
	extract($_GET);
	extract($_FILES);

	// check if account is valid
	if ( isset($_GET["aid"]) ) {
		if ( ! $v->isOk( $_GET["aid"], "num", 0, 9, "" ) )
			return "Invalid account number specified";

		// check if you may send mail from here
		$sql = "SELECT 1
			 FROM mail_accounts WHERE ( username='".USER_NAME."' OR \"public\"='1' )
			 	AND enable_smtp = '1' AND account_id='$aid'

		UNION
		SELECT 1
			FROM mail_accounts,mail_priv_accounts
			WHERE mail_accounts.account_id = mail_priv_accounts.account_id AND mail_accounts.account_id='$aid'
				AND priv_owner = '".USER_NAME."' AND enable_smtp = '1'";

		$rslt = db_exec($sql);

		if ( pg_num_rows($rslt) <= 0 )
			return "You may not send mail from this account<br>";
	} else {
		return "No account specified<br>";
	}

	if ( ! isset($_GET["send_to"]) ) $send_to = "";
	if ( ! isset($_GET["send_bcc"]) ) $send_bcc = "";
	if ( ! isset($_GET["send_cc"]) ) $send_cc = "";
	if ( ! isset($_GET["subject"]) ) $subject = "";
	if ( ! isset($_FILES["attachment"]) ) $attachment = "";
	if ( ! isset($_GET["body"]) ) $body = "";

	$v->resetErrors();
	$v->isOK($subject, "string", 1, 255, "Invalid subject.");
	// $v->isOK($send_to, "email", 1, 255, "Invalid recipient.");
	if ( strlen($send_to) <= 0 ) $v->addError("", "Invalid recipient");
	// $v->isOK($send_cc, "email", 0, 255, "Invalid cc recipient.");
	// $v->isOK($send_bcc, "email", 0, 255, "Invalid bcc recipient.");
	//if ( ! $v->isOK($bodydata, "string", 1, 255, "Invalid text in body.") ) {
	//	$_GET["body"] = htmlspecialchars($body); // makes sure we dont get cross site scripting
	//}

	// ok now print errors if any
	if ( $v->isError() ) {
		$errs = $v->getErrors();

		foreach ( $errs as $arr => $errval ) {
			$OUTPUT .= "$errval[msg]<br>";
		}

		$OUTPUT .= writeMsg();

		return $OUTPUT;
	}

	$bodydata = "<html>$bodydata</html>";

	// get the smtp data
	$rslt = db_exec("SELECT smtp_from, smtp_reply, signature, smtp_host, smtp_auth, smtp_user, smtp_pass
		FROM mail_accounts WHERE account_id=$_GET[aid]");
	$smtp_data = pg_fetch_array($rslt);

	// build msg body
	$body = "$body\n\n$smtp_data[signature]";

	// determine whether or not here is an attachment
	$has_attachment = is_uploaded_file($attachment["tmp_name"]);

	// modify message and create content_type header depending on whether or not an attachment was posted
	if ( $has_attachment == FALSE ) {
		$msgtype = $content_type = "text/html";
		$transfer_encoding = "8bit";
	} else { // has attachment
		$msgtype = $content_type = "multipart/mixed";

		// create the main body
		$body_text = "Content-Type: text/html; charset=US-ASCII\n";
		$body_text .= "Content-Transfer-Encoding: base64\n";
		$body_text .= "\n" . chunk_split(base64_encode($bodydata));

		// get the attachment data
		if ( ($fd = fopen($attachment["tmp_name"], "r")) == TRUE ) {
			$attachment_data = "";
			while ( ! feof($fd) ) {
				$attachment_data .= fgets( $fd, 4096 );
			}
			fclose($fd);

			// delete the temporary file
			unlink($attachment["tmp_name"]);

			$attachment_data = chunk_split(base64_encode($attachment_data));

			$attachment_headers = "Content-Type: $attachment[type]; name=\"$attachment[name]\"\n";
			$attachment_headers .= "Content-Transfer-Encoding: base64\n";
			$attachment_headers .= "Content-Disposition: attachment; filename=\"$attachment[name]\"\n";

			$attachment_data = "$attachment_headers\n$attachment_data";
		} else { // error opening the attachment file
			$attachment_data = "";
		}

		// generate a unique boundary ( md5 of filename + ":=" + filesize )
		$boundary = md5($attachment["name"]) . "=:" . $attachment["size"];
		$content_type .= "; boundary=\"$boundary\"";

		// put together the body
		$bodydata = "\n--$boundary\n$body_text\n\n--$boundary\n$attachment_data\n\n--$boundary--\n";
	}

	// generate the msg id
	list($buf, $domain) = explode("@", $smtp_data["smtp_from"]);

	// build headers
	$headers[] = "From: $smtp_data[smtp_from]";
	$headers[] = "Reply-To: $smtp_data[smtp_reply]";
	$headers[] = "X-Mailer: Cubit Mail";
	$headers[] = "Return-Path: $smtp_data[smtp_reply]";
	$headers[] = "Message-ID: <".date("YmdHi").".".md5($bodydata)."@$domain>";
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "Content-Type: $content_type; charset=US-ASCII";
	$headers[] = "cc: $send_cc";
	$headers[] = "bcc: $send_bcc";

	// create the header variable (it is done this way, to make management of headers easier, since there
	// may be no tabs and unnecesary whitespace in mail headers)
	//$headers[] = "\n"; // add another new line to finish the headers
	$headers = implode("\n", $headers);

        // send the message
	$sendmail = & new clsSMTPMail;
	$OUTPUT = $sendmail->sendMessages($smtp_data["smtp_host"], 25, $smtp_data["smtp_auth"], $smtp_data["smtp_user"],
		$smtp_data["smtp_pass"],$send_to, $smtp_data["smtp_from"], $subject, $bodydata, $headers);

	if ( $sendmail->bool_success ) {
		$account_id = "$_GET[aid]";

		$type_id = getMsgType($msgtype);

		// data and header is base64_encoded so weird characters can also be stored
		$buf = "$headers\n\n$bodydata";
		$data = chunk_split(base64_encode( $buf ));

		db_conn("cubit");

		// insert body into Cubit
		if ( ! pglib_transaction("BEGIN") ) continue;

		$rslt = db_exec("INSERT INTO mail_msgbodies (type_id, data)
			VALUES( $type_id, '$data' )");

		if ( pg_cmdtuples($rslt) <= 0 ) continue;

		$msgbody_id = pglib_lastid("mail_msgbodies", "msgbody_id");

		pglib_transaction("COMMIT");

		// get the folder this message should be inserted into
		$rslt = db_exec("SELECT fid_sent FROM mail_account_settings WHERE account_id='$account_id'");

		if ( pg_num_rows($rslt) > 0 )
			$infolder = pg_fetch_result($rslt, 0, 0);
		else
			$infolder = 0; // move to no folder, but store, this way all is not lost

		// insert the message linked to body
		$sql = " INSERT INTO mail_messages ( account_id, folder_id, subject, add_from, add_to, add_cc,
					add_bcc, priority, attachments, msgbody_id, flag, date)
				VALUES ( '$account_id', '$infolder', '$subject', '$smtp_data[smtp_from]', '$send_to',
					'$send_cc', '$send_bcc', '1', '".($has_attachment?"1":"0")."', '$msgbody_id',
					'1', CURRENT_TIMESTAMP)";

		$rslt = db_exec($sql) or errDie("Error saving message in Sent Items.");
	}

	/*if ( mail($send_to, $subject, $body, $headers) == TRUE )
		$OUTPUT = "Successfully sent mail to $send_to.<br>";
	else
		$OUTPUT = "Error sending mail.<br>";*/

	return $OUTPUT;
}

// function that get's the type id out of mail_datatypes, if it doesn't exist, it creates it
function getMsgType($msg_type) {
	$rslt = db_exec("SELECT type_id FROM mail_datatypes WHERE name = '$msg_type'");

	// does it exist? return it
	if ( pg_num_rows($rslt) > 0 ) {
		return pg_fetch_result( $rslt, 0, 0 );
	}

	// it doesn't! create it and get the insert id
	if ( ! pglib_transaction("BEGIN") )
		return 1;

	if ( ! db_exec("INSERT INTO mail_datatypes (name,icon) VALUES('$msg_type', 'icon_blank.gif')") )
		return;

	$type_id = pglib_lastid("mail_datatypes","type_id");

	if ( ! pglib_transaction("COMMIT") )
		return 1;

	return $type_id;
}
?>
