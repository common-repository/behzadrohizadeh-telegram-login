<?php 
namespace wp\telegram\login\Tg_Login_Register ; 

/**
* Plugin Name: Tg Login And Register
* Plugin URI: http://linkyab.net
* Description: A plugin for login And Register with telegram messanger Account
* Version: 1.3.0
* Author: Behzad Rohizadeh
* Author URI: http://linkyab.net/contact-us/
*
 * @package Telegram
 * @category Wordpress
 * @author Behzad Rohizadeh
*
*/
define( 'PLUGIN_PATH', plugins_url( __FILE__ ) ); 
/*
*
*check for correct directory
*/
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tg_Login_Register' ) ) :
/**
* 
*define Cunter  class
* @version 1.2
*/
class Tg_Login_Register 
{
/**
 * WooCommerce version.
 *
 * @var string
*/
  public $version = '1.3.0';
/**
*
 *@var string
*/

  function __construct()
  {
    add_action('init',array(&$this, 'tglr_BehzadRohizadeh_localization_init'));
    register_activation_hook( __FILE__,array(__CLASS__, 'tglr_BehzadRohizadeh_activate_pliugin' ));
    add_shortcode('lg-telegram', array(&$this,'tglr_BehzadRohizadeh_Login_Telegram'));
    add_action('admin_menu',array(&$this, 'tglr_login_BehzadRohizadeh'));
    add_action('init',array(&$this, 'tglr_login_callback'));
    add_filter( 'get_avatar' , array(&$this,'tglr_avatar_BehzadRohizadeh_tg') , 1 , 5 );
    add_filter( 'manage_users_columns', array(&$this,'tglr_manage_users_course_section' ));
    add_filter('manage_users_custom_column',array(&$this,'tglr_modify_user_column_content'),10,3);
  }
  function tglr_login_BehzadRohizadeh ()
  {
   add_menu_page(__('Telegram Login', 'tg' ),__('Telegram Login', 'tg' ), 'administrator', 'BehzadRohizadeh_tg_lg', array(&$this,'tglr_BehzadRohizadeh_Telegram_Login_Setting'),"dashicons-unlock");   
  }
  function tglr_BehzadRohizadeh_localization_init()
  {

    $path = dirname(plugin_basename( __FILE__ )) . '/lang/';
    $loaded = load_plugin_textdomain( 'tg', false, $path);
  }
function tglr_avatar_BehzadRohizadeh_tg( $avatar, $id_or_email, $size, $default, $alt ) {    
  if (is_user_logged_in())
  {  
      $im=get_user_meta( get_current_user_id(), 'avatar_tg',true );
      if(!empty($im))
        {
          $avatar = '<img src="'.$im.'" alt="avatar" height="'.$size.'" width="'.$size.'" class="avatar avatar-'.$size.' photo" />';
        }

   return $avatar;
  }
}
  function tglr_manage_users_course_section($columns ) {
    $columns['type_register'] = __("Type Register","tg");
    return $columns;
}
function tglr_modify_user_column_content($val,$column_name,$user_id) {
    $user = get_userdata($user_id);
    switch ($column_name) {
        case 'type_register':
            $tg=get_user_meta( $user_id, 'user_tg',true );
            if(!empty($tg)) 
            {
              return __("Telegram","tg");
            }else
            {
              return __("Website","tg"); 
            }
            break;
        default:
        break;
    }
    return $return;
}
  function tglr_login_callback()
  {
    if (!is_user_logged_in() && @isset($_GET["hash"])){
   
      if($tg_user=$this->tglr_checkTelegramAuthorization($_GET))
      {
     
       $first_name = htmlspecialchars($tg_user['first_name']);
       $last_name = htmlspecialchars($tg_user['last_name']); 
       $user_name=  htmlspecialchars($tg_user['id']);
       $avatar= "";
       $passwordbef=$user_name;
       $password=$user_name.uniqid();
       if (isset($tg_user['username'])) 
       {
        $user_name=$tg_user['username'];
       }

        $role_user=get_option( "role_user" ,true);
          $userdata = array(
                        "user_login" =>  $user_name,
                        "user_pass" => $password,
                        "display_name" =>$first_name." ".$last_name,
                        "first_name"=>$first_name,
                        "last_name"=> $last_name,
                        "role"=>$role_user,
          );
        $user = wp_insert_user( $userdata );
        if($user){
          update_user_meta( $user, 'tg_pass',$password );
          update_user_meta( $user, 'user_tg', $user_name );
          if ( class_exists( 'WooCommerce' ) ) 
          {
          update_user_meta( $user, 'shipping_last_name', $last_name );
          update_user_meta( $user, 'shipping_first_name', $first_name );
          update_user_meta( $user, 'billing_last_name', $last_name );
          update_user_meta( $user, 'billing_first_name', $first_name );
        }
           if (isset($tg_user['photo_url'])) {
              $avatar=$tg_user['photo_url'] ;
              update_user_meta( $user, 'avatar_tg', $avatar );
           }
           $userex = get_userdatabylogin($user_name);
          if(isset($userex->ID)) 
            {
              $password=get_user_meta( $userex->ID, 'tg_pass',true );;
            }elseif (!isset($userex->ID)) {
              $password=$passwordbef;
            } 

               global $user;
                $creds = array();
                $creds['user_login'] = $user_name;
                $creds['user_password'] =  $password;
                $s=wp_signon( $creds, false );

                $redirect_url=get_option( "tg_redirect_url" ,true);
                wp_redirect($redirect_url);
                exit();
              }else
              {
              $redirect_url=get_option( "tg_redirect_url" ,true);
              wp_redirect($redirect_url);
              exit();
              }
        } 
     }    
  }
  function tglr_BehzadRohizadeh_Telegram_Login_Setting()
  {
   
    if( current_user_can('administrator')) {

      if(isset($_POST["tg_lg"])) 
      {
       if(isset($_POST["Show_User_Photo"])) 
      {
          update_option( "Show_User_Photo", "yes", false );     
      }elseif(!isset($_POST["Show_User_Photo"]))
      {
          update_option( "Show_User_Photo", "no", false );      
      }   
        
        update_option( "bot_name", sanitize_text_field($_POST["bot_name"]), false );      
        update_option( "s_button", sanitize_text_field($_POST["s_button"]), false );
        update_option( "bot_token", sanitize_text_field($_POST["bot_token"]), false );
        update_option( "role_user", sanitize_text_field($_POST["role_user"]), false );
        update_option( "tg_redirect_url", sanitize_text_field($_POST["redirect_url"]), false );
      }
        $bot_name=get_option( "bot_name" ,true);
        $s_button=get_option( "s_button" ,true);
        $Show_User_Photo=get_option( "Show_User_Photo" ,true);
        $bot_token=get_option( "bot_token" ,true);
        $role_user=get_option( "role_user" ,true);
        $redirect_url=get_option( "tg_redirect_url" ,true);

      ?>
      <table class="form-table">
            <form id="form" action="" method="POST">
              <tr class="top">
                <th class="titledesc" scope="row"><?php echo __('Bot Username', 'tg' )?>  </th>
                <td>
                  Bot Username (Without @)
                  <input type="text" placeholder="Username" name="bot_name" value="<?php echo $bot_name; ?>"; size="60" />
                </td>
              </tr>
              <tr class="top">
                <th class="titledesc" scope="row"><?php echo __('Bot Token', 'tg' )?>  </th>
                <td>
                  <input type="text" placeholder="Token" name="bot_token" value="<?php echo $bot_token; ?>"; size="60"/>
                </td>
              </tr>
              <tr class="top">
                <th class="titledesc" scope="row"><?php echo __('Size Button', 'tg' )?>  </th>
                <td>
                  Small <input <?php if($s_button=="small"){echo "checked";} ?> type="radio" value="small" name="s_button"    >
                            Medium <input <?php if($s_button=="medium"){echo "checked";} ?> type="radio" value="medium" name="s_button"    >
                            Large <input <?php if($s_button=="large"){echo "checked";} ?> type="radio" value="large" name="s_button"    >
                </td>
              </tr>
              <tr class="top">
                <th class="titledesc" scope="row"><?php echo __('Show User Photo', 'tg' )?>  </th>
                <td>
                  Show User Photo <input <?php if($Show_User_Photo=="yes"){echo "checked";} ?> type="checkbox" value="show" name="Show_User_Photo"    >
                </td>
              </tr>
              <tr class="top">
                <th class="titledesc" scope="row"><?php echo __('User Role When Login', 'tg' )?>  </th>
                <td>
                <select  name="role_user">
                  <?php foreach (get_editable_roles() as $role_name => $role_info){ 
                   $se= "";
                   if($role_user==$role_name)
                    {
                     $se="selected";
                     }
                  ?>
                    <option <?php echo $se; ?> value="<?php echo $role_name; ?>"><?php echo $role_name; ?></option>
                  <?php } ?>
                 </select>
                </td>
              </tr>
              <tr class="top">
                <th class="titledesc" scope="row"><?php echo __('Redirect Url After Login', 'tg' )?>  </th>
                <td>
                <input type="url" placeholder="Url" name="redirect_url" value="<?php echo $redirect_url; ?>"; size="60"/>
                </td>
               </tr>
               <tr class="top">
                  <td>
                    <input class="button button-primary"  type="submit" value="<?php echo __('Update', 'tg' )?>" name="tg_lg">
                  </td>
               </tr>
           </form>
         </table>   
              <hr> 
              <p>
                <h3><?php echo __('Steps Configration', 'tg' )?></h3> 
                 1- <?php echo __('Create New Bot By @Botfather', 'tg' )?> 
                 <a href="https://t.me/botfather">@Botfather</a>
              </p>
              <p>
                 2- <?php echo __('send the /setdomain command to @Botfather to link your website,s domain to the bot. Then configure your widget by on top options.', 'tg' ) ; ?> 
              </p>
              <p>
                 3- <?php echo __('Use Shortcode [lg-telegram] For Show Button', 'tg' ) ; ?>
              </p>
      
    <?php   
          }
        }

