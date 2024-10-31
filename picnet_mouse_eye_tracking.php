<?php
/*
Plugin Name: PicNet Mouse Eye Tracking
Plugin URI: http://met.picnet.com.au/
Description: The <a href='http://met.picnet.com.au/' target='_blank'>PicNet Mouse Eye Tracking</a> Plugin gives you heat maps, click maps and page departure diagrams of the visitors on your site.  After activation please go to Settings -> PicNet Mouse Eye Tracking to configure the service.  To view visitor heat maps go to the <a href='http://met.picnet.com.au/' target='_blank'>Mouse Eye Tracking home page</a>.  Note: A free Mouse Eye Tracking account displays a small message in the footer of your page.
Version: 5.1
Date: Sep 04, 2012
Author: PicNet Pty Ltd
Author URI: http://www.picnet.com.au
Contributors:  PicNet Pty Ltd
*/ 

/*	(C)Copyright, 2010  PicNet Pty Ltd  (contact: http://www.picnet.com.au/)
	
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

// Admin Panel
function met_add_pages() {
	add_options_page('PicNet Mouse Eye Tracking Options', 'PicNet Mouse Eye Tracking', 9, __FILE__, 'met_options_page');
}

function met_show_info_msg($msg) {
	echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
}

function met_options_page() {
	if (isset($_POST['info_update'])) {
		$options = array(
			"met_user_code" => $_POST["met_user_code"],
			"met_is_paid" => $_POST["met_is_paid"],
			"met_is_active" => true,
			"met_user_email"=> $_POST["met_user_email"],
		);
		update_option("mouse_eye_tracking_opts", $options);
	} elseif ($_POST["info_reset_full"] == true) {
		delete_option("mouse_eye_tracking_opts");
		met_show_info_msg("Mouse Eye Tracking deleted.  Your site is no longer recording visitor pageviews.");
	} elseif (isset($_POST["info_reset"]) || isset($_POST["info_activate"])) {
		$oldDetails = get_option("mouse_eye_tracking_opts");
		$active = isset($_POST["info_reset"]) ? false : true;
		$options = array(
			"met_user_code" => $oldDetails["met_user_code"],
			"met_is_paid" => $oldDetails["met_is_paid"],
			"met_is_active" => $active,
			"met_user_email"=> $oldDetails["met_user_email"],
		);
		update_option("mouse_eye_tracking_opts", $options);

		if(isset($_POST["info_reset"])) met_show_info_msg("Mouse Eye Tracking deactivated.  Your site is no longer recording visitor pageviews.");
		else met_show_info_msg("Mouse Eye Tracking activated.  Your site is now recording visitor pageviews.");
	} else {
		$options = get_option("mouse_eye_tracking_opts");
	}
		
	// Build the html
	$html = '
	<div>
		<a style="text-decoration:none; color:#333" href="http://met.picnet.com.au/" target="_blank">
			<img src="http://www.picnet.com.au/images/MET_logo.jpg" alt="PicNet Mouse Eye Tracking" title="PicNet Mouse Eye Tracking" style="margin:13px 13px 0px 0px; display:inline; position:relative; float:left">
			<h2 style="display:inline; position:relative; float:left; font-weight:normal; font-size:24px; text-shadow: 0px 1px 1px #ffffff;">PicNet Mouse Eye Tracking</h2>
		</a>
		<div style="clear:both"></div>
		<p>
			Welcome to the Mouse Eye Tracking plugin for Wordpress. If you have any questions, suggestion or issues when using this plugin 
			please feel free to <a target="_blank" href="http://www.picnet.com.au" target="_blank">contact us</a>.
		</p>				
		<p>
		To begin, register using the form below. To view your visitor heat maps, click maps and page departure diagrams go to the <a href="http://met.picnet.com.au/" target="_blank">Mouse Eye Tracking service home page</a>.
		</p>
		<p><b>A valid email must be provided in order to view the your data</b></p>
		<p>
			<strong>Note:</strong> If you have a custom footer.php file in your chosen theme please ensure that the following code exists somewhere in your footer.php file (or add it if it does not).
			<pre>
				&lt;?php wp_footer(); ?&gt;
			</pre>
		</p>
	</div>';
	if (!get_option("mouse_eye_tracking_opts")) {
	$html = $html .'

		<h2 style="font-size:16px">Registration Details</h2>
		<!-- REGO FORM -->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
		<script type="text/javascript">	
		var registerTimeout =  null;
		
		function register() {
			if ($("#met_submit").attr("disabled")) { return; }
			$("#met_submit").attr("disabled", "disabled");

			$("#registermessage").html("");	    
			
			if ($("#cbTAndC").filter(":checked").length === 0) {
				$("#registermessage").html("<ul class=\"registererror\"><li>Please read and accept the Terms and Conditions before registering</li></ul>");
				$("#met_submit").removeAttr("disabled");
				return;
			}

			var url = "http://met.picnet.com.au/NewUser.mvc/RegisterJSONP?";
			$("#registerinputs").find("input").each(function() {
				var i = $(this);
				url = url + i.attr("id") + "=" + i.val() + "&";
			});
			url = url.substring(0, url.length - 1);
			registerTimeout = setTimeout(checkJSONPTimeout, 5000, []);
			makeJSONPCall(url, "registerCallback");
		}

		function checkJSONPTimeout() {
			if (!$("#met_submit").attr("disabled") || !registerTimeout) { return; }
			$("#registermessage").html("<ul class=\"registererror\"><li>Could not communicate with the Mouse Eye Tracking service.  Please try again shortly.</li></ul>");
			$("#met_submit").removeAttr("disabled")
		}

		function registerCallback(data) {
			clearTimeout(registerTimeout);
			registerTimeout = null;
			$("#met_submit").removeAttr("disabled");

			if (data.msg === "success") {
				registrationSuccess(data.acccode);
			} else {
				$("#registermessage").html(data.msg);	  
			}	
		}
				
		function registrationSuccess(acccode) {
			if ($("#met_user_code").length === 0) {
				console.log("met_user_code: " + acccode);
			} else {
				$("#met_user_code").val(acccode);			
				$("#met_user_email").val($("#rEmail").val());			
				document.met_rego_form.submit();						
			}
		}

		function makeJSONPCall(url, callbackname)
		{             
			url += "&cb=" + callbackname + "&" + new Date().getTime();
			var script = createJSONPScriptElement(url);
			appendScriptToDocument(script);        
		}
			
		function createJSONPScriptElement(url) {
			var script = document.createElement("script");            
			script.setAttribute("id", "JSONP");
			script.setAttribute("src", url);
			script.setAttribute("type", "text/javascript");                        
			return script;
		}
			
		function appendScriptToDocument(script) {
			// This is quite slow, this is a big issue especially on unloading 
			// as the browser may not have time to add this. (1-3ms per JSONP call)
			var target = document.head || document.documentElement;
			target.insertBefore(script, target.firstChild);
		}
		
		$(document).ready(function(){
			$("#rDomain").val(location.hostname);
		});
		
		</script>

		<style type="text/css">
			#registerlabels, 
			#registerinputs {
				list-style:none;
				padding: 0px;
				margin: 0px;
				float:left;
				display:inline;
				position:relative
				}

			#registerlabels {
				margin-right:20px
				}

			#registerlabels li{
				padding-bottom:6px
				}
				
			#registerinputs input {
				width:200px
				}

			.fineprint {
				font-size:10px
				}
			
			.clear {
				clear:both
				}
			
			.registermessage {
			}
			
			.registererror ul, .message {
				margin:0px;
				padding:0px;
				text-indent:0px
			}
			
			.registererror li {
				list-style:none;
				color:red;
				margin:0px;
				padding:0px;
				font-size:10px;
				text-indent:0px
			}
			
		</style>

		<ul id="registerlabels">
			<li><label for="rName"><strong>Name:</strong></label></li>
			<li><label for="rEmail"><strong>Email:</strong></label></li>       			
			<li><label for="rPassword" ><strong>Password:</strong></label></li>
			<li>
				<label for="rDomain" >
					<strong>Domain:&dagger;</strong>
				</label>				
			</li>
			<li><label for="rCompany">Company:</label></li>
			<li><label for="rTelephone">Telephone:</label></li>
			<li><label for="rCountry">Country:</label></li>
		</ul>
		<ul id="registerinputs">
			<li><input id="rName" type="text" title="Name" value="" /></li>
			<li><input id="rEmail" type="text" title="Email" value=""/></li>				
			<li><input id="rPassword" type="password" title="Password" value="" /></li>
			<li><input id="rDomain" type="text" title="e.g. picnet.com.au (This is the domain to record, Note: www. is optional)" /></li>
			<li><input id="rCompany" type="text" title="Company" value="" /></li>
			<li><input id="rTelephone" type="text" title="Telephone" value="" /></li>
			<li>
				<input id="rCountry" type="text" title="Country" value="" />
				<input id="rReseller" type="hidden" value="WordPressPlugin5.0" />
			</li>
		</ul>

		<div class="clear"></div>
		<p class="fineprint">
		&dagger; localhost is always included for free. You do not need to enter http://www when typing in your domain. Example: picnet.com.au
		</p>
		
		<p class="fineprint">
			* Required fields in bold <br />
			I have read and accept the <a href="http://met.picnet.com.au/NewUser.mvc/TermsAndConditions" target="new">Terms and Conditions</a>.
			<input type="checkbox" id="cbTAndC"/>
		</p>       
		<p>	
			<span class="message" id="registermessage" ></span>
		</p>	          
		<p><input id="met_submit" type="submit" name="submit" value="Register" tabindex="9" class="btn" onclick="register(); return false;"/></p>    
		<!-- REGO FORM END -->
		';
	}
	$html = $html .'
		<form name="met_rego_form" method="post" action="">
			<input type="hidden" name="met_user_code" id="met_user_code" value="' .$options["met_user_code"] .' "></input>
			<input type="hidden" name="met_user_email" id="met_user_email" value="' .$options["met_user_email"] .' "></input>
			<input type="hidden" name="met_is_paid" id="met_is_paid" value="Y"></input>';
	if (get_option("mouse_eye_tracking_opts")) {
		$html = $html .'
			<p>Please refer to the <a href="http://met.picnet.com.au/">Home Page</a> for all details on this service.</p>
			</form>';
		if($options["met_is_active"]){
			$html = $html .'
			<h3 style="color:red">Mouse Eye Tracking is Active</h3>
			<div><span>Log into your account using: '.(isset($options["met_user_email"]) ? $options["met_user_email"] : "").' at <a href="http://met.picnet.com.au/" target="_blank">Home Page</a></span></div>';
		}
		else{
			$html = $html .'
			<h3 style="color:red">Mouse Eye Tracking is Inactive</h3>';
		}
		if(!$options["met_is_active"]){
		$html = $html .'
			<div>
				<h2>Activate Tracking</h2>
				<form name="formmetreset" method="post" action="">
					<p>By pressing the "Activate" button you will allow Mouse Eye Tracking to track your viewer\'s mouse movements.</p>
					<p class="submit">
						<input type="submit" name="info_activate" value="Activate" />
					</p>
				</form>
			</div>';
		}
		else	{
		$html = $html .'
			<div>
				<h2>Deactivate Tracking</h2>
				<form name="formmetreset" method="post" action="">
					<p>Deactivating your account will stop recording, you will be able to reactivate at any time in the future.</p>
					<p class="submit">
						<input type="submit" name="info_reset" value="Deactivate" />
					</p>
				</form>
			</div>';
		}
		$html = $html .'
			<div>
				<h2>Delete Account</h2>
				<form name="formmetreset" method="post" action="">
					<p>Deleting your account will stop recording and you will not be able to reactivate your account in the future.</p>
					<p class="submit">
						<input type="submit" name="info_reset_full" value="Delete" />
					</p>
				</form>
				<p style="font-size:10px; padding-top:5px; border-top:1px dotted #eeeeee; color:#999">Thanks for using Mouse Eye Tracking. <a style="color:#999" href="http://www.picnet.com.au" target="_blank">PicNet</a> is 
					a leading provider of <a style="color:#999" href="http://www.picnet.com.au/IT_consulting.html" target="_blank">IT consulting</a>, 
					<a style="color:#999" href="http://www.picnet.com.au/software_development.html" target="_blank">custom software development</a> and 
					<a style="color:#999" href="http://www.picnet.com.au/IT_managed_support.html" target="_blank">managed IT services.</a></p>
			</div>';
	} 
	else {
		$html = $html .'
			<input type="hidden" name="info_update" value="Update Options &raquo;" />
		</form>
';
	}
	
	_e($html);	
}

function get_met_code($met_user_code, $met_is_paid) {		
	$text = '
<span id="PicNetEyeTracker">';
	if (!$met_is_paid) { $text .= 'Mouse Eye Tracking by PicNet <a href="http://www.picnet.com.au/it_managed_support.html" title="IT support">IT support</a> services'; }
$text .='</span>
<script type="text/javascript">
	var host = ("https:" == document.location.protocol ? "https://ssl." : "http://met."); 
	document.write(unescape("%3Cscript src=\'" + host + "picnet.com.au/resources/scripts/met.client.min.js?usercode='.trim($met_user_code).'\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>';	
	return $text;
}

function add_met_code_to_footer() {		
	$options = get_option("mouse_eye_tracking_opts");
	$met_user_code = $options["met_user_code"];
	$met_is_paid = $options["met_is_paid"];
			
	$met_show_all = $options["met_show_all"];
	if ($options["met_is_active"] == false){ return $text; }	
	echo get_met_code($met_user_code, $met_is_paid);			
}

add_action('admin_menu', 'met_add_pages');
add_action('wp_footer', 'add_met_code_to_footer',0);
?>
