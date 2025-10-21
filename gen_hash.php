<?php
// gen_hash.php
if (PHP_SAPI !== 'cli') {
    echo "Ce script est prévu pour CLI.\n";
    exit(1);
}
if ($argc < 2) {
    echo "Usage: php gen_hash.php MOTDEPASSE\n";
    exit(1);
}
echo password_hash($argv[1], PASSWORD_BCRYPT) . PHP_EOL;
