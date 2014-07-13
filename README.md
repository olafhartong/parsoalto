parsoalto
=========

Palo Alto Networks Rule Parser

This toolset generates human readable ip - ip rules in csv (Note: it does it in memory so reserve some)
Requirements:
 - xmllint 
 - PHP 5.x
 - curl
 - Config files
 - A Generated api key
 - A Palo Alto firewall and/or Panorama ;)

Usage:

Generate an api key by going to the firewall:
 https://FIREWALL/api?type=keygen&user=USERNAME&password=PASSWORD

Create the file `keyfile` in the root directory and paste the generated key in this file.

Create the following files also: `panorama`, `vsys` and `firewall`

run `./get-firewall-configs.sh` or `./get-panorama-configs.sh`

run `./make-csv.sh`

done!
