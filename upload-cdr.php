<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Items to invoice</title>
<style type="text/css">
	hr.pme-hr		     { border: 0px solid; padding: 0px; margin: 0px; border-top-width: 1px; height: 1px; }
	table.pme-main 	     { border: #004d9c 1px solid; border-collapse: collapse; border-spacing: 0px; width: 100%; }
	table.pme-navigation { border: #004d9c 0px solid; border-collapse: collapse; border-spacing: 0px; width: 100%; }
	td.pme-navigation-0, td.pme-navigation-1 { white-space: nowrap; }
	th.pme-header	     { border: #004d9c 1px solid; padding: 4px; background: #add8e6; }
	td.pme-key-0, td.pme-value-0, td.pme-help-0, td.pme-navigation-0, td.pme-cell-0,
	td.pme-key-1, td.pme-value-1, td.pme-help-0, td.pme-navigation-1, td.pme-cell-1,
	td.pme-sortinfo, td.pme-filter { border: #004d9c 1px solid; padding: 3px; }
	td.pme-buttons { text-align: left;   }
	td.pme-message { text-align: center; }
	td.pme-stats   { text-align: right;  }
</style>
</head>
<body>
<h3>Upload CDR and create invoice lines</h3>
<?php

// This lookup string is used to link the charges to the subscriptions
$callchargetext = "Call charges";

// MySQL host name, user name, password, database, and table
require_once("service-admin.inc.php");
$opts['tb'] = 'subscriptions';
$opts['sv'] = 'services';
$opts['in'] = 'invoicelines';

$showform = true;
if(isset($_POST["processcdr"])) {
  // apparently we have been submitted, so lets update some records
  $file = $_FILES['cdr'];
  if($file['error'] == UPLOAD_ERR_OK) {
    // Proper file uploaded
    echo "Processing ".$file['name']."...<br>";
    $csvfile = file($file['tmp_name']);
    // Process the cdrfile
    for($l=0; $l<count($csvfile); $l++) {
      $line = $csvfile[$l];
      $entry = split(",", $line);
      $proper = true;
      if(count($entry) != 8) $proper = false;
      if($proper) {
        // Validated line, add to the record
        if($entry[6] != 0) $cdr[$entry[4]] += $entry[6];
      } else {
        echo "Dropping invalid line<br>";
      }
    }

    // Connect to database
    mysql_connect($opts['hn'], $opts['un'], $opts['pw']);
    mysql_select_db($opts['db']);

    // Get all subscriptions
    $qry = "SELECT * FROM ".$opts['tb']." LEFT JOIN ".$opts['sv']." ";
    $qry .= "ON ".$opts['tb'].".serviceid = ".$opts['sv'].".serviceid WHERE servicename='$callchargetext'";
    $result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());

    // Loop through them
    while ($subscription = mysql_fetch_array($result)) {
      // Process the subscription
      $subid = $subscription['subscriptionid'];
      $custid = $subscription['customerid'];
      $description = $subscription['servicename']." ".$subscription['description'];
      $today = date("Y-m-d");
      $updated = false;
      $callcharges = $cdr[$subscription['description']];

      if($callcharges != 0) { 
        // Store the accumulated records in a related subscription
       $ins = "INSERT INTO ".$opts['in']." (customerid, subscriptionid, description, invoicelinedate, amount, charge) ";
        $ins .= "VALUES ($custid, $subid, '$description', NOW(), 1, $callcharges)";
        $insres = mysql_query($ins) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $ins . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
        $upd = "UPDATE ".$opts['tb']." SET lastinvoiced=NOW() WHERE subscriptionid=$subid";
        $updres = mysql_query($upd) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $upd . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());

        echo "Invoiced $description $callcharges<br>";
        $totalcharged += $callcharges;
      }
      // Unset the stored data so we can check what might be left behind in the end
      unset($cdr[$subscription['description']]); 
    }
    echo "Total call charges invoiced: $callcharges<br>";
    if(count($cdr) > 0) {
      // Warning that not all acounts have been invoiced
      echo "<p><font color=red><b>Warning: Remaining records:</b><br>";
      foreach($cdr as $account => $value) echo "Account $account has not been invoiced (missed $value)<br>";
      echo "</font>";
    }

    // Everything went OK, no need to show the form again.
    $showform = false;
  } else {
    echo "<font color=red><b>File upload failed (".$file['error'].")<b></font>";
  }
}

if($showform) {
?>
<p>Upload a Comma Separated CDR file. The CDR file is processed with these fields:
<pre>cid,time,from,to,account,duration,costs,destination</pre>
<p>Account is matched to the description field of a subscription 'Call charges'
<form class="pme-form" enctype="multipart/form-data" method="post" action="<? echo $_SERVER['PHP_SELF'] ?>" name="newinvoiceline"><input type="hidden" name="MAX_FILE_SIZE" value="8000000" />
<table class="pme-main" summary="invoicelines">
<tr class="pme-header">
<th class="pme-header" colspan="1">&nbsp;</th>
<th class="pme-header">CDR file</th>
</tr>
<tr class="pme-row-0">
<td class="pme-navigation-1">&nbsp;</td>
<td class="pme-cell-1">
<input type="file" name="cdr" size="40"> 
</td>
</tr>
</table>
<hr size="1" class="pme-hr" />
<table summary="navigation" class="pme-navigation">
<tr class="pme-navigation">
<td class="pme-buttons">
<b>Process CDR</b><input type="submit" class="pme-add" name="processcdr" value="Go!" />
</td></tr></table>
</form>
<?
}
?>
</body>
</html>
