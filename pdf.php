<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	require_once('init.php');
	require_once('tcpdf/tcpdf.php');
	
	$factoryItem = Factory::createItem($_GET['type']);
	$return = $factoryItem->generatePDF($_GET['id'], $_GET['pdfID']);
	
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetTitle($return[0]);
	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
	//$pdf->SetFont('times', 'BI', 20);
	$pdf->AddPage();
	$pdf->writeHTML($return[1], true, false, true, false, '');
	
	$pdf->Output($return[0].'.pdf', 'I');
	exit();
?>