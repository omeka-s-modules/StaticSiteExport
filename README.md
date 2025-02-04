# Static Site Export

An [Omeka S](https://omeka.org/s/) module for exporting static sites.

## Developer notes

The "static-site template" is a directory containing Hugo layouts, static files,
and default site configuration that the export job copies to the sites directory
then modifies according to the export settings. Once exported, you may use Hugo
to build the static site (see below).

If you need to add a JS package to the static-site template:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data/static-site/static/js
$ npm install <package_name>
```

If you made changes to the static-site template, make sure you create the template
ZIP file before pushing changes:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data
$ rm -rf static-site.zip && zip -r static-site.zip static-site/
```

After exporting a static site, you can use Hugo to build the static site (note that
this installs a test theme):

```
$ cd /path/to/static-sites/<export_name>
$ git init && git submodule add https://github.com/theNewDynamic/gohugo-theme-ananke.git themes/ananke && hugo server
```

# Copyright

DataVis is Copyright © 2020-present Corporation for Digital Scholarship, Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code under the GNU General Public License, version 3 (GPLv3). The full text of this license is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.
