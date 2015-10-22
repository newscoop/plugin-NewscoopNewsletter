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

Installing Newscoop Newsletter Plugin Guide
-------------
Installation is a quick process:


1. Installing plugin through our Newscoop Plugin System
2. That's all!

### Step 1: Installing plugin through our Newscoop Plugin System
Run the command:
``` bash
$ php application/console plugins:install "newscoop/newsletter-plugin-bundle"
$ php application/console assets:install public/
```
Plugin will be installed to your project's `newscoop/plugins/Newscoop` directory.


### Step 2: That's all!
Go to Newscoop Admin panel and then hit `Plugins` tab. Newscoop Newsletter Plugin will show up there.

Documentation:
----

Documentation can be found [here]().

License
-------

This bundle is under the GNU General Public License v3. See the complete license in the bundle:

    LICENSE.txt

About
-------
Newsletter Plugin Bundle is a [Sourcefabric z.ú.](https://github.com/sourcefabric) initiative.
