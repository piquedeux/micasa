# MICASA FTP Deployment

This project is a no-build PHP frontend. Upload the folder contents to any PHP-capable FTP host.

## Required Setup

1. Copy `config.local.example.php` to `config.local.php`.
2. Set the Shopify domain and one token:
   - `access_mode => storefront` with `storefront_token` for public product reads.
   - `access_mode => admin` with `admin_token` for server-side Admin API product reads.
   - Keep `allow_tokenless => false` unless you intentionally want Shopify tokenless Storefront API calls.
3. Set a real admin password hash in `config.local.php`.
4. Make `data/` and `assets/uploads/` writable by PHP.
5. Open `/panel/` to manage MICASA projects.

## Admin Login

The default login is only for first local testing:

- User: `admin`
- Password: `change-this-password`

Generate a replacement hash with:

```bash
php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Put that hash into `config.local.php`.

## FTP Notes

Upload the project contents as-is. The admin entry point is the `panel/` folder, so both `/panel/` and `/panel/index.php` work on typical PHP hosts. Keep the included `.htaccess` files when uploading; they protect PHP library files, partials, config files, and JSON data from direct browser access.

## Shopify Notes

Products are managed in Shopify and pulled into `shop.php`. MICASA projects/campaigns are managed in the custom admin panel and can optionally link to a Shopify product handle.

Keep Shopify tokens in `config.local.php` or server environment variables. Do not put tokens into JavaScript, HTML, or CSS.
