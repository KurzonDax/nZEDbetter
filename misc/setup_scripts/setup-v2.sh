#!/bin/bash
grbold='\e[01;32m'
ylbold='\e[01;33m'
rdbold='\e[01;31m'
endColor='\e[0;37m'
if [ -z `lsb_release -si | grep Ubuntu` ]
then
    echo -e "\n${rdbold}ERROR - The operating system does not appear to be Ubuntu"
    echo -e "This script was written specifically for Ubuntu, 13.04 or higher.\n"
    exit 1
fi
clear

echo -e "\n${grbold}Welcome to the nZEDbetter installation script.\n"
echo -e "During this process, you will be prompted to enter some"
echo -e "information occasionally."
#
# REMOVE BLOATWARE APPS (LibreOffice, Ubuntu One, Shotwell, Empathy, Thunderbird)
#
echo -e "\n${ylbold}Ubuntu Bloatware Removal"
echo -e "Includes LibreOffice, Ubuntu One, Shotwell, Empathy, Thunderbird, games, etc.\n"
echo -n "Do you wish to uninstall these apps [Y/n] "
read BLOAT
if [[ -z "$BLOAT" || "$BLOAT" = "Y" || "$BLOAT" = "y" ]]
then
    echo -e "${grbold}Removing bloatware...${endColor}"
    sudo apt-get remove --purge --yes libreoffice* >/dev/null 2>&1
    killall ubuntuone-login ubuntuone-preferences ubuntuone-syncdaemon  >/dev/null 2>&1
    sudo rm -rf ~/.local/share/ubuntuone  >/dev/null 2>&1
    rm -rf ~/.cache/ubuntuone  >/dev/null 2>&1
    rm -rf ~/.config/ubuntuone  >/dev/null 2>&1
    mv ~/Ubuntu\ One/ ~/UbuntuOne_old/``  >/dev/null 2>&1
    sudo apt-get remove --purge --yes ubuntuone-*  >/dev/null 2>&1
    sudo apt-get remove --purge --yes unity-webapps-common >/dev/null 2>&1
    sudo apt-get remove --purge --yes shotwell-*  >/dev/null 2>&1
    sudo apt-get remove --purge --yes empathy  >/dev/null 2>&1
    sudo apt-get remove --purge --yes thunderbird*  >/dev/null 2>&1
    sudo apt-get remove --purge --yes aisleriot gnome-mahjongg gnome-sudoku gnome-mines  >/dev/null 2>&1
    sudo apt-get remove --purge --yes gnome-contacts brasero unity-scope-gdrive totem gnome-orca onboard  >/dev/null 2>&1
    sudo apt-get clean --yes  >/dev/null 2>&1
    sudo apt-get autoremove --yes  >/dev/null 2>&1
fi
#
# Install OpenSSH and ProFTPd
#
echo -e "${ylbold}\n"
echo -n "Do you want to install openSSH and ProFTPd [Y/n]  "
read OPENFTP
if [[ -z "$OPENFTP" || "$OPENFTP" = "Y" || "$OPENFTP" = "y" ]]
then
    echo "proftpd-basic shared/proftpd/inetd_or_standalone select standalone" | sudo debconf-set-selections
    echo -e "${grbold}Installing openSSH and ProFTPd${endColor}\n"
    sudo apt-get -q=2 install openssh-server proftpd  >/dev/null 2>&1
fi
echo -e "${grbold}\nInstalling ancillary applications"
echo -e "Includes unrar, tmux, and server monitors${endColor}"
sudo apt-get -y install htop unrar software-properties-common tmux >/dev/null 2>&1
sudo apt-get -y install nmon vnstat tcptrack bwm-ng mytop >/dev/null 2>&1
getSQLpass () 
{
    while true; do
        echo -e "${ylbold}"
        echo -n "Please enter your desired root password for mysql/Percona: "
        read -s SQLPASS
        echo -e "\n"
        echo -n "Please re-enter your desired password to confirm: "
        read -s SQLPASSCONF
        if [[ "$SQLPASS" = "$SQLPASSCONF" ]]
        then
            echo -e "\n${endColor}"
            break
        else
            echo -e "\n${rdbold}Passwords do not match!"
        fi
    done
}
#
# Configure repos
#
echo -e "${grbold}Configuring repositories for PHP${endColor}"
DISTRO=$(lsb_release -cs)
sudo add-apt-repository -y ppa:ondrej/php5 >/dev/null 2>&1
sudo apt-key adv --keyserver keys.gnupg.net --recv-keys 1C4CBDCDCD2EFD2A >/dev/null 2>&1
if [ -z $(grep -q "repo.percona.com" /etc/apt/sources.list) ]
then
    echo -e "${grbold}Adding Percona repositories for Ubuntu ${DISTRO}${endColor}"
    echo 'deb http://repo.percona.com/apt '$DISTRO' main' | sudo tee -a /etc/apt/sources.list >/dev/null 2>&1
    echo 'deb-src http://repo.percona.com/apt '$DISTRO' main' | sudo tee -a /etc/apt/sources.list >/dev/null 2>&1
    if [ -z $(ls -1 /etc/apt/preferences.d | grep 00percona.pref) ]
    then
        echo 'Package: *' | sudo tee /etc/apt/preferences.d/00percona.pref >/dev/null 2>&1
        echo 'Pin: release o=Percona Development Team' | sudo tee -a /etc/apt/preferences.d/00percona.pref >/dev/null 2>&1
        echo 'Pin-Priority: 1001' | sudo tee -a /etc/apt/preferences.d/00percona.pref >/dev/null 2>&1
    fi
