#!/bin/sh
if [ $# -ne 4 ]; then
  echo "useage: $0 download_url target_path artisan video_id!"
  exit
fi
DOWNLOAD_URL=$1
TARGET_PATH=$2
ARTISAN=$3
VID=$4
DATETIME=`date "+%Y_%m_%d"`

wget -c -O $TARGET_PATH $DOWNLOAD_URL -a log_$DATETIME.log
if [ $? -eq 0 ]; then
	DOWNLOAD_STATUS=1
else
	DOWNLOAD_STATUS=0
fi
php $ARTISAN video:download_check $VID $DOWNLOAD_STATUS

exit