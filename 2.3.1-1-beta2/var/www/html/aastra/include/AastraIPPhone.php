<?php 
###################################################################################################
# Aastra XML API - AastraIPPhone.php
# Copyright Aastra Telecom 2005-2010
#
# This file includes common functions to be used with the Aastra XML API.
#
# Public functions
#     Aastra_decode_HTTP_header()
#        This function decodes the HTTP header of an XML GET coming from the phone and returns all the
#        information.
#     Aastra_escape_encode($string)
#        Escape encode a string for XML compliancy
#     Aastra_escape_decode(string)
#        Escape decode a string for XML compliancy
#     Aastra_test_phone_version(version,type,header=NULL)
#        This function tests if the phone version is at least a certain version.
#     Aastra_test_phone_model(models,check,type)
#        This function checks if the current phone is part of the list of supported phones for this 
#        application.
#     Aastra_is_wrap_title_supported(header)
#     Aastra_is_style_textmenu_supported(header)
#     Aastra_is_formattedtextscreen_supported(header)
#     Aastra_is_multipleinputfields_supported(header)
#     Aastra_is_icons_supported(header)
#     Aastra_is_pixmap_graphics_supported(header)
#     Aastra_is_png_graphics_supported(header)
#     Aastra_is_fastreboot_supported(header)
#     Aastra_is_ledcontrol_supported(header)
#     Aastra_is_configuration_supported(header)
#     Aastra_is_lockin_supported(header)
#     Aastra_is_emptyphoneexecute_supported(header)
#     Aastra_is_triggerdestroyonexit_supported(header)
#     Aastra_is_softkeys_supported(header)
#     Aastra_number_softkeys_supported(header)
#     Aastra_is_doneaction_supported(header)
#     Aastra_is_Answer_key_supported(header)
#     Aastra_is_Refresh_supported(header)
#     Aastra_is_textmenu_wrapitem_supported(header)
#     Aastra_is_local_reset_supported(header)
#     Aastra_is_allow_DTMF_supported(header)
#     Aastra_is_play_wav_supported(header)
#     Aastra_is_sip_notify_supported(header)
#     Aastra_is_keypress_supported(header)
#     Aastra_is_dialkey_supported(header)
#     Aastra_is_dial2key_supported(header)
#     Aastra_is_dialuri_supported(header)
#     Aastra_is_timeout_supported(header)
#     Aastra_is_datetime_input_supported(header)
#     Aastra_is_dynamic_sip_supported(header)
#     Aastra_is_formattedtextscreen_color_supported(header)
#     Aastra_is_lockincall_supported(header)
#     Aastra_size_formattedtextscreen()
#         Returns the number of lines available for formatted text screen
#     Aastra_size_display_line()
#         Returns the number of characters per line on the display
#     Aastra_get_custom_icon(icon_name)
#         Returns hex representation of icon matching the given name. If no icon found, empty icon is returned.
#     Aastra_push2phone(server,phone,data)
#         Push an XML object to the phone.
#     Aastra_getvar_safe(var_name,default,method)
#         This function helps protect against injection style attacks by formatting the variable value.
#     Aastra_getphone_fingerprint()
#         This function returns the phone fingerprint which is a md5 hash of its model, MAC address and 
#         IP address.
#     Aastra_phone_type()
# 	   This function returns the type of phone for an XML perspective.
#
# Private functions
#     None
###################################################################################################

