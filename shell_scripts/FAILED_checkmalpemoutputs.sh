#!/bin/bash


DIR="/var/www/html/joomla/malpem_outputs"
TIMEFMT="%Y-%m-%d %H:%M:%S"
FMT="%f %T"
#$FMT = "%f %T"
#%f :
#When an event occurs within a directory, this will be replaced with the name of the File 
#which caused the event to occur. Otherwise, this will be replaced with an empty string.
#%T :
#Replaced with the current Time in the format specified by the --timefmt option
TIME=""
FILENAME=""
#IFS= read -r line; to read whitespaces and special chars
inotifywait -e modify,moved_to,create --timefmt "$TIMEFMT" --format "$FMT" $DIR | while IFS= read -r LINE;  
do
  echo $(LINE);
done