fi
echo -e "${grbold}Updating apt cache${endColor}"
sudo apt-get update >/dev/null 2>&1
#
# Install Percona
#
getSQLpass
if [ -e /etc/mysql/my.cnf ]
then
    sudo mv /etc/mysql/my.cnf /etc/mysql/my.original
fi
echo -e "${grbold}Installing Percona Server${endColor}"
echo "percona-server-server-5.6 percona-server-server/root_password password ${SQLPASS}" | sudo debconf-set-selections
echo "percona-server-server-5.6 percona-server-server/root_password_again password ${SQLPASS}" | sudo debconf-set-selections
sudo apt-get -y -q install percona-server-server-5.6 percona-server-client-5.6 percona-server-common-5.6 percona-toolkit >/dev/null 2>&1
#
# Install Apache, PHP, Cymysql, Dev tools
#
echo -e "${grbold}Installing Apache v2.4"
sudo apt-get -y install apache2 >/dev/null 2>&1
sudo a2enmod rewrite >/dev/null 2>&1
sudo service apache2 restart >/dev/null 2>&1
echo -e "${grbold}Installing PHP v5.5${endColor}"
sudo apt-get -qq install php5 php5-dev php-pear php5-gd php5-mysql php5-curl php5-xdebug php5-mcrypt >/dev/null 2>&1
echo -e "${grbold}Installing Python Tools${endColor}"
sudo apt-get -qq install python3-setuptools >/dev/null 2>&1
sudo python3 -m easy_install pip >/dev/null 2>&1
sudo pip-3.3 install cymysql >/dev/null 2>&1
echo -e "${grbold}Installing required development tools${endColor}"
sudo apt-get -y install autoconf automake build-essential git libass-dev libgpac-dev \
  libsdl1.2-dev libtheora-dev libtool libva-dev libvdpau-dev libvorbis-dev libx11-dev \
  libxext-dev libxfixes-dev pkg-config texi2html zlib1g zlib1g-dev >/dev/null 2>&1
#
# Clone, setup nZEDbetter
#
  cd /var/www
