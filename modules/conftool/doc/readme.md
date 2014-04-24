# <a id="Migration"></a> Migration

## Icinga 1.x to 2.x Migration

    $ ./configure --prefix=/usr/share/icingaweb --datadir=/usr/share/icingaweb && sudo make install && sudo icingacli conftool parse v1 /etc/icinga/icinga.cfg

