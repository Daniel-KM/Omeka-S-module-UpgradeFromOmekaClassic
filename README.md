Upgrade from Omeka Classic
==========================

[![Build Status](https://travis-ci.org/Daniel-KM/Omeka-S-module-UpgradeFromOmekaClassic.svg?branch=master)](https://travis-ci.org/Daniel-KM/Omeka-S-module-UpgradeFromOmekaClassic)

[Upgrade from Omeka Classic] is a module for [Omeka Semantic] that allows to
reuse a theme built for [Omeka 2] and upgraded via the plugin [Upgrade to Omeka Semantic].

Furthermore, it adds a route for old urls `items/show/#id` to the new format
`item/#id` and redirects them to the items of the specified site of Omeka S.

If the theme was not upgraded, this module is useless.

For more information about the upgrade, see [Upgrade to Omeka Semantic]. See a
full list of [modules] and [themes] for Omeka S.

Omeka S is still in a beta phase, but it can be already used for common sites.


Installation
------------

Uncompress files and rename module folder `UpgradeFromOmekaClassic`.

Then install it like any other Omeka module and follow the config instructions.

*IMPORTANT*

If you change the slug of the main site, don't forget to set it in the config
file `config/module.config.php`.


Usage
-----

As a compatibility layer, some visual glitches and bugs may subsist, in
particular when the theme is heavily customized.
You may check the themes and change the main layout and each view in order to
replace old Omeka Classic functions by Omeka S ones. See the [official themes]
to discover the new  methods, or check the integrated views in `application/view-admin`
and `application/view-shared`.

If you use a new theme, you don't need this plugin. If you just want to keep
routes, simply copy the config of the routes from the file `config/module.config.php`
to the main config of the site `config/local.config.php`.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and database regularly so you can
roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitHub.


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


Contact
-------

Current maintainers:

* Daniel Berthereau (see [Daniel-KM] on GitHub)


Copyright
---------

* Copyright Daniel Berthereau, 2017


[Upgrade from Omeka Classic]: https://github.com/Daniel-KM/Omeka-S-module-UpgradeFromOmekaClassic
[Upgrade to Omeka Semantic]: https://github.com/Daniel-KM/UpgradeToOmekaS
[Omeka]: https://www.omeka.org
[Omeka Classic]: https://omeka.org
[Omeka Semantic]: https://omeka.org/s
[Omeka 2]: https://omeka.org
[Omeka S]: https://omeka.org/s
[modules]: https://daniel-km.github.io/UpgradeToOmekaS/omeka_s_modules.html
[themes]: https://daniel-km.github.io/UpgradeToOmekaS/omeka_s_themes.html
[official themes]: https://github.com/omeka-s-themes
[module issues]: https://github.com/Daniel-KM/Omeka-S-module-UpgradeFromOmekaClassic/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