echo -e "${grbold}Cloning nZEDbetter${endColor}"
sudo git clone https://github.com/KurzonDax/nZEDbetter.git >/dev/null 2>&1
sudo chmod 777 nZEDbetter
cd nZEDbetter
sudo chmod -R 755 .
sudo chmod 777 /var/www/nZEDbetter/www/lib/smarty/templates_c
sudo chmod -R 777 /var/www/nZEDbetter/www/covers
sudo chmod 777 /var/www/nZEDbetter/www
sudo chmod 777 /var/www/nZEDbetter/www/install
sudo chmod -R 777 /var/www/nZEDbetter/nzbfiles
sudo mkdir /var/www/nZEDbetter/nzbfiles/tmpunrar
sudo chmod 777 /var/www/nZEDbetter/nzbfiles/tmpunrar
#
# Setup RAM disk, configure swappiness
#
echo -e "${grbold}Setting up RAM drive and swappiness${endColor}\n"
sudo cp /etc/fstab /etc/fstab.backup
echo "ramdisk /var/www/nZEDbetter/nzbfiles/tmpunrar tmpfs mode=1777,size=256m" | sudo tee -a /etc/fstab >/dev/null 2>&1
sudo mount /var/www/nZEDbetter/nzbfiles/tmpunrar/
sudo sysctl vm.swappiness=3 >/dev/null 2>&1
echo "vm.swappiness=3" | sudo tee -a /etc/sysctl.conf >/dev/null 2>&1
echo -e "${grbold}Configuring PHP ini files${endColor}"
#
# Configure PHP ini
#
TIMEZONE=$(cat /etc/timezone | sed 's/\//\\\//')
sudo sed -i 's/pdo_mysql\.default_socket=$/pdo_mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/cli/php.ini
sudo sed -i 's/mysql\.default_socket =$/mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/cli/php.ini
sudo sed -i 's/mysqli\.default_socket =$/mysqli\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/cli/php.ini
sudo sed -i 's/max_execution_time = 30$/max_execution_time = 120/' /etc/php5/cli/php.ini
sudo sed -i 's/memory_limit = 128M$/memory_limit = 1024M/' /etc/php5/cli/php.ini
sudo sed -i 's/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ERROR/' /etc/php5/cli/php.ini
sudo sed -i 's/;date.timezone =/date.timezone = '$TIMEZONE'/' /etc/php5/cli/php.ini
sudo sed -i 's/pdo_mysql\.default_socket=$/pdo_mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/apache2/php.ini
sudo sed -i 's/mysql\.default_socket =$/mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/apache2/php.ini
sudo sed -i 's/mysqli\.default_socket =$/mysqli\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/apache2/php.ini
sudo sed -i 's/max_execution_time = 30$/max_execution_time = 120/' /etc/php5/apache2/php.ini
sudo sed -i 's/memory_limit = 128M$/memory_limit = 1024M/' /etc/php5/apache2/php.ini
sudo sed -i 's/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ERROR/' /etc/php5/apache2/php.ini
sudo sed -i 's/;date.timezone =/date.timezone = '$TIMEZONE'/' /etc/php5/apache2/php.ini
#
# Install innotop
#
echo -e "${grbold}Installing innotop database monitoring tool${endColor}"
cd ~/Downloads
wget https://innotop.googlecode.com/files/innotop-1.9.1.tar.gz  >/dev/null 2>&1
tar -xzf innotop-1.9.1.tar.gz
cd innotop-1.9.1
perl ./Makefile.PL >/dev/null 2>&1
sudo cp innotop /usr/bin
#
# Powerline fonts, tmux config
#
cd ~/Downloads
echo -e "${grbold}Cloning Powerline Fonts${endColor}\n"
git clone https://github.com/jonnyboy/powerline-fonts.git >/dev/null 2>&1
sudo cp -r ~/Downloads/powerline-fonts/Consolas  /usr/share/fonts/truetype
sudo fc-cache -f
sudo cp /var/www/nZEDbetter/misc/update_scripts/nix_scripts/tmux/powerline/powerline/themes/default.sh /var/www/nZEDbetter/misc/update_scripts/nix_scripts/tmux/powerline/powerline/themes/tmux.sh
#
# Configure nZEDbetter site in Apache
#
sudo cp /var/www/nZEDbetter/misc/setup_scripts/nzedbetter.conf /etc/apache2/sites-available/nZEDbetter.conf
sudo a2ensite nZEDbetter >/dev/null 2>&1
sudo a2dissite 000-default.conf >/dev/null 2>&1
sudo service apache2 reload >/dev/null 2>&1
#
# Configure Percona my.cnf file
#
echo -e "${grbold}Configuring Percona my.cnf file${endColor}"
PROCS=$(nproc)
RAMMULTI=$(echo "scale=0; $(grep MemTotal /proc/meminfo | awk '{print $2}')/4194304" | bc)
if (( RAMMULTI<5 ))
then
    SORTBUFFER='16M'
    BUFFERINST='2'
    case "$RAMMULTI" in
        0)  LOGFILE='512M'
            BUFFERPOOL='2G'
            echo -e "\n${rdbold}WARNING: It appears you have less than 8GB"
            echo -e "of RAM.  Performance will not be optimal, and you will"
            echo -e "not be able to index more than a few newsgroups.\n${endColor}"
            ;;
        1)  LOGFILE='512M'
            BUFFERPOOL='2G'
            echo -e "\n${rdbold}WARNING: It appears you have less than 8GB"
            echo -e "of RAM.  Performance will not be optimal, and you will"
            echo -e "not be able to index more than a few newsgroups.\n${endColor}"
            ;;
        2)  LOGFILE='512M'
            BUFFERPOOL='5G'
            ;;
        3)  LOGFILE='1024M'
            BUFFERPOOL='8G'
            ;;
        4)  LOGFILE='1024M'
            BUFFERPOOL='12G'
            ;;
    esac
