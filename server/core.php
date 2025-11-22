<?php
date_default_timezone_set('Asia/Jakarta');
 
// Buat RSA Key 1024 bit atau 2048 bit di Linux/FreeBSD 
// $ openssl genrsa 1024
// $ openssl genrsa 2048
$key = "MIICWwIBAAKBgQCZDcDCKRS/ml0muH29w+1vgegJpK3eUrA0yx1KU1vSLABIQwoiNaLWv7RwCagdL+wIUyllvoWAOYr+Twr+tjy3jiWY2xLLA3+8g8+kryylf4o3gh5JVycxAe3VsLWmYZUMHE4CG2ZMKiryup4ZseoL8o8pLtS/BNGl+V3MCM1SKQIDAQABAoGAAsR15MzbXC+NWaLiWykMxQRjTrFUl32FRB8cE3j4Yw96ndPgfgfcPufOemwiRwzTxr7CM93DCjOAKOMC/uIKrPvTa8lOeQa1jGP6MeVPazcWpT5XHbkunfF+jVIAzSvsnf3m7W+PLp1KikgiGFyvnOjR5Sdfdn/uZr79zHGuAwECQQDHdLqxSUJfecUeam3LNTjas6pTINLqLByEQxR6IqTltvxzm26JCkV5pNm50occk4FVVAJ6yaBOqMelMmi9UU/JAkEAxHFwC3yNyUXkDjUQ+bfMBW9ugoR4Tjr/ukP2dB4YArfXYMaYOCPR4ASR1ie2RrUJHdA4XDNUazeg4+4n/47fYQJBAMYhCTcEy97lqk7NcCU0yDZP1Ljg2ULu8KDdtaChe2YJQHtiggm1X1A31mQFYlublxT478GjOhAJDtDl4y90bykCQBjJ78ejSgkSBrs8Ow4oAVjWPO2/ZacJjuekV99DROhi5ozRwrei3YMVUInjrP6zLZlTgykvWQHGnUjl7qozD0ECP2z9qx2S7YC/2EbIFHzKHtCeK5Ms/FcYvN7svprpHUGRjuc5i/TGRk0QIYerWxIIbcsfwqVth29U9tEw8tu1dg==";
$issued_at = time();
$expiration_time = $issued_at+(60*60); // valid selama 1 jam
$issuer = "RestApiAuthJWT";
?>