<?php

/* * ********************************************************************
 * Customization Services by ModulesGarden.com
 * Copyright (c) ModulesGarden, INBS Group Brand, All Rights Reserved 
 * (2013-07-08, 08:39:37)
 * 
 *
 *  CREATED BY MODULESGARDEN       ->        http://modulesgarden.com
 *  CONTACT                        ->       contact@modulesgarden.com
 *
 *
 *
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 *
 *
 * ******************************************************************** */

/**
 * @author Maciej Husak <maciej@modulesgarden.com>
 */
if (!defined('DS'))
    define('DS', DIRECTORY_SEPARATOR);

/*
 * DEBUG MODE
 */
if (isset($_REQUEST['_debug']) && $_REQUEST['_debug'] == 'turnon') {
    $_SESSION['mg_onappVPS_debug'] = 1;
} elseif (isset($_REQUEST['_debug']) && $_REQUEST['_debug'] == 'turnoff') {
    $_SESSION['mg_onappVPS_debug'] = NULL;

}

if($_SESSION['mg_onappVPS_debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors',1); 
}
else{
    error_reporting(E_ALL);
    ini_set('display_errors',0); 
    ini_set('log_errors',0); 
} 

$modulename = 'OnApp VPS';
include_once ROOTDIR . DS . 'includes' . DS . 'onappWrapper' . DS . 'utility.php';
onapp_loadCLass();
include_once dirname(__FILE__)         . DS . 'class'        . DS . 'class.Product.php';


if (!function_exists('onappVPS_ConfigOptions')) {
    function onappVPS_ConfigOptions($loadValuesFromServer = true) {
        $ex = explode(DS, $_SERVER['SCRIPT_FILENAME']);
        if ($loadValuesFromServer && end($ex) == 'configproducts.php' && isset($_GET['id']) && $_GET['id']) {
            // setup params
            $product = new onappVPS_Product($_GET['id']);
            $params  = $product->getParams();
            //
            echo '<div id="dialog_template" title="Synchronize templates"></div>';
            if (empty($params)) {
                echo '<div class="errorbox"><strong><span class="title">Please setup onappVPS server first</span></strong></div>';
                return array();
            }
            
            $billing       = new NewOnApp_Billing();
            $hpv           = new NewOnApp_Hypervisor(null);
            $hpvZone       = new NewOnApp_HypervisorZone(null);
            $template      = new NewOnApp_Template(null);
            $networkZone   = new NewOnApp_NetworkZone(null);
            $dataStoreZone = new NewOnApp_DataStoreZone(null);
            $userGroup     = new NewOnApp_UserGroup(null);
            $userRole      = new NewOnApp_UserRole(null);
            
            $hpv           -> setconnection($params);
            $hpvZone       -> setconnection($params);
            $template      -> setconnection($params);
            $networkZone   -> setconnection($params);
            $dataStoreZone -> setconnection($params);
            $userGroup     -> setconnection($params);
            $userRole      -> setconnection($params);
            $billing       -> setconnection($params);
            
            $hypervisors    = $hpv->getHypervisors();
            if(!$hpv->error())
            foreach ($hypervisors as $key => $value) {
                $product->defaultConfig['hypervisor_id']['options'][$value['hypervisor']['id']] = $value['hypervisor']['label'];
            }

            $hypervisor_zones = $hpvZone->getZones();
            if(!$hpvZone->error())
                foreach ($hypervisor_zones as $key => $value) {
                    $product->defaultConfig['hypervisor_zone']['options'][$value['hypervisor_group']['id']] = $value['hypervisor_group']['label'];
                }

            $templates = $template->getSystemTemplates();
            if(!$template->error())
                foreach ($templates as $template) {
                    $product->defaultConfig['template_id']['options'][$template['image_template']['id']]                    = $template['image_template']['label'];
                    $product->defaultConfig['template_group']['options'][$template['image_template']['operating_system']]   = $template['image_template']['operating_system'];
                }
            asort($product->defaultConfig['template_id']['options']);
                
                
            $networks  = $networkZone->getList();
            if(!$networkZone->error())
                foreach ($networks as $network) {
                    $product->defaultConfig['primary_network_id']['options'][$network['network_group']['id']] = $network['network_group']['label'];
                }
            
            $data_zones = $dataStoreZone->getList();
            if(!$dataStoreZone->error())
            foreach ($data_zones as $zone) {
                $product->defaultConfig['data_store_group_primary_id']['options'][$zone['data_store_group']['id']] = $zone['data_store_group']['label'];
                $product->defaultConfig['data_store_group_swap_id']['options'][$zone['data_store_group']['id']]    = $zone['data_store_group']['label'];
            }
            
            $user_groups = $userGroup->getList();
            if(!$userGroup->error())
            foreach ($user_groups as $group) {
                $product->defaultConfig['user_group']['options'][$group['user_group']['id']]   = $group['user_group']['label'];
                $product->defaultConfig['user_group']['options'][$group['user_group']['id']]   = $group['user_group']['label'];
            }
            
            $user_roles = $userRole->getList();
            if(!$userRole->error())
            foreach ($user_roles as $role) {
                $product->defaultConfig['user_role']['options'][$role['role']['id']]   = $role['role']['label'];
                $product->defaultConfig['user_role']['options'][$role['role']['id']]   = $role['role']['label'];
            }
            
            $billing_plans = $billing->getPlans();
            if(!$billing->error())
            foreach ($billing_plans as $plan) {
                $product->defaultConfig['user_billing_plan']['options'][$plan['billing_plan']['id']]   = $plan['billing_plan']['label'];
                $product->defaultConfig['user_billing_plan']['options'][$plan['billing_plan']['id']]   = $plan['billing_plan']['label'];
            }
            
            asort($product->defaultConfig['user_billing_plan']['options']);
            
            $scripts = '<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery("select[name=\'customconfigoption[template_group]\']").change(function(){
                                                jQuery("select[name=\'customconfigoption[template_id]\']").attr("disabled",true);
						var group = jQuery(this).val();
						jQuery.post(window.location.href, {action: \'gettemplates\',group:group},function(data){
                                                        jQuery("select[name=\'customconfigoption[template_id]\']").attr("disabled",false);
							if(data!="")
								jQuery("select[name=\'customconfigoption[template_id]\']").html(data);
						});
					});

				});
			</script>';
            $scripts .='<script type="text/javascript">
                        jQuery(document).ready(function(){
                                jQuery("#onappVPS_configurable_options").click(function(){
                                        jQuery.post(window.location.href, {"action":"onappVPS_setup_configurable_options", "productid":'.(int)$_GET['id'].',"packageconfigoption":null}, function(res){
                                                alert(res.result);
                                                window.location.href = "configproducts.php?action=edit&id='.(int)$_GET['id'].'&tab=4";
                                        }, "json");
                                        return false;
                                });

                                jQuery("#onappVPS_custom_fields").click(function(){
                                        jQuery.post(window.location.href, {"action":"onappVPS_setup_custom_fields", "productid":'.(int)$_GET['id'].',"packageconfigoption":null}, function(res){
                                                alert(res.result);
                                                window.location.href = "configproducts.php?action=edit&id='.(int)$_GET['id'].'&tab=3";
                                        }, "json");
                                        return false;
                                });
                                
                                jQuery("#onappVPS_synchronize_templates").click(function(){
                                     jQuery.post(window.location.href, {"action":"onappVPS_synchronize_templates", "productid":'.(int)$_GET['id'].',"packageconfigoption":null}, function(res){
                                            jQuery("#dialog_template").html(res);
                                            jQuery( "#dialog_template" ).dialog({width:700,modal: true, buttons: {
                                                "Synchronie all items": function() {
                                                  jQuery.post(window.location.href, {"action":"onappVPS_synchronize_templates", "productid":'.(int)$_GET['id'].',"packageconfigoption":null,replace:1}, function(res){
                                                        if(res=="success")
                                                            alert("Synchronize process: success");
                                                       
                                                        
                                                    });
                                                    jQuery( this ).dialog( "close" );
                                                },
                                                Cancel: function() {
                                                  jQuery( this ).dialog( "close" );
                                                }
                                          }});
                                        });
                                        return false;
                                });

                        });
                        </script>';
        echo	'
                <tr>
                    <td class="fieldlabel mg">Configurable Options</td>
                    <td class="fieldarea mg"><a href="" id="onappVPS_configurable_options">Generate default</a> <a href="" class="so_popup"><img src="../images/help.gif" title="This button will create Configurable Options for your product that optionally can be enabled. Your clients will be able to choose resources and server options during Create/Upgrade Process." /></a></td>
                    <td class="fieldlabel mg">Custom Fields</td>
                    <td class="fieldarea mg"><a href="" id="onappVPS_custom_fields">Generate default</a> <a href="" class="so_popup"><img src="../images/help.gif" title="This button will create Custom Fields for your product that must be enabled." /></a></td>
               </tr>
               <tr>
                    <td class="fieldlabel mg">Templates</td>
                    <td class="fieldarea mg"><a href="" id="onappVPS_synchronize_templates">Synchronize</a> <a href="" class="so_popup"><img src="../images/help.gif" title="This button will sychronize templates from OnApp." /></a></td>
                    <td class="fieldlabel mg></td>
                    <td class="fieldarea mg"></td>
               </tr>';
            
        
            //remove auto scalling    
            $clone = array();
            if($product->getConfig('vmware')=='Yes'){
                foreach($product->defaultConfig as $key=> $value){
                    $clone[$key] = $value;
                  if($key =='gr7')
                      break;
                }
                $product->defaultConfig = $clone;
               // print_r($product->defaultConfig);
               // print_r($clone);
            }

            echo $product->renderConfigOptions($scripts);
            return array();
        }
    }
}


