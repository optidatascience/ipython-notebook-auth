/* ipython.php --- 
 * 
 * Filename: ipython.php
 * Description: 
 * Author: Liang Zhou
 * Maintainer: 
 * Created: Thu Jul 24 15:13:04 2014 (-0500)
 * Last-Updated: Thu Jul 24 15:15:49 2014 (-0500)
 *           By: Liang Zhou
 *     Update #: 1
 * URL: 
 * Doc URL: 
 * Keywords: 

/* Change Log:
 * 
 * 
 */

/* This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; see the file COPYING.  If not, write to
 * the Free Software Foundation, Inc., 51 Franklin Street, Fifth
 * Floor, Boston, MA 02110-1301, USA.
 */

/* Code: */


<?php 
# TODO: change the exturl to the external url of your web server
$exturl = 'https://www.optidatascience.com';


require_once('Logging.php');
$log = new Logging();
 
# set path and name of log file (optional)
$log->lfile('/var/log/ipython_server.log');
$user = getenv('REMOTE_USER');

$log->lwrite('The logged user is '.$user.'.');

unset($out);

# The log file is owned by apache, so we'll let ipynb-launch write to a temp log
# and then copy that into our log. This will also help our log be a little more coherent
# when multiple processes are writing to it. (Might also consider locking the log with flock)

$tmplog = '/tmp/ipython_'.$user.'_'.getmypid().'.log';
exec("sudo -n -u $user /usr/local/bin/ipynb-launch ".$tmplog, $out, $stat);
$log->lwrite(file_get_contents($tmplog));

# TODO: Check return status. If 0, then a new kernel was launched. If 1, then exiting kernel was used.
$port = trim(file_get_contents("/home/$user/.ipython/lock"));

# The password might not be what we requested (e.g., if an existing kernel was used).
$passwd = trim(file_get_contents("/home/$user/.ipython/pass"));
$url = $exturl.':'.$port;

$log->lclose();
echo "<form action='".$url."/login?next=%2F' method='post' name='frm'>\n";
echo "<input type='hidden' name='password' value='".$passwd."'>";
echo "<input type='submit' value='Log in' id='login_submit'>\n";
echo "</form>\n";
echo "<script language=\"JavaScript\">\ndocument.frm.submit();\n</script>\n";
?>

/* ipython.php ends here */