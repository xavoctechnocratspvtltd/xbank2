
<?php
if (!defined('PDO::ATTR_DRIVER_NAME')) {
echo 'PDO unavailable';
}else{echo "yes";}
echo  "<pre>";
print_r(get_loaded_extensions()); 
echo "</pre>";
echo extension_loaded ('PDO' );

echo phpinfo();
