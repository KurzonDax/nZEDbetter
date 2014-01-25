#! /bin/bash
grbold='\e[01;32m'
ylbold='\e[01;33m'
rdbold='\e[01;31m'
endColor='\e[0;37m'
if [ -z `lsb_release -si | grep Ubuntu` ]
then
    echo -e "\n${rdbold}ERROR - The operating system does not appear to be Ubuntu"
    echo -e "This script was written specifically for Ubuntu, 12.04 or higher.\n"
    exit 1
fi
echo -e "\n${ylbold}This script will install the MusicBrainz Server application."
echo -e "\nFor more information about what this script does, please visit"
echo -e "http://nzedbetter.org/index.php?title=MusicBrainz"
echo -e "\n\n${rdbold}IMPORTANT: The configuration that is created by this script"
echo -e "is NOT SECURE.  Do NOT expose the final server to a public network"
echo -e "or the Internet.\n"
echo -e "\n${ylbold}You will be prompted for your sudo password several times"
echo -e "throughout the script, so you may want to stay close. The script will"
echo -e "take as long as 90 minutes to complete."
echo -e "\nThis script requires the latest Musicbrainz database dump files."
echo -e "If you have downloaded these already, please enter the full path to where they"
echo -e "are located.  If not, just press [enter] and we'll download the latest for you.${endColor}"
read mbdumps
if [ "$mbdumps" != "" ]
then
    mbdumps=${mbdumps/\~/$HOME}
    if [ -e "$mbdumps/mbdump.tar.bz2" ]; then
        echo -e "${ylbold}\nFound the dump files.${endColor}"
    else
        echo -e "${rdbold}Unable to locate the dump files.  Exiting script.${endColor}"
        exit
    fi
else
    mbdumps=$HOME/dumps
    LATEST="$(wget -O - http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/LATEST)"
    mkdir $mbdumps
    cd $mbdumps
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump-cdstubs.tar.bz2
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump-cover-art-archive.tar.bz2
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump-derived.tar.bz2
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump-documentation.tar.bz2
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump-editor.tar.bz2
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump-stats.tar.bz2
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump-wikidocs.tar.bz2
    wget http://ftp.musicbrainz.org/pub/musicbrainz/data/fullexport/$LATEST/mbdump.tar.bz2
fi
echo -e "\n${ylbold}If prompted, please enter your sudo password.${endColor}"
echo -e "\n${grbold}Configuring Postgre Repository${endColor}\n"
echo "deb http://apt.postgresql.org/pub/repos/apt/ precise-pgdg main" | sudo tee /etc/apt/sources.list.d/pgdg.list > /dev/null
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
sudo apt-get update > /dev/null
if [ -z `which mkpasswd | grep "mkpasswd"` ]
then
    sudo apt-get install -y whois > /dev/null
