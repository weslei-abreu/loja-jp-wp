<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'FORCE_SSL_ADMIN', true ); // Redirect All HTTP Page Requests to HTTPS - Security > Settings > Enforce SSL
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

define('DISABLE_WP_CRON', true);

define( 'ITSEC_ENCRYPTION_KEY', 'I2Y7W182ZG5maXF6I1VHcFM3NT1AX198PEZ5NzkrdixfUHVaZGQoLS59MWNlRVpSMWVbenpmTndbSkhMMUFyTA==' );

// =========================
// Configurações do Banco
// =========================
define( 'DB_NAME', 'loja_jp_wp' );
define( 'DB_USER', 'lojajp' );
define( 'DB_PASSWORD', 'Lojajp2024#' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// =========================
// Chaves de autenticação
// =========================
define( 'AUTH_KEY',         '81R$#@L 659;bho;k-v1)jYdQ)qjxOq4~[3>t blVr9nQi0^G]~Rz+n}BwbL(M=t' );
define( 'SECURE_AUTH_KEY',  'fR1:U6ZIYTz3jPY-Homi!(`+Aljab5a,i#cvsC!zi^?g*891^A+wGIquq)}JaN,m' );
define( 'LOGGED_IN_KEY',    '0M)#da$35o$84}A7Q(deZh;+Iik @{Qt8sCFcXvD8WQ@-;5e5dhGo:7jT]5#e%Q>' );
define( 'NONCE_KEY',        'y>D|3tq$MSq0dm@E#np~(FmBB?P}OMft;RF9mFg,2J{jxEi0OLxU#e$)3f0nq9aS' );
define( 'AUTH_SALT',        '}94o4- n%@# i=h!Ac{6rdC.AjRIPCD0&m,JW1z-b_^*fUy{}oni{}>*Z1MQ^:[r' );
define( 'SECURE_AUTH_SALT', 'gcyz|Q/f~J@qLI!:n.Nc=:}ZmM$eZ{W`^?9!u;X5Kr8Lw7-J* OxR69v-sXC TMo' );
define( 'LOGGED_IN_SALT',   'Qg 5ev/B<dhC.mHlfUEYqv-wMi:_mg-P8$1@d} 0=:rn<$/6Fh)Rp/gBTbj}#K^u' );
define( 'NONCE_SALT',       '0Zq#Ct7ff5=:G95y.@!4J$JmX%H22A|M1C*.V7<toVtg|6oqsCc:r4lR_@@E^osV' );

// =========================
// Prefixo das tabelas
// =========================
$table_prefix = 'wpio_';

// =========================
// Debug (desative em produção)
// =========================
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// =========================
// Restrições de segurança
// =========================
define( 'DISALLOW_FILE_MODS', false ); // Impede instalação/remoção de plugins e temas

// =========================
// Caminho absoluto
// =========================
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

// =========================
// Inicializa o WordPress
// =========================
require_once ABSPATH . 'wp-settings.php';

//redis
define( 'WP_REDIS_CLIENT', 'phpredis' );

