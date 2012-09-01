<?
/* Okay so all those whois scripts don't really do much usefull */
/* Let's do it ourselves once more *sigh*                       */

define(CACHEDIR,"/home/florian/apps/safe/cache"); /* Must be writeable for webserver */
define(CACHEEXPIRE,86400);                        /* 24 hours cache time */
define(NSSERVER,"ns1.snt.utwente.nl");            /* Must allow queries from here */

/* Simple wrapper function that creates or reuses a zonecheck object */
function checkzone($domain, $usecache=true) {
  global $checkzone_obj;
  if(empty($checkzone_obj)) {
    $checkzone_obj = new zonecheck($domain, $usecache, $debug);
  } else {
    $checkzone_obj->lookup($domain, $usecache);
  }
  return $checkzone_obj;
} // End of function checkzone

// This works similar to array_intersect_key, but uses regexps in the second array
// I initially used array_intersect_ukey with a callback function, but 
// for some reason that was behaving erroneously
function array_intersect_ekey($haystack, $needles) {
  foreach($haystack as $payload => $value) {
    // Test this twig against the regexps in $needles
    foreach($needles as $pattern => $noop) {
      $matches = preg_match($pattern, $payload);
      if($matches == 1) {
        $intersect[$payload] = $value;
      }
    }
  }
  if(empty($intersect)) $intersect = array();
  return $intersect;
} // End of function array_intersect_ekey

function get_array_intersect_entry($haystack, $needles) {
  $intersect = array_intersect_ekey($haystack, $needles);
  //$entry = current(array_values($intersect));
  foreach($intersect as $member) {
    foreach($member as $particle) {
      $entry[] = $particle;
    }
  }
  if(empty($entry)) {
    $entry = array();
  }
  return $entry;
}

// Test purposes only
if(false) {
  $pattern = "/^Technical Contact (?!ID).+$/";
  $payload = "Technical Contact Pass ID";
  echo "$pattern <-> $payload == ";
  echo preg_match($pattern, $payload);
  die;
}

/* Class zonecheck */
class zonecheck {
  var $domain;
  var $debug;
  var $tld;
  var $info = array();
  var $meta = array();
  var $ns = array();
  var $parseddomain;
  var $registrar;
  var $rsp;
  var $registrant;
  var $admincontact;
  var $techcontact;
  var $ns_ns = array();
  var $ns_mx = array();

  // Definition of array matrices to intercept keys
  // This array has all tokens that we have seen to contain domain name data
  var $tokens_domain = array(
		"/^Domain$/" => 0,
		"/^Domain name$/i" => 0,
		"/^Domain Name \(ASCII\)$/i" => 0);
  // This array has all tokens that we have seen to contain nameserver data
  var $tokens_ns = array(
		"/^Domain nameservers$/" => 0, 
		"/^Domain servers in listed order$/" => 0, 
		"/^Nserver$/" => 0, 
		"/^Nameserver(s)?$/" => 0, 
		"/^Name Server$/" => 0);
  // This array has all tokens that we have seen to contain registrant data
  var $tokens_registrant = array(
		"/^Registrars\.Registrant$/" => 0,
		"/^Registrant (?!ID).+$/" => 0,
		"/^Holder (?!ID).+$/" => 0,
		"/^Registrant$/" => 0);
  // This array has all tokens that we have seen to contain registrar data
  var $tokens_registrar = array(
		"/^Sponsoring Registrar$/" => 0,
		"/^Zone-C (?!ID).+$/" => 0,
		"/^Registrar$/" => 0);
  // This array has all tokens that we have seen to contain admin contact data
  var $tokens_admincontact = array(
		"/^Administrative Contact (?!ID).+$/" => 0,
		"/^Administrative Contact,/" => 0,
		"/^Admin(-C)? (?!ID).+$/" => 0,
		"/^Administrative Contact$/i" => 0);
  // This array has all tokens that we have seen to contain tech contact data
  var $tokens_techcontact = array(
		"/^Technical Contact,/" => 0,
		"/^.+Technical Contact$/" => 0,
		"/^Technical Contact (?!ID).+$/" => 0,
		"/^Tech(-C)? (?!ID).+$/" => 0,
		"/^Technical contact(?:\(s\))?$/i" => 0);
  // This array has all tokens that we have seen to contain rsp data
  var $tokens_rsp = array(
		"/^Registration Service Provider$/" => 0);

