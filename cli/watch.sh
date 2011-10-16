#!/bin/bash

# Recursively watches a directory for new files
# Run this in the background after a crawl to keep
# The index current

if [ -z $1 ]; then
	echo "$0 : recursively watch directories and index new files" >&2
	echo "Usage: $0 <directory> ..." >&2
	echo "To be used after an inital crawl" >&2
	exit
fi

if ! which inotifywait > /dev/null; then
	echo "inotify-tools required" >&2
	exit
fi

# REQUIRES inotify-tools for inotifywait
nice -n 3 inotifywait -q -m --format '%w%f' -e moved_to -e close_write -e create -r "$@" | while read
do
	"`dirname $0`"/add.sh "$REPLY"
done
