<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload295d67fc8e1b1e13fcd7f5c362cde38a($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'proftpdplugin' => '/proftpdPlugin.class.php',
            'proftpdplugindescriptor' => '/ProftpdPluginDescriptor.class.php',
            'proftpdplugininfo' => '/ProftpdPluginInfo.class.php',
            'tuleap\\proftpd\\xferlog\\dao' => '/ProFTPd/Xferlog/Dao.class.php',
            'tuleap\\proftpd\\xferlog\\entry' => '/ProFTPd/Xferlog/Entry.class.php',
            'tuleap\\proftpd\\xferlog\\fileimporter' => '/ProFTPd/Xferlog/FileImporter.class.php',
            'tuleap\\proftpd\\xferlog\\invalidentryexception' => '/ProFTPd/Xferlog/InvalidEntryException.class.php',
            'tuleap\\proftpd\\xferlog\\parser' => '/ProFTPd/Xferlog/Parser.class.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload295d67fc8e1b1e13fcd7f5c362cde38a');
// @codeCoverageIgnoreEnd