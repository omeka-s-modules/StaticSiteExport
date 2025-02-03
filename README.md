# Static Site Export

An [Omeka S](https://omeka.org/s/) module for exporting static sites.

## Developer notes

Build the static site (note we're installing a test theme):

```
$ cd /path/to/static-site
$ git init && git submodule add https://github.com/theNewDynamic/gohugo-theme-ananke.git themes/ananke && hugo server
```

Create the template ZIP file (must run after modifying data/static-site):

```
$ cd /path/to/static-site/data
$ rm -rf static-site.zip && zip -r static-site.zip static-site/
```

Install JS dependencies:

```
$ cd /path/to/static-site/data/static-site/static/js
$ npm install
```

# Copyright

DataVis is Copyright © 2020-present Corporation for Digital Scholarship, Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code under the GNU General Public License, version 3 (GPLv3). The full text of this license is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.
