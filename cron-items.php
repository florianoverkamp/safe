<?

require_once("service-admin.inc.php");

$opts['tb'] = 'subscriptions';
$opts['sv'] = 'services';
$opts['in'] = 'invoicelines';

// Connect to database
mysql_connect($opts['hn'], $opts['un'], $opts['pw']);
mysql_select_db($opts['db']);

// Get all subscriptions
$qry = "SELECT * FROM ".$opts['tb']." LEFT JOIN ".$opts['sv']." ";
$qry .= "ON ".$opts['tb'].".serviceid = ".$opts['sv'].".serviceid";

$result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());

header("Content-Type: text/plain");

// Loop through them
while ($subscription = mysql_fetch_array($result)) {
  // Process the subscription
  $subid = $subscription['subscriptionid'];
  $custid = $subscription['customerid'];
  $description = $subscription['servicename']." ".$subscription['description'];
  $amount = $subscription['amount'];
  $nrc = $subscription['nrc'];
  $yrc = $subscription['yrc'];
  $qrc = $subscription['qrc'];
  $mrc = $subscription['mrc'];
//  $substart = $subscription['startdate'];
  $lastinv = $subscription['lastinvoiced'];
  $lastinvtime = strtotime($lastinv);
  $yearseconds = 60*60*24*365;
  $today = date("Y-m-d");
  $updated = false;
  if($lastinv == "0000-00-00") {
    $firsttime = true;
  } else {
    $firsttime = false;
  }

  // Test for NRC
  if(($lastinv == "0000-00-00") && ($nrc != 0)) {
    // Invoice the NRC
    $ins = "INSERT INTO ".$opts['in']." (customerid, subscriptionid, description, invoicelinedate, amount, charge) ";
    $ins .= "VALUES ($custid, $subid, '$NRC: $description', NOW(), $amount, $nrc)";
    $insres = mysql_query($ins) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $ins . "<br />\nError: (" . mysql_errno() . ") " . mysql_error()); 
    $updated = true;
    $firsttime = true;
    echo "Invoiced $NRC $description $amount x $nrc\n";
  }

  // Test for MRC
  if((($MRC != strtolower(date('F', $lastinvtime))) || $firsttime) && ($mrc != 0)) {
    // Invoice the MRC
    $ins = "INSERT INTO ".$opts['in']." (customerid, subscriptionid, description, invoicelinedate, amount, charge) ";
    $ins .= "VALUES ($custid, $subid, '$description $MRC', NOW(), $amount, $mrc)";
    $insres = mysql_query($ins) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $ins . "<br />\nError: (" . mysql_errno() . ") " . mysql_error()); 
    $updated = true;
    echo "Invoiced $description $MRC $amount x $mrc\n";
  }

  // Test for QRC
  if((($QRC != 'Q'.ceil(date('n', $lastinvtime)/3)) || $firsttime) && ($qrc != 0)) {
    // Invoice the QRC
    $ins = "INSERT INTO ".$opts['in']." (customerid, subscriptionid, description, invoicelinedate, amount, charge) ";
    $ins .= "VALUES ($custid, $subid, '$description $QRC', NOW(), $amount, $qrc)";
    $insres = mysql_query($ins) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $ins . "<br />\nError: (" . mysql_errno() . ") " . mysql_error()); 
    $updated = true;
    echo "Invoiced $description $QRC $amount x $qrc\n";
  }

  // Test for YRC
  if((($YRC != date('Y', $lastinvtime)) || $firsttime) && ($yrc != 0)) {
    // Invoice the YRC
    $ins = "INSERT INTO ".$opts['in']." (customerid, subscriptionid, description, invoicelinedate, amount, charge) ";
    $ins .= "VALUES ($custid, $subid, '$description $YRC', NOW(), $amount, $yrc)";
    $insres = mysql_query($ins) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $ins . "<br />\nError: (" . mysql_errno() . ") " . mysql_error()); 
    $updated = true;
    echo "Invoiced $description $YRC $amount x $yrc\n";
  }

  if($updated) {
    $upd = "UPDATE ".$opts['tb']." SET lastinvoiced=NOW() WHERE subscriptionid=$subid";
    $updres = mysql_query($upd) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $upd . "<br />\nError: (" . mysql_errno() . ") " . mysql_error()); 
  }
}
?>