fi
sudo useradd musicbrainz -m -s /bin/bash -p `mkpasswd musicbrainz`
sudo adduser musicbrainz sudo
sudo chmod a+rw $mbdumps/*.tar.bz2
echo -e "${ylbold}Installing Postgres 9.3"
sudo apt-get install -y postgresql-9.3 postgresql-server-dev-9.3 postgresql-contrib-9.3 postgresql-plperl-9.3 > /dev/null
echo -e "Installing git, memcached, redis, and build essentials"
sudo apt-get install -y git-core memcached redis-server build-essential > /dev/null
echo -e "Installing Perl dependencies"
sudo apt-get install -y curl libxml2-dev libpq-dev libexpat1-dev libdb-dev memcached liblocal-lib-perl libicu-dev > /dev/null
echo -e "Cloning MusicBrainz server repository to /home/musicbrainz/musicbrainz-server${endColor}"
cd /home/musicbrainz
sudo git clone --recursive https://github.com/KurzonDax/musicbrainz-server.git > /dev/null
sudo cp /home/musicbrainz/musicbrainz-server/nZEDbetter/DBDefs.pm /home/musicbrainz/musicbrainz-server/lib/DBDefs.pm
sudo chmod -R 777 /home/musicbrainz/musicbrainz-server/postgresql-musicbrainz-unaccent
sudo chmod -R 777 /home/musicbrainz/musicbrainz-server/postgresql-musicbrainz-collate
cd /home/musicbrainz/musicbrainz-server/postgresql-musicbrainz-unaccent
make > /dev/null
sudo make install > /dev/null
cd ..
cd postgresql-musicbrainz-collate
make > /dev/null
sudo make install > /dev/null
sudo chmod -R 755 /home/musicbrainz/musicbrainz-server/postgresql-musicbrainz-unaccent
sudo chmod -R 755 /home/musicbrainz/musicbrainz-server/postgresql-musicbrainz-collate
cd /home/musicbrainz
sudo mkdir dumps
sudo mv $mbdumps/mbdump*.tar.bz2 /home/musicbrainz/dumps
sudo chown -R musicbrainz:musicbrainz *
echo -e "${ylbold}Upgrading cpanminus. Please enter your sudo password when prompted.${endColor}"
curl -L http://cpanmin.us | perl - --sudo App::cpanminus
echo -e "\n${ylbold}Configuring CPAN libraries. Please be patient."
echo -e "This process will take quite a while to complete.${endColor}\n"
cd /home/musicbrainz/musicbrainz-server
sudo cpanm --installdeps --notest --force --quiet .
sudo mv /etc/postgresql/9.3/main/pg_hba.conf /etc/postgresql/9.3/main/pg_hba.conf.backup
echo "local  all  all                       trust" | sudo tee /etc/postgresql/9.3/main/pg_hba.conf > /dev/null
echo "local  all  postgres                  peer" | sudo tee -a /etc/postgresql/9.3/main/pg_hba.conf > /dev/null
echo "local  all  all                       peer" | sudo tee -a /etc/postgresql/9.3/main/pg_hba.conf > /dev/null
echo "host   all  all       127.0.0.1/32    md5" | sudo tee -a /etc/postgresql/9.3/main/pg_hba.conf > /dev/null
echo -e "${ylbold}Restarting postgres database engine.${endColor}"
sudo chown postgres:postgres /etc/postgresql/9.3/main/pg_hba.conf
sudo service postgresql stop > /dev/null
sudo service postgresql start > /dev/null
echo -e "${ylbold}Creating database and importing data."
echo -e "This process will take an extremely long time to complete.${endColor}\n"
sudo su musicbrainz <<'EOF'
cd /home/musicbrainz/musicbrainz-server
./admin/InitDb.pl --createdb --import /home/musicbrainz/dumps/mbdump*.tar.bz2 --echo
EOF
echo -e "\n${ylbold}Installing nginx and daemontools"
sudo apt-get install -y nginx daemontools daemontools-run > /dev/null
echo -e "Configuring MusicBrainz site in nginx"
cd /etc/nginx/sites-available
sudo ln -s /home/musicbrainz/musicbrainz-server/admin/nginx/001-musicbrainz . > /dev/null
cd ../sites-enabled/
sudo rm default
sudo ln -s ../sites-available/001-musicbrainz . > /dev/null
cd ..
sudo mv nginx.conf nginx.conf.default
sudo ln -s /home/musicbrainz/musicbrainz-server/admin/nginx/mbserver-rewrites.conf . > /dev/null
sudo ln -s /home/musicbrainz/musicbrainz-server/admin/nginx/nginx.conf . > /dev/null
echo "server_name nzedbetter.org;" | sudo tee /etc/nginx/site-name.conf > /dev/null
echo "Restarting nginx${endColor}"
sudo service nginx stop > /dev/null
sudo service nginx start > /dev/null
cd /usr/local
sudo mkdir musicbrainz-server
cd musicbrainz-server/
sudo cp /home/musicbrainz/musicbrainz-server/admin/nginx/service/run .
sudo mkdir log
cd log
sudo ln -s /home/musicbrainz/musicbrainz-server/admin/nginx/service/log/run . > /dev/null
cd ..
sudo ln -s /home/musicbrainz/musicbrainz-server mb_server > /dev/null
echo -e "${ylbold}Starting daemontools${endColor}"
sudo start svscan
cd /etc/service
sudo ln -s /usr/local/musicbrainz-server . > /dev/null
sudo chmod a+rw /home/musicbrainz/musicbrainz-server/nZEDbetter/crontab.txt
number=$RANDOM
let "number %= 59"
sudo sed -i 's/^18/'$number'/' /home/musicbrainz/musicbrainz-server/nZEDbetter/crontab.txt
sudo su musicbrainz -c "crontab /home/musicbrainz/musicbrainz-server/nZEDbetter/crontab.txt"
echo -e "${ylbold}\nA crontab job has been scheduled to run once an hour"
echo -e "for the musicbrainz user.  This job will automatically download"
echo -e "updates from the main MusicBrainz database."
IPADDY=`ifconfig  | grep 'inet addr:'| grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'`
echo -e "\n\n${grbold}The MusicBrainz configuration script has completed successfully."
echo -e "To access the webpage for your server, enter http://$IPADDY into"
echo -e "your web browser."
echo -e "\n${ylbold}This script brought to you by kurzondax.  For more information about"
echo -e "the nZEDbetter Usenet indexing application, visit http://nzedbetter.org${endColor}"
echo -e "\n"