###################################################################################################
# Aastra_decode_HTTP_header()
#
# This function decodes the HTTP header of an XML GET coming from the phone and returns all the
# information.
#
# Parameters
#   None
#
# Returns an array
#   'model' 		Phone Type
#   'mac' 		Phone MAC Address
#   'firmware' 	Phone firmware version
#   'ip' 		Phone Remote IP address
#   'language'	Phone Language 
#   'module'[x] 	Expansion Modules
#   'rp'		Boolean for RP set
###################################################################################################
function Aastra_decode_HTTP_header()
{
Global $TEST;

# Debug mode
if($TEST)
	{
 	# Calculate fake mac address suffix based on client's source address 
 	$fake_mac_suffix = strtoupper(substr(md5(Aastra_getvar_safe('REMOTE_ADDR','','SERVER')),0,6));
 	$array=array(	'model'=>'Aastra57i',
   			'mac'=>'00085D'.$fake_mac_suffix,
   			'firmware'=>'2.5.3.26',
			'ip'=>Aastra_getvar_safe('REMOTE_ADDR','','SERVER'),
			'language'=>'en',
			'rp'=>False
			);
	return($array);
	}

# User Agent
$user_agent=Aastra_getvar_safe('HTTP_USER_AGENT','','SERVER');

if(stristr($user_agent,'Aastra'))
	{
	$value=preg_split('/ MAC:/',$user_agent);
	$fin=preg_split('/ /',$value[1]);
	$value[1]=preg_replace('/\-/','',$fin[0]);
	$value[2]=preg_replace('/V:/','',$fin[1]);
	}
else
	{
	$value[0]='MSIE';
	$value[1]='NA';
	$value[2]='NA';
	}

# Modification for RP phones
$rp=False;
if(strstr($value[0],'RP'))
	{
	$rp=True;
	$value[0]=preg_replace(array('/67/','/ RP/'),array('',''),$value[0]);
	}

# Modules
$module[1]=Aastra_getvar_safe('HTTP_X_AASTRA_EXPMOD1','','SERVER');
$module[2]=Aastra_getvar_safe('HTTP_X_AASTRA_EXPMOD2','','SERVER');
$module[3]=Aastra_getvar_safe('HTTP_X_AASTRA_EXPMOD3','','SERVER');

# Create array
$array=array('model'=>$value[0],'mac'=>$value[1],'firmware'=>$value[2],'ip'=>Aastra_getvar_safe('REMOTE_ADDR','','SERVER'),'module'=>$module,'language'=>Aastra_getvar_safe('HTTP_ACCEPT_LANGUAGE','','SERVER'),'rp'=>$rp);
return($array);
}

###################################################################################################
# Aastra_escape_encode($string)
#
# Escape encode a string for XML compliancy
#
# Parameters
#   @string 		string to encode
# 
# Returns
#   Encoded string
###################################################################################################
function Aastra_escape_encode($string)
{
return(str_replace(
		array('<', '>', '&'),
		array('&lt;', '&gt;', '&amp;'),
		$string
		));
}

###################################################################################################
# Aastra_escape_decode(string)
#
# Escape decode a string for XML compliancy
# 
# Parameters
#   @string 	string to decode
#
# Returns
#   Decoded string
###################################################################################################
function Aastra_escape_decode($string)
{
return(str_replace(
		array('&lt;', '&gt;', '&amp;'),
		array('<', '>', '&'),
		$string
		));
}

###################################################################################################
# Aastra_test_phone_version(version,type,header=NULL)
#
# This function tests if the phone version is at least a certain version.
#
# Parameters
#   @version 		minimum phone version '1.3.1.'
#   @type 		if 0 then function takes care of the error messages
#         		if 1 then the result of the test is sent back, no display 
#   @header		info header if you want to test a different phone (optional)
# Returns
#   0 everything is fine
#   1 Not an Aastra phone
#   2 Wrong firmware version
###################################################################################################
function Aastra_test_phone_version($version,$type,$header=NULL)
{
# OK by default
$return=0;

# Start with the HTTP header
if(!$header) $header=Aastra_decode_HTTP_header();

# Must be an Aastra
if(stristr($header['model'],'Aastra'))
	{
	# Retrieve firmware version
	$phone_version=$header['firmware'];
	$piece=preg_split("/\./",$phone_version);	
	$count=count($piece)-1;
	$phone_version=$piece[0]*100;
	if($count>1)$phone_version+=$piece[1]*10;
	if($count>2)$phone_version+=$piece[2];
	$piece=preg_split("/\./",$version);	
	$count=count($piece)-1;
	$test_version=$piece[0]*100;
	if($count>1)$test_version+=$piece[1]*10;
	if($count>2)$test_version+=$piece[2];

	# Compare to passed version
	if($test_version>$phone_version) 
		{
		$return=2;
		if($type==0)
			{
			$output = "<AastraIPPhoneTextScreen>\n";
			$output .= "<Title>Firmware not compatible</Title>\n";
			$output .= "<Text>This XML application needs firmware version $version or better.</Text>\n";
			$output .= "</AastraIPPhoneTextScreen>\n";
			header('Content-Type: text/xml');
			header('Content-Length: '.strlen($output));
			echo $output;
			exit;
			}
		}
	}
else 
	{
	$return=1;
	if($type==0)
		{
		echo "This XML application works better when using an Aastra SIP phone, not a Web browser.<p>See <a href=\"http://www.aastratelecom.com/cps/rde/xchg/SID-3D8CCB73-C78FC1E1/03/hs.xsl/23485.htm\">here</a> for instructions and information.<p>Copyright Aastra Telecom 2005-2011.";
		exit;
		}
	}

# Return results
return($return);
}