  // Constructor
  function zonecheck($domainname, $usecache=true, $debug=false) {
    $this->debug = $debug;
    return $this->lookup($domainname, $usecache, $debug);
  } // End function zonecheck (Constructor)

  function debug($debugtext) {
    if($this->debug) echo $this->domain.": ".$debugtext."\n";
  } // End of function debug

  function lookup($domainname, $usecache=true, $debug=false) {
    $this->debug = $debug;
    $this->domain = $domainname;
    $this->tld = substr($this->domain, strrpos($this->domain, "."));
    $this->info = array();
    $this->meta = array();
    $this->ns = array();
    $this->parseddomain = "";
    $this->registrar = "";
    $this->rsp = "";
    $this->registrant = "";
    $this->admincontact = "";
    $this->techcontact = "";
    $this->ns_ns = array();
    $this->ns_mx = array();

    // Check cache
    $cached = false;
    $cachefilename = CACHEDIR."/".$domainname.".txt";
    if($usecache) {
      if(file_exists($cachefilename)) {
        $this->debug("found cached in file $cachefilename");
        if((time()-filemtime($cachefilename)) < CACHEEXPIRE) {
          $this->debug("accepted cached file $cachefilename");
          $this->info = file($cachefilename);
          $cached = true;
        } else {
          $this->debug("expired cached file $cachefilename");
        }
      }
    } else {
      $this->debug("cache not used by request");
    }

    if(!$cached) {
      $this->debug("performing whois query");
      // Get the raw data
      $args = "";
      switch($this->tld) {
        case ".me": $args = "-h whois.nic.me"; break;
      }
      $cmd = "whois $args $domainname";
      $retcode = exec($cmd, $this->info);
    }

    // Normalize the array and save to cache if needed
    for($line=0; $line<count($this->info); $line++) {
      $this->info[$line] = trim($this->info[$line]);
    }
    if(!$cached && is_writable(CACHEDIR)) {
      $cachefp = fopen($cachefilename, 'w');
      if($cachefp !== false) {
        for($line=0; $line<count($this->info); $line++) {
          fwrite($cachefp, $this->info[$line]."\r\n");
        }
        fclose($cachefp);
      }
    }

    // Parse more intelligent stuff
    $this->enkey_data();
    $this->compose_members();
    $this->extract_registrar();
    $this->extract_domainname();
    $this->extract_nameservers();
    $this->extract_registrant();
    $this->extract_admincontact();
    $this->extract_techcontact();
    $this->extract_rsp();
    $this->nslookup();
  } // End function lookup

