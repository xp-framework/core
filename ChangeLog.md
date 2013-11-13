XP Framework Core ChangeLog
========================================================================

## 6.0.0 / ????-??-??

### Heads up!

* Removed deprecated `ref()` and `deref()` functionality - (@thekid)
* Moved classes inside text.util - Random(Code|Password)Generator to the
  package security.password - (@thekid)
* Moved classes inside scriptlet.rpc to webservices.rpc - (@thekid)

### RFCs

* Implemented RFC #186: Drop SAPI feature alltogether; after it was 
  deprecated since 5.9.0 - (@thekid)
* Implemented RFC #262: Split up framework into minimal pieces:
  - Extracted `util.telephony` into https://github.com/xp-framework/telephony
  - Extracted `xml` into https://github.com/xp-framework/xml
  - Extracted `xp.codegen` into https://github.com/xp-framework/codegen
  - Extracted `peer.webdav` into https://github.com/xp-framework/webdav
  - Extracted `peer.sieve` into https://github.com/xp-framework/sieve
  - Extracted `peer.irc` into https://github.com/xp-framework/irc
  - Extracted `peer.news` into https://github.com/xp-framework/news
  - Extracted `peer.mail` into https://github.com/xp-framework/mail
  - Extracted `peer.ldap` into https://github.com/xp-framework/ldap
  - Extracted `peer.http` into https://github.com/xp-framework/http
  - Extracted `peer.ftp` into https://github.com/xp-framework/ftp
  - Extracted `img` into https://github.com/xp-framework/imaging
  - Extracted `scriptlet` into https://github.com/xp-framework/scriptlet
  - Extracted `webservices` into https://github.com/xp-framework/webservices
  - Extracted `webservices.rest` into https://github.com/xp-framework/rest
  - Extracted `rdbms` into https://github.com/xp-framework/rdbms
  - Extracted `io.archive.zip` into https://github.com/xp-framework/zip
  - Extracted `text.spelling` into https://github.com/xp-framework/spelling
  - Extracted `text.parser` into https://github.com/xp-framework/parser
  - Extracted `text.csv` into https://github.com/xp-framework/csv
  - Extracted `text.doclet` into https://github.com/xp-framework/doclet
  - Extracted `remote` into https://github.com/xp-framework/remote
  (@kiesel, @thekid)

### Features

* Extended the `with` statement to to work with lang.Closeable instances.
  See xp-framework/core#2 - (@thekid)