if (!function_exists('ausweb_getTemplate')){

    function ausweb_getTemplate($params){ 

        $templateGetter = new NewOnApp_Template(null);
        $templateGetter->setconnection($params);
        $templates = $templateGetter->getSystemTemplates();

        // Get the selected template
        $selected_template = $params['configoptions']['Operating System'];

        // Match selected template to label
        foreach ($templates as $template) {

            if ($template['image_template']['label'] == $selected_template) {

                $template_id = $template['image_template']['id'];

            }

        }

        return $template_id;

    }

}

/**
* FUNCTION onappVPS_CreateAccount
* Create user & VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_CreateAccount')){


	function onappVPS_CreateAccount($params){    

                $client             = new NewOnApp_Client($params['clientsdetails']['id']);
                $cn_acc             = mysql_num_rows(mysql_query("
                    SELECT h.id FROM tblhosting h
                    JOIN tblproducts p ON (p.id = h.packageid) 
                    WHERE 
                        h.userid='".$params['clientsdetails']['id']."'
                        AND h.username !=''
                        AND h.password !=''
                        AND p.servertype='onappVPS'"
                ));
                if($cn_acc>1){
                    $lastPassword       = $client->getLastPasswordFromHostings($params['pid'], array($params['serviceid']));
                } else               
                    $lastPassword       = $client->getLastPasswordFromHostings($params['pid'], array());
                                
                if($lastPassword!==null)
                    $params['password'] = $lastPassword;
                else
                    $params['password'] = onapp_pass_generator();
            
                $vpsid   = empty($params['customfields']['vmid']) ? null : $params['customfields']['vmid'];
                $product = new onappVPS_Product($params['pid'],$params['serviceid']);
                $vm      = new NewOnApp_VM($vpsid);
                $vm      -> setconnection($params);
                $user    = new NewOnApp_User(null);
                $user    -> setconnection($params);
                
                if(!onapp_customFieldExists($params['pid'], 'vmid'))
                    return 'Custom fields dosen\'t exists.';
                
                if(!onapp_customFieldExists($params['pid'], 'userid'))
                    return 'Custom fields dosen\'t exists.';
                
                if(!empty($params['customfields']['vmid'])){
                     $exists  = $vm->getDetails();
                     if($exists['virtual_machine']['id']>0){
                         return 'VM already exists, please remove it and try again.';
                     }
                }
                
                $newmail            = uniqid().'@'.$_SERVER['HTTP_HOST'];
                
                $username_prefix    = $product->getConfig('user_prefix');
                $username_counter   = (int)$product->getConfig('user_counter');
		$lastUsername       = $client->getLastUsernameFromHostings($params['pid'], array($cn_acc>1 ? $params['serviceid'] : 0));
                
                if ($lastUsername!== null){
			$username   = $lastUsername;
                }
		elseif ($username_prefix && $username_counter){
                        $search     = $user->search($newmail);
                        if(!$user->error()){
                            if(isset($search) && isset($search[0]) && $search[0]['user']['id']==$params['customfields']['userid'] && $search[0]['user']['status']=='active'){
                                $user_id  = $search[0]['user']['id'];
                                $username = $search[0]['user']['login']; 
                            }    
                        }
                        if(!isset($username)){                    
                            $username   = $username_prefix . $username_counter;
                            $call_clientCounterExists = $user->isUserExists($username,$newmail); 
                            if ($call_clientCounterExists && isset($call_clientCounterExists['valid']) && !$call_clientCounterExists['valid']){
                                    return 'Could not create Virtual Machine - Username already exists. Increment your /Username counter/.';
                            }
                            $product->load();
                            $product->saveConfig('user_counter',++$username_counter);
                        }
		} else {
			$username = $username_prefix . uniqid();
		}       
                $u_exists = $user->isExists(array('login'=>$username));
                $e_exists = $user->isExists(array('email'=>$newmail));

                $hosting_counter = mysql_num_rows(mysql_query("SELECT h.id FROM tblhosting h LEFT JOIN tblproducts p ON(p.id=h.packageid) WHERE h.userid='".$params['clientsdetails']['id']."' AND p.servertype='OnAppVPS'"));
                
                if(isset($u_exists) && $u_exists['valid'] && isset($e_exists) && $e_exists['valid'] && !isset($user_id)){
                    //create new user
                    $data = array(
                        'user' => array(
                            'login'             => $username,
                            'first_name'        => $params['clientsdetails']['firstname'],
                            'last_name'         => $params['clientsdetails']['lastname'],
                            'email'             => $newmail,
                            'password'          => $params['password'],
                            'role_ids'          => $product->getConfig('user_role'),
                            'status'            => 'active',
                            'billing_plan_id'   => $product->getConfig('user_billing_plan'),
                            'user_group_id'     => $product->getConfig('user_group')
                        )
                    ); 
                    
                    $result = $user->create($data);
                    if($user->error()){
                        $client -> updateHostingUsername('',$params['serviceid'],$params['pid']);
                        $client -> updateHostingPassword('',$params['serviceid'],$params['pid']);
                        return $user->error();
                    } else {
                        $search   = $user->search($username);
                        if($user->isSuccess()){
                            $user_id  = $search[0]['user']['id'];
                            onapp_addCustomFieldValue('userid', $params['pid'],$params['serviceid'],$user_id);
                        }    
                        else
                            return $user->error();
                    }
                } 
                else if($e_exists['valid']==false && strpos($e_exists['message'],'E-mail has already been taken')===false  && $hosting_counter>1 ){
                    $client -> updateHostingUsername('',$params['serviceid'],$params['pid']);
                    $client -> updateHostingPassword('',$params['serviceid'],$params['pid']);
                    return $e_exists['message'].". You should change email or fill username or password.";
                }
                
                $params['username'] = $username;
                $vm                 -> setconnection($params,true);
                   

                //check template
                $swap     = 1;
                $template = mysql_fetch_assoc(mysql_query_safe("SELECT ps.optionname FROM  tblproductconfiggroups pg LEFT JOIN tblproductconfiglinks pl ON (pg.id = pl.gid) LEFT JOIN tblproductconfigoptions po ON (po.gid=pg.id) LEFT JOIN tblproductconfigoptionssub ps ON (ps.configid = po.id) WHERE po.optionname LIKE 'template_id|%' AND ps.optionname LIKE '".(isset($params['configoptions']['template_id'])          ? (int)$params['configoptions']['template_id']       : (int)$product->getConfig('template_id'))."|%'"));



                if(strpos(strtolower($template['optionname']),'windows')!== false)
                        $swap = 0;
                

                //create VM 
                $result = $vm->create(array('virtual_machine'=> array(
                        'label'                                 => (!empty($params['customfields']['label'])                ? $params['customfields']['label']              : $product->getConfig('label')),
                        'memory'                                => (isset($params['configoptions']['memory'])               ? $params['configoptions']['memory']            : $product->getConfig('memory')),
                        'cpu_shares'                            => (isset($params['configoptions']['cpu_shares'])           ? $params['configoptions']['cpu_shares']        : $product->getConfig('cpu_shares')),
                        'hostname'                              => (!empty($params['domain'])                               ? $params['domain']                             : $params['customfields']['hostname']),
                        'cpus'                                  => (isset($params['configoptions']['cpus'])                 ? $params['configoptions']['cpus']              : $product->getConfig('cpus')),
                        'primary_disk_size'                     => (isset($params['configoptions']['primary_disk_size'])    ? $params['configoptions']['primary_disk_size'] : $product->getConfig('primary_disk_size')),
                        'swap_disk_size'                        => ($swap===0 ? null : (isset($params['configoptions']['swap_disk_size'])       ? $params['configoptions']['swap_disk_size']    : $product->getConfig('swap_disk_size'))),
                        'template_id'                           => (int)ausweb_getTemplate($params),
                        'initial_root_password'                 => $params['password'],
                        'hypervisor_id'                         => $product->getConfig('hypervisor_id'),
                        'hypervisor_group_id'                   => $product->getConfig('hypervisor_zone'),
                        'type_of_format'                        => $product->getConfig('type_of_format'),
                        'rate_limit'                            => (int)$product->getConfig('rate_limit'), 
                        'primary_network_group_id'              => (isset($params['configoptions']['network_group'])       ? $params['configoptions']['network_group']      : $product->getConfig('primary_network_id')),
                        'data_store_group_primary_id'           => (isset($params['configoptions']['data_store'])          ? $params['configoptions']['data_store']         : $product->getConfig('data_store_group_primary_id')),
                        'data_store_group_swap_id'              => (isset($params['configoptions']['swap_store'])          ? $params['configoptions']['swap_store']         : $product->getConfig('data_store_group_swap_id')),
                        'selected_ip_address_id'                => '',
                        'licensing_key'                         => (!empty($params['customfields']['licensing_key'])       ? $params['customfields']['licensing_key']       : $product->getConfig('licensing_key')),
                        'licensing_type'                        => $product->getConfig('licensing_type'),
                        'licensing_server_id'                   => $product->getConfig('licensing_server_id'),
                        'required_virtual_machine_build'        => 1,
                        'required_ip_address_assignment'        => 1,
                        'required_virtual_machine_startup'      => 1,
                        'initial_root_password_confirmation'    => $params['password'],
                        'required_automatic_backup'             => $product->getConfig('required_automatic_backup'),           
		)));
             
             


                if(!$vm->isSuccess()){
                    if(isset($user_id) && $user_id>0)
                        $user->setUserID($user_id);
                    
                    $list = $user-> getVMList();
                    if(count($list)==0){
                        $user->delete(array('force'=>1));

                    }  
                    return $vm->error();
                        
                }else {  
                        $vm->setconnection($params);
                        $client             -> updateHostingUsername($username,$params['serviceid'],$params['pid']);
                        $client             -> updateHostingPassword($params['password'],$params['serviceid'],$params['pid']);
                        onapp_addCustomFieldValue('vmid', $params['pid'],$params['serviceid'],$result['virtual_machine']['id']);
                        onapp_addCustomFieldValue('userid', $params['pid'],$params['serviceid'],$result['virtual_machine']['user_id']);
                        $vm->setID($result['virtual_machine']['id']);
                        $cn_ip  = (isset($params['configoptions']['ip_addresses']) ? $params['configoptions']['ip_addresses'] : $product->getConfig('ip_addresses'));
                        $vm->assignIP($cn_ip, $result['virtual_machine']['ip_addresses'][0]['ip_address']['network_id']);
                        mysql_query_safe("UPDATE tblhosting SET `dedicatedip`=? WHERE `id`=?",array(($cn_ip>0 ? $result['virtual_machine']['ip_addresses'][0]['ip_address']['address'] : null),$params['serviceid']));
                        if(!$vm->isSuccess())
                            return $vm->error ();
                        
			return 'success';
		}
                
        }
}

/**
* FUNCTION onappVPS_TerminateAccount
* Remove VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_TerminateAccount')){
	function onappVPS_TerminateAccount($params){	
            if(empty($params['customfields']['vmid']))
                return 'VM not found!';
           
            $vm = new NewOnApp_VM($params['customfields']['vmid']);
            $vm -> setconnection($params);
            
            $vm->unsuspend();
            $vm->delete();
            if($vm->isSuccess()){
                $user = new NewOnApp_User($params['customfields']['userid']);
                $user -> setconnection($params);
                $list = $user->getVMList();
                
                if(count($list)==1)
                    $user->delete(array('force'=>1));
                
                onapp_addCustomFieldValue('vmid', $params['pid'],$params['serviceid'],'');
                return 'success';
            }
            else
                return $vm->error();
            
        }
}

/**
* FUNCTION onappVPS_SuspendAccount
* Disable VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_SuspendAccount')){
	function onappVPS_SuspendAccount($params){	
            if(empty($params['customfields']['vmid']))
                return 'VM not found!';
            
            $vm      = new NewOnApp_VM($params['customfields']['vmid']);
            $vm      -> setconnection($params);
            
            $vm->suspend();
            if($vm->isSuccess())
                return 'success';
            else
                return $vm->error();
        }
}


/**
* FUNCTION onappVPS_UnsuspendAccount
* Enable VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_UnspendAccount')){
	function onappVPS_UnsuspendAccount($params){	
            if(empty($params['customfields']['vmid']))
                return 'VM not found!';
            
            $vm      = new NewOnApp_VM($params['customfields']['vmid']);
            $vm      -> setconnection($params);
            
            $vm->unsuspend();
            if($vm->isSuccess())
                return 'success';
            else
                return $vm->error();
        }
}


/**
* FUNCTION onappVPS_ChangePackage
* Modify VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_ChangePackage')){
	function onappVPS_ChangePackage($params){	
            if(empty($params['customfields']['vmid']))
                return 'VM not found!';
            
            $product = new onappVPS_Product($params['pid']);
            $vm      = new NewOnApp_VM($params['customfields']['vmid']);
            $vm      -> setconnection($params);
            $details = $vm -> getDetails();
            if(
                    $details['virtual_machine']['label']       != (!empty($params['customfields']['label'])      ? $params['customfields']['label']       : $product->getConfig('label')) ||
                    $details['virtual_machine']['memory']      != (isset($params['configoptions']['memory'])     ? $params['configoptions']['memory']     : $product->getConfig('memory')) || 
                    $details['virtual_machine']['cpu_shares']  != (isset($params['configoptions']['cpu_shares']) ? $params['configoptions']['cpu_shares'] : $product->getConfig('cpu_shares')) ||
                    $details['virtual_machine']['cpus']        != (isset($params['configoptions']['cpus'])       ? $params['configoptions']['cpus']       : $product->getConfig('cpus'))
            ) {
                $data    = array('virtual_machine'=> array(
                            'label'               => (!empty($params['customfields']['label'])      ? $params['customfields']['label']       : $product->getConfig('label')),
                            'memory'              => (isset($params['configoptions']['memory'])     ? $params['configoptions']['memory']     : $product->getConfig('memory')),
                            'cpu_shares'          => (isset($params['configoptions']['cpu_shares']) ? $params['configoptions']['cpu_shares'] : $product->getConfig('cpu_shares')),
                            'cpus'                => (isset($params['configoptions']['cpus'])       ? $params['configoptions']['cpus']       : $product->getConfig('cpus')),
                ));
                $vm->modify($data);
                if(!$vm->isSuccess()){
                    return $vm->error();
                }
            }
            
            $tempid = (int) ausweb_getTemplate($params);

            if($details['virtual_machine']['template_id']!=$tempid){
                $vm -> rebuild(array('virtual_machine' => array('template_id' =>  $tempid)));
                if(!$vm->isSuccess()){
                    return $vm->error();
                }
            }
            
            $cn_ip  = (isset($params['configoptions']['ip_addresses']) ? $params['configoptions']['ip_addresses'] : $product->getConfig('ip_addresses'));
            if(count($details['virtual_machine']['ip_addresses'])!= $cn_ip){
                $cn_ip  = (isset($params['configoptions']['ip_addresses']) ? $params['configoptions']['ip_addresses'] : $product->getConfig('ip_addresses'));
                $vm->assignIP($cn_ip, $result['virtual_machine']['ip_addresses'][0]['ip_address']['network_id']);
            }    
            
            $swap       = isset($params['configoptions']['swap_disk_size'])       ? $params['configoptions']['swap_disk_size']       : $product->getConfig('swap_disk_size');
            $primary    = isset($params['configoptions']['primary_disk_size'])    ? $params['configoptions']['primary_disk_size']    : $product->getConfig('primary_disk_size');
            
            if($details['virtual_machine']['total_disk_size']!= ($swap+$primary)){
                //resize disk
                $disk = new NewOnApp_Disk();
                $disk ->setconnection($params);
                $disk_list = $disk->getList($params['customfields']['vmid']);
                if($disk->isSuccess()){
                    foreach($disk_list as $key=>$val){
                        $disk ->setID($val['disk']['id']);
                        if($val['disk']['is_swap']){
                            $data = array('disk'=> array(
                                'disk_size' => $swap
                                ));
                        } else {
                            $data = array('disk'=> array(
                                'disk_size' => $primary
                                ));
                        }
                        $disk->edit($data);
                        if(!$disk->isSuccess())
                            return $disk->error();
                    }    
                }
            }
                
                //change rate limit
                $interface = new NewOnApp_NetworkInterface($params['customfields']['vmid']);
                $interface -> setconnection($params);
                $list = $interface->getList();
                if($interface->isSuccess()){
                    foreach($list as $value){
                        $interface->save($value['network_interface']['id'],array(
                            'network_interface' => array(
                                'label'         => $value['network_interface']['label'],
                                'rate_limit'    => (isset($params['configoptions']['rate_limit'])          ? $params['configoptions']['rate_limit']         : ($product->getConfig('rate_limit') == null  ? $product->getConfig('rate_limit') : 0)),
                        
                            )
                        ));
                        if(!$interface->isSuccess())
                            return $interface->error();
                    }
                }
                
                return 'success';
          
        }
}        
         
/**
* FUNCTION onappVPS_ChangePassword
* Change root password for VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_ChangePassword')){
	function onappVPS_ChangePassword($params){	
            if(empty($params['customfields']['vmid']))
                return 'VM not found!';
            
            $vm = new NewOnApp_VM($params['customfields']['vmid']);
            $vm -> setconnection($params);
            
            $data    = array('virtual_machine'=> array(
			'initial_root_password'  => $params['password'],
            ));
                        
            $vm -> changePassword($data);
            if($vm->isSuccess()){
                $user = new NewOnApp_User($params['customfields']['userid']);
                $user -> setconnection($params);
                $user -> edit(array(
                   'user' => array(
                       'password' => $params['password']
                   ) 
                ));
                if($user->isSuccess()){
                    mysql_query("UPDATE tblhosting SET `password`='".encrypt($params['password'])."' WHERE `username`='".mysql_real_escape_string($params['username'])."' AND `userid`='".(int)$params['clientsdetails']['id']."' ");
                    return 'success';
                }
                else
                    return $user->error();
                
            }    
            else
                return $vm->error();
        }
}

/**
* FUNCTION onappVPS_Start
* Start VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_Unlock')){
	function onappVPS_Unlock($params){	
           $vm = new NewOnApp_VM($params['customfields']['vmid']);
           $vm -> setconnection($params);     
           $vm -> unlock();
           
           if($vm->isSuccess()){
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()-360);  
                return 'success';
           }else
                return $vm->error();
           
        }
}


/**
* FUNCTION onappVPS_Start
* Start VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_Start')){
	function onappVPS_Start($params){	
           $vm = new NewOnApp_VM($params['customfields']['vmid']);
           $vm -> setconnection($params);     
           $vm -> start();
           
           if($vm->isSuccess()){
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()-360);  
                return 'success';
           }else
                return $vm->error();
           
        }
}


/**
* FUNCTION onapVPS_Stop
* Stop VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_Stop')){
	function onappVPS_Stop($params){	
           $vm = new NewOnApp_VM($params['customfields']['vmid']);
           $vm -> setconnection($params);     
           $vm -> stop();
           
           if($vm->isSuccess()){
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()-360);  
                return 'success';
           }else
                return $vm->error();
        }
}

/**
* FUNCTION onapVPS_Shutdown
* Shutdown VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_Shutdown')){
	function onappVPS_Shutdown($params){	
           $vm = new NewOnApp_VM($params['customfields']['vmid']);
           $vm -> setconnection($params);     
           $vm -> shutdown();
           
           if($vm->isSuccess()){
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()-360);  
                return 'success';
           }
           else
                return $vm->error();
        }
}

/**
* FUNCTION onapVPS_Recovery
* Start VM with recovery mode
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_Recovery')){
	function onappVPS_Recovery($params){	
           $product = new onappVPS_Product($params['pid']);
           if($product->getConfig('vmware')=='Yes')
               return 'Method not allowed for VMware.';
           $vm = new NewOnApp_VM($params['customfields']['vmid']);
           $vm -> setconnection($params);     
           $vm -> recovery();
           
           if($vm->isSuccess()){
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()-360);  
                return 'success';
           }
           else
                return $vm->error();
        }
}

/**
* FUNCTION onapVPS_Rebuild
* Rebuild VM disk
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_Rebuild')){
	function onappVPS_Rebuild($params){	

            $tempid = (int) ausweb_getTemplate($params);

            $product = new onappVPS_Product($params['pid']);
            $vm      = new NewOnApp_VM($params['customfields']['vmid']);
            $vm -> setconnection($params);     
            $vm -> rebuild(array('virtual_machine' => array('template_id' => (isset($tempid)          ? $tempid       : $product->getConfig('template_id')))));
            

            if($vm->isSuccess()){
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()-360);  
                return 'success';
                
            }else
                return $vm->error();

        }
}

/**
* FUNCTION onapVPS_Reboot
* Reboot VM
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_Reboot')){
	function onappVPS_Reboot($params){	
           $vm = new NewOnApp_VM($params['customfields']['vmid']);
           $vm -> setconnection($params);     
           $vm ->reboot();
           
           if($vm->isSuccess()){
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()-360);
                return 'success';  
           }
           else
                return $vm->error();
        }
}


/**
* FUNCTION onappVPS_AdminCustoButtonArray
* Display actions buttons
* @params array
* @return array
*/ 
if (!function_exists('onappVPS_AdminCustomButtonArray')){
	function onappVPS_AdminCustomButtonArray(){
            
            $buttonarray = array(
                'Start VM'      => "start",
                'Stop VM'       => "stop",
                'Shutdown VM'   => "shutdown",
                'Reboot VM'     => "reboot",
                'Rebuild VM'    => "rebuild",
                'Recovery VM'   => "recovery",
                'Unlock VM'     => "unlock"
            );
            
            return $buttonarray;
        }
}


