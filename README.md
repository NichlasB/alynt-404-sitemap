# Alynt 404 & Sitemap

Enhanced 404 error handling and dynamic sitemap generation with extensive customization options.

![WordPress Plugin Required PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![WordPress Plugin: Tested WP Version](https://img.shields.io/badge/WordPress-6.7.2-green)
![WordPress Plugin Version](https://img.shields.io/badge/Version-1.0.3-blue)
![License](https://img.shields.io/badge/License-GPLv2-blue)

## Description

Alynt 404 & Sitemap provides a comprehensive solution for handling 404 errors and creating dynamic sitemaps on your WordPress website.

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later
- Pretty permalinks enabled for the custom sitemap route
- Media Library access if you want to assign featured images in plugin settings

### Key Features

#### Custom 404 Page
- AJAX-powered search functionality
- Configurable quick links
- Mobile-responsive design
- Accessibility compliant

#### Dynamic Sitemap
- Automatic content organization
- Customizable column layout
- Post type filtering
- Content exclusion options

#### Extensive Customization
- Color scheme customization
- Featured images support
- Custom CSS options
- SEO meta descriptions
- Responsive layout controls

### 404 Page Features

- Customizable heading and error message
- AJAX search with post type filtering
- Configurable quick link buttons
- Mobile-responsive design
- Featured image support
- Custom CSS options
- SEO meta description

### Sitemap Features

- Automatic content organization
- Configurable column layout
- Post type filtering
- Content exclusion
- Custom URL slug
- Featured image support
- Custom CSS options
- SEO meta description

### Accessibility

- WCAG 2.1 compliant
- Keyboard navigation support
- Screen reader optimized
- High contrast support
- ARIA attributes
- Proper heading hierarchy

## Installation

1. Upload the plugin files to `/wp-content/plugins/alynt-404-sitemap`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure settings under '404 & Sitemap' in the admin menu

## Usage

After activation, open `404 & Sitemap` in the WordPress admin menu and configure the three tabs:

- `General`: shared color palette for buttons, links, headings, and search UI
- `404`: heading, message, quick links, search post types, featured image, meta description, and custom CSS
- `Sitemap`: heading, message, URL slug, included post types, excluded IDs, responsive columns, featured image, meta description, and custom CSS

The sitemap becomes available at `/{url_slug}` using the slug saved in the Sitemap tab. If the requested slug conflicts with an existing route, the plugin automatically adjusts it to an available value during sanitization.

## Documentation

- Administrator guide: [docs/admin-guide.md](docs/admin-guide.md)
- Settings reference: [docs/SETTINGS.md](docs/SETTINGS.md)
- Hooks reference: [docs/HOOKS.md](docs/HOOKS.md)

## Development

- Editable source assets live in `assets/src/`.
- Runtime assets are served from `assets/dist/`.
- Run `npm install` once, then `npm run build` before testing or packaging the plugin.
- Use `npm run dev` while working on assets if your environment supports watch mode.
- PHP tooling is configured through Composer in `composer.json`.

## Release Checklist

1. Run `composer install`.
2. Run `npm install`.
3. Run `npm run build`.
4. Verify the generated files in `assets/dist/` are up to date.
5. Create the release package or publish the GitHub release.

## Examples

- Use the 404 tab to add quick links back to key landing pages when visitors hit a missing URL.
- Use the Sitemap tab to exclude utility pages or private landing pages by ID while still exposing your main site structure.
- Use the General tab to align the plugin UI colors with your theme without editing template files.

## Frequently Asked Questions

### Can I customize the colors to match my theme?
Yes, the plugin includes a comprehensive color customization system in the General settings tab.

### How do I customize the featured images for the 404 and sitemap pages?
Both the 404 and sitemap pages support featured images that can be uploaded through their respective settings tabs. Simply click the "Upload Image" button and select your desired image from the media library.

### How do I exclude specific pages from the sitemap?
In the Sitemap settings tab, you can enter comma-separated page/post IDs in the "Excluded Content" field.

### Can I change the sitemap URL?
Yes, you can customize the sitemap URL slug in the Sitemap settings tab.

### Is the plugin accessibility compliant?
Yes, the plugin follows WCAG 2.1 guidelines and includes various accessibility features.

### Does the plugin support custom CSS?
Yes, both the 404 page and sitemap support custom CSS through their respective settings tabs, allowing you to further customize their appearance beyond the built-in options.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed release history, including the latest tagged release and current unreleased maintenance work.

## Credits

- Alynt for the plugin
- WordPress core APIs for settings, routing, templating, and media integration

## License
This plugin is licensed under the GPL v2 or later.

[GPLv2 License](https://www.gnu.org/licenses/gpl-2.0.html)
