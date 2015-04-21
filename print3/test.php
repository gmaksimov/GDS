<?php
require_once('tcpdf.php');


class MYPDF extends TCPDF {

}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPageOrientation('P', true, 10);

$pdf->setFont('dejavusans');

$pdf->addPage();

$pdf->Write(1, 'azazaz ');
$pdf->WriteHTML('&nbsp;<b>az</b>azazaza');



$pdf->output();