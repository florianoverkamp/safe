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
<?php

/*
 * IMPORTANT NOTE: This generated file contains only a subset of huge amount
 * of options that can be used with phpMyEdit. To get information about all
 * features offered by phpMyEdit, check official documentation. It is available
 * online and also for download on phpMyEdit project management page:
 *
 * http://platon.sk/projects/main_page.php?project_id=5
 *
 * This file was generated by:
 *
 *                    phpMyEdit version: 5.7.1
 *       phpMyEdit.class.php core class: 1.204
 *            phpMyEditSetup.php script: 1.50
 *              generating setup script: 1.50
 */

// MySQL host name, user name, password, database, and table
require_once("service-admin.inc.php");
$opts['tb'] = 'invoicelines';
$opts['cu'] = 'customers';

// Connect to database
mysql_connect($opts['hn'], $opts['un'], $opts['pw']);
mysql_select_db($opts['db']);

// Identify first free invoice number
$qry = "SELECT MAX(invoiceid) FROM ".$opts['tb'];
$result = mysql_query($qry);
$maxinvoice = mysql_fetch_row($result);
$nextinvoice = $maxinvoice[0]+1;

// Get all non-invoiced invoicelines
$qry = "SELECT * FROM ".$opts['tb']." LEFT JOIN customers ON ".$opts['tb'].".customerid=customers.customerid WHERE invoiceid IS NULL ORDER BY ".$opts['tb'].".customerid,invoicelinedate";

$result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
$count = mysql_num_rows($result);

?>

<? if($count>0) { ?>
<h3>Items to invoice</h3>
<form class="pme-form" method="post" action="createinvoice.php" name="createinvoice">
<table class="pme-main" summary="invoicelines">
<tr class="pme-header">
<th class="pme-header" colspan="1">&nbsp;</th>
<th class="pme-header">Customer</th>
<th class="pme-header">Description</th>
<th class="pme-header">Invoicelinedate</th>
<th class="pme-header">Amount</th>
<th class="pme-header">Charge</th>
</tr>

<?
// Loop through them
while ($invoiceline = mysql_fetch_array($result)) {
  echo "<tr class=\"pme-row-0\">";
  echo "<td class=\"pme-navigation-1\"><input type=\"checkbox\" name=\"submitlines[]\" value=\"".$invoiceline["invoicelineid"]."\"></td>";
  echo "<td class=\"pme-cell-1\">".$invoiceline["customername"]."</td>";
  echo "<td class=\"pme-cell-1\">".$invoiceline["description"]."</td>";
  echo "<td class=\"pme-cell-1\">".$invoiceline["invoicelinedate"]."</td>";
  echo "<td class=\"pme-cell-1\">".$invoiceline["amount"]."</td>";
  echo "<td class=\"pme-cell-1\">".$invoiceline["charge"]."</td>";
  echo "</tr>\n";
}

?>
</table>
<hr size="1" class="pme-hr" />
<table summary="navigation" class="pme-navigation">
<tr class="pme-navigation">
<td class="pme-buttons">
<b>Create invoice number </b><input name="invnr" type="text" value="<? echo $nextinvoice; ?>" size=10>
<input type="submit" class="pme-add" name="create" value="Go!" />
</td></tr></table>
</form>
<? } ?>

<h3>One-time invoice line</h3>
<form class="pme-form" method="post" action="newinvoiceline.php" name="newinvoiceline">
<table class="pme-main" summary="invoicelines">
<tr class="pme-header">
<th class="pme-header" colspan="1">&nbsp;</th>
<th class="pme-header">Customer</th>
<th class="pme-header">Description</th>
<th class="pme-header">Invoicelinedate</th>
<th class="pme-header">Amount</th>
<th class="pme-header">Charge</th>
</tr>
<tr class="pme-row-0">
<td class="pme-navigation-1">&nbsp;</td>
<td class="pme-cell-1"><select class="pme-input-0" name="customerid">
<option value="0">---Select---</option>
<?
// Get all customers
$qry = "SELECT * FROM ".$opts['cu']." ORDER BY customername";

$result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());

while($customer = mysql_fetch_array($result)) {
  echo "<option value=\"".$customer["customerid"]."\">".$customer["customername"]."</option>\n";
}
?>
</select></td>
<td class="pme-cell-1"><input size=40 type="text" name="description" value=""></td>
<td class="pme-cell-1"><input size=10 type="text" name="invoicelinedate" value="<? echo date("Y-m-d"); ?>"></td>
<td class="pme-cell-1"><input size=5 type="text" name="amount" value="1"></td>
<td class="pme-cell-1"><input size=5 type="text" name="charge" value=""></td>
</tr>
</table>
<hr size="1" class="pme-hr" />
<table summary="navigation" class="pme-navigation">
<tr class="pme-navigation">
<td class="pme-buttons">
<b>Add new invoice line </b><input type="submit" class="pme-add" name="addinvline" value="Go!" />
</td></tr></table>
</form>

</body>
</html>