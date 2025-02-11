# Static Site Export

An [Omeka S](https://omeka.org/s/) module for exporting static sites.

## Developer notes

### Building your static-site

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
        'directory-name' => '/path/to/js/directory',
    ],
]
```

Where "directory-name" is the name of the directory that will be created in the
static-site JS directory; and "/path/to/js/directory" is the absolute path of the
directory that contains all assets needed for the JS dependency. These assets will
be copied to the newly created directory.

To include a script on a page, add the script's path the "js" array on the page's
front matter:

```
$frontMatter['js'][] = 'js/path/to/script.js';
```

### Log commands

The export job executes quite a few shell commands. These are not logged by default
because for large sites the log will likely grow to surpass the memory limit. For
debugging, modules can turn on command logging in module configuration:

```
'static_site_export' => [
    'log_commands' => true,
]
```

### Services

Modules can add content to their static sites via the provided services in module
configuration:

- `['static_site_export']['block_layouts']`: Add site page block layouts (Hugo page content)
- `['static_site_export']['data_types']`: Add data types (Hugo page content)
- `['static_site_export']['file_renderers']`: Add file renderers (Hugo page content)
- `['static_site_export']['media_renderers']`: Add media renderers (Hugo page content)
- `['static_site_export']['navigation_links']`: Add naigation links (Hugo menu entries)
- `['static_site_export']['resource_page_block_layouts']`: Add resource page block layouts (Hugo page content)

See their respective interfaces to see how to implement them.

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
