#!/bin/bash

do_it () {
   	echo 'cron run'
   	# Drush script comes here 
	drush --root=/var/www eca:trigger:custom_event query
}

do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
do_it
sleep 5
