parsoalto
=========

Palo Alto Networks Rule Parser

This toolset generates human readable ip - ip rules in csv (Note: it does it in memory so reserve some)

It also generates a csv file with all rules that are unused on firewalls. 

Panorama is not able to output unused rules so generating used rules for panorama configs.

Note! 
---
Firewalls managed by a panorama need to be polled through panorama, otherwise your results will be useless

Requirements:
---
 - xmllint 
 - PHP 5.x
 - curl
 - Config files
 - A Generated api key
 - A Palo Alto firewall and/or Panorama ;)

Usage:
---
Generate an api key by going to the firewall:
 `https://FIREWALL/api?type=keygen&user=USERNAME&password=PASSWORD`

Create the file `keyfile` in the root directory and paste the generated key in this file.

And create the following files: `panorama`, `vsys` and `firewall`
 - panorama, add the panorama IP
 - vsys, add the device-groups available on panorama
 - firewall, add the IPs of the firewalls you want to poke

run `./get-firewall-configs.sh` or `./get-panorama-configs.sh`

run `./make-csv.sh`

done!
