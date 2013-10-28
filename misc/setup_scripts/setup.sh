#!/bin/bash
sudo add-apt-repository ppa:ondrej/php5
sudo apt-key adv --keyserver keys.gnupg.net --recv-keys 1C4CBDCDCD2EFD2A
echo "deb http://repo.percona.com/apt raring main" | sudo tee -a /etc/apt/sources.list
echo "deb-src http://repo.percona.com/apt raring main" | sudo tee -a /etc/apt/sources.list
sudo apt-get update
sudo apt-get -y install openssh-server proftpd htop unrar software-properties-common tmux
sudo apt-get -y install nmon vnstat tcptrack bwm-ng mytop
sudo apt-get -y install percona-server-server-5.6 percona-toolkit
sudo apt-get -y install apache2
sudo a2enmod rewrite
sudo service apache2 restart
sudo apt-get -y install php5 php5-dev php-pear php5-gd php5-mysql php5-curl php5-xdebug php5-mcrypt
sudo apt-get -y install python3-setuptools
sudo python3 -m easy_install pip
sudo pip-3.3 install cymysql
sudo apt-get -y install autoconf automake build-essential git libass-dev libgpac-dev \
  libsdl1.2-dev libtheora-dev libtool libva-dev libvdpau-dev libvorbis-dev libx11-dev \
  libxext-dev libxfixes-dev pkg-config texi2html zlib1g zlib1g-dev
cd /var/www
sudo git clone https://github.com/KurzonDax/nZEDbetter.git
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
sudo cp /etc/fstab /etc/fstab.backup
echo "ramdisk /var/www/nZEDbetter/nzbfiles/tmpunrar tmpfs mode=1777,size=256m" | sudo tee -a /etc/fstab
sudo mount /var/www/nZEDbetter/nzbfiles/tmpunrar/
sudo sysctl vm.swappiness=3
echo "vm.swappiness=3" | sudo tee -a /etc/sysctl.conf
sudo sed -i 's/pdo_mysql\.default_socket=$/pdo_mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/cli/php.ini
sudo sed -i 's/mysql\.default_socket =$/mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/cli/php.ini
sudo sed -i 's/mysqli\.default_socket =$/mysqli\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/cli/php.ini
sudo sed -i 's/max_execution_time = 30$/max_execution_time = 120/' /etc/php5/cli/php.ini
sudo sed -i 's/memory_limit = 128M$/memory_limit = 1024M/' /etc/php5/cli/php.ini
sudo sed -i 's/pdo_mysql\.default_socket=$/pdo_mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/apache2/php.ini
sudo sed -i 's/mysql\.default_socket =$/mysql\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/apache2/php.ini
sudo sed -i 's/mysqli\.default_socket =$/mysqli\.default_socket=\/var\/lib\/mysql\/mysql\.sock/' /etc/php5/apache2/php.ini
sudo sed -i 's/max_execution_time = 30$/max_execution_time = 120/' /etc/php5/apache2/php.ini
sudo sed -i 's/memory_limit = 128M$/memory_limit = 1024M/' /etc/php5/apache2/php.ini
cd ~/Downloads
wget https://innotop.googlecode.com/files/innotop-1.9.1.tar.gz
tar -xvzf innotop-1.9.1.tar.gz
cd innotop-1.9.1
perl ./Makefile.PL
sudo cp innotop /usr/bin
cd ~/Downloads
git clone https://github.com/jonnyboy/powerline-fonts.git
sudo gnome-font-viewer ~/Downloads/powerline-fonts/Consolas/Consolas\ for\ Powerline.ttf
sudo cp /var/www/nZEDbetter/misc/setup_scripts/nzedbetter.conf /etc/apache2/sites-available/nZEDbetter.conf
sudo a2ensite nZEDbetter
sudo a2dissite 000-default.conf
sudo service apache2 reload
cd ~/Downloads
wget http://mediaarea.net/download/binary/libzen0/0.4.29/libzen0_0.4.29-1_amd64.xUbuntu_13.04.deb
wget http://mediaarea.net/download/binary/libmediainfo0/0.7.64/libmediainfo0_0.7.64-1_amd64.xUbuntu_13.04.deb
wget http://mediaarea.net/download/binary/mediainfo/0.7.64/mediainfo_0.7.64-1_amd64.Debian_7.0.deb
sudo dpkg -i libzen0_0.4.29-1_amd64.xUbuntu_13.04.deb
sudo dpkg -i ibmediainfo0_0.7.64-1_amd64.xUbuntu_13.04.deb
sudo dpkg -i mediainfo_0.7.64-1_amd64.Debian_7.0.deb
sudo apt-get -f -y install
mkdir ~/ffmpeg_sources
cd ~/ffmpeg_sources
wget http://www.tortall.net/projects/yasm/releases/yasm-1.2.0.tar.gz
tar xzvf yasm-1.2.0.tar.gz
cd yasm-1.2.0
./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin"
make
make install
make distclean
cd ~
. ~/.profile
export PATH=$HOME/bin:$PATH
cd ~/ffmpeg_sources
git clone --depth 1 git://git.videolan.org/x264.git
cd x264
./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin" --enable-static
make
make install
make distclean
cd ~/ffmpeg_sources
git clone --depth 1 git://github.com/mstorsjo/fdk-aac.git
cd fdk-aac
autoreconf -fiv
./configure --prefix="$HOME/ffmpeg_build" --disable-shared
make
make install
make distclean
sudo apt-get -y install libmp3lame-dev
cd ~/ffmpeg_sources
wget http://downloads.xiph.org/releases/opus/opus-1.0.3.tar.gz
tar xzvf opus-1.0.3.tar.gz
cd opus-1.0.3
./configure --prefix="$HOME/ffmpeg_build" --disable-shared
make
make install
make distclean
cd ~/ffmpeg_sources
git clone --depth 1 http://git.chromium.org/webm/libvpx.git
cd libvpx
./configure --prefix="$HOME/ffmpeg_build" --disable-examples
make
make install
make clean
cd ~/ffmpeg_sources
git clone --depth 1 git://source.ffmpeg.org/ffmpeg
cd ffmpeg
PKG_CONFIG_PATH="$HOME/ffmpeg_build/lib/pkgconfig"
export PKG_CONFIG_PATH
cd ~
. ~/.profile
cd ~/ffmpeg_sources/ffmpeg
./configure --prefix="$HOME/ffmpeg_build" \
 --extra-cflags="-I$HOME/ffmpeg_build/include" --extra-ldflags="-L$HOME/ffmpeg_build/lib" \
 --bindir="$HOME/bin" --extra-libs="-ldl" --enable-gpl --enable-libass --enable-libfdk-aac \
 --enable-libmp3lame --enable-libopus --enable-libtheora --enable-libvorbis --enable-libvpx \
 --enable-libx264 --enable-nonfree --enable-x11grab
