<?

require_once("service-admin.inc.php");

$opts['tb'] = 'subscriptions';
$opts['sv'] = 'services';
$opts['in'] = 'invoicelines';

// Connect to database
mysql_connect($opts['hn'], $opts['un'], $opts['pw']);
mysql_select_db($opts['db']);

if(isset($_POST["addinvline"]) && ($_POST["customerid"]!=0) && (!empty($_POST["description"])) && (!empty($_POST["amount"])) && (!empty($_POST["charge"]))) {
  // apparently we have been submitted, so lets update some records

  $customerid = $_POST["customerid"];
  $description = $_POST["description"];
  $invoicelinedate = $_POST["invoicelinedate"];
  $amount = $_POST["amount"];
  $charge = $_POST["charge"];

  $qry = "INSERT INTO ".$opts['in']." (customerid, description, invoicelinedate, amount, charge) VALUES ($customerid, '$description', '$invoicelinedate', $amount, $charge)";
  $result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
}

header('Location: itemstoinvoice.php');
?>
