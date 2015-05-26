#!/bin/bash
#script to start radio at boot up time if mpd is running
mpds='/etc/init.d/mpd status'
startRadio='sudo -u www-data php /var/www/radio/php/switchStation0Standard.php'
$mpds > /dev/null 
result=$?
#wait until mpd is running
while [ $result -ne 0 ]
do
  sleep 5
  $mpds > /dev/null
  result=$?  
done
$startRadio 