  function tglr_BehzadRohizadeh_Login_Telegram () 
  {
    $bot_name=get_option( "bot_name" ,true);
    $s_button=get_option( "s_button" ,true);
    $data_user_pic="false";
    $Show_User_Photo=get_option( "Show_User_Photo" ,true); 
    if($s_button!="large" || $s_button!="medium" || $s_button!="large")
    {
      $s_button="large" ; 
    }
    if($Show_User_Photo=="yes")
    {
      $data_user_pic="true";   
    }
    if($Show_User_Photo=="no")
    {
      $data_user_pic="false";   
    }
    
    $GUID= get_page_link(get_the_ID());
    
    if (!is_user_logged_in()) 
      {

      $html = <<<HTML
           <script async src="https://telegram.org/js/telegram-widget.js?2" data-telegram-login="{$bot_name}" data-userpic="{$data_user_pic}" data-size="{$s_button}" data-auth-url="{$GUID}"></script>
HTML;
}
  return <<<HTML
  {$html}
HTML;

  }


function tglr_checkTelegramAuthorization($auth_data) {
 $bot_token=get_option( "bot_token" ,true);
  $check_hash = $auth_data['hash'];
  unset($auth_data['hash']);
  $data_check_arr = [];
  foreach ($auth_data as $key => $value) {
    $data_check_arr[] = $key . '=' . $value;
  }
  sort($data_check_arr);
  $data_check_string = implode("\n", $data_check_arr);
  $secret_key = hash('sha256', $bot_token, true);
  $hash = hash_hmac('sha256', $data_check_string, $secret_key);
  if (strcmp($hash, $check_hash) !== 0) {
    return false ; 
  }
  if ((time() - $auth_data['auth_date']) > 86400) {
   return false; 
  }
  return $auth_data;
}
function tglr_BehzadRohizadeh_activate_pliugin()
{
    
    update_option( "s_button", "large", false );
    update_option( "tg_redirect_url", home_url(), false );
}
  
} new Tg_Login_Register();
endif;?>