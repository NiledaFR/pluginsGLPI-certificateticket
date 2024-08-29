This plugin add an automatic task that create a ticket when a certificate soon expire

To activate it:
1 - Download the zip package and uncompress it in the GLPI plugins directory (or do a "git clone https://github.com/NiledaFR/pluginsGLPI-certificateticket.git certificateticket" in the GLPi Plugins directory)
2 - Go to your GLPi instance and install then activate the Certificate Ticket plugin

When activated, a new automatic task is created, named certificateticket. This task will check if some certificates as a notification alert then create a ticket if yes. The certificate responsibles group and user are linked to the ticket as assigned, and the certificate group is linked as an observer

ToDo: 
- give the possibility to attribute a default requester
- give the possibility to configure a ticket template
