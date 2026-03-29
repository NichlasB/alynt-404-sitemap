# Hooks Reference

This plugin currently exposes custom filters and relies on WordPress core hooks for registration and rendering.

## Actions

No custom `do_action()` hooks are defined in the current codebase.

## Filters

### `alynt_404_post_types`

Filters the list of public post types that can be used by the sitemap and 404 search settings.

**Parameters:**
- `$post_types` (`array`) Array of post type objects keyed by post type slug.

**Return:** `array` Filtered array of post type objects.

**Source:** `includes/class-post-types.php`

**Example:**
```php
add_filter( 'alynt_404_post_types', function( $post_types ) {
    unset( $post_types['product'] );
    return $post_types;
} );
```

### `alynt_404_reserved_slugs`

Filters the list of reserved slugs that cannot be used for the sitemap URL.

**Parameters:**
- `$reserved_slugs` (`array`) Array of slug strings that should be treated as unavailable.

**Return:** `array` Updated list of reserved slugs.

**Source:** `includes/class-utilities.php`

**Example:**
```php
add_filter( 'alynt_404_reserved_slugs', function( $reserved_slugs ) {
    $reserved_slugs[] = 'resources';
    return $reserved_slugs;
} );
```
