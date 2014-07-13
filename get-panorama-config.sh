#!/bin/sh
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#################################################################
# Author Olaf Hartong (splitbrains.eu)
# 
# Palo Alto Networks firewall rules grabber
#
# Multiple entries supported, separate by <space>
#################################################################
# CREATE/MODIFY THESE FILES AND SETTINGS
# API key
key=$(cat keyfile)
# Panorama IP
panorama=$(cat panoramaip)
# Panorama or enviroment name
prefix='jubit2'
# Virtual systems
vsys=$(cat vsys)
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
config='pre-rulebase/security address address-group application-group service-group'
if [ ! -f 'keyfile' -o ! -f 'panoramaip' -o ! -f 'vsys' ];
then
        echo 'Unable to find the files needed to continue.'
        exit 2
fi
echo "==================================================="
echo "                  getting configs"
echo "==================================================="
   for host in $vsys; do
    for url in $config; do
      fileurl=`echo $url | sed 's@/@-@g'`
      echo "Fetching $host/$url"
      curl -s -k "https://$panorama/api/?type=config&action=get&key=$key&xpath=/config/devices/entry\[@name='localhost.localdomain'\]/device-group/entry\[@name='$host'\]/$url" | xmllint --format --recover -  > $location$host.$fileurl.$timestamp.xml
    done
  done
  echo "Fetching $panorama/shared/address"
  curl -s -k "https://$panorama/api/?type=config&action=get&key=$key&xpath=/config/shared/address" | xmllint --format --recover - > $location$prefix.shared.address.$timestamp.xml
  echo "Fetching $panorama/shared/address-group"
  curl -s -k "https://$panorama/api/?type=config&action=get&key=$key&xpath=/config/shared/address-group" | xmllint --format --recover - > $location$prefix.shared.address-group.$timestamp.xml

echo "==================================================="
echo "                     all done"
echo "==================================================="
                                                                                                    
