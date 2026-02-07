![boson](https://github.com/user-attachments/assets/712dda46-101e-46ca-af97-908d5f548b3b)
# SPARXSTAR™ Boson Scaffold

A WordPress multisite plugin scaffold that loads Secure Custom Field (SCF) options and enforces runtime rules with full multi-site network support.

## Features

- **Multi-Site Network Support**: Designed for WordPress multi-site installations with subsite-specific settings
- **Secure Custom Field Integration**: Load and manage SCF options per subsite
- **Runtime Rules Engine**: Enforce access control and validation rules dynamically
- **Composer-Ready**: Install via WordPress Composer using `wordpress-muplugin` type
- **Subsite-Specific Settings**: Each subsite has its own configuration (no network-level settings)
- **Extensible Architecture**: Filter and action hooks for custom integrations

## Requirements

- PHP 7.4 or higher
- WordPress 5.8 or higher
- Multi-site network (optional, works on single sites too)

## Installation

### Method 1: Composer (Recommended)

Add to your `composer.json`:

```json
{
  "require": {
    "starisian-technologies/sparxstar-access-manager": "^1.0"
  },
  "extra": {
    "installer-paths": {
      "wp-content/mu-plugins/{$name}/": ["type:wordpress-muplugin"]
    }
  }
}
```

Then run:

```bash
composer install
```

For multi-site, create a loader file at `wp-content/mu-plugins/sparxstar-access-manager-loader.php`:

```php
<?php
/**
 * Plugin Name: Sparxstar Access Manager Loader
 * Description: Loads the Sparxstar Access Manager MU-plugin
 */
require_once WPMU_PLUGIN_DIR . '/sparxstar-access-manager/sparxstar-access-manager.php';
```

### Method 2: Manual Installation

1. Clone or download this repository to `wp-content/mu-plugins/sparxstar-access-manager/`
2. For multi-site, create the loader file as shown above
3. The plugin will be automatically loaded on all subsites

### Multi-Site Network Activation

For multi-site installations:

1. The plugin loads automatically via the MU-plugins system
2. Each subsite gets its own settings page under Settings → Access Manager
3. There are NO network-level settings - all configuration is per subsite
4. When a new subsite is created, default settings are automatically initialized

## Configuration

### Admin Interface

Each subsite has its own settings page at **Settings → Access Manager**.

#### General Settings
- Enable/disable the plugin for the current subsite

#### Secure Custom Field Options
Configure SCF options in JSON format:

```json
{
  "field_name": "custom_field_key",
  "access_level": "editor",
  "validation_rule": "email"
}
```

#### Runtime Rules
Define enforcement rules in JSON format:

```json
[
  {
    "type": "access_control",
    "enabled": true,
    "condition": "user_role",
    "value": "editor"
  },
  {
    "type": "field_validation",
    "enabled": true,
    "field": "email",
    "pattern": "^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$"
  }
]
```

### Programmatic Configuration

#### Filters

**Modify SCF Options:**
```php
add_filter('sparxstar_access_manager_scf_options', function($options) {
    $options['custom_key'] = 'custom_value';
    return $options;
});
```

**Modify Rules:**
```php
add_filter('sparxstar_access_manager_rules', function($rules) {
    $rules[] = [
        'type' => 'custom_rule',
        'enabled' => true,
        'config' => ['key' => 'value']
    ];
    return $rules;
});
```

**Handle Custom Rule Types:**
```php
add_filter('sparxstar_access_manager_handle_rule', function($handled, $rule) {
    if ($rule['type'] === 'my_custom_type') {
        // Handle your custom rule
        return true;
    }
    return $handled;
}, 10, 2);
```

#### Actions

**After Options Loaded:**
```php
add_action('sparxstar_access_manager_options_loaded', function($options) {
    // Do something after SCF options are loaded
});
```

**After Rules Enforced:**
```php
add_action('sparxstar_access_manager_rules_enforced', function($rules) {
    // Do something after rules are enforced
});
```

## Development

### Setup Development Environment

```bash
# Install dependencies
composer install

# Run linter
composer lint

# Fix code style issues
composer lint:fix

# Run tests
composer test

# Generate coverage report
composer test:coverage
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
vendor/bin/phpunit tests/unit
vendor/bin/phpunit tests/integration
```

### Code Standards

This project follows WordPress Coding Standards. Run the linter before committing:

```bash
composer lint
```

## Architecture

### Core Components

1. **Plugin Class** (`class-plugin.php`): Main plugin orchestrator
2. **Secure Custom Field Manager** (`class-secure-custom-field-manager.php`): Manages SCF options per subsite
3. **Rules Engine** (`class-rules-engine.php`): Enforces runtime rules
4. **Admin Manager** (`class-admin-manager.php`): Handles subsite-specific admin interface

### Multi-Site Architecture

- Each subsite stores its configuration in its own options table
- No network-level settings or configurations
- New subsites automatically get initialized with default settings
- Plugin can be activated/deactivated per subsite if installed as a regular plugin
- When installed as MU-plugin, it's always active but can be enabled/disabled per subsite via settings

## API Reference

### SecureCustomFieldManager

```php
// Get all SCF options
$options = $scf_manager->get_options();

// Get specific option with default
$value = $scf_manager->get_option('key', 'default');

// Set SCF options
$scf_manager->set_options(['key' => 'value']);

// Check if plugin is enabled for current site
$enabled = $scf_manager->is_enabled();
```

### RulesEngine

```php
// Get all rules
$rules = $rules_engine->get_rules();

// Enforce rules manually
$rules_engine->enforce_rules();
```

## Security

- All admin inputs are sanitized
- JSON configurations are validated before saving
- Proper WordPress nonces and capability checks
- Follows WordPress security best practices

## Support

For issues, questions, or contributions, please visit:
https://github.com/Starisian-Technologies/sparxstar-boson-scaffold

## License

MIT License - see [LICENSE](LICENSE) file for details

## Credits

Developed by [Starisian Technologies](https://starisian.tech)
Copyright (c) 2026 Starisian Technologies. 

SPARXSTAR™ and Starisian Technologies™ are trademarks of Starisian Technologies. WordPress is a trademark of WorkPress Inc. Starisian Technologies is in no way associated with WordPress. 
