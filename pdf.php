<?php

add_action('wp_ajax_nopriv_pdf', 'tsml_pdf_gen');
add_action('wp_ajax_pdf', 'tsml_pdf_gen');

function tsml_pdf_gen(){
	global $wpdb, $margins, $font_table_rows, $page_width, $page_height, $table_padding, $font_header, $header_top, $font_footer, $footer_bottom, $first_column_width, $table_border_width, $font_table_rows, $table_padding, $font_table_header, $first_column_width, $day_column_width, $table_border_width, $font_table_rows, $table_padding, $first_column_width, $day_column_width, $table_border_width, $inner_page_height, $font_table_rows, $index, $exclude_from_indexes, $zip_codes, $table_padding, $line_height_ratio, $fontsz_group_name, $updated_short, $medium;

	//must be a logged-in user to run this page (otherwise last_contact will be null)
	if (!isset($_GET['start']) || !isset($_GET['size']) || !isset($_GET['districts'])) {
		die('variables missing');
	}

	ini_set('max_execution_time', 60);

	//output PDF of NYC meeting list using the TCPDF library

	//don't show these in indexes
	$exclude_from_indexes	= array('Beginner', 'Candlelight', 'Closed', 'Grapevine', 'Literature', 'Open', 'Topic Discussion');

	//config dimensions, in inches
	$table_border_width		= .1;
	
	date_default_timezone_set('America/New_York');
	$updated = date('F') . ', ' . date('Y');
	$updated_short = date("m/d/Y");
	
	//convert dimensions to mm
	$inch_converter			= 25.4; //25.4mm to an inch

	if ($_GET['size'] == 'letter') {
		$table_padding		= 1.5; //in mm
		$header_top			= 9;
		$footer_bottom 		= -13;
		$font_header		= array('helvetica', 'b', 18);
		$font_footer		= array('helvetica', 'r', 8);
		$font_table_header	= array('helvetica', 'b', 9);
		$font_table_rows	= array('helvetica', 'r', 8);
		$font_index_header	= array('helvetica', 'b', 8);
		$font_index_rows	= array('helvetica', 'r', 8);
		$fontsz_group_name	= "8px";
		$big				= "16";
		$medium				= "12";
		$medium_2			= "10";
		$loc_td_width		= "350";
		$margins = array(
			'left'			=> .75,
			'right'			=> .75,
			'top'			=> .95, //include header
			'bottom'		=> .45, //include footer
		);
		$page_width			= 8.5 * $inch_converter;
		$page_height		= 11 * $inch_converter;
		$line_height_ratio	= 3.7;
		$index_width		= 57; // in mm
		$table_gap			= .25 * $inch_converter; //gap between tables
	} elseif ($_GET['size'] == 'book') {
		$table_padding		= 1; //in mm
		$header_top			= 3;
		$footer_bottom 		= -10;
		$font_header		= array('helvetica', 'b', 12);
		$font_footer		= array('helvetica', 'r', 6);
		$font_table_header	= array('helvetica', 'b', 6);
		$font_table_rows	= array('helvetica', 'r', 5.8); //for the unicode character
		$font_index_header	= array('helvetica', 'b', 5.8);
		$font_index_rows	= array('helvetica', 'r', 6);
		$fontsz_group_name	= "6px";
		$big				= "14";
		$medium				= "10";
		$medium_2			= "8.3";
		$loc_td_width		= "255";
		$margins = array(
			'left'				=> .3,
			'right'				=> .3,
			'top'				=> .45, //include header
			'bottom'			=> .25, //include footer
		);
		$page_width			= 6.5 * $inch_converter;
		$page_height			= 9.5 * $inch_converter;
		$line_height_ratio	= 2.58;
		$index_width			= 47; // in mm
		$table_gap			= .2 * $inch_converter; //gap between tables
	}

	foreach ($margins as $key => $value) $margins[$key] *= $inch_converter;
	$inner_page_width		= $page_width - $margins['left'] - $margins['right'];
	$inner_page_height		= $page_height - $margins['top'] - $margins['bottom'];
	$first_column_width		= $inner_page_width * .30;
	$day_column_width		= ($inner_page_width - $first_column_width) / 7;
	$page_threshold			= .1 * $inch_converter; //amount of space to start a new section
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


	$coverHTML = '
	<style>
	table, td {border:none;}
	h1, h2, h3, h4, p{text-align:center;}
	.types td p{text-align:left;}
	</style>
	<h2 style="margin-bottom:0px;"><font size="' . $big . '">GRAND STRAND INTERGROUP AA MEETINGS</font></h2>
	<h4 style="margin-bottom:0px;"><font size="' . $big . '">DISTRICTS 80, 81 & 82</font></h4>
	<h4><font size="' . $medium . '">Updated ' . $updated . '</font></h4>
	<h3>24 - HOUR HOTLINE: 843-445-7119</h3>
	<h3><font size="11">OTHER AREAS</font></h3>
	<table class="hotline" style="width:100%;">
	<tr>
	<td style="width:50%;">
	<div style="width:100%;text-align:center;"><font size="' . $medium . '">COLUMBIA, SC 803-254-5301<br>CHARLESTON,SC 843-723-9633<br>HILTON HEAD, SC 843-322-5903</font></div></td>
	<td style="width:50%;">
	<div style="width:100%;text-align:center;"><font size="' . $medium . '">GREENVILLE, SC: 864-233-6454<br>WILMINGTON, NC: 910-794-1840<br>CHARLOTTE, NC 704-332-4387</font></div></td>
	</tr>
	</table>
	<h3><font size="10">Meeting Types</font></h3>
	<table class="types" style="width:100%;">
	<tr>
	<td style="width:15%;"></td>
	<td style="width:43%;text-align:left;padding-left:5px;">
	<font size="' . $medium . '"><img src="' . $_SERVER['DOCUMENT_ROOT'] . 'wp-content/plugins/grandstrand-pdf/images/handicap.jpg" width="' . $medium . '" height="' . $medium . '"border="0" />=WHEELCHAIR ACCESIBLE<br>
	C=CLOSED<br>
	O=OPEN<br>
	D=DISCUSSION<br>
	S=SPEAKER<br>
	BB=BIG BOOK<br>
	NC=NEWCOMERS<br>
	M=MENS<br>
	W=WOMENS<br>
	SP=SPANISH</font></td>
	<td style="width:42%;"><font size="' . $medium . '">LIT=LITERATURE<br>
	ABSI=AS BILL SEES IT STUDY<br>
	CAN=CANDLELIGHT<br>
	GR=GRAPEVINE<br>
	LS=LIVING SOBER STUDY<br>
	MED=MEDITATION<br>
	SE=SECULAR<br>
	ST=STEP STUDY<br>
	TR=TRADITIONS<br>
	ONL=ONLINE OPTION*</font></td>
	</tr>
	<tr><td colspan="3"><div style="width:100%;text-align:center;"><font size="8">* See website and click Join to reveal password. All meetings are In Person also unless \'ONL ONLY\' specified.</font></div></td></tr>
	</table>
	<h4 style="margin-bottom:0px;">OPEN MEETINGS ARE OPEN TO ANYONE TO ATTEND</h4>
	<h4>CLOSED MEETINGS ARE FOR THOSE WITH A DESIRE TO STOP DRINKING</h4>
	<p><font size="' . $medium . '">A.A. PREAMBLE<br/>Alcoholics Anonymous is a fellowship of men and women who share their experience, strength and hope with each other that they may solve their common problem and help others to recover from alcoholism. The only requirement for membership is a desire to stop drinking. There are no dues or fees for AA membership; we are self-supporting through our own contributions. AA is not allied with any sect, denomination, politics, organization or institution; does not wish to engage in any controversy, neither endorses nor opposes any causes. Our primary purpose is to stay sober and help other alcoholics to achieve sobriety.</font></p>
	<p><font size="' . $medium_2 . '">GRAND STRAND INTERGROUP OF ALCOHOLICS ANONYMOUS<br/>PO Box 2553, Myrtle Beach, SC 29578<br />grandstrandintergroup@gmail.com<br />www.aamyrtlebeach.org</font></p>
	<p><font size="9">THIS LIST OF AA MEETINGS IS NOT TO BE USED FOR MAILINGS UNDER ANY CIRCUMSTANCES</font></p>';
	
	$innerHTML = '
	<style>
	table, td {border:none;}
	h1, h2, h3, h4, p{text-align:center;}
	p {font-size:normal;}
	.types td p{text-align:left;}
	ol li{line-height:1.3}
	</style>
	<table>
	<tr><td width="90" style="text-align:center"><img src="' . $_SERVER['DOCUMENT_ROOT'] . 'wp-content/plugins/grandstrand-pdf/images/aa.jpg" width="90" height="90"></td>
	<td width="' . $loc_td_width . '" style="text-align:center">
	<table><tr>
	<td><font size="' . $medium_2 . '"><span style="font-weight: bold;">District 80</span><br/>Dillon<br/>Florence<br/>Hemingway<br/>Marion</font></td>
	<td><font size="' . $medium_2 . '"><span style="font-weight: bold;">District 81</span><br/>Carolina Forest<br/>Conway<br/>Little River<br/>Longs<br/>Loris<br/>Myrtle Beach<br/>N. Myrtle Beach</font></td>
	<td><font size="' . $medium_2 . '"><span style="font-weight: bold;">District 82</span><br/>Garden City<br/>Georgetown<br/>Murrells Inlet<br/>Pawleys Island<br/>Surfside</font></td>
	</tr></table></td>
	<td  width="70" style="text-align:center"><img src="' . $_SERVER['DOCUMENT_ROOT'] . 'wp-content/plugins/grandstrand-pdf/images/mgicon.jpg" width="70" height="70"><br/><font size="6"><span style="font-weight:bold">Meeting Guide App</span><br/>aamyrtlebeach.org/app</font></td>
	</tr>
	</table>
	<p> </p>
	<h4><font size="' . $medium_2 . '"><span style="font-weight: bold;">The Twelve Steps</span></font></h4>
	<ol><font size="' . $medium_2 . '"><li>We admitted we were powerless over alcohol - that our lives had become unmanageable.</li>
	<li>Came to believe that a Power greater than ourselves could restore us to sanity.</li>
	<li>Made a decision to turn our will and our lives over to the care of God as we understood Him.</li>
	<li>Made a searching and fearless moral inventory of ourselves.</li>
	<li>Admitted to God, to ourselves and to another human being the exact nature of our wrongs.</li>
	<li>Were entirely ready to have God remove all these defects of character.</li>
	<li>Humbly asked Him to remove our shortcomings.</li>
	<li>Made a list of all persons we had harmed, and became willing to make amends to them all.</li>
	<li>Made direct amends to such people wherever possible, except when to do so would injure them or others.</li>
	<li>Continued to take personal inventory and when we were wrong promptly admitted it.</li>
	<li>Sought through prayer and meditation to improve our conscious contact with God as we understood Him, praying only for knowledge of His will for us and the power to carry that out.</li>
	<li>Having had a spiritual awakening as the result of these steps, we tried to carry this message to alcoholics and to practice these principles in all our affairs.</li>
	</font></ol>
	<h4><font size="' . $medium_2 . '"><span style="font-weight: bold;">The Twelve Traditions</span></font></h4>
	<ol><font size="' . $medium_2 . '"><li>Our common welfare should come first; personal recovery depends upon AA unity.</li>
	<li>For our group purpose there is but one ultimate authority - a loving God as He may express Himself in our group conscience.&nbsp; Our leaders are but trusted servants; they do not govern.</li>
	<li>The only requirement for AA membership is a desire to stop drinking.</li>
	<li>Each group should be autonomous except in matters affecting other groups or AA as a whole.</li>
	<li>Each group has but one primary purpose-to carry its message to the alcoholic who still suffers.</li>
	<li>An AA group ought never endorse, finance or lend the AA name to any related facility or outside enterprise, lest problems of&nbsp;money, property and prestige divert us from our primary purpose.</li>
	<li>Every AA group ought to be fully self-supporting, declining outside contributions.</li>
	<li>Alcoholics Anonymous should remain forever nonprofessional, but our service centers may employ special workers.</li>
	<li>AA, as such, ought never be organized; but we may create service boards or committees directly responsible to those they serve</li>
	<li>Alcoholics Anonymous has no opinion on outside issues; hence the AA name ought never be drawn into public controversy.</li>
	<li>Our public relations policy is based on attraction rather than promotion; we need always maintain personal anonymity at the level of&nbsp; press, radio and films.</li>
	<li>Anonymity is the spiritual foundation of all our traditions, ever reminding us to place principles before personalities.</li>
	</font></ol>
	
	';

	//create new PDF
	$pdf = new MyTCPDF();
	$pdf->NewPage();
	$pdf->writeHTML($coverHTML, true, false, true, false, '');
	$pdf->NewPage();
	$pdf->writeHTML($innerHTML, true, false, true, false, '');
	
	
	if ($_GET['districts'] == 'district80' || $_GET['districts'] == 'all') {
		$pdf->header = 'District 80';
		$pdf->NewPage();
		
		//Did this dirty because I couldn't get it to loop through array of districts correctly!
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
	}
	
	if ($_GET['districts'] == 'district81' || $_GET['districts'] == 'all') {
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
	}
	
	if ($_GET['districts'] == 'district82' || $_GET['districts'] == 'all') {
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
	}

	$name = 'GSIG Guide ' . $updated . '.pdf';
	$pdf->Output($name, 'I');

	exit;
};
