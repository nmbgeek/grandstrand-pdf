<?php

add_shortcode('pdf-form', function(){
	
	//security
/* 	if (!is_user_logged_in()) {
		return 'please log in';
	} elseif (!current_user_can('edit_posts')) {
		return 'you do not have access to view this page';
	} */
	
	return '
	<style>
	.form-group label.control-label{font-size:1.3em;font-weight:600;}
	.form-group label input{margin-left:15px;font-size:1.2em;font-weight:400;}
	input[type="radio"] {margin-right: 5px;}
	.form-group:not(:first-child){margin-top:10px;}
	</style>
	<script>
	function enableFields(){
		document.getElementById("d80").disabled = false;
		document.getElementById("d81").disabled = false;
		document.getElementById("d82").disabled = false;
		document.getElementById("printinstructions").style.display = "none";
	}
	function disableFields(){
		document.getElementById("d80").disabled = true;
		document.getElementById("d81").disabled = true;
		document.getElementById("d82").disabled = true;
		document.getElementById("all").checked = true;
		document.getElementById("printinstructions").style.display = "block";
	}
	</script>
	
	<h2>Select Options To Generate GSIG PDF</h2>
	<form method="get" class="form-horizontal" action="' . admin_url('admin-ajax.php') . '">
		<input type="hidden" name="action" value="pdf">
		<input type="hidden" name="start" id="start" value="1">
		<div class="form-group">
			<label for="index" class="col-sm-3 control-label">Paper Size</label>
			<div class="col-sm-9">
				<div class="radio">
					<label><input type="radio" name="size" id="letter" onClick="enableFields()" value="letter">US Letter (8.5&times;11")</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="size" id="book" value="book" onClick="disableFields()" checked>Meeting Book (4.25&times;8.5") </label>
				</div>
			</div>
		</div>
		<div id="printinstructions" class="form-group">
			<span class="adobeins"><i><b>Note:</b> For best Meeting Book results you must have a printer capable of double-sided printing and use Adobe Reader. In print settings from Adobe Reader select "Booklet".  This will automatically order the pages and provide best results.  You can <a href="https://get.adobe.com/reader" target="_blank">download Adobe Reader here</a>.</i>
		</div>
		<div class="form-group">
			<label for="districts" class="col-sm-3 control-label">Select Districts</label>
			<div class="col-sm-9">
				<div class="radio">
					<label><input type="radio" name="districts" id="all" value="all" checked>All</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="districts" id="d80" value="district80" disabled>District 80</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="districts" id="d81" value="district81" disabled>District 81</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="districts" id="d82" value="district82" disabled>District 82</label>
				</div>
			</div>
		</div>
		<div class="form-group" style="margin-top:20px;">
			<div class="col-sm-9 col-sm-offset-3">
				<button class="btn btn-primary" type="submit">Generate PDF</button>
			</div>
		</div>
	</form>';
});