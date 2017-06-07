<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| SFTP Related Variables
|--------------------------------------------------------------------------
|
| 'sftp_domain'     = Set to the domain that your login user belongs to
| 'sftp_user'       = Set to your sftp login user
| 'sftp_user_domain'= Set to your sftp login user domain
| 'sftp_password'   = Set to your sftp login password
| 'zip_path'        = Set to your zip files location for download
| 'zip_expire'      = Set to your zip files expire time in seconds
| 'zip_memory'      = Set to your memory limit for zip file
|
*/
$config['sftp_domain']	        = ".";
$config['sftp_user']	        = "Administrator";
$config['sftp_user_domain']     = ".";
$config['sftp_password']        = "abc123-";
$config['zip_path']             = "/usr/local/vshell2/api/application/downloads/";
$config['zip_expire']           = "900";
$config['zip_memory']           = "2048M";

/* End of file sftp_config.php */
/* Location: ./application/config/sftp_config.php */

