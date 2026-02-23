# Marketing Hero

Marketing Hero is a plugin-first WordPress admin product for tracking marketing **Inputs** (Activities) and **Outputs** (Results), built with clean architecture:

- Contracts → Repositories → Services → Admin UI
- Custom database tables via `dbDelta`
- Server-rendered admin pages with minimal JavaScript
- Scoped premium CSS for fast, clean UX

## Requirements

- WordPress 6.x
- PHP 8.1+

## Installation

1. Install plugin files in `wp-content/plugins/marketing-hero`.
2. Optional: run `composer install` for PSR-4 autoload generation.
3. Activate **Marketing Hero** in wp-admin.

On activation, the plugin:
- creates custom tables (`mh_activity`, `mh_result`, `mh_campaign`) with indexes
- grants administrators the capability `manage_marketing_hero`

## Data and Timezone

- Forms accept dates in the **site timezone** (`wp_timezone()`).
- Datetimes are stored in UTC in custom tables for consistent reporting.

## Admin Pages

- Dashboard
- Activities
- Results
- Campaigns
- Settings (stub + uninstall data retention toggle)

## Security Highlights

- Capability checks on all screens/actions
- Nonces on all write/delete handlers
- Sanitization + output escaping throughout
- Scoped assets enqueued only on Marketing Hero admin screens

## Future-Ready Stubs

- `SyncClientInterface`
- `IntegrationProviderInterface`

No cloud sync or HTMX is implemented in this MVP.
