<?php
// ** Database settings ** //
define('DB_NAME', 'wordpress_db'); // Replace with your actual database name
define('DB_USER', 'wordpress_user'); // Use a database user with appropriate privileges
define('DB_PASSWORD', 'test@Wordpress@123456'); // Replace with your actual password
define('DB_HOST', 'localhost'); // Usually 'localhost' unless using an external database
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// ** Authentication Unique Keys and Salts ** //
define('AUTH_KEY',         '~+Y!V07&_HBJ9K.?eB2+`>&s3Nw5=8=G:m$BmASj-<pwx)RT,0xSEWAaw5G+]}iO');
define('SECURE_AUTH_KEY',  '=i}hZ*-+.Q=-it5KK@%9L?aw3W:YNr~HLN@[T1qRv,oi|2>tdEZfAv<O# OJyIlT');
define('LOGGED_IN_KEY',    'u9_b@EY )|Q(|l1;-|5}N|9o-`O_ozjw7|wnJsz=k+O/V89Xc}%[;E0Il#d-$J_l');
define('NONCE_KEY',        '@qICM,_tL?fAL]D{2_Y0F5I7)CWXXK)+8s]knbvoE+H;GU9awG7-wpQ{_ckDNg.!');
define('AUTH_SALT',        '$pqmd5=P2N7B,+ZS+|*8.]k@28P|v|1Vl Zwe6Eu?!+-0k<gAE|3D5CiNKUkV+xW');
define('SECURE_AUTH_SALT', 't2^1ipC`l/|+>?s+ja8KZQi;VE|hzV33Lmj+qYqli/<mt~p5)e%-LRn~1=;V{$%8');
define('LOGGED_IN_SALT',   ']4L(gZ!&yiIb}$m++!]_LbN]YSO#@IaIS65|~UZn^)##DAcvn:Q|rIxe(I;KQ-k9');
define('NONCE_SALT',       'PMg 3ql`#;ZCs~[}?Zr&)7b0ZKY[*=)beT-F2d1Y6WN -7FE5X<$bhSAQ5Xjn-o)');

// ** Generate secure keys here: https://api.wordpress.org/secret-key/1.1/salt/ **

// ** Table Prefix ** //
$table_prefix = 'wp_'; // Change 'wp_' to something unique like 'wp123_' for security

// ** Debug Mode ** //
define('WP_DEBUG', false);

// ** Allow WordPress to recognize HTTPS correctly (if needed) **
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy publishing. */
define('FS_METHOD', 'direct');
require_once ABSPATH . 'wp-settings.php';