else
    BUFFERINST='4'
    SORTBUFFER='24M'
    LOGFILE='2048M'
    BUFFERPOOL=$((RAMMULTI*4-2))'G'
fi
sudo cp /var/www/nZEDbetter/misc/setup_scripts/my.cnf.nzedbetter /etc/mysql/my.cnf
sudo sed -i 's/@@LOGFILESIZE@@/'$LOGFILE'/' /etc/mysql/my.cnf
sudo sed -i 's/@@BUFFERPOOLSIZE@@/'$BUFFERPOOL'/' /etc/mysql/my.cnf
sudo sed -i 's/@@JOINSORTBUFFER@@/'$SORTBUFFER'/' /etc/mysql/my.cnf
sudo sed -i 's/@@BUFFERINSTANCES@@/'$BUFFERINST'/' /etc/mysql/my.cnf
echo -e "${grbold}Restarting Percona with new configuration${endColor}"
sudo service mysql restart >/dev/null 2>&1
#
# Compile, Install mediainfo and ffmpeg
#
echo -e "${grbold}Mediainfo and ffmpeg are required to create"
echo -e "sample video and audio files for releases.${ylbold}\n"
echo -n "Do you want to install these tools? [Y/n] "
read FFMPEG_INSTALL
if [[ -z "$FFMPEG_INSTALL" || "$FFMPEG_INSTALL" = "Y" || "$FFMPEG_INSTALL" = "y" ]]
then
    cd ~/Downloads
    echo -e "${grbold}Installing mediainfo${endColor}\n"
    wget http://mediaarea.net/download/binary/libzen0/0.4.29/libzen0_0.4.29-1_amd64.xUbuntu_13.04.deb >/dev/null 2>&1
    wget http://mediaarea.net/download/binary/libmediainfo0/0.7.64/libmediainfo0_0.7.64-1_amd64.xUbuntu_13.04.deb >/dev/null 2>&1
    wget http://mediaarea.net/download/binary/mediainfo/0.7.64/mediainfo_0.7.64-1_amd64.Debian_7.0.deb >/dev/null 2>&1
    sudo dpkg -i libzen0_0.4.29-1_amd64.xUbuntu_13.04.deb >/dev/null 2>&1
    sudo dpkg -i libmediainfo0_0.7.64-1_amd64.xUbuntu_13.04.deb >/dev/null 2>&1
    sudo dpkg -i mediainfo_0.7.64-1_amd64.Debian_7.0.deb >/dev/null 2>&1
    mkdir ~/ffmpeg_sources
    cd ~/ffmpeg_sources
    echo -e "${grbold}Compiling and installing yasm${endColor}\n"
    wget http://www.tortall.net/projects/yasm/releases/yasm-1.2.0.tar.gz >/dev/null 2>&1
    tar xzf yasm-1.2.0.tar.gz
    cd yasm-1.2.0
    ./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin" >/dev/null 2>&1
    make >/dev/null 2>&1
    make install >/dev/null 2>&1
    make distclean >/dev/null 2>&1
    source ~/.profile >/dev/null 2>&1
    export PATH=$HOME/bin:$PATH
    cd ~/ffmpeg_sources
    echo -e "${grbold}Compiling and installing x264${endColor}\n"
    git clone --depth 1 git://git.videolan.org/x264.git >/dev/null 2>&1
    cd x264
    ./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin" --enable-static >/dev/null 2>&1
    make >/dev/null 2>&1
    make install >/dev/null 2>&1
    make distclean >/dev/null 2>&1
    cd ~/ffmpeg_sources
    git clone --depth 1 git://github.com/mstorsjo/fdk-aac.git >/dev/null 2>&1
    cd fdk-aac
    autoreconf -fiv >/dev/null 2>&1
    ./configure --prefix="$HOME/ffmpeg_build" --disable-shared >/dev/null 2>&1
    make >/dev/null 2>&1
    make install >/dev/null 2>&1
    make distclean >/dev/null 2>&1
    echo -e "${grbold}Installing libmp3lame${endColor}\n"
    sudo apt-get -y install libmp3lame-dev >/dev/null 2>&1
    cd ~/ffmpeg_sources
    echo -e "${grbold}Compiling and installing opus${endColor}\n"
    wget http://downloads.xiph.org/releases/opus/opus-1.0.3.tar.gz >/dev/null 2>&1
    tar xzf opus-1.0.3.tar.gz >/dev/null 2>&1
    cd opus-1.0.3
    ./configure --prefix="$HOME/ffmpeg_build" --disable-shared >/dev/null 2>&1
    make >/dev/null 2>&1
    make install >/dev/null 2>&1
    make distclean >/dev/null 2>&1
    cd ~/ffmpeg_sources
    echo -e "${grbold}Compiling and installing libvpx${endColor}\n"
    git clone --depth 1 http://git.chromium.org/webm/libvpx.git >/dev/null 2>&1
    cd libvpx
    ./configure --prefix="$HOME/ffmpeg_build" --disable-examples >/dev/null 2>&1
    make >/dev/null 2>&1
    make install >/dev/null 2>&1
    make clean >/dev/null 2>&1
    cd ~/ffmpeg_sources
    echo -e "${grbold}Compiling and installing ffmpeg"
    echo -e "This will take several minutes.${endColor}\n"
    git clone --depth 1 git://source.ffmpeg.org/ffmpeg >/dev/null 2>&1
    cd ffmpeg
    PKG_CONFIG_PATH="$HOME/ffmpeg_build/lib/pkgconfig"
    export PKG_CONFIG_PATH
    source ~/.profile >/dev/null 2>&1
    cd ~/ffmpeg_sources/ffmpeg
    ./configure --prefix="$HOME/ffmpeg_build" \
     --extra-cflags="-I$HOME/ffmpeg_build/include" --extra-ldflags="-L$HOME/ffmpeg_build/lib" \
     --bindir="$HOME/bin" --extra-libs="-ldl" --enable-gpl --enable-libass --enable-libfdk-aac \
     --enable-libmp3lame --enable-libopus --enable-libtheora --enable-libvorbis --enable-libvpx \
     --enable-libx264 --enable-nonfree --enable-x11grab >/dev/null 2>&1
    make >/dev/null 2>&1
    make install >/dev/null 2>&1
    make distclean >/dev/null 2>&1
    hash -r >/dev/null 2>&1
