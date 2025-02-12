# Static Site Export

An [Omeka S](https://omeka.org/s/) module for exporting static sites.

## Building a static site

After exporting a static site, you can unzip the resulting ZIP file and immediately
use Hugo to build and view the static site:

```
$ cd /path/to/static-sites/
$ unzip <export-name>.zip
$ cd <export-name>/
$ hugo server
```

After the build is complete, follow the instructions and go to http://localhost:1313/
in your browser.

## Developer notes

### The Omeka theme

To make changes to the Omeka theme, extract the theme ZIP file and do whatever work
you need in the resulting directory:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data
$ unzip gohugo-theme-omeka-s.zip
```

After modifying the theme, make sure you update the theme ZIP file before pushing
changes:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data
$ rm -rf gohugo-theme-omeka-s.zip && zip -r gohugo-theme-omeka-s.zip gohugo-theme-omeka-s/
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
be copied to the newly created static site directory.

To include a script on a page, add the script's path the "js" array on the page's
front matter:

```
$frontMatter['js'][] = 'js/path/to/script.js';
```

### Shortcodes

Modules can add Hugo shortcodes by registering them in module configuration:

```
'static_site_export' => [
    'shortcodes' => [
        'shortcode-name' => '/path/to/shortcode-name.html',
    ],
]
```

Where "shortcode-name" is the name of the shortcode; and "/path/to/shortcode-name.html"
is the absolute path of the shortcode file. These shortcodes will be copied to the
newly created static site directory.

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

- static_site_export.item_page
    - `item`: The item representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `markdown`: An `ArrayObject` containing page Markdown
- static_site_export.media_page
    - `media`: The media representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `markdown`: An `ArrayObject` containing page Markdown
- static_site_export.item_set_page
    - `itemSet`: The item set representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `markdown`: An `ArrayObject` containing page Markdown

# Copyright

StaticSiteExport is Copyright © 2020-present Corporation for Digital Scholarship, Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code under the GNU General Public License, version 3 (GPLv3). The full text of this license is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.
