# Static Site Export

An [Omeka S](https://omeka.org/s/) module for exporting static sites.

## End user notes

This section is meant for end users. If you are using this module only to export
and build a static site, the following applies to you.

### Configuring the module

After installing this module, you will need to add a "Sites directory path" in the
module configuration page. This is the path to the directory where your static sites
will be saved on your server. The path must exist and be writable by the web server.

### Exporting a static site

After installing and configuring this module, go to an Omeka site in the administrative
interface, click on "Static Site Export" in the navigation, and click the "Export
new static site" button.

On this page you will configure the export by entering a "Base URL", selecting a
"Theme", and declaring whether to "Include private resources".

After configuring the export, click the "Begin export" button. The new export will
be the first in the list. Click on the "Details" icon for information about the
export, including status of the export job. The export is finished once the status
is marked as "Completed".

Note that an export may take a long time, depending on the size of the site.

Note that the export runs several widely used commands on your server: `cd`, `cp`,
`rm`, `unzip`, and `zip`. While these commands are required during export, they
are likely already installed on your server, so there's nothing you need to do.

### Building a static site

After exporting a static site, you can unzip the resulting ZIP file and immediately
use [Hugo](https://gohugo.io/) to build the site, run a local testing server, and
view the site:

```
cd /path/to/static-sites/
unzip <export-name>.zip
cd <export-name>/
hugo server
```

After the build is complete, follow the instructions in your terminal and go to
the specified web server in your browser. If your site is very large, you may need
to disable the default "watch for changes and recreate" behavior by running
`hugo server --watch=false`.

When you are ready to deploy your site, just run `hugo` in your project directory.
See Hugo's documentation to learn more about how to use the [command line interface (CLI)](https://gohugo.io/commands/)
to manage your site, and how to [host and deploy](https://gohugo.io/host-and-deploy/)
your site.

## Developer notes

This section is meant for developers. If you are using this module only to export
and build a static site, none of the following should apply to you.

### Modifying the Hugo theme

To modify the Hugo theme, first clone it in the module's data/ directory:

```
$ cd /path/to/omeka/modules/StaticSiteExport/data/
$ git clone git@github.com:omeka/gohugo-theme-omeka-s.git
```

It's not required, but we recommend that you clone the repository in the module's
data/ direcotry, alongside the theme's ZIP file. Here, Git will ignore the theme
directory so you don't accidentally commit it to the module repository.

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

```php
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

```php
$frontMatter['js'][] = 'vendor/path/to/script.js';
```

To include CSS on a page, add the CSS path the "css" array on a page's front matter:

```php
$frontMatter['css'][] = 'vendor/path/to/style.css';
```

These can be set in the bundle events (see below), or in various services (see below).

### Adding Hugo shortcodes

Modules can add Hugo shortcodes by registering them in module configuration:

```php
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

### Events

Attach events to the shared event manager in `Module::attachListeners()`. For example:

```php
$sharedEventManager->attach(
    'StaticSiteExport\Job\ExportStaticSite',
    'static_site_export.page_bundle.item',
    function (Event $event) {
        $job = $event->getTarget();
        $resource = $event->getParam('resource');
        $frontMatter = $event->getParam('frontMatter');
        /* Do something */
    }
);
```

In your handler, use `$event->getTarget()` to get the export job object, which
has methods that could be useful. Note that some parameters are array objects (`ArrayObject`).
You can append to (and otherwise modify) array objects, and changes will take effect
without having to set them back on the event.

#### Using events to add pages (resources)

Modules can add resources (items, media, item sets, and assets) as page bundles
via the following events:

- `static_site_export.resource_add.items`
    - `ids`: An array of item IDs added automatically
    - `addIDs`: An `ArrayObject` containing IDs of items to add
- `static_site_export.resource_add.media`
    - `ids`: An array of media IDs added automatically
    - `addIDs`: An `ArrayObject` containing IDs of media to add
- `static_site_export.resource_add.item_set`
    - `ids`: An array of item set IDs added automatically
    - `addIDs`: An `ArrayObject` containing IDs of item sets to add
- `static_site_export.resource_add.assets`
    - `ids`: An array of asset IDs added automatically
    - `addIDs`: An `ArrayObject` containing IDs of assets to add

These events are needed when you want to add resources that are not added automatically.
For example, site page blocks may add items and assets that are not explicitly attached
to the site. You may add the new resource IDs using the `addIDs` parameter, like so:

```php
$addIds = $event->getParam('addIDs');
$addIds[] = $asset->id();
```

#### Using events to modify page (resource) bundles

Modules can modify page bundles via the following events:

- `static_site_export.page_bundle.item`
    - `resource`: The item representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `blocks`: An `ArrayObject` containing resource page blocks, if any
- `static_site_export.page_bundle.media`
    - `resource`: The media representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `blocks`: An `ArrayObject` containing resource page blocks, if any
- `static_site_export.page_bundle.item_set`
    - `resource`: The item set representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `blocks`: An `ArrayObject` containing resource page blocks, if any
- `static_site_export.page_bundle.asset`
    - `resource`: The asset representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `blocks`: An `ArrayObject` containing page blocks, if any
- `static_site_export.page_bundle.site_page`
    - `resource`: The site page representation
    - `frontMatter`: An `ArrayObject` containing page front matter
    - `blocks`: An `ArrayObject` containing site page blocks, if any

You may modify Hugo page front matter using the `frontMatter` parameter, like so:

```php
$frontMatter = $event->getParam('frontMatter');
$frontMatter['params']['myParam'] = 'foobar';
```

You may add content (blocks) to pages using the `blocks` parameter, like so:

```php
$blocks = $event->getParam('blocks');
$frontMatter = [];
$markdown = 'My block';
$blocks[] = [
    'name' => 'my-block',
    'frontMatter' => $frontMatter,
    'markdown' => $markdown,
];
```

#### Other events

You may do things immediately before and after exporting using these events (in
this case, "exporting" means to create the site directory):

- `static_site_export.site_export.pre`:
    - Runs before the site directory is created
- `static_site_export.site_export.post`
    - Runs after the site directory is created, but before it is archived

The `export.post` event is particularly useful to create directories and files needed
by your module.

You may modify the Hugo site configuration using this event:

- `static_site_export.site_config`:
    - `siteConfig`: An `ArrayObject` of Hugo site configuration

Use the `siteConfig` parameter, like so:

```php
$siteConfig = $event->getParam('siteConfig');
$siteConfig['params']['myParam'] = 'foobar';
```

### Logging commands

The export job executes quite a few shell commands. These are not logged by default
because for large sites the log will likely grow to surpass the memory limit. For
debugging, modules can turn on command logging in module configuration:

```php
'static_site_export' => [
    'log_commands' => true,
]
```

# Copyright

StaticSiteExport is Copyright © 2020-present Corporation for Digital Scholarship,
Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code under
the GNU General Public License, version 3 (GPLv3). The full text of this license
is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.