  function enkey_data() {
    $key = "general";
    $initoken = "";
    $this->debug("building key/value array");
    // Basically a simple state machine that parses the whois output
    for($line=0; $line<count($this->info); $line++) {
      // Check if this is an INI-style whois line
      if((substr($this->info[$line],0,1) == "[") && (substr($this->info[$line],-1,1) == "]")) {
        // INI-style formatting found (like .de domain)
        $initoken = substr($this->info[$line],1,strlen($this->info[$line])-2);
      }
      
      // Locate the first : in line
      $tokenpos = strpos($this->info[$line], ":");

      if($tokenpos !== false) {
        // Don't count it if it is part of something generic
        $contextpos = strpos($this->info[$line], "http://");
        if($contextpos !== false && $contextpos == $tokenpos-4) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "Last update of whois database:");
        if($contextpos !== false && $contextpos == $tokenpos-29) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "to: (1) allow");
        if($contextpos !== false && $contextpos == $tokenpos-2) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "terms of use: You agree");
        if($contextpos !== false && $contextpos == $tokenpos-12) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "NOTICE: The expiration");
        if($contextpos !== false && $contextpos == $tokenpos-6) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "TERMS OF USE: You are not");
        if($contextpos !== false && $contextpos == $tokenpos-12) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "NOTE: THE WHOIS DATABASE");
        if($contextpos !== false && $contextpos == $tokenpos-4) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "under no circumstances will you use this data to:");
        if($contextpos !== false && $contextpos == $tokenpos-48) $tokenpos = false;
        $contextpos = strpos($this->info[$line], "% Version: ");
        if($contextpos !== false && $contextpos == $tokenpos-9) $tokenpos = false;
      }

      // Split key/value pairs
      if($tokenpos === false) {
        // No key in this line, so use the previous key
        $value = trim($this->info[$line]);
        if($value == "") {
          // Empty lines reset the key and inittoken
          $key = "general";
          $initoken = "";
        } else {
          $this->meta[$key][] = $value;
        }
      } else {
        // Key in line, so split the parts
        $key = trim(substr($this->info[$line], 0, strpos($this->info[$line], ":")));
        if($initoken != "") $key = "$initoken $key";
        $value = trim(substr($this->info[$line], strpos($this->info[$line], ":")+1));
        if($value != "") {
          // Don't care about superfluous empty lines
          $this->meta[$key][] = $value;
        }
      }
    }
    // Clean up metadata we really don't need again
    unset($this->meta["general"]);
  } // End function build_array

  function compose_members() {
    // For .info:
    switch($this->tld) {
      case ".info":
			$this->meta["Registrant"][] = $this->meta["Registrant ID"];
			$this->meta["Registrant"][] = $this->meta["Registrant Name"];
			$this->meta["Registrant"][] = $this->meta["Registrant Organisation"];
			$this->meta["Registrant"][] = $this->meta["Registrant Address1"];
			$this->meta["Administrative Contact"][] = $this->meta["Administrative Contact ID"];
      			$this->meta["Administrative Contact"][] = $this->meta["Administrative Contact Name"];
    }
  }

  function extract_nameservers() {
    $this->debug("locating nameserver info");
    $this->ns=get_array_intersect_entry($this->meta, $this->tokens_ns);
    for($i=0; $i<count($this->ns); $i++) {
      if(strpos($this->ns[$i], " ") !== false) {
        $this->ns[$i] = substr($this->ns[$i], 0, strpos($this->ns[$i], " "));
      }
    }
    sort($this->ns);
  } // End function extract_nameservers

  function extract_registrant() {
    $this->debug("locating registrant info");
    $this->registrant=implode("\n", get_array_intersect_entry($this->meta, $this->tokens_registrant));
  } // End function extract_registrant

  function extract_admincontact() {
    $this->debug("locating admin contact info");
    $this->admincontact=implode("\n", get_array_intersect_entry($this->meta, $this->tokens_admincontact));
  } // End function extract_admincontact

  function extract_techcontact() {
    $this->debug("locating tech contact info");
    $this->techcontact=implode("\n", get_array_intersect_entry($this->meta, $this->tokens_techcontact));
  } // End function extract_techcontact

  function extract_domainname() {
    $this->debug("locating domainname info");
    $this->parseddomain=implode("\n", array_unique(get_array_intersect_entry($this->meta, $this->tokens_domain)));
  } // End function extract_domainname

  function extract_registrar() {
    $this->debug("locating registrar info");
    $this->registrar=implode("\n", get_array_intersect_entry($this->meta, $this->tokens_registrar));
  } // End function extract_registrar

  function extract_rsp() {
    $this->debug("locating registration service provider info");
    $this->rsp=implode("\n", get_array_intersect_entry($this->meta, $this->tokens_rsp));
  } // End function extract_rsp

  function nslookup() {
//    $ttl = array();
//    $ns = array();
    $this->ns_mx = dns_get_record($this->domain, DNS_MX, $ns, $ttl);
echo "<pre>";
var_dump($ns);
var_dump($this->ns_mx);
echo "</pre>";
  } // End function nslookup_ns
}
?>
