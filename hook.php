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
use Dropdown as GlpiDropdown;


/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_certificateticket_install() {

   //We get the global db acces
   global $DB;

   /**
   * May be used later
   */
   //$migration = new Migration(PLUGIN_CERTIFICATETICKET_VERSION);
   //Config::setConfigurationValues('CertificateTicket', ['configuration' => false]);

   //Used to configure the good parameters of the DB
   $default_charset = DBConnection::getDefaultCharset();
   $default_collation = DBConnection::getDefaultCollation();
   $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

   // We create a table that'll stock if ticket was already created, to create only 1 ticket
   if (!$DB->tableExists("glpi_plugin_certificate_ticket")) {
      $query = "CREATE TABLE `glpi_plugin_certificate_ticket` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `certificate_id` int,
                  `ticket_id` int,
                  `date` date,
                PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
      $DB->query($query) or die("error creating glpi_plugin_certificate_ticket ". $DB->error());
   }



   // To be called for each task the plugin manage
   // task in class
   CronTask::Register(CertificateTicket::class, 'CertificateTicket', DAY_TIMESTAMP, ['param' => 50]);
   return true;
}


/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_certificateticket_uninstall() {
   global $DB;

   // We drop the table when we uninstall the plugin
   if ($DB->tableExists("glpi_plugin_certificate_ticket")) {
      $query = "DROP TABLE `glpi_plugin_certificate_ticket`";
      $DB->query($query) or die("error deleting glpi_dropdown_plugin_example");
   }

   // May be used later
   //$config = new Config();
   //$config->deleteConfigurationValues('CertificateTicket', ['configuration' => false]);

   // We notify what is done when uninstall
   $notif = new Notification();
   $options = ['itemtype' => 'Ticket',
               'event'    => 'plugin_certificate_ticket',
               'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   return true;
}


// Nothing to do here
function plugin_certificateticket_postinit() {
   global $CFG_GLPI;

}


