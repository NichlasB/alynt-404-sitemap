# Settings Reference

This document maps each stored plugin option to its structure, defaults, sanitization rules, and admin tab.

## Option Groups

| Option Key | Type | Default | Sanitization | Tab | Description |
|------------|------|---------|--------------|-----|-------------|
| `alynt_404_colors` | array | See color fields below | `Alynt_404_Settings_Sanitizer::sanitize_colors()` with per-field validation via `Alynt_404_Color_Manager::validate_color()` | General | Stores shared color settings used by the 404 page and sitemap templates. |
| `alynt_404_404_settings` | array | See 404 fields below | `Alynt_404_Settings_Sanitizer::sanitize_404_settings()` | 404 | Stores content, search, SEO, and style settings for the custom 404 page. |
| `alynt_404_sitemap_settings` | array | See sitemap fields below | `Alynt_404_Settings_Sanitizer::sanitize_sitemap_settings()` | Sitemap | Stores content, layout, SEO, and filtering settings for the sitemap page. |
| `alynt_404_version` | string | Current plugin version on activation | Direct `update_option()` during activation | System | Tracks the installed plugin version. |
| `alynt_404_css_version` | string | `1.0.0` until regenerated | Direct `update_option()` in `Alynt_404_Color_Manager::regenerate_css()` | System | Cache-busting version for generated stylesheet output. |

## `alynt_404_colors`

| Field | Type | Default | Sanitization | Tab | Description |
|-------|------|---------|--------------|-----|-------------|
| `headings` | string | `#333333` | Valid hex color or fallback to default | General | Heading color used across plugin templates. |
| `paragraph` | string | `#333333` | Valid hex color or fallback to default | General | Paragraph/body text color. |
| `links` | string | `#0073aa` | Valid hex color or fallback to default | General | Link color. |
| `buttons` | string | `#0073aa` | Valid hex color or fallback to default | General | Button background color. |
| `button_text` | string | `#ffffff` | Valid hex color or fallback to default | General | Button text color. |
| `search_border` | string | `#dddddd` | Valid hex color or fallback to default | General | Search input border color. |
| `search_text` | string | `#333333` | Valid hex color or fallback to default | General | Search input text color. |
| `search_background` | string | `#ffffff` | Valid hex color or fallback to default | General | Search input background color. |

## `alynt_404_404_settings`

| Field | Type | Default | Sanitization | Tab | Description |
|-------|------|---------|--------------|-----|-------------|
| `heading` | string | `Oops! That page can't be found.` | `sanitize_text_field()` | 404 | Main 404 heading shown to visitors. |
| `message` | string | `Looks like this page took a wrong turn. Let's get you back to where you need to be.` | `sanitize_text_field()` | 404 | Supporting copy shown below the heading. |
| `button_links` | array | `[]` | Each item requires non-empty `text` and `url`; `text` via `sanitize_text_field()`, `url` via `esc_url_raw()` | 404 | Quick-link buttons rendered on the 404 template. |
| `search_post_types` | array | `['post', 'page']` | Limited to public post types returned by `Alynt_404_Post_Types::get_public_post_types()` | 404 | Post types available to the AJAX search experience. |
| `meta_description` | string | `Page not found. Use our search or navigation to find what you are looking for.` | `sanitize_text_field()` | 404 | Meta description output on 404 requests. |
| `custom_css` | string | `''` | `Alynt_404_Utilities::sanitize_css()` | 404 | Custom CSS applied to the 404 page. |
| `featured_image` | integer | `0` | `absint()` and verified with `wp_get_attachment_image()` | 404 | Attachment ID for the optional 404 hero image. |

## `alynt_404_sitemap_settings`

| Field | Type | Default | Sanitization | Tab | Description |
|-------|------|---------|--------------|-----|-------------|
| `heading` | string | `Sitemap` | `sanitize_text_field()` | Sitemap | Main sitemap page heading. |
| `message` | string | `Here's our website at a glance. Use this sitemap to quickly find what you're looking for.` | `sanitize_text_field()` | Sitemap | Introductory text shown on the sitemap page. |
| `url_slug` | string | `sitemap` | Sanitized with `Alynt_404_Utilities::sanitize_slug()` and adjusted to a unique available slug | Sitemap | Public URL slug for the sitemap route. |
| `post_types` | array | `['post', 'page']` | Limited to valid public post types | Sitemap | Post types included in the sitemap output. |
| `excluded_ids` | string | `''` | Comma-delimited IDs filtered to existing posts only | Sitemap | IDs excluded from sitemap queries. |
| `meta_description` | string | `Looking for something specific? Use our sitemap to easily navigate all our website content.` | `sanitize_text_field()` | Sitemap | Meta description output on the sitemap page. |
| `custom_css` | string | `''` | `Alynt_404_Utilities::sanitize_css()` | Sitemap | Custom CSS applied to the sitemap page. |
| `featured_image` | integer | `0` | `absint()` and verified with `wp_get_attachment_image()` | Sitemap | Attachment ID for the optional sitemap hero image. |
| `columns_desktop` | integer | `4` | `absint()` clamped to `1-4` | Sitemap | Number of columns used on desktop layouts. |
| `columns_tablet` | integer | `2` | `absint()` clamped to `1-4` | Sitemap | Number of columns used on tablet layouts. |
| `columns_mobile` | integer | `1` | `absint()` clamped to `1-2` | Sitemap | Number of columns used on mobile layouts. |
| `sort_order` | array | `['post' => 'alphabetical', 'page' => 'alphabetical']` | Stored as provided when the value is an array; otherwise reset to defaults | Sitemap | Per-post-type display order preferences used by the sitemap UI. |

## Notes

- No orphaned settings were found during this review.
- No additional persisted options were found beyond the option groups listed above.
