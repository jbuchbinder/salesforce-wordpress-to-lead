<?php
/*
Plugin Name: WordPress-to-Lead for Salesforce CRM
Plugin URI: http://www.salesforce.com/form/signup/wordpress-to-lead.jsp?d=70130000000F4Mw
Description: Easily embed a contactform into your posts, pages or your sidebar, and capture the entries straight into Salesforce CRM!
Author: Joost de Valk - OrangeValley
Version: 2.0
Author URI: http://www.orangevalley.nl/
*/

if ( ! class_exists( 'Salesforce_Admin' ) ) {

	require_once('ov_plugin_tools.php');
	
	class Salesforce_Admin extends OV_Plugin_Admin {

		var $hook 		= 'salesforce-wordpress-to-lead';
		var $filename	= 'salesforce/salesforce.php';
		var $longname	= 'WordPress-to-Lead for Salesforce CRM Configuration';
		var $shortname	= 'Salesforce.com';
		var $optionname = 'salesforce';
		var $homepage	= 'http://www.salesforce.com/wordpress/';
		var $ozhicon	= 'salesforce-16x16.png';
		
		function Salesforce_Admin() {
			add_action( 'admin_menu', array(&$this, 'register_settings_page') );
			add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );
			add_filter( 'ozh_adminmenu_icon', array(&$this, 'add_ozh_adminmenu_icon' ) );				
			
			add_action('admin_print_scripts', array(&$this,'config_page_scripts'));
			add_action('admin_print_styles', array(&$this,'config_page_styles'));	
			add_action('admin_footer', array(&$this,'warning'));
		}
				
		function warning() {
			$options  = get_option($this->optionname);
			if (!isset($options['org_id']) || empty($options['org_id']))
				echo "<div id='message' class='error'><p><strong>Your WordPress-to-Lead  settings are not complete.</strong> You must <a href='".$this->plugin_options_url()."'>enter your Salesforce.com Organisation ID</a> for it to work.</p></div>";
			
		}
		
		function config_page() {
			if ( isset($_POST['submit']) ) {
				$options  = get_option($this->optionname);
				if (!current_user_can('manage_options')) die(__('You cannot edit the WordPress-to-Lead options.', 'salesforce'));
				check_admin_referer('salesforce-udpatesettings');
				
				foreach (array('usecss') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = true;
					} else {
						$options[$option_name] = false;
					}
				}

				$newinputs = array();
				foreach ($options['inputs'] as $id => $input) {
					foreach (array('show','required') as $option_name) {
						if (isset($_POST[$id.'_'.$option_name])) {
							$newinputs[$id][$option_name] = true;
							unset($_POST[$id.'_'.$option_name]);
						} else {
							$newinputs[$id][$option_name] = false;
						}
					}	
					foreach (array('type','label','value','pos') as $option_name) {
						if (isset($_POST[$id.'_'.$option_name])) {
							$newinputs[$id][$option_name] = $_POST[$id.'_'.$option_name];
							unset($_POST[$id.'_'.$option_name]);
						}
					}	
				}
				
				w2l_sksort($newinputs,'pos',true);
				$options['inputs'] = $newinputs;
								
		        foreach (array('successmsg','errormsg','sferrormsg','org_id','source','submitbutton') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = $_POST[$option_name];
					}
				}

				update_option($this->optionname, $options);
			}
			
			$options  = get_option($this->optionname);

			if (!is_array($options['inputs']))
				$options = salesforce_default_settings();
			
			?>
			<div class="wrap">
				<a href="http://salesforce.com/"><div id="yoast-icon" style="background: url(<?php echo plugins_url('',__FILE__); ?>/salesforce-50x50.png) no-repeat;" class="icon32"><br /></div></a>
				<h2 style="line-height: 50px;"><?php echo $this->longname; ?></h2>
				<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<?php if (!isset($_GET['tab']) || $_GET['tab'] == 'home') { ?>
							<form action="" method="post" id="salesforce-conf">
								<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
								<input type="hidden" value="<?php echo $options['version']; ?>" name="version"/>
								<?php 
									$content = $this->textinput('successmsg',__('Success message after sending message', 'salesforce') );
									$content .= $this->textinput('errormsg',__('Error message when not all form fields are filled', 'salesforce') );
									$content .= $this->textinput('sferrormsg',__('Error message when Salesforce.com connection fails', 'salesforce') );
									$this->postbox('basicsettings',__('Basic Settings', 'salesforce'),$content); 
									
									$content = $this->textinput('org_id',__('Your Salesforce.com organisation ID','salesforce'));
									$content .= '<small>'.__('To find your Organisation ID, in your Salesforce.com account, go to Setup &raquo; Company Profile &raquo; Company Information','salesforce').'</small><br/><br/><br/>';
									$content .= $this->textinput('source',__('Lead Source to display in Salesforce.com'));
									$this->postbox('sfsettings',__('Salesforce.com Settings', 'salesforce'),$content); 

									$content = $this->textinput('submitbutton',__('Submit button text', 'salesforce') );
									$content .= $this->textinput('requiredfieldstext',__('Required fields text', 'salesforce') );
									$content .= $this->checkbox('usecss',__('Use Form CSS?', 'salesforce') );
									$content .= '<br/><small><a href="'.$this->plugin_options_url().'&amp;tab=css">'.__('Read how to copy the CSS to your own CSS file').'</a></small>';
									$this->postbox('formsettings',__('Form Settings', 'salesforce'),$content); 
																		
									$content = '<style type="text/css">th{text-align:left;}</style><table>';
									$content .= '<tr>'
									.'<th width="15%">ID</th>'
									.'<th width="10%">Enable</th>'
									.'<th width="10%">Required</th>'
									.'<th width="10%">Type</th>'
									.'<th width="20%">Label</th>'
									.'<th width="20%">Value</th>'
									.'<th width="10%">Position</th>'
									.'</tr>';
									$i = 1;
									foreach ($options['inputs'] as $id => $input) {
										if (empty($input['pos']))
											$input['pos'] = $i;
										$content .= '<tr>';
										$content .= '<th>'.$id.'</th>';
										$content .= '<td><input type="checkbox" name="'.$id.'_show" '.checked($input['show'],true,false).'/></td>';
										$content .= '<td><input type="checkbox" name="'.$id.'_required" '.checked($input['required'],true,false).'/></td>';
										$content .= '<td><select name="'.$id.'_type">';
										$content .= '<option '.selected($input['type'],'text',false).'>text</option>';
										$content .= '<option '.selected($input['type'],'textarea',false).'>textarea</option>';
										$content .= '<option '.selected($input['type'],'hidden',false).'>hidden</option>';
										$content .= '</select></td>';
										$content .= '<td><input size="20" name="'.$id.'_label" type="text" value="'.$input['label'].'"/></td>';
										$content .= '<td><input size="20" name="'.$id.'_value" type="text" value="'.$input['value'].'"/></td>';
										$content .= '<td><input size="2" name="'.$id.'_pos" type="text" value="'.$input['pos'].'"/></td>';
										$content .= '</tr>';
										$i++;
									}
									$content .= '</table>';
									$this->postbox('sffields',__('Form Fields', 'salesforce'),$content); 
								?>
								<div class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e("Save WordPress-to-Lead Settings", 'salesforce'); ?>" /></div>
							</form>
							<?php } else if ($_GET['tab'] == 'css') { ?>
							<p><a href="<?php echo $this->plugin_options_url(); ?>">&laquo; Back to config page.</a></p>
							<p>If you don't want the inline styling this plugins uses, but add the CSS for the form to your own theme's CSS, you can start by just copying the proper CSS below into your CSS file. Just copy the correct text, and then you can usually find &amp; edit your CSS file <a href="<?php echo admin_url('theme-editor.php'); ?>?file=<?php echo str_replace(WP_CONTENT_DIR,'',get_stylesheet_directory()); ?>/style.css&amp;theme=<?php echo urlencode(get_current_theme()); ?>&amp;dir=style">here</a>.</p>
							<div style="width:260px;margin:0 10px 0 0;float:left;">
								<div id="normalcss" class="postbox">
									<div class="handlediv" title="Click to toggle"><br /></div>
									<h3 class="hndle"><span>CSS for the normal form</span></h3>
									<div class="inside">
<pre>form.w2llead {
  text-align: left;
  clear: both;
}
.w2llabel, .w2linput {
  display: block;
  width: 120px;
  float: left;
}
.w2llabel.error {
  color: #f00;
}
.w2llabel {
  clear: left;
  margin: 4px 0;
}
.w2linput.text {
  width: 200px;
  height: 18px;
  margin: 4px 0;
}
.w2linput.textarea {
  clear: both;
  width: 320px;
  height: 75px;
  margin: 10px 0;
}
.w2linput.submit {
  float: none;
  margin: 10px 0 0 0;
  clear: both;
  width: 150px;
}
#salesforce {
  margin: 3px 0 0 0;
  color: #aaa;
}
#salesforce a {
  color: #999;
}</pre>
</div>
</div></div>
<div style="width:260px;float:left;">
	<div id="widgetcss" class="postbox">
		<div class="handlediv" title="Click to toggle"><br /></div>
		<h3 class="hndle"><span>CSS for the sidebar widget form</span></h3>
		<div class="inside">
