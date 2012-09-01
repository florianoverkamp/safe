<?

require_once("service-admin.inc.php");

$opts['tb'] = 'subscriptions';
$opts['sv'] = 'services';
$opts['in'] = 'invoicelines';

// Connect to database
mysql_connect($opts['hn'], $opts['un'], $opts['pw']);
mysql_select_db($opts['db']);

if(isset($_POST["create"])) {
  // apparently we have been submitted, so lets update some records
  $combine = $_POST["submitlines"];
  $invnr = $_POST["invnr"];
 
  // In previous versions, the same invoicenumber could be used for two companies
  // Obviously this is wrong, so:
  // For safety, find the first customer for who an invoiceline is requested
  $qry = "SELECT distinct(customerid) FROM invoicelines WHERE invoiceid IS NULL AND invoicelineid IN (".implode(",",$combine).") LIMIT 1";
  $result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
  $customerset = mysql_fetch_array($result);
  $onecustomer = $customerset["customerid"];
  
  // Okay, update the selected customer
  $qry = "UPDATE ".$opts['in']." SET invoiceid=$invnr, invoicelinedate=NOW() WHERE invoiceid IS NULL AND customerid=$onecustomer AND invoicelineid IN (".implode(",",$combine).")";
  $result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());

  header('Location: invoicelist.php');
} else {
  header('Location: itemstoinvoice.php');
}

?>
