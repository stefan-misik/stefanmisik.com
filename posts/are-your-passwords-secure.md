# Are Your Passwords Secure?
- published: 2019-03-14T18:58:20+0000
- tags: security

Recently I discovered interesting internet service, which claims to had
collected

> 551,509,767 real world passwords previously exposed in data breaches.

<span style="display: block; float: right;">
[Pwned Passwords](https://haveibeenpwned.com/Passwords)
</span>
<span style="display:block; clear:both;">
</span>

Whats better this service uses this information for good, since it lets you
verify security level of your passwords by looking them up in this giant
database of password breaches.

It, however, does this in a secure way too: it uses approach called
[*k*-anonymity](https://en.wikipedia.org/wiki/K-anonymity) to protect you form
not only exposing your password, but also form revealing the information whether
your password indeed had been exposed in an data breach.

The service provides an [API](https://haveibeenpwned.com/API/v2) to let you
access this database. Naturally I had to take an advantage of this and I wrote a
simple shell script that lets you verify security of your passwords. You can
find the script in its [GitHub
repository](https://github.com/stefan-misik/pwnpass).


