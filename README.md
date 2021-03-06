Upgrade to Omeka Semantic
=========================

[Upgrade to Omeka Semantic] is a plugin for [Omeka Classic] that allows to
upgrade automatically your installation from [Omeka 2] to [Omeka S]. It upgrades
records, files, config, themes and some plugins.

[Omeka S] is a remastered, up-to-date and improved release of Omeka, built for
the semantic web, multi-sites and multilingual. So, this plugin allows to use
this new features without the mess-up of a manual upgrade process. The main
point is to keep original metadata unchanged, but improved to take care of new
features of Omeka S.

The original install of Omeka Classic is kept and not modified: it remains
available. If the upgrade is fine for your digital library or your exhibit,
generally with a new standard theme, simply modify the paths on your server.

A compatibility layer is available for themes with the module [Upgrade from Omeka Classic],
that is installed automatically too. The themes are restructured and upgraded,
but custom functions may fail. In that case, use new [themes], or use the
template engine [Twig] via the [module Twig], or contact me. Anyway, in all
cases, the theme should be reviewed, because there will be visual glitches and
fixes in most of the cases. This compatibility layer is needed only when an
upgraded theme is used.

Of course, if a plugin doesn’t exist under Omeka S, it won’t be upgraded.
Furthermore, a processor should be written to upgrade data. There are already
such a  processor for the most common plugins, that are integrated or have an
equivalent module: [Dublin Core Extended], [Embed Codes], [Exhibit Builder],
[Geolocation],  [More User Roles],  [Simple Pages], [Social Bookmarking], [Universal Viewer],
[Guest User], etc. If enabled, the modules will be automatically installed. For
other plugins, contact me.

See the full list of [plugins for Omeka 2 and matching modules for Omeka S], and
all [modules] and [themes] that are already available for Omeka S.


Benefits
--------

* Ids of items

One important benefit against the standard upgrade process or via [Omeka 2 Importer]
is that the id of items are kept, so the common urls publicly used on the web
are not lost. The id of collections and files are lost. This is related to the
fact that Omeka Semantic uses a single id for all resources.

Users are upgraded too, but they have to ask for a new password on the login
page. The pages and exhibits keep their slugs.

Anyway, it’s always recommended you set your own single and permanent
identifiers that don’t depend on an internal position in a database. The term
`Dublin Core Identifier` is designed for that and a record can have multiple
single identifiers. There are many possibilities: named number like in a library
or a museum, isbn for books, or random id like with ark, noid, doi, etc. In
Omeka 2, they can be displayed in the public url with the plugin [Clean Url].

Furthermore, the compatibility layer adds aliases for old urls `items/show/#id`
to the new format `item/#id` and redirects them to the items of the specified
site of Omeka S, and the same for collections, files and homepage.

* Automatic process and speed

A second benefit is that the process is fast and automatic. You don’t need to
ask your IT department for a new server, or to process an initial setup, that is
a little longer in Omeka S (even very simple) since there is no default site.
Indeed, the config itself is upgraded. For example, the path to the php  cli, a
common question on the forum of Omeka forum, is kept.

This is mostly transparent to the admin and to the users: they won’t see
anything change (if the theme is fully checked). Conversely, the curators will
benefits from all the new features of Omeka S. The machines too: Omeka S is
designed to share its resources larger and in a more standardized way than
Omeka 2.

The last important benefit is that your site will respond visibly faster,
especially when PHP 7 is used, since Omeka S is based on [Zend 3] and [Doctrine].


Installation
------------

Uncompress files and rename plugin folder `UpgradeToOmekaS`.

Then install it like any other Omeka plugin and follow the config instructions.

*IMPORTANT*: Even if the original files and the original database are only read,
backup your database AND your files before the process and check them before the
process.

The plugin has been tested from v2.3.1, but it probably works with Omeka v2.2.2.
Just change the setting in plugin.ini to try it. Nevertheless, it’s always
recommended to upgrade the core, all the plugins and the theme to the last
version.

You may need to upgrade your plugins to the current version. Only enabled and
managed plugins are processed.


Usage
-----

The process is automatic from an up-to-date Omeka install. Log in as a super
user, then simply click on "Upgrade" in the top admin bar, fill the short form,
click the `Submit` button, wait from a few tens of seconds, and click on the
provided url.

The urls of the main page and of the login page are provided. If the main page
is broken, fix the theme or log in and change the theme to one of the [official themes]
or use on upgraded official theme of Omeka Classic.

To add a new upgrader, simply add it in the directory `/libraries/UpgradeToOmekaS/Processor`
or use the filter `upgrade_omekas`. You can look in the upgrader `Escher.php`
for a basic upgrader.

The last update of this plugin install the last stable version of Omeka S and
try to install the last version of the modules, when possible, but you may have
to update the modules inside Omeka S.


Database encoding fix
---------------------

In rare cases, the encoding of characters in Omeka S is not the good one.
To fix it, run the following commands on the Omeka S database (via adminer,
phpmysql, or in command line):

