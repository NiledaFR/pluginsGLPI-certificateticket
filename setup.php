<?php

/**
 * -------------------------------------------------------------------------
 * Certificate Ticket plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * This Plugin was developped to add the functionnality to create ticket when certificate will expire
 */
require_once(__DIR__."/src/CertificateTicket.php");
require_once(__DIR__."/src/Config.php");
use Glpi\Plugin\Hooks;


define('PLUGIN_CERTIFICATETICKET_VERSION', '0.0.1');

// Minimal GLPI version, inclusive
define('PLUGIN_CERTIFICATETICKET_MIN_GLPI', '10.0.0');
// Maximum GLPI version, exclusive
define('PLUGIN_CERTIFICATETICKET_MAX_GLPI', '10.0.99');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_certificateticket() {
   global $PLUGIN_HOOKS,$CFG_GLPI;


/**
*
* Config class created to add in the future the possibility to customize the creation
* As of today, not used
*
*/
//   Plugin::registerClass(Config::class, ['addtabon' => 'Config']);

/**
*
* Register the class that will create the ticket with an automated action
* 
*
*/
   Plugin::registerClass(CertificateTicket::class,
                         ['notificationtemplates_types' => true]);

   if (version_compare(GLPI_VERSION, '9.1', 'ge')) {
      if (class_exists(Example::class)) {
         Link::registerTag(Example::$tags);
      }
   }

   // Config page
/*   if (Session::haveRight('config', UPDATE)) {
*      $PLUGIN_HOOKS['config_page']['certificateticket'] = 'front/config.php';
*   }
*/


   $PLUGIN_HOOKS[Hooks::POST_INIT]['certificateticket'] = 'plugin_certificateticket_postinit';

   $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['certificateticket'] = true;

}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_certificateticket() {
   return [
      'name'           => 'Certificate Ticket',
      'version'        => PLUGIN_CERTIFICATETICKET_VERSION,
      'author'         => 'ADZ',
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_CERTIFICATETICKET_MIN_GLPI,
            'max' => PLUGIN_CERTIFICATETICKET_MAX_GLPI,
         ]
      ]
   ];
}


/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_certificateticket_check_prerequisites() {
   if (false) {
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_certificateticket_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'example');
   }
   return false;
}
