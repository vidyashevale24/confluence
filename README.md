# Confluence

Confluence is a collaboration software program developed and published by Australian software company Atlassian. Atlassian wrote Confluence in the Java programming language, and first published it in 2004. The company markets Confluence as enterprise software, licensed as either on-premises software or software as a service.
-----------------------------------------------------------------------------
1)In this project we are passing directory to the script
It reads entire directory structure and create folders and files accordingly also attachments of images , docs and ppts files can be done.
You can easily download and view the documents

<h2>To config</h2> 

1.INSTALL AND SETUP JAVA
==============================================================================
Use the command below to download JDK 8:
wget --no-cookies --no-check-certificate --header "Cookie: oraclelicense=accept-securebackup-cookie" "http://download.oracle.com/otn-pub/java/jdk/8u60-b27/jdk-8u60-linux-x64.rpm" -O /opt/jdk-8-linux-x64.rpm

Install JAVA:
==============================================================================
0# yum install /opt/jdk-8-linux-x64.rpm

Configure the JAVA package using the alternatives command:<br>
1# JDK_DIRS=($(ls -d /usr/java/jdk*)) <br>
2# JDK_VER=${JDK_DIRS[@]:(-1)}   <br>
3# alternatives --install /usr/bin/java java /usr/java/"${JDK_VER##*/}"/jre/bin/java 20000 <br>
4# alternatives --install /usr/bin/jar jar /usr/java/"${JDK_VER##*/}"/bin/jar 20000 <br>
5# alternatives --install /usr/bin/javac javac /usr/java/"${JDK_VER##*/}"/bin/javac 20000<br>
6# alternatives --install /usr/bin/javaws javaws /usr/java/"${JDK_VER##*/}"/jre/bin/javaws 20000<br>
7# alternatives --set java /usr/java/"${JDK_VER##*/}"/jre/bin/java<br>
8# alternatives --set javaws /usr/java/"${JDK_VER##*/}"/jre/bin/javaws<br>
9# alternatives --set javac /usr/java/"${JDK_VER##*/}"/bin/javac<br>
Check the version of java that it is properly installed<br>
10.# java -version<br>

2.INSTALL MYSQL (If database is required then install)
==============================================================================
You need to install MySQL from the community repository.
Download and install the repo:

1# wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm<br>

2# sudo rpm -ivh mysql-community-release-el7-5.noarch.rpm<br>

Update the package index:<br>
3 # yum update

Now install and start MySQL:<br>
	1.		# sudo rpm -ivh mysql-community-release-el7-5.noarch.rpm<br><br>

	2.		# systemctl start mysqld<br><br>

Enable MySQL to start on boot:<br>
	1.	# systemctl enable mysqld<br>

With the MySQL installation out of our way, we can now create a database for the Confluence installation.<br>
But first, run the mysql_secure_installation script to harden your MySQL server:<br><br>

	1.	# mysql_secure_installation <br><br>
 
Just press Enter if asked for password of root<br>

Now, log into MySQL as root and create the database:<br>
# mysql -u root -p<br>
give password for sql server pws set during coniguration a while ago<br>
y
mysql> CREATE DATABASE confluence CHARACTER SET utf8 COLLATE utf8_bin;<br>

mysql> GRANT ALL PRIVILEGES ON confluencedb.* TO 'confluenceuser'@'localhost' IDENTIFIED BY 'Abc@1234';<br>

mysql> flush privileges;<br>
mysql> exit<br>

3.INSTALL CONFLUENCE
==============================================================================
You need to download the appropriate Confluence ‘Linux 64-bit/ 32-bit installer’ from their download page. <br>
https://www.atlassian.com/software/confluence/download-archives version : <br>atlassian-confluence-6.3.1-x64.bin<br>
We are using a 64-bit CentOS 7 VPS, so we will use the 64-bit installer<br>


You can use the arch command to check whether you are running a 64 or 32 bit OS on your server.
For example our CentOS 7 OS is 64-bit:<br>
[root@linuxvps /]# Arch	 86_64<br>

We are downloading the 64-bit installer:<br>
1.	# sudo wget https://downloads.atlassian.com/software/confluence/downloads/atlassian-confluence-6.3.1-x64.bin<br>

will take little time to download 255mb setup bin file<br>
Make the bin file executable/to allow permission:<br>
2.	# sudo chmod a+x atlassian-confluence-6.3.1-x64.bin<br>

3.  # sudo ./atlassian-confluence-6.3.1-x64.bin<br>

You will get the following output:<br>

Unpacking JRE ...<br>
Starting Installer ...<br>
Dec 03, 2015 10:43:54 AM java.util.prefs.FileSystemPreferences$1 run<br>
INFO: Created user preferences directory.<br>

This will install Confluence 6.3.1 on your computer.<br>
OK [o, Enter], Cancel [c]<br>

Press enter.<br>

Choose the appropriate installation or upgrade option.<br>
Please choose one of the following:<br>
Express Install (uses default settings) [1],<br>
Custom Install (recommended for advanced users) [2, Enter],<br>
Upgrade an existing Confluence installation [3]<br>

You can proceed with a custom install if you want, but we will enter 1 in our CLI for an Express install with the default settings:<br>

