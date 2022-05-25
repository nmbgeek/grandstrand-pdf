<?php

add_action('wp_ajax_pdf', function(){
	global $wpdb, $margins, $font_table_rows, $page_width, $page_height, $table_padding, $font_header, $header_top, $font_footer, $footer_bottom, $first_column_width, $table_border_width, $font_table_rows, $table_padding, $font_table_header, $first_column_width, $day_column_width, $table_border_width, $font_table_rows, $table_padding, $first_column_width, $day_column_width, $table_border_width, $inner_page_height, $font_table_rows, $index, $exclude_from_indexes, $zip_codes, $table_padding, $line_height_ratio;

	//must be a logged-in user to run this page (otherwise last_contact will be null)
	if (!isset($_GET['start']) || !isset($_GET['size'])) {
		die('variables missing');
	}

	ini_set('max_execution_time', 60);

	//output PDF of NYC meeting list using the TCPDF library

	//don't show these in indexes
	$exclude_from_indexes	= array('Beginner', 'Candlelight', 'Closed', 'Grapevine', 'Literature', 'Open', 'Topic Discussion');

	//config dimensions, in inches
	$table_border_width		= .1;

	//convert dimensions to mm
	$inch_converter			= 25.4; //25.4mm to an inch

	if ($_GET['size'] == 'letter') {
		$table_padding		= 1.5; //in mm
		$header_top			= 9;
		$footer_bottom 		= -15;
		$font_header			= array('helvetica', 'b', 18);
		$font_footer			= array('helvetica', 'r', 10);
		$font_table_header	= array('helvetica', 'b', 8);
		$font_table_rows		= array('dejavusans', 'r', 6.4); //for the unicode character
		$font_index_header	= array('helvetica', 'b', 9);
		$font_index_rows		= array('helvetica', 'r', 6);
		$margins = array(
			'left'			=> .2,
			'right'			=> .2,
			'top'			=> .65, //include header
			'bottom'		=> .5, //include footer
		);
		$page_width			= 4.2 * $inch_converter;
		$page_height			= 8 * $inch_converter;
		$line_height_ratio	= 2.87;
		$index_width			= 57; // in mm
		$table_gap			= .25 * $inch_converter; //gap between tables
	} elseif ($_GET['size'] == 'book') {
		$table_padding		= 1.2; //in mm
		$header_top			= 6;
		$footer_bottom 		= -10;
		$font_header			= array('helvetica', 'b', 16);
		$font_footer			= array('helvetica', 'r', 8);
		$font_table_header	= array('helvetica', 'b', 6);
		$font_table_rows		= array('dejavusans', 'r', 5.4); //for the unicode character
		$font_index_header	= array('helvetica', 'b', 7);
		$font_index_rows		= array('helvetica', 'r', 5.4);
		$margins = array(
			'left'				=> .25,
			'right'				=> .25,
			'top'				=> .65, //include header
			'bottom'				=> .55, //include footer
		);
		$page_width			= 6.5 * $inch_converter;
		$page_height			= 9.5 * $inch_converter;
		$line_height_ratio	= 2.4;
		$index_width			= 47; // in mm
		$table_gap			= .2 * $inch_converter; //gap between tables
	}

	foreach ($margins as $key => $value) $margins[$key] *= $inch_converter;
	$inner_page_width		= $page_width - $margins['left'] - $margins['right'];
	$inner_page_height		= $page_height - $margins['top'] - $margins['bottom'];
	$first_column_width		= $inner_page_width * .37;
	$day_column_width		= ($inner_page_width - $first_column_width) / 7;
	$page_threshold			= .5 * $inch_converter; //amount of space to start a new section
	$index = $zip_codes		= array();

	//main sections are here manually to preserve book order
	$district80 = array();
	foreach (array( 
		'Dillon', 
		'Florence',
		'Hemingway',
		'Marion'
	) as $region) {
		$region_id = $wpdb->get_var('SELECT term_id FROM wp_jdbl_terms where name = "' . $region . '"');
		if (!$region_id) die('could not find region with name ' . $region);
		$district80[$region_id] = array();
	}

	$district81 = array();
	foreach (array(
		'Carolina Forest', 
		'Conway',
		'Little River', 
		'Longs', 
		'Loris', 
		'Myrtle Beach', 
		'North Myrtle Beach'
	) as $region) {
		$region_id = $wpdb->get_var('SELECT term_id FROM wp_jdbl_terms where name = "' . $region . '"');
		if (!$region_id) die('could not find region with name ' . $region);
		$district81[$region_id] = array();
	}

	$district82 = array();
	foreach (array(
		'Garden City', 
		'Georgetown',
		'Murrells Inlet',
		'Pawleys Island',
		'Surfside Beach'
	) as $region) {
		$region_id = $wpdb->get_var('SELECT term_id FROM wp_jdbl_terms where name = "' . $region . '"');
		if (!$region_id) die('could not find region with name ' . $region);
		$district82[$region_id] = array();
	}

	//load libraries
	require_once(__DIR__ . '/tcpdf/tcpdf.php');
	require_once(__DIR__ . '/mytcpdf.php');

	//run function to attach meeting data to $regions
	$district80 = attachPdfMeetingData($district80);
	$district81 = attachPdfMeetingData($district81);
	$district82 = attachPdfMeetingData($district82);


	$updated = date('F') . ', ' . date('Y');

	$coverHTML = '
	<style>
	table, td {border:none;}
	h1, h2, h3, h4, p{text-align:center;}
	p {font-size:normal;}
	.types td p{text-align:left;}
	</style>
	<h2 style="margin-bottom:0px;">GRAND STRAND INTERGROUP AA MEETINGS</h2>
	<h4 style="margin-bottom:0px;">DISTRICTS 80, 81 & 82<br />' . $updated . '</h4>
	<h3>24 - HOUR HOTLINE: 843-445-7119</h3>
	<h3><font size="10">OTHER AREAS</font></h3>
	<table class="hotline" style="width:100%;">
	<tr>
	<td style="width:50%;">
	<div style="width:100%;text-align:center;"><font size="8">COLUMBIA, SC 803-254-5301<br>CHARLESTON,SC 843-723-9633<br>HILTON HEAD, SC 843-322-5903</font>
	</div></td>
	<td style="width:50%;">
	<div style="width:100%;text-align:center;"><font size="8">GREENVILLE, SC: 864-233-6454<br>WILMINGTON, NC: 910-794-1840<br>CHARLOTTE, NC 704-332-4387</font>
	</div></td>
	</tr>
	</table>
	<h3><font size="10">Meeting Types</font></h3>
	<table class="types" style="width:100%;">
	<tr>
	<td style="width:50%;text-align:left;padding-left:5px;">
	<div style="padding-left:20px;">
	<font size="8">
	♿= WHEELCHAIR ACCESIBLE<br>
	ABSI=AS BILL SEES IT STUDY<br>
	B=BEGINNERS<br>
	BB=BIG BOOK<br>
	C=CLOSED<br>
	CL=CANDLELIGHT<br>
	CTB=CAME TO BELIEVE<br>
	D=DISCUSSION<br>
	GR=GRAPEVINE
	</font>
	</div>
	</td>
	<td style="width:50%;font-size:medium;">
	<font size="8">
	LS=LIVING SOBER STUDY<br>
	L=LITERATURE<br>
	M=MENS<br>
	MDTN=MEDITATION<br>
	O=OPEN<br>
	S=SPEAKER MEETING<br>
	SS=STEP STUDY<br>
	TRAD=TRADITIONS<br>
	W=WOMENS
	</font>
	</td>
	</tr>
	</table>
	<h4 style="margin-bottom:0px;">OPEN MEETINGS ARE OPEN TO ANYONE TO ATTEND</h4>
	<h4>CLOSED MEETINGS ARE FOR THOSE WITH A DESIRE TO STOP DRINKING</h4>
	<p><font size="12">A.A. PREAMBLE</font><br/>
	<font size="10">Alcoholics Anonymous is a fellowship of men and women who share their experience, strength and hope with each other that they may solve their common problem and help others to recover from alcoholism. The only requirement for membership is a desire to stop drinking. There are no dues or fees for AA membership; we are self-supporting through our own contributions. AA is not allied with any sect, denomination, politics, organization or institution; does not wish to engage in any controversy, neither endorses nor opposes any causes. Our primary purpose is to stay sober and help other alcoholics to achieve sobriety.</font></p>
	<p><font size="10">GRAND STRAND INTERGROUP OF ALCOHOLICS ANONYMOUS<br />grandstrandintergroup@gmail.com<br />www.aamyrtlebeach.org</font></p>
	<p><font size="8">THIS LIST OF AA MEETINGS IS NOT TO BE USED FOR MAILINGS UNDER ANY CIRCUMSTANCES</font></p>';

	//create new PDF
	$pdf = new MyTCPDF();
	$pdf->NewPage();
	$pdf->writeHTML($coverHTML, true, false, true, false, '');
	$pdf->header = 'District 80';
	$pdf->NewPage();
	foreach ($district80 as $region) {
		
		
		if (!empty($region['sub_regions'])) {

			//make page jump for city borough zone maps
			//if (!in_array($region['name'], array('Manhattan', 'Westchester County'))) $pdf->addPage();
			
			//array_shift($region['sub_regions']);
			foreach ($region['sub_regions'] as $sub_region => $rows) {
				
				//create a new page if there's not enough space
				if (($inner_page_height - $pdf->GetY()) < $page_threshold) {
					$pdf->NewPage();
				}
				
				//draw rows
				$pdf->drawTable($sub_region, $rows, $region['name']);
				
				//draw a gap between tables if there's space
				if (($inner_page_height - $pdf->GetY()) > $table_gap) {
					$pdf->Ln($table_gap);
				}
				
				//break; //for debugging
			}
		} elseif ($region['rows']) {
			$pdf->drawTable($region['name'], $region['rows'], $region['name']);
		}

		//break; //for debugging
	}
	
	$pdf->header = 'District 81';
	$pdf->NewPage();
	foreach ($district81 as $region) {
		
		
		if (!empty($region['sub_regions'])) {

			//make page jump for city borough zone maps
			//if (!in_array($region['name'], array('Manhattan', 'Westchester County'))) $pdf->addPage();
			
			//array_shift($region['sub_regions']);
			foreach ($region['sub_regions'] as $sub_region => $rows) {
				
				//create a new page if there's not enough space
				if (($inner_page_height - $pdf->GetY()) < $page_threshold) {
					$pdf->NewPage();
				}
				
				//draw rows
				$pdf->drawTable($sub_region, $rows, $region['name']);
				
				//draw a gap between tables if there's space
				if (($inner_page_height - $pdf->GetY()) > $table_gap) {
					$pdf->Ln($table_gap);
				}
				
				//break; //for debugging
			}
		} elseif ($region['rows']) {
			$pdf->drawTable($region['name'], $region['rows'], $region['name']);
		}

		//break; //for debugging
	}
	
	$pdf->header = 'District 82';
	$pdf->NewPage();
	foreach ($district82 as $region) {
		
		
		if (!empty($region['sub_regions'])) {

			//make page jump for city borough zone maps
			//if (!in_array($region['name'], array('Manhattan', 'Westchester County'))) $pdf->addPage();
			
			//array_shift($region['sub_regions']);
			foreach ($region['sub_regions'] as $sub_region => $rows) {
				
				//create a new page if there's not enough space
				if (($inner_page_height - $pdf->GetY()) < $page_threshold) {
					$pdf->NewPage();
				}
				
				//draw rows
				$pdf->drawTable($sub_region, $rows, $region['name']);
				
				//draw a gap between tables if there's space
				if (($inner_page_height - $pdf->GetY()) > $table_gap) {
					$pdf->Ln($table_gap);
				}
				
				//break; //for debugging
			}
		} elseif ($region['rows']) {
			$pdf->drawTable($region['name'], $region['rows'], $region['name']);
		}

		//break; //for debugging
	}

/* 	//index
	ksort($index);
	$pdf->header = 'Index';
	$pdf->NewPage();
	$pdf->SetEqualColumns(3, $index_width);
	$pdf->SetCellPaddings(0, 1, 0, 1);
	$index_output = '';
	foreach ($index as $category => $rows) {
		ksort($rows);
		$pdf->SetFont($font_index_header[0], $font_index_header[1], $font_index_header[2]);
		$pdf->Cell(0, 0, $category, array('B'=>array('width' => .25)), 1);
		$pdf->SetFont($font_index_rows[0], $font_index_rows[1], $font_index_rows[2]);
		foreach ($rows as $group => $page) {
			if ($pos = strpos($group, ' #')) $group = substr($group, 0, $pos);
			if (strlen($group) > 33) $group = substr($group, 0, 32) . '…';
			$pdf->Cell($index_width * .88, 0, $group, array('B'=>array('width' => .1)), 0);
			$pdf->Cell($index_width * .12, 0, $page, array('B'=>array('width' => .1)), 1, 'R');
		}
		$pdf->Ln(4);
	}

	//zips are a little different, because page numbers is an array
	$pdf->SetFont($font_index_header[0], $font_index_header[1], $font_index_header[2]);
	$pdf->Cell(0, 0, 'ZIP Codes', array('B'=>array('width' => .25)), 1);
	$pdf->SetFont($font_index_rows[0], $font_index_rows[1], $font_index_rows[2]);
	ksort($zip_codes);
	foreach ($zip_codes as $zip => $pages) {
		$pages = array_unique($pages);
		$pdf->Cell($index_width * .35, 0, $zip, array('B'=>array('width' => .1)), 0);
		$pdf->Cell($index_width * .65, 0, implode(', ', $pages), array('B'=>array('width' => .1)), 1, 'R');
	} */

	$pdf->Output($_GET['size'] . '.pdf', 'I');

	exit;
});
