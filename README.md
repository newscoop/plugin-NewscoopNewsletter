Newsletter Plugin
=========================

Simple and flexible plugin integrated with MailChimp service. It allows Newscoop users to subscribe to MailChimp lists. It also allows administrator to manage MailChimp lists in backend.

Features:
----
  - very simple
  - possibility to subscribe to your MailChimp lists
  - simple MailChimp' lists management in backend
  - can be integrated with Newscoop registration form or user profile
  - manual lists synchronization
  - possibility to disable/enable lists
  - partial MailChimp groups support (see documentation)

Installation
-------------
Installation is a quick process:


1. How to install this plugin?
2. That's all!

### Step 1: How to install this plugin?
Run the command:
``` bash
$ php application/console plugins:install "newscoop/newsletter-plugin-bundle"
$ php application/console assets:install public/
```
Plugin will be installed to your project's `newscoop/plugins/Newscoop` directory.

### Step 2: That's all!
Go to Newscoop Admin panel and then open `Plugins` tab. The Plugin will show up there. You can now use the plugin.


**Note:**

To update this plugin run the command:
``` bash
$ php application/console plugins:update "newscoop/newsletter-plugin-bundle"
$ php application/console assets:install public/
```

To remove this plugin run the command:
``` bash
$ php application/console plugins:remove "newscoop/newsletter-plugin-bundle"
```

Documentation:
-------------

The extended documentation can be found [here](https://wiki.sourcefabric.org/display/NPS/Newsletter).

Technical documentation can be found [here](https://github.com/newscoop/plugin-NewscoopNewsletter/blob/master/Resources/doc/index.md).

License
-------

This bundle is under the GNU General Public License v3. See the complete license in the bundle:

    LICENSE

About
-------
Newsletter Plugin Bundle is a [Sourcefabric z.ú.](https://github.com/sourcefabric) initiative.