###################################################################################################
# Aastra_test_phone_versions(versions,type,header=NULL)
#
# This function tests if the phone version is at least a certain version.
#
# Parameters
#   @version 		array of minimum phone version '1.3.1.' for each phone type ('1'=>'1.3.1')
#   @type 		if 0 then function takes care of the error messages
#         		if 1 then the result of the test is sent back, no display 
#   @header		info header if you want to test a different phone (optional)
#
# Phone types
#	1=9112i,9133i
#	2=480i,480i Cordless
#	3=6730i,6731i,6751i,6753i,9143i
#	4=6755i,6757i,6757iCT.9480i,9480iCT
#	5=6739i, Aastra8000i
#
# Returns
#   0 everything is fine
#   1 Not an Aastra phone
#   2 Wrong firmware version
#
# Example
#   Aastra_test_phone_versions(array('1'=>'1.4.2.','2'=>'1.4.2.','3'=>'2.5.3.','4'=>'2.5.3.','5'=>'3.0.1.'),'0');
###################################################################################################
function Aastra_test_phone_versions($versions,$type,$header=NULL)
{
# Start with the HTTP header
if(!$header) $header=Aastra_decode_HTTP_header();

# Retrieve phone type and requested version
$model_type=Aastra_phone_type($header);

# Perform the check
if(($versions[$model_type]) or ($model_type=='0')) return(Aastra_test_phone_version($versions[$model_type],$type,$header));
else 
	{
	switch($model_type)
		{
		case '1':
			$models=array('Aastra9112i','Aastra9133i');
			break;
		case '2':
			$models=array('Aastra480i','Aastra480i Cordless');
			break;
		case '3':
			$models=array('Aastra6730i','Aastra6731i','Aastra51i','Aastra53i');
			break;
		case '4':
			$models=array('Aastra55i','Aastra57iCTi','Aastra9480i','Aastra9480iCT');
			break;
		case '5':
			$models=array('Aastra6739i','Aastra8000i');
			break;
		}
	return(Aastra_test_phone_model($models,False,$type));
	}
}

###################################################################################################
# Aastra_test_phone_model(models,check,type)
#
# This function checks if the current phone is part of the list of supported phones for this 
# application.
#
# Parameters
#    @models 	array with the list of supported or not supported phones
#    @check 	boolean that indicates if True or False is expected
#    @type 	if 0 then function takes care of the error messages
#       	if 1 then the result of the test is displayed 
# 
# Returns
#   Boolean
#
# Example
#   Aastra_test_phone_model(array('Aastra55i','Aastra57i'),True,0)
#     True if the phone is an Aastra55i or an Aastra57i
###################################################################################################
function Aastra_test_phone_model($models,$check,$type)
{
Global $TEST;

# Debug mode
if($TEST) return True;

# Get phone characteristics
$header=Aastra_decode_HTTP_header();
if(in_array($header['model'],$models)==$check) return True;
else 
	{
	if($type==0)
		{
		$output = "<AastraIPPhoneTextScreen>\n";
		$output .= "<Title>Phone not supported</Title>\n";
		$output .= "<Text>This XML application is not supported by your phone.</Text>\n";
		$output .= "</AastraIPPhoneTextScreen>\n";
		header('Content-Type: text/xml');
		header('Content-Length: '.strlen($output));
		echo $output;
		exit;
		}
	return False;
	}
}

