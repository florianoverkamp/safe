<?

$neutralnameserver = "toffee.snt.utwente.nl";

require_once("service-admin.inc.php");
require_once("lib/zonecheck.class.php");

$opts['tb'] = 'domains';

// Connect to database
mysql_connect($opts['hn'], $opts['un'], $opts['pw']);
mysql_select_db($opts['db']);

// Get all subscriptions
$qry = "SELECT * FROM ".$opts['tb']." WHERE domain= 'vanommeren.biz'";

$result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());

header("Content-Type: text/plain");

// Loop through them
while ($domain = mysql_fetch_array($result)) {
  // Check domain parameters
  $domainname = $domain['domain'];
  echo $domainname.": ";

  // Check Whois NS records
  if(empty($zonecheck)) { 
    $zonecheck = new zonecheck($domainname);
  } else {
    $zonecheck->lookup($domainname);
  }
  echo $zonecheck->registrar;
  echo " / ";
  echo $zonecheck->rsp;
  echo " / ";
  $nstxt = implode(",", $zonecheck->ns);
  echo $nstxt;
  echo " / ";

  if(!empty($zonecheck->registrar) && ($domain['registrar'] != $zonecheck->registrar)) {
    $updqry = "UPDATE ".$opts['tb']." SET registrar='".$zonecheck->registrar."' WHERE domain='$domainname'";
    $updres = mysql_query($updqry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $updqry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
  }
  if(!empty($zonecheck->rsp) && ($domain['rsp'] != $zonecheck->rsp)) {
    $updqry = "UPDATE ".$opts['tb']." SET rsp='".$zonecheck->rsp."' WHERE domain='$domainname'";
    $updres = mysql_query($updqry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $updqry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
  }
  if(!empty($nstxt) && ($domain['whoisglue'] != $nstxt)) {
    $updqry = "UPDATE ".$opts['tb']." SET whoisglue='$nstxt' WHERE domain='$domainname'";
    $updres = mysql_query($updqry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $updqry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
  }

  // Check DNS NS records
  $output = array();
  $nshosts = array();
  $cmd = "host -t NS $domainname $neutralnameserver";
  $retcode = exec($cmd, $output);
  foreach($output as $nsline) {
    $tok = strtok($nsline, " \n\t");
    while($tok !== false) {
      $host = $tok;
      $tok = strtok(" \n\t");
    }
    $nshosts[] = $host;
  }
  sort($nshosts);
  $nstxt = implode(",",$nshosts);
  echo $nstxt;
  echo " / ";
  if($domain['dnshosts'] != $nstxt) {
    $updqry = "UPDATE ".$opts['tb']." SET dnshosts='$nstxt' WHERE domain='$domainname'";
    $updres = mysql_query($updqry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $updqry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
  }

  // Check DNS MX records
  $output = array();
  $mxhosts = array();
  $cmd = "host -t MX $domainname $neutralnameserver";
  $retcode = exec($cmd, $output);
  foreach($output as $mxline) {
    $tok = strtok($mxline, " \n\t");
    while($tok !== false) {
      $host = $tok;
      $tok = strtok(" \n\t");
    }
    $mxhosts[] = $host;
  }
  sort($mxhosts);
  $mxtxt = implode(",",$mxhosts);
  echo $mxtxt;
  echo " / ";
  if($domain['mxhosts'] != $mxtxt) {
    $updqry = "UPDATE ".$opts['tb']." SET mxhosts='$mxtxt' WHERE domain='$domainname'";
    $updres = mysql_query($updqry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $updqry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
  }

  // Check DNS www record
  $output = array();
  unset($host);
  $cmd = "host www.$domainname $neutralnameserver | grep -v 'CNAME'";
  $retcode = exec($cmd, $output);
  $tok = strtok($output[count($output)-1], " \n\t");
  // Gets only the last line of output (which holds the host IP)
  while($tok !== false) {
    $host = $tok;
    $tok = strtok(" \n\t");
  }
  switch($host) {
    case "217.114.110.194": $host = "clio"; break;
    case "217.114.110.195": $host = "toledo"; break;
  }
  echo $host;
  if($domain['web'] != $host) {
    $updqry = "UPDATE ".$opts['tb']." SET web='$host' WHERE domain='$domainname'";
    $updres = mysql_query($updqry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $updqry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
  }

  echo "\n";
}
?>