make
make install
make distclean
hash -r
sudo cp /var/www/nZEDbetter/misc/update_scripts/nix_scripts/tmux/powerline/powerline/themes/default.sh /var/www/nZEDbetter/misc/update_scripts/nix_scripts/tmux/powerline/powerline/themes/tmux.sh
echo "export PATH=$HOME/bin:$PATH" >> ~/.bashrc
echo 'alias tmux-dir="cd /var/www/nZEDbetter/misc/update_scripts/nix_scripts/tmux"' >> ~/.bash_aliases
echo 'alias www-dir="cd /var/www/nZEDbetter/www"' >> ~/.bash_aliases
echo 'alias misc-dir="cd /var/www/nZEDbetter/misc"' >> ~/.bash_aliases
echo 'alias sqlstart="sudo service mysql start"' >> ~/.bash_aliases
echo 'alias sqlstop="sudo service mysql stop"' >> ~/.bash_aliases
echo 'alias sqllog="sudo tail -f /var/lib/mysql/mysql-error.log"' >> ~/.bash_aliases
echo -e '\n\n\nCONGRATULATIONS - The setup script has completed.  Do not forget to do the following:\n'
echo -e '	1. Set the default time zone in /etc/php5/cli/php.ini'
echo -e '	2. Set the default time zone in /etc/php5/apache2/php.ini'
echo -e '	3. Create a my.cnf and save it in /etc/mysql/my.cnf'
echo -e '	   Go to http://tools.percona.com to create a base my.cnf tailored to your hardware\n'
cd ~
. .bashrc