See where Confluence will be installed and the settings that will be used.<br>
Installation Directory: /opt/atlassian/confluence<br>
Home Directory: /var/atlassian/application-data/confluence<br>
NOTE:HTTP default port is :8080 and and RMI port is 8000
HTTP Port: 3000<br>
RMI Port: 6000<br>
Install as service: Yes<br>
Install [i, Enter], Exit [e]<br>

Press Enter again to start the Confluence installation which will give you the below output:

Extracting files ...<br>

Please wait a few moments while Confluence starts up.<br>
Launching Confluence ...<br>
Installation of Confluence 6.3.1 is complete<br>
Your installation of Confluence 6.3.1 is now ready and can be accessed via
your browser.<br>
Confluence 6.3.1 can be accessed at http://localhost:3000<br>
Finishing installation ...<br>

ADD/ALLOW PORTS THROUGH FIREWALL<br>
=================================================================================<br>
$ sudo firewall-cmd --zone=public --add-port=5000/tcp --permanent<br>
$ sudo firewall-cmd --zone=public --add-port=4000/tcp --permanent<br>
$ sudo firewall-cmd --zone=public --add-port=3306/tcp --permanent<br>
$ sudo firewall-cmd --reload<br>

Next step is to configure a MySQL datasource connection for Confluence if required
=================================================================================
----------------- OPTIONAL IF DATABASE IS REQUIRED THEN DO BELOW CHANGES--------<br>
i- install the MySQL JDBC driver<br>
# cd/opt <br>
ii- Extract it<br>
# sudo tar -zxvf mysql-connector-java-5.1.35.tar.gz<br>

Go to following location<br>
# cd /opt/mysql-connector-java-5.1.35<br>

iii- move the unpacked jar file in the appropriate Confluence directory<br>
# sudo mv mysql-connector-java-5.1.35-bin.jar /opt/atlassian/confluence/confluence/WEB-INF/lib/<br>

iv-shutdown Confluence first and then edit the server.xml file.<br>
# sudo sh /opt/atlassian/confluence/bin/shutdown.sh<br>
v- edit the server.xml<br>
vi /opt/atlassian/confluence/conf/server.xml<br>

vii- paste following after this line
<Context path="" docBase="../confluence" debug="0" reloadable="true"><br>
<Context path="/confluence" docBase="../confluence" debug="0" reloadable="false" useHttpOnly="true"><br>

<Resource name="jdbc/confluence" auth="Container" type="javax.sql.DataSource"
          username="confluenceuser"<br>
          password="Abc@1234"<br>
          driverClassName="com.mysql.jdbc.Driver"<br>
          url="jdbc:mysql://localhost:3306/confluence?useUnicode=true&amp;characterEncoding=utf8"<br>
          maxActive="15"<br>
          maxIdle="7"<br>
          defaultTransactionIsolation="READ_COMMITTED"<br>
          validationQuery="Select 1" /><br>

viii- Now edit the web.xml file located in the WEB-INF directory:<br>

ix- /opt/atlassian/confluence/confluence/WEB-INF/web.xml<br>
Insert the following components just before </web-app><br>

<resource-ref><br>
    <description>Connection Pool</description><br>
    <res-ref-name>jdbc/confluence</res-ref-name><br>
    <res-type>javax.sql.DataSource</res-type><br>
   <res-auth>Container</res-auth><br>
</resource-ref><br>
Save and close the web.xml file.<br>

================================================================================<br>
4.INSTALL cURL and Imagick library if you are converting images<br>

# sudo sh /opt/atlassian/confluence/bin/start-confluence.sh<br>

now start Confluence.<br>

now go to web url to configure further e.g http://0.0.0.0:3000 or http://localhost:3000<br>

Other information<br>
sudo pkill -9 -f tomcat<br>
sudo service tomcat8 start<br>
sudo service tomcat8 stop<br>
sudo service tomcat8 status<br>

------------------------BELOW COMMAND SHOW ERRORS ON UBUNTU -------------------<br>

tail -f /var/log/apache2/error.log <br>

================================================================================

Some important links  <br>
------------------------ CONFLUENCE REST API EXAMPLES ------------------------<br>
https://developer.atlassian.com/server/confluence/confluence-rest-api-examples/<br>

------------------------ CONFLUENCE STORAGE FORMAT 	--------------------------<br>
https://confluence.atlassian.com/doc/confluence-storage-format-790796544.html<br>

------------------------- cURL command to create space -----------------------<br>
curl -u admin:Abc@1234 -X POST -H 'Content-Type: application/json' -d' { "key":"XYZ", "name":"XYZ",
"type":"global",  "description":{"plain": { "value": "XYZ Space for neos","representation":
"plain" }}}' http://localhost:3000/rest/api/space<br>

------------------------- cURL + PHP script to crete space -------------------<br>
	INPUT 
    $request = array (
		"key"=>  "XYZ", 
	    "name"=> "XYZ",
	    "type"=>"global", 
	    "description"=>array(
	                    "plain"=>array
	                        (
	                            "value"=> "XYZ Space",
	                            "representation"=>"plain"
	                        )
	                    )
	);

	cURL + PHP
	$qbody = json_encode($request);
	$ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/space/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $qbody);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);die;
    }
    curl_close ($ch);
    
    print_r($result);
<br>
Note- while sending the html content you need to take care about confluence storage format.
================================================================================
https://confluence.atlassian.com/doc/confluence-storage-format-790796544.html<br>


Script.php is the main file which reads all directories <br>