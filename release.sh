#!/bin/sh

filename="komtetkassa.zip";
rm -f $filename;
zip -r $filename includes komtetkassa.php uninstall.php README.md LICENSE;