<pre>.sidebar form.w2llead {
  clear: none;
  text-align: left;
}
.sidebar .w2linput, 
.sidebar .w2llabel {
  float: none;
  display: inline;
}
.sidebar .w2llabel.error {
  color: #f00;
}
.sidebar .w2llabel {
  margin: 4px 0;
}
.sidebar .w2linput.text {
  width: 160px;
  height: 18px;
  margin: 4px 0;
}
.sidebar .w2linput.textarea {
  width: 160px;
  height: 50px;
  margin: 10px 0;
}
.sidebar .w2linput.submit {
  margin: 10px 0 0 0;
}
#salesforce {
  margin: 3px 0 0 0;
  color: #aaa;
}
#salesforce a {
  color: #999;
}</pre>
</div></div></div>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="postbox-container" style="width:20%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<?php
								$this->postbox('usesalesforce',__('How to Use This Plugin','salesforce'),__('<p>To use this form, copy the following shortcode into a post or page:</p><pre style="padding:5px 10px;margin:10px 0;background-color:lightyellow;">[salesforce]</pre><p>Make sure you have entered all the correct settings on the left, including your Organisation ID.</p>','salesforce'));
								$this->plugin_like(false);
								$this->plugin_support();
								// $this->news(); 
							?>
						</div>
						<br/><br/><br/>
					</div>
				</div>
			</div>
			<?php
		}
	} // end class SalesForce_Admin
	$salesforce = new Salesforce_Admin();
}

