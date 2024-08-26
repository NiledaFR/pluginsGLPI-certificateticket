<?php


/**
 * -------------------------------------------------------------------------
 * Certificate Ticket plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * This Plugin was developped to add the functionnality to create ticket when certificate will expire
 */

//We load the needed classes
use CommonDBTM;
use Certificates;

// Class of the defined type
class CertificateTicket extends CommonDBTM {

   // Should return the localized name of the type
   static function getTypeName($nb = 0) {
      return 'CertificateTicket';
   }

   /**
    * Give localized information about 1 task
    *
    * @param $name of the task
    *
    * @return array of strings
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'CertificateTicket' :
            return ['description' => ('Cron description for certificateticket'),
                    'parameter'   => ('Cron parameter for certificateticket')];
      }
      return [];
   }


   /**
    *
    * The function that will check expiry then create ticket if needed
    * 
    */
   static function cronCertificateTicket($task) {
        global $CFG_GLPI, $DB;

        $errors = 0;
        $total = 0;

        // We check all entities having the notification activated for certificates
        foreach (array_keys(Entity::getEntitiesToNotify('use_certificates_alert')) as $entity) {

            $before = Entity::getUsedConfig('send_certificates_alert_before_delay', $entity);
            $repeat = Entity::getUsedConfig('certificates_alert_repeat_interval', $entity);

            $iterator = $DB->request(
                [
                    'SELECT'    => [
                        'glpi_certificates.id','glpi_certificates.date_expiration','glpi_certificates.users_id_tech','glpi_certificates.groups_id_tech','glpi_plugin_certificate_ticket.date'
                    ],
                    'FROM'      => 'glpi_certificates',
                    'LEFT JOIN' => [
                        'glpi_plugin_certificate_ticket' => [
                            'FKEY'   => [
                                'glpi_plugin_certificate_ticket'       => 'certificate_id',
                                'glpi_certificates' => 'id',
                              ]
                        ]
                    ],
                    'WHERE'     => [
                        'glpi_certificates.is_deleted'  => 0,
                        'glpi_certificates.is_template' => 0,
                        [
                            'NOT' => ['glpi_certificates.date_expiration' => null],
                        ],
                        [
                            'RAW' => [
                                'DATEDIFF(' . DBmysql::quoteName('glpi_certificates.date_expiration') . ', CURDATE())' => ['<', $before]
                            ]
                        ],
                        'glpi_certificates.entities_id' => $entity,
                    ],
                ]
            );

            // We parse all certificate that'll expire soon
            foreach ($iterator as $certificate_data) {

                $certificate_id = $certificate_data['id'];
                $certificate = new Certificate();
                if (!$certificate->getFromDB($certificate_id)) {
                    $errors++;
                    trigger_error(sprintf('Unable to load Certificate "%s".', $certificate_id), E_USER_WARNING);
                    continue;
                }

	        // ticket name preparation
	        $tktname = "Certificate ".$certificate->fields['name'] . (!empty($certificate->fields['serial']) ? ' - ' . $certificate->fields['serial'] : '')." expired on ".Html::convDate($certificate->fields['date_expiration']);
                
                // ticket options preparation
                $tkt = [];
	        $tkt['entities_id'] = $entity;
		$task->log($certificate_data['date_expiration']." == ".$certificate_data['date']);
                $tkt['name'] = $tktname;
                $tkt['content'] = "Certificate will soon expire or is expired, please correct this !";
                $tkt['_users_id_assign'] = $certificate_data['users_id_tech'];
	        $tkt['_groups_id_observer'] = $certificate_data['groups_id_tech'];
                    
                $ticket = new Ticket();
                if(!$certificate_data['date']){
                    $task->addVolume(1);
                    $total++;
                    $ticket_id = $ticket->add($tkt);

		    $query = "INSERT INTO `glpi_plugin_certificate_ticket` (`certificate_id`, `ticket_id`, `date`) VALUES (".$certificate_data['id'].",$ticket_id,'".$certificate_data['date_expiration']."')";
    	            $DB->query($query) or die("error populate glpi_plugin_example ". $DB->error());
                }elseif($certificate_data['date_expiration'] !== $certificate_data['date']){
                    $task->addVolume(1);
                    $total++;
                    $ticket_id = $ticket->add($tkt);

		    $query = "UPDATE `glpi_plugin_certificate_ticket` SET date='".$certificate_data['date_expiration']."' WHERE certificate_id=".$certificate_data['id'];
    	            $DB->query($query) or die("error populate glpi_plugin_example ". $DB->error());
		}
            }
        }
        $task->log($ticket_id);
        return $errors > 0 ? -1 : ($total > 0 ? 1 : 0);
   }
}
