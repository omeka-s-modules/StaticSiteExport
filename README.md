# Static Site Export

An [Omeka S](https://omeka.org/s/) module for exporting static sites.

## Developer notes

### Static-site template

The "static-site template" is a directory containing Hugo layouts, static files,
and default site configuration that the export job copies to the sites directory
then modifies according to the export settings. Once exported, you may use Hugo
to build the static site (see below).


If you made changes to the static-site template, make sure you update the template
ZIP file before pushing changes:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data
$ rm -rf static-site.zip && zip -r static-site.zip static-site/
```

After exporting a static site and unzipping the resulting ZIP file, you can use
Hugo to build the static site (note that this installs a test theme):

```
$ cd /path/to/static-sites/<export_name>
$ git init && git submodule add https://github.com/theNewDynamic/gohugo-theme-ananke.git themes/ananke && hugo server
```

### JS dependencies

Modules can add JavaScript dependencies by registering them in module configuration:

```
'static_site_export' => [
    'js_dependencies' => [
        'vendor-directory-name' => '/path/to/js/directory',
    ],
]
```

Where "vendor-directory-name" is the name of the directory that will be created
in the static-site JS vendor directory; and "/path/to/js/directory" is the absolute
path of the directory that contains all assets needed for the JS dependency. These
assets will be copied to the new vendor directory.

### Log commands

The export job executes quite a few shell commands. These are not logged by default
because for large sites the log will likely grow to surpass the memory limit. For
debugging, modules can turn on command logging in module configuration:

```
'static_site_export' => [
    'log_commands' => true,
]
```

### Events

There are several events that modules can use to modify resource pages:

#### static_site_export.item_page

- `item`: The item representation
- `frontMatter`: An `ArrayObject` containing page front matter
- `markdown`: An `ArrayObject` containing page Markdown

#### static_site_export.media_page

- `media`: The media representation
- `frontMatter`: An `ArrayObject` containing page front matter
- `markdown`: An `ArrayObject` containing page Markdown

#### static_site_export.item_set_page

- `itemSet`: The item set representation
- `frontMatter`: An `ArrayObject` containing page front matter
- `markdown`: An `ArrayObject` containing page Markdown

# Copyright

StaticSiteExport is Copyright © 2020-present Corporation for Digital Scholarship, Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code under the GNU General Public License, version 3 (GPLv3). The full text of this license is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.
