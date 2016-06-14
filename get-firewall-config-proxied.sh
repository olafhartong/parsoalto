#!/bin/sh
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#################################################################
# Author Olaf Hartong (splitbrains.eu)
# 
# Palo Alto Networks firewall rules grabber
#
# Multiple entries supported, separate by <space>
#################################################################
# MODIFY THESE SETTINGS 
# API key
key=$(cat keyfile)
# Firewall IPs
fw=$(cat firewallip)
# File prefix per prefixiroment
prefix='firewall'
# Virtual system(s)
vsys='vsys1 vsys2 vsys3 vsys4'
# Location to store the configs
location='configs/'
#################################################################

year=`date +%Y`
month=`date +%m`
day=`date +%d`
hour=`date +%H`
min=`date +%M`
sec=`date +%S`
timestamp=${year}${month}${day}${hour}
config='rulebase/security address address-group application-group service-group'

if [ ! -f 'keyfile' -o ! -f 'firewallip' ];
then
        echo 'Unable to find the files needed to continue.'
        exit 2
fi
echo "==================================================="
echo "                  getting configs"
echo "==================================================="
  for firewall in $fw; do
    for host in $vsys; do
     for url in $config; do
      fileurl=`echo $url | sed 's@/@-@g'`
      ipfilename=`echo $firewall | sed 's/\.//g'`
      echo "Fetching $firewall/$host/$url"
      curl -s -k --socks5 localhost:8080 "https://$firewall/api/?type=config&action=get&key=$key&xpath=/config/devices/entry\[@name='localhost.localdomain'\]/vsys/entry\[@name='$host'\]/$url" | xmllint --format --recover - > $location$prefix-$ipfilename-$host.$fileurl.$timestamp.xml
    done
  done
 echo "Fetching $firewall/$host/unused-rules"
 curl -s -k --socks5 localhost:8080 "https://$firewall/api/?type=op&cmd=<show><running><rule-use><rule-base>security</rule-base><type>unused</type><vsys>$vsys</vsys></rule-use></running></show>&key=$key" | xmllint --format --recover - > $location$prefix-$ipfilename-$host.unused.$timestamp.xml
 done
echo "==================================================="
echo "                     all done"
echo "==================================================="
