parsoalto
=========

Palo Alto Networks Rule Parser

This toolset generates human readable ip - ip rules in csv

Requirements:
 - xmllint 
 - PHP 5.x
 - curl
 - Config files
 - A Generated api key
 - A Palo Alto firewall and/or Panorama ;)

Usage:

Generate an api key by going to the firewall > https://FIREWALL/api?type=keygen&user=USERNAME&password=PASSWORD
Create a keyfile in the dir and paste the generated key into it.

Create a panorama, vsys and firewall file.

Create a configs dir


run get-firewall-configs.sh or get-firewall-configs.sh

run make-csv

done!
