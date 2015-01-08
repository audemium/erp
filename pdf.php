<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	require_once('init.php');
	require('mpdf/mpdf.php');
	
	$factoryItem = Factory::createItem($_GET['type']);
	$return = $factoryItem->generatePDF($_GET['id'], $_GET['pdfID']);
	
	$mpdf = new mPDF('c');
	$mpdf->WriteHTML($return[1]);
	$mpdf->Output($return[0].'.pdf', 'I');
	exit();
?>