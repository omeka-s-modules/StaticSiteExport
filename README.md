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

### Modifying the Omeka theme

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

### Adding JavaScript dependencies

Modules can add JS dependencies by registering them in module configuration:

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

### Adding Hugo shortcodes

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

### Using services to add content

Modules can add content to static sites via the provided services in module configuration:

- `['static_site_export']['block_layouts']`: Add site page block layouts (Hugo page content)
- `['static_site_export']['data_types']`: Add data types (Hugo page content)
- `['static_site_export']['file_renderers']`: Add file renderers (Hugo page content)
- `['static_site_export']['media_renderers']`: Add media renderers (Hugo page content)
- `['static_site_export']['navigation_links']`: Add naigation links (Hugo menu entries)
- `['static_site_export']['resource_page_block_layouts']`: Add resource page block layouts (Hugo page content)

These services are parallel to existing Omeka services, and have identical purposes,
but they are for static sites instead of Omeka sites. See the module configuration
file and the respective interfaces to see how to implement them.

### Using events to add content

Modules can add content to static sites via the provided events `Module::attachListeners()`:

- **static_site_export.item_page**
    - `item`: The item representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `markdown`: An `ArrayObject` containing page Markdown
- **static_site_export.media_page**
    - `media`: The media representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `markdown`: An `ArrayObject` containing page Markdown
- **static_site_export.item_set_page**
    - `itemSet`: The item set representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `markdown`: An `ArrayObject` containing page Markdown

In your handler, append Hugo front matter to pages using the `frontMatter` parameter,
like so:

```
$frontMatter = $event->getParam('frontMatter');
$frontMatter['params']['myParam'] = 'foobar';
```

Also in your handler, append markdown to pages using the `markdown` parameter, like
so:

```
$markdown = $event->getParam('markdown');
$markdown[] = 'foobar';
```

Note that `frontMatter` and `markdown` are array objects, so you can modify them
and they will take effect without having to set them back on the event.

### Logging commands

The export job executes quite a few shell commands. These are not logged by default
because for large sites the log will likely grow to surpass the memory limit. For
debugging, modules can turn on command logging in module configuration:

```
'static_site_export' => [
    'log_commands' => true,
]
```

# Copyright

StaticSiteExport is Copyright © 2020-present Corporation for Digital Scholarship, Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code under the GNU General Public License, version 3 (GPLv3). The full text of this license is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.
