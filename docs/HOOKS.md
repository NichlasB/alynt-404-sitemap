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

**Source:** `includes/class-alynt-404-post-types.php`

**Example:**
```php
add_filter( 'alynt_404_post_types', function( $post_types ) {
    unset( $post_types['product'] );
    return $post_types;
} );
```

### `alynt_404_search_result_limit`

Filters the maximum number of results returned by the public 404-page AJAX search.

**Parameters:**
- `$limit` (`int`) Maximum number of search results. Default: `10`.

**Return:** `int` Positive result limit.

**Source:** `includes/class-alynt-404-ajax-handler.php`

**Example:**
```php
add_filter( 'alynt_404_search_result_limit', function( $limit ) {
    return 5;
} );
```

### `alynt_404_trusted_proxies`

Filters the list of trusted reverse-proxy IP addresses or CIDR ranges that are allowed to supply forwarded client-IP headers for search rate limiting.

Without this filter, the plugin uses `REMOTE_ADDR` and ignores forwarded IP headers.

**Parameters:**
- `$trusted_proxies` (`array`) Exact IPs or CIDR ranges. Default: `[]`.

**Return:** `array` Trusted proxy IPs and/or CIDR ranges.

**Source:** `includes/class-alynt-404-ajax-handler.php`

**Example:**
```php
add_filter( 'alynt_404_trusted_proxies', function( $trusted_proxies ) {
    $trusted_proxies[] = '203.0.113.10';
    $trusted_proxies[] = '198.51.100.0/24';
    return $trusted_proxies;
} );
```

### `alynt_404_trusted_proxy_headers`

Filters which forwarded headers may be read when the request came through a trusted proxy.

Allowed values are:
- `HTTP_CF_CONNECTING_IP`
- `HTTP_X_FORWARDED_FOR`
- `HTTP_X_REAL_IP`

Default value:
- `HTTP_CF_CONNECTING_IP`
- `HTTP_X_FORWARDED_FOR`

**Parameters:**
- `$headers` (`array`) Allowed server-header names to inspect.

**Return:** `array` Filtered list of allowed header names.

**Source:** `includes/class-alynt-404-ajax-handler.php`

**Example:**
```php
add_filter( 'alynt_404_trusted_proxy_headers', function( $headers ) {
    return array( 'HTTP_X_REAL_IP' );
} );
```

#### Trusted Proxy Setup Notes

Only configure trusted proxies when your origin is actually behind a reverse proxy or CDN that overwrites forwarding headers. If visitors can reach PHP directly and send their own `X-Forwarded-For` or similar headers, do not trust those headers.

If your web server already rewrites `REMOTE_ADDR` to the real client IP safely, you do not need either of these filters.

**Cloudflare example**

If your origin only accepts traffic from Cloudflare, prefer `CF-Connecting-IP` and trust only Cloudflare egress ranges.

```php
add_filter( 'alynt_404_trusted_proxies', function( $trusted_proxies ) {
    return array(
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
    );
} );

add_filter( 'alynt_404_trusted_proxy_headers', function( $headers ) {
    return array( 'HTTP_CF_CONNECTING_IP' );
} );
```

Keep the proxy ranges in sync with Cloudflare's official published IP list.

**nginx or internal load balancer example**

If requests reach WordPress through an internal reverse proxy that overwrites `X-Real-IP`, trust only that private proxy range and read `HTTP_X_REAL_IP`.

```nginx
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
```

```php
add_filter( 'alynt_404_trusted_proxies', function( $trusted_proxies ) {
    return array(
        '10.0.0.15',
        '10.0.1.0/24',
    );
} );

add_filter( 'alynt_404_trusted_proxy_headers', function( $headers ) {
    return array( 'HTTP_X_REAL_IP' );
} );
```

### `alynt_404_sitemap_posts_per_page`

Filters the batch size used when rendering non-hierarchical sitemap entries.

**Parameters:**
- `$batch_size` (`int`) Number of posts to load per sitemap query. Default: `100`.

**Return:** `int` Positive batch size.

**Source:** `templates/partials/archive-column.php`

**Example:**
```php
add_filter( 'alynt_404_sitemap_posts_per_page', function( $batch_size ) {
    return 50;
} );
```

### `alynt_404_reserved_slugs`

Filters the list of reserved slugs that cannot be used for the sitemap URL.

**Parameters:**
- `$reserved_slugs` (`array`) Array of slug strings that should be treated as unavailable.

**Return:** `array` Updated list of reserved slugs.

**Source:** `includes/class-alynt-404-utilities.php`

**Example:**
```php
add_filter( 'alynt_404_reserved_slugs', function( $reserved_slugs ) {
    $reserved_slugs[] = 'resources';
    return $reserved_slugs;
} );
```