function salesforce_default_settings() {
	$options = array();
	$options['successmsg'] 			= 'Success!';
	$options['errormsg'] 			= 'There was an error, please fill all required fields.';
	$options['requiredfieldstext'] 	= 'These fields are required.';
	$options['sferrormsg'] 			= 'Failed to connect to Salesforce.com.';
	$options['source'] 				= 'Lead form on '.get_bloginfo('name');
	$options['submitbutton']	 	= 'Submit';

	$options['usecss']				= true;

	$options['inputs'] = array(
		'first_name' 	=> array('type' => 'text', 'label' => 'First name', 'show' => true, 'required' => true),
		'last_name' 	=> array('type' => 'text', 'label' => 'Last name', 'show' => true, 'required' => true),
		'email' 		=> array('type' => 'text', 'label' => 'Email', 'show' => true, 'required' => true),
		'phone' 		=> array('type' => 'text', 'label' => 'Phone', 'show' => true, 'required' => false),
		'description' 	=> array('type' => 'textarea', 'label' => 'Message', 'show' => true, 'required' => true),
		'title' 		=> array('type' => 'text', 'label' => 'Title', 'show' => false, 'required' => false),
		'company' 		=> array('type' => 'text', 'label' => 'Company', 'show' => false, 'required' => false),
		'street' 		=> array('type' => 'text', 'label' => 'Street', 'show' => false, 'required' => false),
		'city'	 		=> array('type' => 'text', 'label' => 'City', 'show' => false, 'required' => false),
		'state'	 		=> array('type' => 'text', 'label' => 'State', 'show' => false, 'required' => false),
		'zip'	 		=> array('type' => 'text', 'label' => 'ZIP', 'show' => false, 'required' => false),
		'country'	 	=> array('type' => 'text', 'label' => 'Country', 'show' => false, 'required' => false),
		'Campaign_ID'	=> array('type' => 'hidden', 'label' => 'Campaign ID', 'show' => false, 'required' => false),
	);
	update_option('salesforce', $options);
	return $options;
}

/**
 * Sort input array by $subkey
 * Taken from: http://php.net/manual/en/function.ksort.php
 */
function w2l_sksort(&$array, $subkey="id", $sort_ascending=false) {
    if (count($array))
        $temp_array[key($array)] = array_shift($array);

    foreach($array as $key => $val){
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }

    if ($sort_ascending) $array = array_reverse($temp_array);

    else $array = $temp_array;
}


