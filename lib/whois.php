<html>
<body>
<form target="<? echo $PHP_SELF; ?>" method="POST">
Domain: <input type="text" name="domain" value="<? echo $_POST["domain"] ?>">
<input type="submit" value="Lookup">
</form>
<?
if(isset($_POST["domain"]) && !empty($_POST["domain"])) {
  $neutralnameserver = "toffee.snt.utwente.nl";
  require_once("zonecheck.class.php");

  $domainname = $_POST["domain"];
  echo "<h1>Whois records for $domainname</h1>";
  $zonecheck = checkzone($domainname, true, false);

?>
<table border=1 cellspacing=0 cellpadding=4>
<tr><td valign=top><h2>Raw output</h2><pre>
<?
  for($i=0; $i<count($zonecheck->info); $i++) {
    echo $zonecheck->info[$i]."\n";
  }
?>
</pre></td><td valign=top><h2>Parsed output</h2>
<?
  echo "<h3>Domain name</h3>";
  echo "<pre>".$zonecheck->parseddomain."</pre>";
  echo "<h3>Domain holder</h3>";
  echo "<pre>".$zonecheck->registrant."</pre>";
  echo "<h3>Admin contact</h3>";
  echo "<pre>".$zonecheck->admincontact."</pre>";
  echo "<h3>Tech contact</h3>";
  echo "<pre>".$zonecheck->techcontact."</pre>";
  echo "<h3>Nameservers</h3>";
  echo "<pre>";
  for($i=0; $i<count($zonecheck->ns); $i++) {
    echo $zonecheck->ns[$i]."\n";
  }
  echo "</pre>";
  echo "<h3>Registrar</h3>";
  echo "<pre>".$zonecheck->registrar."</pre>";
  echo "<h3>Service provider</h3>";
  echo "<pre>".$zonecheck->rsp."</pre>";
?>
<hr><h3>Unparsed data</h3>
<pre>
<?
  var_dump($zonecheck->meta);
?>
</pre></td></tr>
</table>
<?
} // if domain was posted
?>
