<?php

// MySQL host name, user name, password, database, and table
require_once("service-admin.inc.php");
$opts['tb'] = 'invoicelines';
$opts['cu'] = 'customers';

// Connect to database
mysql_connect($opts['hn'], $opts['un'], $opts['pw']);
mysql_select_db($opts['db']);

// Get the last 20 generated invoices
$qry = "SELECT invoiceid, ".$opts['tb'].".customerid, customername, description, amount, charge, invoicelinedate FROM ".$opts['tb']." LEFT JOIN ".$opts['cu']." ";
$qry .= "ON (".$opts['tb'].".customerid = ".$opts['cu'].".customerid) WHERE invoiceid = ".$_GET["id"];

$result = mysql_query($qry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $qry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
$inv = mysql_fetch_array($result);

$cqry = "SELECT * FROM ".$opts['cu']." WHERE customerid = ".$inv["customerid"];
$cresult = mysql_query($cqry) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $cqry . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
$customer = mysql_fetch_array($cresult);

// Check if we need to compute VAT
// Yes by default
$includevat = true;
if(!empty($customer["vatnr"])) {
  if(!is_numeric(substr($customer["vatnr"],0,2)) && (strtoupper(substr($customer["vatnr"],0,2)) != "NL")) {
    $includevat = false;
  }
}

$pdf=new PDF();
$fontname="Arial";
$alternatefontname="Times";
$pdf->AliasNbPages();
$pdf->AddPage();

$leftmargin = 15;

// Create customer address box
$pdf->SetFont($fontname,'',12);
$pdf->SetY(45);
$pdf->Cell($leftmargin);
$pdf->Cell(80,6,$customer["customername"]);
$pdf->Ln();
if(!empty($customer["attn"])) {
  $pdf->Cell($leftmargin);
  $pdf->Cell(80,6,$strings[$customer["language"]]["attn"].$customer["attn"]);
  $pdf->Ln();
}
$pdf->Cell($leftmargin);
$pdf->Cell(80,6,$customer["street"]);
$pdf->Ln();
$pdf->Cell($leftmargin);
$pdf->Cell(80,6,$customer["zipcode"]."  ".$customer["city"]);
$pdf->Ln();
if(!empty($customer["country"])) {
  $pdf->Cell($leftmargin);
  $pdf->Cell(80,6,$customer["country"]);
  $pdf->Ln();
}

// Date and reference
$nicedate = strtolower(date("j-M-Y", strtotime($inv["invoicelinedate"])));
$pdf->SetY(80);
$pdf->Cell($leftmargin);
$pdf->Cell(80,6,$strings[$customer["language"]]["date"].$nicedate);
$pdf->Ln();
$pdf->Cell($leftmargin);
$pdf->Cell(80,6,$strings[$customer["language"]]["subject"].$inv["invoiceid"]);
$pdf->Ln();
$pdf->Ln();

// Introductory text
$pdf->Cell($leftmargin);
$pdf->MultiCell(170,6,$strings[$customer["language"]]["pretext"]);
$pdf->Ln();

// Header row
$pdf->SetFont($alternatefontname,'',10);
$pdf->Cell($leftmargin);
$pdf->Cell(10,6,"#");
$pdf->Cell(120,6,"");
$pdf->Cell(20,6,$strings[$customer["language"]]["itemprice"],0,0,'R');
$pdf->Cell(20,6,$strings[$customer["language"]]["linetotal"],0,0,'R');
$pdf->Ln();

// Build the table with invoiceable items
mysql_data_seek($result, 0);
$total = 0;
$pdf->SetFont($fontname,'',10);
while ($invoiceline = mysql_fetch_array($result)) {
  $pdf->Cell($leftmargin);
  $pdf->Cell(10,6,$invoiceline["amount"]);
  $pdf->Cell(120,6,$invoiceline["description"]);
  $pdf->Cell(20,6,EURO." ".number_format($invoiceline["charge"],2,",",""),0,0,'R');
  $pdf->Cell(20,6,EURO." ".number_format($invoiceline["amount"]*$invoiceline["charge"],2,",",""),0,0,'R');
  $pdf->Ln();
  $total += $invoiceline["amount"]*$invoiceline["charge"];
}

// Summation, VAT
$pdf->Cell($leftmargin);
$pdf->Cell(10,6,"",'T');
$pdf->Cell(120,6,$strings[$customer["language"]]["invtotal"],'T');
$pdf->Cell(20,6,"",'T');
$pdf->Cell(20,6,EURO." ".number_format($total,2,",",""),'T',0,'R');
$pdf->Ln();
if($includevat) {
  $vat = $total*($VATpct/100);
  $pdf->Cell($leftmargin);
  $pdf->Cell(10,6,"");
  $pdf->Cell(120,6,"$VATpct% ".$strings[$customer["language"]]["vat"]);
  $pdf->Cell(20,6,"");
  $pdf->Cell(20,6,EURO." ".number_format($vat,2,",",""),0,0,'R');
  $pdf->Ln();
  $pdf->Cell($leftmargin);
  $pdf->Cell(10,6,"",'T');
  $pdf->Cell(120,6,$strings[$customer["language"]]["invoiceable"],'T');
  $pdf->Cell(20,6,"",'T');
  $pdf->Cell(20,6,EURO." ".number_format($total+$vat,2,",",""),'T',0,'R');
  $pdf->Ln();
} else {
  if(!empty($customer["vatnr"])) {
    $pdf->Cell($leftmargin);
    $pdf->Cell(10,6,"");
    $pdf->Cell(120,6,$strings[$customer["language"]]["your"]." ".$strings[$customer["language"]]["vat"]."nr: ".$customer["vatnr"]);
    $pdf->Ln();
  }
  $pdf->Cell($leftmargin);
  $pdf->Cell(10,6,"",'T');
  $pdf->Cell(120,6,$strings[$customer["language"]]["invoiceable"],'T');
  $pdf->Cell(20,6,"",'T');
  $pdf->Cell(20,6,EURO." ".number_format($total,2,",",""),'T',0,'R');
  $pdf->Ln();
}
// Finalisation text
$pdf->SetFont($fontname,'',12);
$pdf->Ln();
$pdf->Cell($leftmargin);
$pdf->MultiCell(170,6,$strings[$customer["language"]]["posttext"]);
$pdf->Ln();
$pdf->Cell($leftmargin);
$pdf->MultiCell(170,6,$strings[$customer["language"]]["signature"]);
$pdf->Ln();

$filename = $inv["invoiceid"]." ".$customer["customername"]." ".$inv["invoicelinedate"].".pdf";
$pdf->Output(str_replace(" ", "_", $filename), "I");
?>
