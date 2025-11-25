<?php
date_default_timezone_set('Asia/Jakarta');
 
// Buat RSA Key 1024 bit atau 2048 bit di Linux/FreeBSD 
// $ openssl genrsa 1024
// $ openssl genrsa 2048
$key = "MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAOiIPIUPZMM3t5Er
PsKFVxSvxawEj0Z3P7r4NwlVM4vfrvNPTrJZ1Z4xD7mhghG8ESxeLAOzcWKsLGJL
wZ2ilSnyb8trGajkB60iTHzXFnT5mGNYf/lHDSSTUaUdSZBcISaTIFkevoEE9RYJ
zIa6yxB+KB9aR6N6n2wojnOTL62rAgMBAAECgYEA0ihnAh9Cg0k1B+fcxfO7G0At
WGWalCYsnC+/lhjCCJW2ScKQrpKQhXNz8eKtapfdsYq/Hu0r+fEAACrVp0GMqu6j
E1AcjQ3GlFPJZGhNBRsppP/gLkPrOuuA8LqYyTSroKcAXx1Q9pCS5KKlFyuR24cX
2b5ky/mvc3loj7ItqpECQQD0UPwqrI2HfchOU7qwcliURrh2Cg7MrCD7mFgHse/8
RarL9TekcyFqX0z1xTIaQ4oT03cnSgkfmPHGq/ABSbm5AkEA86b8JxqUqtfPwIcp
LEQ7nhg9Vdz5kOCh2bR8Po33rrN8WryLx7gwxIY/Kub0brNCc38DkQX8dKPr4Vrv
TGTEgwJBALAVlkkjaDXY//8D9a+qGhF0Dwp0IUp+XJ84o2mYY0DM7VZmfB34JINI
AG26HGw1dVTouh4FDCJL1yW7UhV7j3ECQQDcsALz6D9//rlNyR6h2aumJo5nlx70
+oGejrt6bVxKAIhCJ0T0QQDrAC12znnXSsaFliXE1Yc0nsSn+ZWFvD/BAkB1SbvV
fYmQg6RuFTSAFUFmmv4pK0U9C3zLZIOKpeWWI05Z3sUQTL2fe/+5ZuXegFYAMP3C
0i/9zxOpmFCt+0ZY";
$issued_at = time();
$expiration_time = $issued_at+(60*60); // valid selama 1 jam
$issuer = "RestApiAuthJWT";
?>