```SQL
# IMPORTANT: Backup the Omeka S database first, even if you can redo the upgrade!

# Check conversion on the first 100 rows.
# The original character encoding should be checked (here "latin1").
SELECT id, value, CONVERT(BINARY CONVERT(value USING latin1) USING utf8mb4) AS conv FROM `value` WHERE CHAR_LENGTH(value) != LENGTH(value) LIMIT 100;

# WARNING: the following query works only one time: it will fail if executed a second time.
# You may have to set another original encoding (here "latin1").
UPDATE `value` SET value = CONVERT(BINARY CONVERT(value USING latin1) USING utf8mb4) WHERE CHAR_LENGTH(value) != LENGTH(value);

# To get all the rows to fix.
SELECT id, value FROM (SELECT id, value, CHAR_LENGTH(value) AS c, LENGTH(value) AS l, CHAR_LENGTH(CONVERT(BINARY CONVERT(value USING latin1) USING utf8mb4)) AS cc FROM `value`) AS x WHERE c != l AND c != cc;
```

Or you can execute file [`fix_utf8mb4.sql`] directly, so all tables will be
processed. Note: only core tables are fixed currently, you must check the
specific tables.


Internal Upgrade Process
------------------------

The upgrade process follows these steps. All steps are automatic except the
first and the last.

* *IMPORTANT*: backup your database and your files manually and check them.
* Optional: create manually the database if you want a separate one, else, the
  database will be shared with the one of Omeka Classic. This is not a problem,
  but currently, Omeka S doesn’t manage prefixes. Fortunately, there was one by
  default in Omeka 2 ("omeka_"). Furthermore, Omeka 2 creates tables with plural
  names (like "users") and Omeka S uses singular names ("user"), so even if the
  prefix was removed during install, there won’t be issue. Anyway, it’s
  recommended to create a separate database.
* Checks.
  * rights to write in the specified directory
  * version and config of Omeka
  * version of plugins
* Set the site down, except for the super user.
* Fetch last release of Omeka S from Github, uncompress it and install it.
* Set parameters.
  * new database or current one
  * main settings
* Conversion of all Omeka Classic tables (users, records, plugins...) into the
Omeka Semantic ones via simple mysql queries.
* Copy of files, hard links (recommended on linux servers), soft links or dummy
  files.
* Copy, reorganize and upgrade themes.
* Install the compatibility layer and upgraded plugins.
* Check manually main settings and parameters of each module of Omeka Semantic.

The site will be available automatically at the end of the process with the
specified link.

Enjoy it!


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [plugin issues] page on GitHub.


License
-------

This plugin is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user’s
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.


TODO
----

* Clean the utf8mb4 database encoding issue (add a function on each value, like
  for the user).
* Include translations of Omeka Classic.
* Check "todo" in the source of the plugin.


Contact
-------

Current maintainers:

* Daniel Berthereau (see [Daniel-KM] on GitHub)


Copyright
---------

* Copyright Daniel Berthereau, 2017


[Upgrade to Omeka Semantic]: https://github.com/Daniel-KM/Omeka-plugin-UpgradeToOmekaS
[Upgrade from Omeka Classic]: https://github.com/Daniel-KM/Omeka-S-module-UpgradeFromOmekaClassic
[Omeka]: https://www.omeka.org
[Omeka Classic]: https://omeka.org/classic
[Omeka Semantic]: https://omeka.org/s
[Omeka 2]: https://omeka.org/classic
[Omeka S]: https://omeka.org/s
[Dublin Core Extended]: http://omeka.org/add-ons/plugins/dublin-core-extended/
[Embed Codes]: http://omeka.org/add-ons/plugins/embed-codes/
[Exhibit Builder]: http://omeka.org/add-ons/plugins/exhibit-builder/
[Geolocation]: http://omeka.org/add-ons/plugins/geolocation/
[More User Roles]: https://github.com/ebellempire/MoreUserRoles
[Simple Pages]: http://omeka.org/add-ons/plugins/simple-pages/
[Social Bookmarking]: http://omeka.org/add-ons/plugins/social-bookmarking/
[Universal Viewer]: https://github.com/Daniel-KM/Omeka-plugin-UniversalViewer
[Guest User]: https://github.com/omeka/plugin-GuestUser
[plugins for Omeka 2 and matching modules for Omeka S]: https://daniel-km.github.io/UpgradeToOmekaS
[modules]: https://daniel-km.github.io/UpgradeToOmekaS/omeka_s_modules.html
[official themes]: https://daniel-km.github.io/UpgradeToOmekaS/omeka_s_themes.html
[themes]: https://daniel-km.github.io/UpgradeToOmekaS/omeka_s_themes.html
[Twig]: http://twig.sensiolabs.org
[module Twig]: https://github.com/digihum/omeka-s-twig
[Omeka 2 Importer]: https://github.com/omeka-s-modules/Omeka2Importer
[Clean Url]: https://github.com/Daniel-KM/Omeka-plugin-CleanUrl
[Zend 3]: https://framework.zend.com/
[Doctrine]: http://www.doctrine-project.org/
[`fix_utf8mb4.sql`]: https://github.com/Daniel-KM/Omeka-plugin-UpgradeToOmekaS/blob/master/fix_utf8mb4.sql
[plugin issues]: https://github.com/Daniel-KM/Omeka-plugin-UpgradeToOmekaS/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
