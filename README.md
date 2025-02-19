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

### Modifying the Hugo theme

To modify the Hugo theme, first clone it in the module's data/ directory:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data/
$ git clone git@github.com:omeka/gohugo-theme-omeka-s.git
```

Make sure that you clone the repository in the module's data/ direcotry. Here, Git
will ignore the theme directory so you don't accidentally commit it to the module
repository.

After modifying the theme, make sure you update the theme's ZIP file before pushing
changes:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data/
$ zip --recurse-paths gohugo-theme-omeka-s.zip gohugo-theme-omeka-s/ --exclude '*.git*'
```

Note that we're excluding the .git/ directory from the ZIP file.

### Adding vendor packages

Modules can add vendor packages (e.g. scripts, styles, images) by registering them
in module configuration:

```
'static_site_export' => [
    'vendor_packages' => [
        'package-name' => '/path/to/vendor/directory',
    ],
]
```

Where "package-name" is the name of the directory that will be created in the
static site vendor directory; and "/path/to/vendor/directory" is the absolute path
of the directory that contains all assets needed for the package. These assets will
be copied to the newly created static site vendor directory.

### Adding JS and CSS

To include JS on a page, add the JS path the "js" array on a page's front matter:

```
$frontMatter['js'][] = 'vendor/path/to/script.js';
```

To include CSS on a page, add the CSS path the "css" array on a page's front matter:

```
$frontMatter['css'][] = 'vendor/path/to/style.css';
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

Modules can add content to (or otherwise modify) static sites via the provided events
in their `Module::attachListeners()`:

- Add content to page bundles:
    - **static_site_export.bundle.item**
        - `resource`: The item representation
    - **static_site_export.bundle.media**
        - `resource`: The media representation
    - **static_site_export.bundle.item_set**
        - `resource`: The item set representation
    - **static_site_export.bundle.asset**
        - `resource`: The asset representation
    - **static_site_export.bundle.site_page**
        - `resource`: The site page representation
- Add content to pages:
    - **static_site_export.page.item**
        - `resource`: The item representation
        - `frontMatter`: An `ArrayObject` containing page front matter
        - `markdown`: An `ArrayObject` containing page Markdown
    - **static_site_export.page.media**
        - `resource`: The media representation
        - `frontMatter`: An `ArrayObject` containing page front matter
        - `markdown`: An `ArrayObject` containing page Markdown
    - **static_site_export.page.item_set**
        - `resource`: The item set representation
        - `frontMatter`: An `ArrayObject` containing page front matter
        - `markdown`: An `ArrayObject` containing page Markdown
    - **static_site_export.page.asset**
        - `resource`: The asset representation
        - `frontMatter`: An `ArrayObject` containing page front matter
        - `markdown`: An `ArrayObject` containing page Markdown
    - **static_site_export.page.site_page**
        - `resource`: The site page representation
        - `frontMatter`: An `ArrayObject` containing page front matter
        - `markdown`: An `ArrayObject` containing page Markdown

In your handler, use `$event->getTarget()` to get the export job object, which
has methods that could be useful. Use `$event->getParam('resource')` to get the
resource representation.

You may append Hugo front matter to pages using the `frontMatter` parameter, like
so:

```
$frontMatter = $event->getParam('frontMatter');
$frontMatter['params']['myParam'] = 'foobar';
```

You may append markdown to pages using the `markdown` parameter, like so:

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
