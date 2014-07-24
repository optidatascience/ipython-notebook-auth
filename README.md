# Deploy Instructions

This instruction shows how to setup the iPython Notebook for multiuser login using Apache2. 

1. Install necessary packages to get started. 

  ```
    sudo apt-get install apache2
    sudo apt-get install libapache2-mod-authnz-external pwauth
    sudo apt-get install libapache2-mod-authz-unixgroup
    sudo a2enmod authnz_external authz_unixgroup
  ```

2. Modify /etc/sudoer to grant sudo execution for ipynb-launch for www-data

  ```
    sudo nano /etc/sudoer
    #and add the following lines
    www-data ALL=(ALL:ALL)  NOPASSWD: /usr/local/bin/ipynb-launch
  ```

3. Modify ipython.php and ipynb-launch and change the url to your own


4. Make change to the apache2 virtual server setting 

  The following code will allow any Unix user to login into ipython Notebook. 

  ```
    <IfModule mod_authnz_external.c>
        AddExternalAuth pwauth /usr/sbin/pwauth
        SetExternalAuthMethod pwauth pipe
    </IfModule>

    <Directory /var/www/html/notebook>
        AuthType Basic
        AuthName "Restricted Area"
        AuthBasicProvider external
        AuthExternal pwauth
        Require valid-user
    </Directory>
  ```

5. (**Optional**) If you want to setup group auth instead of all users, follow the guide below:

  If you also want to have PAM authentication by users group you’ll need to make few extra steps. Missing bit of puzzle here is called ‘unixgroup’ script and for some reason it is not in Ubuntu’s pwauth package where it ought to be. You will need to grab it from here and copy it over to /usr/sbin/unixgroup and make it executable. Here is a quick snippet to do that:

  ```
    wget "http://pwauth.googlecode.com/files/pwauth-2.3.9.tar.gz"
    tar xzvf ./pwauth-2.3.9.tar.gz
    sudo cp pwauth-2.3.9/unixgroup /usr/sbin/
    sudo chmod a+x /usr/sbin/unixgroup
  ```
  Once that’s done, you’ll need to few more lines to you Virtual Host config, so it will look something like this:

  ```
    <IfModule mod_authnz_external.c>
        AddExternalAuth pwauth /usr/sbin/pwauth
        SetExternalAuthMethod pwauth pipe
        AddExternalGroup unixgroup /usr/sbin/unixgroup
        SetExternalGroupMethod unixgroup environment
    </IfModule>
    
    <Directory /var/www/yourlocation>
        AuthType Basic
        AuthName "Restricted Area"
        AuthBasicProvider external
        AuthExternal pwauth
        GroupExternal unixgroup
        Require valid-user
    </Directory>
  ```

6. Put files in place:

  ```
    sudo cp ipynb-launch /usr/local/bin
    sudo mkdir /var/www/html/notebook
    sudo cp *.php /var/www/html/notebook
  ```

7. Finally, make a hardlink of ipython to make sure the killall won't affect anything else. 

  ```
    sudo ln -s /usr/local/ipython /usr/local/apache-ipython
  ```    

8. All set!

### Note to run behind a firewall

If you are running ipython-notebook server behind a firewall, the easiest way is to setup a range forwarding. In this case, 
the port range is starting at 9500. If you anticipate no more than 500 instances of notebooks, set the range from 9500-9999 on the firewall to be 
forwarded to the ipython-notebook server. 

### Note when apache2 and ipython-notebook run on separate servers

If Apache2 and iPython-notebook are running on separate servers, the best way to setup is to use remote ssh to start the server. Below is a sample of convenient setup, 

* /home is shared through NFS across servers
* public key is setup so that no password is needed to remote ssh

All you need to do is to config the $REMOTESERVER variable in ipynb-launch file (as in the current repo).

-- 
Any additional questions, please forward to liang.zhou@optidatascience.com. Thanks