fi
#
# Clean up apt and set some aliases
#
echo -e "${grbold}Removing unnecessary packages${endColor}\n"
sudo apt-get -y autoremove >/dev/null 2>&1
echo -e "${grbold}Adding some useful aliases to your profile${endColor}\n"
echo "export PATH=$HOME/bin:$PATH" >> ~/.bashrc
echo 'alias tmux-dir="cd /var/www/nZEDbetter/misc/update_scripts/nix_scripts/tmux"' >> ~/.bash_aliases
echo 'alias www-dir="cd /var/www/nZEDbetter/www"' >> ~/.bash_aliases
echo 'alias misc-dir="cd /var/www/nZEDbetter/misc"' >> ~/.bash_aliases
echo 'alias sqlstart="sudo service mysql start"' >> ~/.bash_aliases
echo 'alias sqlstop="sudo service mysql stop"' >> ~/.bash_aliases
echo 'alias sqlrestart="sudo service mysql restart"' >> ~/.bash_aliases
echo 'alias sqllog="sudo tail -f /var/lib/mysql/mysql-error.log"' >> ~/.bash_aliases
echo 'alias sqlcmd="mysql -u root -p"' >> ~/.bash_aliases
echo 'alias untar="tar -zxf "' >> ~/.bash_aliases
echo 'alias zedstart="cd /var/www/nZEDbetter/misc/update_scripts/nix_scripts/tmux && php ./start.php"' >> ~/.bash_aliases
IPADD=`ifconfig  | grep 'inet addr:'| grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'`
echo -e "\n${grbold}CONGRATULATIONS - The setup script has completed.\n"
echo -e "Do not forget to do the following:"
echo -e '	Create a my.cnf and save it in /etc/mysql/my.cnf'
echo -e '	Go to http://tools.percona.com to create a base my.cnf' 
echo -e '   tailored to your hardware\n'
echo -e '\nOnce you create a my.cnf file and save it, you will'
echo -e 'need to restart Percona. From a terminal window, type:'
echo -e '\tsqlrestart\n'
echo -e 'After you have created the my.cnf file and restarted Percona,'
echo -e 'you can launch the nZEDbetter installation wizard. The wizard'
echo -e 'will walk you through setting up the database and your Usenet'
echo -e 'Service Provider.'
echo -e '\nAccess the installation wizard from the server at:'
echo -e '\thttp://localhost/install  or  http://'$IPADD'/install\n'$endColor
source ~/.bashrc

