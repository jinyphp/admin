# Tailwind CSS v4 & Vite Setup Guide for @jiny/admin

## Overview

When installing `@jiny/admin` package via Composer, the package is installed in the `vendor/` directory. This guide explains how to configure Tailwind CSS v4 and Vite to properly include the package's styles.

## Automatic Configuration

When you install the `@jiny/admin` package via Composer, the following configurations are automatically applied:

1. **Database Migrations** - Automatically run on package install/update
2. **Tailwind CSS Configuration** - Updated to include vendor package paths
3. **Vite Configuration** - Updated to watch vendor package files

## Manual Configuration (if needed)

### 1. Tailwind CSS Configuration

Ensure your `tailwind.config.js` includes the vendor package paths:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // Your application paths
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        
        // @jiny/admin package paths (in vendor)
        "./vendor/jinyerp/admin/resources/**/*.blade.php",
        "./vendor/jinyerp/admin/resources/**/*.js",
        "./vendor/jinyerp/admin/App/**/*.php",
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}
```

### 2. Vite Configuration

Update your `vite.config.js` to watch the vendor package files:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'resources/**',
                'vendor/jinyerp/admin/resources/**',
            ],
        }),
    ],
    css: {
        postcss: {
            plugins: [
                require('tailwindcss'),
                require('autoprefixer'),
            ],
        },
    },
});
```

### 3. Main CSS File

Ensure your `resources/css/app.css` includes Tailwind directives:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

## Installation Steps

1. **Install the package via Composer:**
   ```bash
   composer require jinyerp/admin
   ```

2. **Install npm dependencies:**
   ```bash
   npm install
   ```

3. **Build assets:**
   ```bash
   npm run build
   ```

   Or for development:
   ```bash
   npm run dev
   ```

## Troubleshooting

### Issue: Tailwind classes not being compiled

**Solution:** Ensure the vendor paths are included in your `tailwind.config.js` content array.

### Issue: Changes in vendor package not reflecting

**Solution:** 
1. Clear Laravel caches:
   ```bash
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

2. Rebuild assets:
   ```bash
   npm run build
   ```

### Issue: Vite not watching vendor files

**Solution:** Check that the `refresh` array in `vite.config.js` includes the vendor path.

## Package Structure

When installed via Composer, the package structure in vendor will be:

```
vendor/
└── jinyerp/
    └── admin/
        ├── App/
        ├── resources/
        │   └── views/
        ├── database/
        ├── routes/
        └── ...
```

## Notes for Tailwind CSS v4

Tailwind CSS v4 has some changes from v3:

1. **CSS-in-JS**: Tailwind v4 uses CSS instead of JavaScript for configuration
2. **Performance**: Improved performance with faster builds
3. **Content Detection**: More efficient content detection for purging unused styles

The automatic configuration script handles these differences and ensures compatibility with both Tailwind v3 and v4.

## Support

If you encounter any issues with the Tailwind CSS or Vite configuration, please check:

1. The package installation was successful
2. The configuration files were properly updated
3. You've run `npm install` and `npm run build`

For further assistance, please refer to the main documentation or create an issue in the repository.