###################################################################################################
# Aastra_is_wrap_title_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_wrap_title_supported($header=NULL)
{
# True by default
$return=True;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test the model
switch($header['model'])
	{
	case 'Aastra9112i':
	case 'Aastra9133i':
	case 'Aastra480i':
	case 'Aastra480i Cordless':
		$return=False;
		break;
	}

# Return Result
return($return);
}

###################################################################################################
# Aastra_is_style_textmenu_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_style_textmenu_supported($header=NULL)
{
# True by default
$return=True;

# Get header info
if(!$header) $header=Aastra_decode_HTTP_header();

# Test model/version
switch($header['model'])
	{
	case 'Aastra9112i':
	case 'Aastra9133i':
	case 'Aastra480i':
	case 'Aastra480i Cordless':
		if(Aastra_test_phone_version('1.4.2.',1,$header)!=0) $return=False;
		else $return=True;
		break;
	case 'Aastra6739i':
	case 'Aastra8000i':
		$return=False;
		break;
	}

# Return Result
return($return);
}

###################################################################################################
# Aastra_is_formattedtextscreen_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_formattedtextscreen_supported($header=NULL)
{
# True by default
$return=True;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test model/version
switch($header['model'])
	{
	case 'Aastra9112i':
	case 'Aastra9133i':
	case 'Aastra480i':
	case 'Aastra480i Cordless':
		$return=False;
		break;
	case 'Aastra6739i':
		if(Aastra_test_phone_version('3.0.1.',1,$header)!=0) $return=False;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_multipleinputfields_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_multipleinputfields_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test model/version
switch($header['model'])
	{
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
		if(Aastra_test_phone_version('2.0.2.',1,$header)==0) $return=True;
		break;
	case 'Aastra6739i':
		if(Aastra_test_phone_version('3.0.1.',1,$header)==0) $return=True;
		break;
	case 'Aastra8000i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_icons_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_icons_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test Model/version
switch($header['model'])
	{
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
		if(Aastra_test_phone_version('2.0.2.',1,$header)==0) $return=True;
		break;
	case 'Aastra6739i':
		if(Aastra_test_phone_version('3.2.0.',1,$header)==0) $return=True;
		break;
	case 'Aastra8000i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_pixmap_graphics_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_pixmap_graphics_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test Model/Version
switch($header['model'])
	{
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
		if(Aastra_test_phone_version('2.2.0.',1,$header)==0) $return=True;
		break;
	case 'Aastra8000i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_png_graphics_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_png_graphics_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test Model/Version
switch($header['model'])
	{
	case 'Aastra6739i':
		if(Aastra_test_phone_version('3.2.0.',1,$header)==0) $return=True;
		break;
	case 'Aastra8000i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_jpeg_graphics_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_jpeg_graphics_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test Model/Version
switch($header['model'])
	{
	case 'Aastra6739i':
		if(Aastra_test_phone_version('3.2.1.',1,$header)==0) $return=True;
		break;
	case 'Aastra8000i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_status_uri_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_status_uri_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test Model/Version
switch($header['model'])
	{
	case 'Aastra6739i':
		if(Aastra_test_phone_version('3.2.1.',1,$header)==0) $return=True;
		break;
	case 'Aastra8000i':
		$return=True;
		break;
	}

# Return result
return($return);
}


###################################################################################################
# Aastra_is_fastreboot_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_fastreboot_supported($header=NULL)
{
# False by default
$return=False;

# Test version
if(Aastra_test_phone_version('2.0.2.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_ledcontrol_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_ledcontrol_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Version OK?
if(Aastra_test_phone_version('2.0.2.',1,$header)==0) 
	{
	# Test model
	switch($header['model'])
		{
		case 'Aastra51i':
		case 'Aastra9480i':
		case 'Aastra9480iCT':
			break;
		default:
			$return=True;
			break;
		}
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_configuration_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_configuration_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.0.1.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_lockin_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_lockin_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.0.1.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_emptyphoneexecute_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_emptyphoneexecute_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.0.1.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_triggerdestroyonexit_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_triggerdestroyonexit_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.0.1.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_softkeys_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_softkeys_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Check Model/Version
switch($header['model'])
	{
	case 'Aastra480i':
	case 'Aastra480i Cordless':
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
	case 'Aastra6739i':
	case 'Aastra8000i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_number_softkeys_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Number of softkeys 6 or 10
###################################################################################################
function Aastra_number_softkeys_supported($header=NULL)
{
# No by default
$return=0;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Check Model/Version
switch($header['model'])
	{
	case 'Aastra480i':
	case 'Aastra480i Cordless':
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
		$return=6;
		break;
	case 'Aastra6739i':
	case 'Aastra8000i':
		$return=10;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_doneaction_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_doneAction_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.1.0.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_Answer_key_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_Answer_key_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.1.0.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_Refresh_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_Refresh_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Depending on the phone
switch($header['model'])
	{
	case 'Aastra6739i':
		if(Aastra_test_phone_version('3.2.0.',1,$header)==0) $return=True;
		break;
	case 'Aastra8000i':
		$return=True;
		break;
	default:
		if(Aastra_test_phone_version('2.0.2.',1,$header)==0) $return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_textmenu_wrapitem_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_textmenu_wrapitem_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.2.0.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_local_reset_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_local_reset_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.3.0.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_allow_DTMF_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_allow_DTMF_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(($header['model']!='Aastra8000i') and (Aastra_test_phone_version('2.3.0.',1,$header)==0)) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_play_wav_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_play_wav_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.3.0.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_sip_notify_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_sip_notify_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(($header['model']!='Aastra8000i') and (Aastra_test_phone_version('2.3.0.',1,$header)==0)) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_keypress_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_keypress_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(($header['model']!='Aastra8000i') and (Aastra_test_phone_version('2.3.0.',1,$header)==0)) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_dialkey_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_dialkey_supported($header=NULL)
{
# False by default
$return=False;

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Check model/version
switch($header['model'])
	{
	case 'Aastra480i':
	case 'Aastra480i Cordless':
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
	case 'Aastra6739i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_dial2key_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_dial2key_supported($header=NULL)
{
# False by default
$return=False;

# Get info header if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Check model/version
switch($header['model'])
	{
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
	case 'Aastra6739i':
		$return=True;
		break;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_dialuri_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_dialuri_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(($header['model']!='Aastra8000i') and (Aastra_test_phone_version('2.0.1.',1,$header)==0)) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_timeout_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_timeout_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.0.1.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_datetime_input_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_datetime_input_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.0.1.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_dynamic_sip_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_dynamic_sip_supported($header=NULL)
{
# False by default
$return=False;

# Check version
if(Aastra_test_phone_version('2.5.0.',1,$header)==0) $return=True;

# Return result
return($return);
}

###################################################################################################
# Aastra_is_lockincall_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_lockincall_supported($header=NULL)
{
# False by default
$return=False;

# Get info header if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# All but the 6739i
if($header['model']!='Aastra6739i')
	{
	if(Aastra_test_phone_version('2.6.0.',1,$header)==0) 
		{
	       if(Aastra_test_phone_version('3.0.0.',1,$header)!=0) $return=True;
		}
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_actionuriconnected_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_actionuriconnected_supported($header=NULL)
{
# False by default
$return=False;

# Get info header if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# All but the 6739i
if($header['model']!='Aastra6739i')
	{
	if(Aastra_test_phone_version('2.6.0.',1,$header)==0) $return=True;
	}

# Return result
return($return);
}

###################################################################################################
# Aastra_is_formattedtextscreen_color_supported(header)
#
# Parameters
#    header		phone HTTP header (optional)
#
# Returns
#    Boolean
###################################################################################################
function Aastra_is_formattedtextscreen_color_supported($header=NULL)
{
# False by default
$return=False;

# Get info header if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Only the 6739i/8000i
if($header['model']=='Aastra6739i')
	{
	if(Aastra_test_phone_version('3.0.1.',1,$header)==0) $return=True;
	}
if($header['model']=='Aastra8000i') $return=True;

# Return result
return($return);
}


###################################################################################################
# Aastra_size_formattedtextscreen()
#
# Returns the number of lines available for formatted text screen
#
# Parameters
#    None
#
# Returns
#    Size of the FormattedTextScreen
###################################################################################################
function Aastra_size_formattedtextscreen($header=NULL)
{
# No size by default
$return=0;

# Get info header if needed
if(!$header) $header=Aastra_decode_HTTP_header();


switch($header['model'])
	{
	case 'Aastra51i':
	case 'Aastra53i':
	case 'Aastra6730i':
	case 'Aastra6731i':
	case 'Aastra9143i':
		$return=2;
		break;
	case 'Aastra55i':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
	case 'Aastra57i':
	case 'Aastra57iCT':
		$return=5;
		break;
	case 'Aastra6739i':
	case 'Aastra8000i':
		$return=14;
		break;
	}

return($return);
}

###################################################################################################
# Aastra_size_display_line()
#
# Returns the number of characters per line on the display
#
# Parameters
#    None
#
# Returns
#    Returns the number of characters per line on the display
###################################################################################################
function Aastra_size_display_line($header=NULL)
{
$return=0;

# Get info header if needed
if(!$header) $header=Aastra_decode_HTTP_header();

switch($header['model'])
	{
	case 'Aastra51i':
	case 'Aastra53i':
	case 'Aastra9112i':
	case 'Aastra9133i':
	case 'Aastra9143i':
	case 'Aastra6730i':
	case 'Aastra6731i':
		$return='16';
		break;
	case 'Aastra480i':
	case 'Aastra480i Cordless':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
		$return='21';
		break;
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
		$return='24';
		break;
	case 'Aastra6739i':
	case 'Aastra8000i':
		$return='32';
		break;
	default:
		$return='24';
		break;
	}

return($return);
}

###################################################################################################
# Aastra_max_items_textmenu()
#
# Returns the maximum number of items for a TextMenu object
#
# Parameters
#    None
#
# Returns
#    15 or 30
###################################################################################################
function Aastra_max_items_textmenu($header=NULL)
{
$return=15;
if(Aastra_test_phone_version('2.2.0.',1,$header)==0) $return=30;
return($return);
}

###################################################################################################
# Aastra_get_custom_icon(icon_name)
#
# Returns hex representation of icon matching the given name. If no icon found, empty icon is returned.
#
# Parameters
#    @iconName  Icon name. List of icons: see below
#
# Returns
#    Hex representation of icon matching the given name
#   Empty icon if icon not found
###################################################################################################
function Aastra_get_custom_icon($icon_name)
{
# Default icon 
$return = '000000000000000000000000';
 
if($icon_name == 'BoxChecked') return '0000FEC6AA92AAC6FE000000';
if($icon_name == 'BoxUnchecked') return '0000FE8282828282FE000000';
if($icon_name == 'Office') return '000000FEAEFAAEFE00000000';
if($icon_name == 'Cellphone') return '000000007E565AFE00000000';
if($icon_name == 'Home') return '000000103E7A3E1000000000';
if($icon_name == 'ArrowRightBold') return '000038383838FE7C38100000';
if($icon_name == 'ArrowLeftBold') return '000010387CFE383838380000';
if($icon_name == 'Bell') return '000000063E7E3E0600000000';
if($icon_name == 'Phone') return '000000664E5A4E6600000000';
if($icon_name == 'MessageUnread') return '00FEC6AA928A8A92AAC6FE00';
if($icon_name == 'MessageRead') return '00FEC6AA924A2A120A060200';
if($icon_name == 'Keypad') return '000000540054005400000000';
if($icon_name == 'DND') return '0000007CEEEEEE7C00000000';
if($icon_name == 'OK') return '0000000804020C30C0000000';
if($icon_name == 'Available') return '000000664E5A4E6600000000';
if($icon_name == 'Offhook') return '00000002060E96F200000000';
if($icon_name == 'Speaker') return '0038387CFE00281044380000';
if($icon_name == 'Muted') return '0038387CFE00386C6C6C3800';

return($return);
}

###################################################################################################
# Aastra_push2phone(server,phone,data)
#
# Push an XML object to the phone.
#
# Parameters
#    @server		IP address or name of the server (must be authorized on the phone)
#    @phone		IP address or name of the phone to send the object to
#    @data		XML object to send
#
# Returns
#    Boolean
###################################################################################################
function Aastra_push2phone($server,$phone,$data)
{
# KO by default
$return=False;

# Prepare the message
$xml = 'xml='.$data;
$post = "POST / HTTP/1.1\r\n";
$post .= "Host: $phone\r\n";
$post .= "Referer: $server\r\n";
$post .= "Connection: Keep-Alive\r\n";
$post .= "Content-Type: text/xml\r\n";
$post .= 'Content-Length: '.strlen($xml)."\r\n\r\n";
$fp = @fsockopen($phone,80,$errno, $errstr, 5);
if($fp)
	{
	fputs($fp, $post.$xml);
	fclose($fp);
	$return=True;
	}
# Return result
return($return);
}

###################################################################################################
# Aastra_getvar_safe(var_name,default,method)
#
# This function helps protect against injection style attacks by formatting the variable value.
#
# Parameters
#    @var_name	name of the submitted variable.
#    @default 	default value returned if the variable was not submitted.
#    @method 		setting the array to look into. This can be set to GET (default), POST, REQUEST,
# 			SERVER, etc.
#
# Returns
#    Boolean
###################################################################################################
function Aastra_getvar_safe($var_name,$default='',$method='GET')
{
eval('$return = (isset($_'.$method.'["'.$var_name.'"])) ? htmlentities(html_entity_decode(stripslashes((trim($_'.$method.'["'.$var_name.'"]))),ENT_QUOTES),ENT_QUOTES) : $default;');
return $return;
}

###################################################################################################
# Aastra_getphone_fingerprint()
#
# This function returns the phone fingerprint which is a md5 hash of its model, MAC address and 
# IP address.
#
# Parameters
#    None 
#
# Returns
#    string MD5 signature
###################################################################################################
function Aastra_getphone_fingerprint()
{
# Retrieve phone information
$header=Aastra_decode_HTTP_header();

# Return signature
return(md5($header['model'].$header['mac'].$header['ip']));
}

###################################################################################################
# Aastra_phone_type()
#
# This function returns the type of phone for an XML perspective.
#
# Parameters
#    None
#
# Return
#    Integer			1=9112i,9133i
#				2=480i,480i Cordless
#				3=6730i,6731i,6751i,6753i,9143i
#				4=6755i,6757i,6757iCT
#				5=6739i,VideoPhone
###################################################################################################
function Aastra_phone_type($header=NULL)
{
# No type by default
$return='0';

# Get header info if needed
if(!$header) $header=Aastra_decode_HTTP_header();

# Test model/version
switch($header['model'])
	{
	case 'Aastra9112i':
	case 'Aastra9133ii':
		$return='1';
		break;
	case 'Aastra480i':
	case 'Aastra480i Cordless':
		$return='2';
		break;
	case 'Aastra6730i':
	case 'Aastra6731i':
	case 'Aastra51i':
	case 'Aastra53i':
	case 'Aastra9143i':
		$return='3';
		break;
	case 'Aastra55i':
	case 'Aastra57i':
	case 'Aastra57iCT':
	case 'Aastra9480i':
	case 'Aastra9480iCT':
		$return='4';
		break;
	case 'Aastra6739i':
	case 'Aastra8000i':
		$return='5';
		break;
	}

# Return result
return($return);
}
?>
