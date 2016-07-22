# avelsieve2roundcube

Konvertierung von avelsieve Filtern zu Roundcube Sieve Filtern

```sh
cat phpscript.sieve | php avelsieve2roundcube.php
```

**Wichtig :exclamation: Derzeit muss man sich im selben Ornder wie das PHP-Script befinden**

**Auch Wichtig :exclamation: php5-imap oder php7.0-imap *muss* installiert sein :bangbang:**

Bsp. für Konvertierung aller Nutzer (nicht getestet, ggf. Syntax und Pfade prüfen :exclamation: :sweat_smile: )
```sh
for script in $( find /var/vmail/users -type f -name phpscript.sieve )
do
	#Falls es "/var/vmail/users/u/user/phpscript.sieve" war...
	user=$(basename $(dirname $script))#
	#Falls es doch "/var/vmail/users/u/user/sieve/phpscript.sieve" war dann...
	#user=$(basename $(dirname $(dirname $script)))
	echo Converting $script to /var/vmail/sieve/$(head -c 1 $user)/${user}/filter.sieve
	cat $script | php avelsieve2roundcube.php > /var/vmail/sieve/$(head -c 1 $user)/${user}/filter.sieve
	echo Done
	echo
done
```
