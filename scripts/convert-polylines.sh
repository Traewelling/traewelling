#!/bin/sh

while true
do
  echo "run!"
  sudo -u www-data php ../artisan app:polylines-to-files
  echo "sleep"
  sleep 15
done