function salesforce_form($options, $is_sidebar = false, $content = '') {
	if (!empty($content))
		$content = wpautop('<strong>'.$content.'</strong>');
	if ($options['usecss'] && !$is_sidebar) {
		$content .= '<style type="text/css">
		form.w2llead{text-align:left;clear:both;}
		.w2llabel, .w2linput {display:block;float:left;}
		.w2llabel.error {color:#f00;}
		.w2llabel {clear:left;margin:4px 0;width:50%;}
		.w2linput.text{width:50%;height:18px;margin:4px 0;}
		.w2linput.textarea {clear:both;width:100%;height:75px;margin:10px 0;}
		.w2linput.submit {float:none;margin:10px 0 0 0;clear:both;}
		#salesforce{margin:3px 0 0 0;color:#aaa;}
		#salesforce a{color:#999;}
		</style>';
	} else if ($is_sidebar && $options['usecss']) {
		$content .= '<style type="text/css">
		.sidebar form.w2llead{clear:none;text-align:left;}
		.sidebar .w2linput, #sidebar .w2llabel{float:none; display:inline;}
		.sidebar .w2llabel.error {color:#f00;}
		.sidebar .w2llabel {margin:4px 0;float:none;display:inline;}
		.sidebar .w2linput.text{width:95%;height:18px;margin:4px 0;}
		.sidebar .w2linput.textarea {width:95%;height:50px;margin:10px 0;}
		.sidebar .w2linput.submit {margin:10px 0 0 0;}
		#salesforce{margin:3px 0 0 0;color:#aaa;}
		#salesforce a{color:#999;}
		</style>';
	}
	$sidebar = '';
	if ($is_sidebar)
		$sidebar = ' sidebar';
	$content .= "\n".'<form class="w2llead'.$sidebar.'" method="post">'."\n";
	foreach ($options['inputs'] as $id => $input) {
		if (!$input['show'])
			continue;
		$val 	= '';
		if (isset($_POST[$id])){
			$val	= esc_attr(strip_tags(stripslashes($_POST[$id])));
		}else{
			$val	= esc_attr(strip_tags(stripslashes($input['value'])));
		}

		$error 	= ' ';
		if ($input['error']) 
			$error 	= ' error ';
			
		if($input['type'] != 'hidden')
			$content .= "\t".'<label class="w2llabel'.$error.$input['type'].'" for="sf_'.$id.'">'.esc_html(stripslashes($input['label'])).':';
		
		if ($input['required'])
			$content .= ' *';
		
		if($input['type'] != 'hidden')
			$content .= '</label>'."\n";
		
		if ($input['type'] == 'text') {			
			$content .= "\t".'<input value="'.$val.'" id="sf_'.$id.'" class="w2linput text" name="'.$id.'" type="text"/><br/>'."\n\n";
		} else if ($input['type'] == 'textarea') {
			$content .= "\t".'<br/>'."\n\t".'<textarea id="sf_'.$id.'" class="w2linput textarea" name="'.$id.'">'.$val.'</textarea><br/>'."\n\n";
		} else if ($input['type'] == 'hidden') {
			$content .= "\t\n\t".'<input type="hidden" id="sf_'.$id.'" class="w2linput hidden" name="'.$id.'" value="'.$val.'">'."\n\n";
		}
	}

	//send me a copy
	$content .= "\t".'<input type="checkbox" name="w2lcc" class="w2linput" value="1"/>Send me a copy'."<br/>\n";

	//spam honeypot
	$content .= "\t".'<input type="text" name="w2lshp" class="w2linput" value="" style="display: none;"/>'."\n";

/*
	if (true){
	
		require_once('lib/recaptchalib.php');
		$publickey = "6LfJBskSAAAAADj6sHl2qHp66vYOb5VQDq5Fxrjm";
		$content .= recaptcha_get_html( $publickey, null, is_ssl() );
	
	}
*/

	$submit = stripslashes($options['submitbutton']);
	if (empty($submit))
		$submit = "Submit";
	$content .= "\t".'<input type="submit" name="w2lsubmit" class="w2linput submit" value="'.esc_attr($submit).'"/>'."\n";
	$content .= '</form>'."\n";

	$reqtext = stripslashes($options['requiredfieldstext']);
	if (!empty($reqtext))
		$content .= '<p id="requiredfieldsmsg"><sup>*</sup>'.esc_html($reqtext).'</p>';
	$content .= '<div id="salesforce"><small>Powered by <a href="http://www.salesforce.com/">Salesforce CRM</a></small></div>';
	return $content;
}

function submit_salesforce_form($post, $options) {
	global $wp_version;
	if (!isset($options['org_id']) || empty($options['org_id']))
		return false;

	//spam honeypot
	if( !empty($_POST['w2lshp']) )
		return false;

	//print_r($_POST); //DEBUG



	$post['oid'] 			= $options['org_id'];
	$post['lead_source']	= $options['source'];
	$post['debug']			= 0;

	// Set SSL verify to false because of server issues.
	$args = array( 	
		'body' 		=> $post,
		'headers' 	=> array(
			'user-agent' => 'WordPress-to-Lead for Salesforce plugin - WordPress/'.$wp_version.'; '.get_bloginfo('url'),
		),
		'sslverify'	=> false,  
	);
	
	$result = wp_remote_post('https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8', $args);

	if( is_wp_error($result) )
		return false;
	
	if ($result['response']['code'] == 200){

		if( $_POST['w2lcc'] == 1 )
			salesforce_cc_user($post, $options);
		
		return true;
	}else{
		return false;
	}
}

function salesforce_cc_user($post, $options){
	
	$headers = 'From: '.get_bloginfo('name').' <' . get_option('admin_email') . ">\r\n";

	//remove hidden fields
	foreach ($options['inputs'] as $id => $input) {
		if( $input['type'] == 'hidden' )
			unset( $post[$id] );
	}
	
	unset($post['oid']);
	unset($post['lead_source']);
	unset($post['debug']);
	
	//format message
	foreach($post as $name=>$value){
		if( !empty($value) )
			$message .= $options['inputs'][$name]['label'].': '.$value."\r\n";
	}

	wp_mail( $_POST['email'], 'Your submission', $message, $headers );

}

function salesforce_form_shortcode($is_sidebar = false) {
	$options = get_option("salesforce");
	if (!is_array($options))
		salesforce_default_settings();

	if (isset($_POST['w2lsubmit'])) {
		$error = false;
		$post = array();
		foreach ($options['inputs'] as $id => $input) {
			if ($input['required'] && empty($_POST[$id])) {
				$options['inputs'][$id]['error'] = true;
				$error = true;
			} else if ($id == 'email' && $input['required'] && !is_email($_POST[$id]) ) {
				$error = true;
				$emailerror = true;
			} else {
				$post[$id] = trim(strip_tags(stripslashes($_POST[$id])));
			}
		}
		if (!$error) {
			$result = submit_salesforce_form($post, $options);
			if (!$result)
				$content = '<strong>'.esc_html(stripslashes($options['sferrormsg'])).'</strong>';			
			else
				$content = '<strong>'.esc_html(stripslashes($options['successmsg'])).'</strong>';
		} else {
			$errormsg = esc_html( stripslashes($options['errormsg']) ) ;
			if ($emailerror)
				$errormsg .= '<br/>The email address you entered is not a valid email address.';
			$content = salesforce_form($options, $is_sidebar, $errormsg);
		}
	} else {
		$content = salesforce_form($options, $is_sidebar);
	}
	return $content;
}
add_shortcode('salesforce', 'salesforce_form_shortcode');	

class Salesforce_WordPress_to_Lead_Widgets extends WP_Widget {

	function Salesforce_WordPress_to_Lead_Widgets() {
		$widget_ops = array( 'classname' => 'salesforce', 'description' => 'Displays a WordPress-to-Lead for Salesforce Form' );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'salesforce' );
		$this->WP_Widget( 'salesforce', 'Salesforce', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['title'] );
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		if ( !empty($instance['desc']) && empty($_POST) ) {
			echo '<p>' . $instance['desc'] . '</p>';
		}
		$is_sidebar = true;
		echo salesforce_form_shortcode(true);
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( array('title', 'desc') as $val ) {
			$instance[$val] = strip_tags( $new_instance[$val] );
		}
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'title' => 'Contact Us', 
			'desc' 	=> 'Contact us using the form below', 
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Title"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'desc' ); ?>"><?php _e("Introduction"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" value="<?php echo $instance['desc']; ?>" style="width:90%;" />
		</p>
	<?php 
	}
}

function salesforce_widget_func() {
	register_widget( 'Salesforce_WordPress_to_Lead_Widgets' );
}
add_action( 'widgets_init', 'salesforce_widget_func' );

?>