/**
* FUNCTION onapVPS_AdminServiceTabFields
* Display VM details and console button
* @params array
* @return string
*/ 
if (!function_exists('onappVPS_AdminServicesTabFields')){
	function onappVPS_AdminServicesTabFields($params){	
            if(empty($params['customfields']['vmid']))
                return array();
        
            $moduledir = substr(dirname(__FILE__), strlen(ROOTDIR)+1);
            $vm = new NewOnApp_VM($params['customfields']['vmid']);
            $vm -> setconnection($params);   
            if(!$_COOKIE['console_vps'][$params['customfields']['vmid']]){
                $console = $vm ->getConsoleKey();
                @setcookie("console_vps[".$params['customfields']['vmid']."]", $console['remote_access_session']['remote_key'], time()+900);             
            }else
                $console['remote_access_session']['remote_key'] = $_COOKIE['console_vps'][$params['customfields']['vmid']];
            $fields  = array();
            $results = $vm->getDetails();   
            $vpsdata = $results['virtual_machine'];    
            $vpsdata['monthly_bandwidth_used'] = round($vpsdata['monthly_bandwidth_used']/1024,2);
            
            if($console['remote_access_session']['remote_key']!='' ){//&& $vpsdata['booted']==true
                $uri  = urlencode('clientarea.php?action=productdetails&id='.$params['serviceid'].'&modop=custom&a=management&page=console');
                $fields['Console']              = '<button class="btn" id="consolebtn" onclick="window.open(\'../dologin.php?username='.urlencode($params['clientsdetails']['email']).'&goto='.$uri.'\',\'\',\'width=820,height=700\'); return false;">Open</button>';   
                
            }
            $lang    = onapVPS_getLang($params);
            $lang    = $lang['mainsite'];
            $ip      = null;
            if(isset($vpsdata['network_address']))
                $ip  = $vpsdata['network_address'];
            elseif($vpsdata['ip_addresses']){
                foreach($vpsdata['ip_addresses'] as $key=>$val)
                    $ip.=$val['ip_address']['address'].'<br />';
             }
            if(isset($_POST['ajax']) && $_POST['ajax']==1 && isset($_POST['doAction']) && $_POST['doAction']=='details'){
                ob_clean();  
                if($vpsdata['id']>0){
                    die(json_encode ($vpsdata));
                }else    
                    die($vm->error());
                 
             }
            $fields['VM Details'] = ' 
            <div id="serverstats">
                <table width="400" class="table">
                    <tr><td>'.$lang['server_status'].'</td><td><span id="serverstatus"></span> <a href="#" onclick="doAction(\'details\');return false;"><img src="../'.$moduledir.'/img/refresh.png" alt="" /></a></td></tr>
                    <tr><td>'.$lang['label'].'</td><td class="vps_label">'.$vpsdata['label'].'</td></tr>
                    <tr><td>'.$lang['booted'].'</td><td class="vps_booted">'.           ($vpsdata['booted']         == true ? '<span class="green">'.$lang['yes'].'</span>' : '<span class="red">'.$lang['no'].'</span>').'</td></tr>
                    <tr><td>'.$lang['built'].'</td><td class="vps_built">'.             ($vpsdata['built']          == true ? '<span class="green">'.$lang['yes'].'</span>' : '<span class="red">'.$lang['no'].'</span>').'</td></tr>
                    <tr><td>'.$lang['recovery_mode'].'</td><td class="vps_recovery">'.  ($vpsdata['recovery_mode']  == true ? '<span class="green">'.$lang['yes'].'</span>' : '<span class="red">'.$lang['no'].'</span>').'</td></tr>
                    <tr><td>'.$lang['cpus'].'</td><td>'.$vpsdata['cpus'].'</td></tr>
                    <tr><td>'.$lang['shares'].'</td><td>'.$vpsdata['cpu_shares'].'%</td></tr>
                    <tr><td>'.$lang['memory_size'].'</td><td>'.$vpsdata['memory'].' '.$lang['MB'].'</td></tr>
                    <tr><td>'.$lang['disk_size'].'</td><td>'.$vpsdata['total_disk_size'].' '.$lang['GB'].'</td></tr>
                    <tr><td>'.$lang['monthly_bandwidth_used'].'</td><td><span class="vps_bandwidth">'.$vpsdata['monthly_bandwidth_used'].'</span> '.$lang['MB'].'</td></tr>
                    <tr><td>'.$lang['ip'].'</td><td>'.$ip.'</td></tr>
                    <tr><td>'.$lang['template_image'].'</td><td class="vps_template">'.$vpsdata['template_label'].'</td></tr> 
                    <tr><td>'.$lang['created_at'].'</td><td class="vps_created">'.$vpsdata['created_at'].'</td></tr>
                    <tr><td>'.$lang['updated_at'].'</td><td class="vps_updated">'.$vpsdata['updated_at'].'</td></tr>
                </table>
            </div>
         <script type="text/javascript">
         function doAction(action){
         jQuery.post(window.location.href,{doAction: action,ajax:1},function(data){     
                if(action=="details"){
                    var obj = jQuery.parseJSON(data);
                    if(typeof obj ==\'object\'){
                        jQuery(".vps_label").text(obj.label);           
                        if(obj.booted==true)
                           jQuery(".vps_booted").html(\'<span class="green">'.$lang['yes'].'</span>\');
                        else
                           jQuery(".vps_booted").html(\'<span class="red">'.$lang['no'].'</span>\');
                       
                        if(obj.built==true)
                           jQuery(".vps_built").html(\'<span class="green">'.$lang['yes'].'</span>\');
                        else
                           jQuery(".vps_built").html(\'<span class="red">'.$lang['no'].'</span>\');
                       
                        if(obj.recovery_mode==true)
                           jQuery(".vps_recovery").html(\'<span class="green">'.$lang['yes'].'</span>\');
                        else
                           jQuery(".vps_recovery").html(\'<span class="red">'.$lang['no'].'</span>\');
                       
                        jQuery(".vps_bandwidth").text(obj.monthly_bandwidth_used);
                        jQuery(".vps_created").text(obj.created_at);
                        jQuery(".vps_updated").text(obj.updated_at);  
                        jQuery("#vm_alerts").html(\'\');
                    } else jQuery("#serverstatus").text(data);
                }
                });
                }
           function dateFormat(date){
                var created = new Date(date);
                var day     = created.getDate();
                var month   = created.getMonth()+1; 
                var year    = created.getFullYear();
                var hours   = created.getHours();
                var min     = created.getMinutes();
                var sec     = created.getSeconds()

                return (day <= 9 ? \'0\' + day : day)+\'-\'+(month<=9 ? \'0\' + month : month)+\'-\'+year+\' \'+(hours <= 9 ? \'0\' + hours : hours)+\':\'+(min <= 9 ? \'0\' + min : min)+\':\'+(sec <= 9 ? \'0\' + sec : sec);
            }     
          
            function sendform(){
               var url = jQuery("#consolebtn").attr("rel");
               jQuery("#consoleform").attr("action",url);
               console.log(url);
               jQuery("#consoleform").submit();
            }
            jQuery(document).ready(function() {
                setInterval("doAction(\'details\')",20000);
                jQuery(document).ajaxStart(function() {
                    jQuery("#serverstatus").show();
                    jQuery("#serverstatus").html( "<img src=\"../images/loadingsml.gif\" />" );
                }).ajaxStop(function() {jQuery("#serverstatus").hide(); });
                
              
        });      
           </script>
           <style type="text/css">
            .green {font-weight:bold;color:green;}
            .red {font-weight:bold;color:red}
            .table td {border-bottom:1px solid #fff;}
           </style>
            ';
            if(empty($vpsdata['id']))
                  $fields['VM Details'] = 'VM not found';
            
            return $fields;         
        }
}

/**
* FUNCTION onapVPS_getLang
* Get user language
* @params array
* @return string
*/ 
if(!function_exists('onapVPS_getLang')){
    function onapVPS_getLang($params){
            global $CONFIG;
            if(!empty($_SESSION['Language']))
                 $language = strtolower($_SESSION['Language']);
             else if(strtolower($params['clientsdetails']['language'])!='')
                 $language = strtolower($params['clientsdetails']['language']);
             else
                 $language = $CONFIG['Language'];

             $langfilename = dirname(__FILE__).DS.'lang'.DS.$language.'.php';
             if(file_exists($langfilename)) 
                require_once($langfilename);
             else
                require_once(dirname(__FILE__).DS.'lang'.DS.'english.php');

             if(isset($lang))
                 return $lang;
    }
}

/**
* FUNCTION onapVPS_ClientAreaCustomButtonArray
* Display buttons in clientArea
* @return array
*/ 
if (!function_exists('onappVPS_ClientAreaCustomButtonArray')){
    function onappVPS_ClientAreaCustomButtonArray() {
        $buttonarray = array(
            "Management" => "management",
        );
    return $buttonarray;
    }
}


/**
* FUNCTION onapVPS_AdminLink
* Login to admin panel
* @params int
* @return array
*/ 
if(!function_exists('onappVPS_AdminLink')){
    function onappVPS_AdminLink($params){
       return '<form target="_blank" action="http'.($params['serversecure']=='on' ? 's' :'').'://'.(empty($params['serverhostname']) ? $params['serverip'] : $params['serverhostname']).'/users/sign_in" method="post">
                  <input type="hidden" name="user[login]" value="'.$params['serverusername'].'" />
                  <input type="hidden" name="user[password]" value="'.$params['serverpassword'].'" />
                  <input type="hidden" name="commit" value="Sign In" />
                  <input type="submit" value="Login to Control Panel" />
               </form>';
    }
}

/**
* FUNCTION onapVPS_ClientArea
* Display clientarea template
* @params array
*/ 
if (!function_exists('onappVPS_ClientArea')){
     function onappVPS_ClientArea($params){	
         global $smarty,$CONFIG;	
         
         $moduledir = substr(dirname(__FILE__), strlen(ROOTDIR)+1);
         $lang    = onapVPS_getLang($params);         
         $product = new onappVPS_Product($params['pid']);
         $vm      = new NewOnApp_VM($params['customfields']['vmid']);
         $vm -> setconnection($params);   
       
         $smarty->assign('lang', $lang['mainsite']);
         $smarty->assign('dir', $moduledir);
         $smarty->assign('params',$params);
        
         if(empty($params['customfields']['vmid'])){
             $smarty->assign('result',1);
             $smarty->assign('resultmsg',$lang['module']['error7']);
             return;
         }
         
         /* AJAX START */
         if(isset($_POST['ajax']) && $_POST['ajax']==1 && isset($_POST['doAction'])){
                $allowed     = array('reboot','rebuild','recovery','stop','start','shutdown','recovery','details','logs','unlock');
                if(!in_array($_POST['doAction'], $allowed))
                        die('Action not supported!');
                $getParams   = array();
                $method      = 'POST';
                $postData    = array();
                switch($_POST['doAction']){
                    case 'recovery':
                        if($product->getConfig('vmware')=='Yes')
                           die('Method not allowed for VMware.');
                        $res = $vm->recovery();
                        break;  
                    case 'reboot':
                        $res = $vm->reboot();
                        break;
                    case 'rebuild':
                        $postData = array('virtual_machine'=>array('template_id'=>$product->getConfig('template_id')));
                        $res = $vm->rebuild($postData);
                        break;
                    case 'stop':
                        $res = $vm->stop();
                        break;
                    case 'start':
                        $res = $vm->start();
                        break;
                    case 'shutdown':
                        $res = $vm->shutdown();
                        break;
                    case 'unlock':
                        $res = $vm->unlock();
                        break;
                    case 'logs':
                            $transactions = $vm->getTransactions(empty($_GET['lp']) ? 0 : (int)$_GET['lp']);
                             if($vm->isSuccess()){
                                 echo json_encode($transactions);
                                 die();
                             }
                        break;
                    case 'details':
                        $res = $vm->getDetails();
                        break;
                }
                
                 if($vm->isSuccess()){
                    $res['virtual_machine']['monthly_bandwidth_used'] = round($res['virtual_machine']['monthly_bandwidth_used']/1024,2);
                    die(json_encode ($res['virtual_machine'])); 
                 }  
                 else
                    die(json_encode (array('error'=>$vm->error())));
                
         }
         /* AJAX END */
         $html5   = $product->getConfig('console');
         if($html5!=1){
            $console = $vm ->getConsoleKey();
            $smarty->assign('console',array(
                                       'url' => 'http'.($params['serversecure']=='on' ? 's' :'').'://'.(empty($params['serverhostname']) ? $params['serverip'] : $params['serverhostname']),
                                       'key' => $console['remote_access_session']['remote_key']
                           ));
         } else {
            $smarty->assign('console',array('html5'=>1));
         }   
         $results = $vm->getDetails();
         $results['virtual_machine']['monthly_bandwidth_used'] = round($results['virtual_machine']['monthly_bandwidth_used']/1024,2);
         if($vm->isSuccess()){
             $smarty->assign('vpsdata',$results['virtual_machine']);
             $transactions = $vm->getTransactions((isset($_GET['lp']) && $_GET['lp']>0 ? (int)$_GET['lp'] : 0));
             if($vm->isSuccess()){
                $smarty->assign('curr_log_page',(isset($_GET['lp']) && $_GET['lp']>0 ? (int)$_GET['lp'] : 0));
                $smarty->assign('logs', $transactions);
             }

         }
         else {
             $smarty->assign('result',1);
             $smarty->assign('resultmsg',$vm->error());
         }
         
         if(isset($_SESSION['msg_status'])){
             $smarty->assign('result','success');
             $smarty->assign('resultmsg',$_SESSION['msg_status']);
             unset($_SESSION['msg_status']);
         }
         
         //OnApp Billing Integration Code
         if(file_exists(ROOTDIR.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'OnAppBilling'.DIRECTORY_SEPARATOR.'core.php'))
         {
             require_once ROOTDIR.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'OnAppBilling'.DIRECTORY_SEPARATOR.'core.php';
             $row = mysql_get_row("SELECT product_id FROM OnAppBilling_settings WHERE product_id = ? AND enable=1", array($params['packageid']));
             if($row)
             {
                 $row = mysql_get_row("SELECT currency FROM tblclients WHERE id = ?", array($_SESSION['uid']));
                 $user_currency = $row['currency'];

                 getCurrency($_SESSION['uid']);
                 
                 $account       =   new OnAppBillingAccount($params['serviceid']);
                 //Get Summay usage
                 $summary       =   $account->getSummaryLines($params['packageid']);

                 $out[] = array();
                 foreach($summary['lines'] as $sum_key => $sum)
                 {
                     $out[$sum_key]['total']            =   formatCurrency(convertCurrency($sum['amount'], 1, $user_currency));
                     $out[$sum_key]['usage']            =   number_format($sum['usage'], 2);
                     $out[$sum_key]['FriendlyName']     =   $sum['name'];
                     $out[$sum_key]['name']             =   isset($sum['partName']) ? $sum['partName'] : '';
                     $out[$sum_key]['unit']             =   $sum['unit'];
                 }
                 
                 if($out)
                 {
                    $smarty->assign('mg_lang', MG_Language::getLang()); 
                    $smarty->assign('billing_resources', $out);
                    $smarty->assign('records_range', array(
                        'start_date'    =>  $summary['startDate'],
                        'end_date'      =>  $summary['endDate'] 
                    ));
                 }
             }
         }
         //End Of OnApp Billing Intergration Code
         
         $smarty->assign('disallow_action',array(
            'firewall'     => $product->getConfig('manage_firewall'),
            'ip'           => $product->getConfig('manage_ip'),
            'network'      => $product->getConfig('manage_network'),
            'stats'        => $product->getConfig('manage_stats'),
            'disk'         => $product->getConfig('manage_disk'),
            'backups'      => $product->getConfig('manage_backups'),
            'autoscalling' => $product->getConfig('manage_autoscalling'),
        ));

     }
}     


/**
* FUNCTION onapVPS_management
* Display extended pages in clientarea
* @params array
* @return array
*/ 
if (!function_exists('onappVPS_management')){
     function onappVPS_management($params){	
         global $CONFIG;	

         $lang              = onapVPS_getLang($params);         
         $moduledir         = substr(dirname(__FILE__), strlen(ROOTDIR)+1);
         $product           = new onappVPS_Product($params['pid']);
         $vm                = new NewOnApp_VM($params['customfields']['vmid']);
         $vm                -> setconnection($params);   
 
         $page              = (isset($_GET['page']) ? preg_replace('/[^A-Za-z0-9]/','',$_GET['page']) : 'mainsite');
         $vars['main_lang'] = $lang['mainsite'];
         $vars['lang']      = $lang[(empty($page)? 'mainsite' : $page)];
         $vars['dir']       = $moduledir;
         $vars['hostname']  = (!empty($params['serverhostname']) ? $params['serverhostname'] : $params['serverip']);
         $vars['params']    = $params;
         $vars['main_dir']  = dirname(__FILE__);

         if(empty($page) || !file_exists(dirname(__FILE__).DS.$page.'.php') || !file_exists(dirname(__FILE__).DS.'templates'.DS.$page.'.tpl')){
                 $vars['lang']      = $lang['mainsite'];
             if(empty($params['customfields']['vmid']))
                 $vars['resultmsg'] = $lang['module']['error7'];
             else
                 $vars['resultmsg'] = $lang['module']['error5'];
             
             return array('vars'         => $vars);
         }        

         $vars['disallow_action'] = array(
            'firewall'     => $product->getConfig('manage_firewall'),
            'ip'           => $product->getConfig('manage_ip'),
            'network'      => $product->getConfig('manage_network'),
            'stats'        => $product->getConfig('manage_stats'),
            'disk'         => $product->getConfig('manage_disk'),
            'backups'      => $product->getConfig('manage_backups'),
            'autoscalling' => $product->getConfig('manage_autoscalling'),
         );
         
         require_once(dirname(__FILE__).DS.$page.'.php');
         
         if(isset($_SESSION['msg_status'])){
             $vars['result']    = 'success';
             $vars['resultmsg'] = $_SESSION['msg_status'];
             unset($_SESSION['msg_status']);
         }
         
         //OnApp Billing Integration Code
         if(file_exists(ROOTDIR.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'OnAppBilling'.DIRECTORY_SEPARATOR.'core.php'))
         {
             require_once ROOTDIR.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'OnAppBilling'.DIRECTORY_SEPARATOR.'core.php';
             $row = mysql_get_row("SELECT product_id FROM OnAppBilling_settings WHERE product_id = ? AND enable=1", array($params['packageid']));
             if($row)
             {
                 $row = mysql_get_row("SELECT currency FROM tblclients WHERE id = ?", array($_SESSION['uid']));
                 $user_currency = $row['currency'];

                 getCurrency($_SESSION['uid']);
                 
                 $account       =   new OnAppBillingAccount($params['serviceid']);
                 //Get Summay usage
                 $summary       =   $account->getSummaryLines($params['packageid']);

                 $out[] = array();
                 foreach($summary['lines'] as $sum_key => $sum)
                 {
                     $out[$sum_key]['total']            =   formatCurrency(convertCurrency($sum['amount'], 1, $user_currency));
                     $out[$sum_key]['usage']            =   number_format($sum['usage'], 2);
                     $out[$sum_key]['FriendlyName']     =   $sum['name'];
                     $out[$sum_key]['name']             =   isset($sum['partName']) ? $sum['partName'] : '';
                     $out[$sum_key]['unit']             =   $sum['unit'];
                 }
                 
                 if($out)
                 {
                    $vars['mg_lang']            =   $out; 
                    $vars['billing_resources']  =   $out;
                    $vars['records_range']      =   array(
                        'start_date'    =>  $summary['startDate'],
                        'end_date'      =>  $summary['endDate'] 
                    );
                 }
             }
         }
         //End Of OnApp Billing Intergration Code       
         
         return array(
                   'templatefile' => 'templates/'.$page,
                   'breadcrumb'   => ' > <a href="#">Server Details</a>',
                   'vars'         => $vars
         );
     }
}


