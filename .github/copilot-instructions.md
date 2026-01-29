# GitHub Copilot Instructions for WordPress Pay Core

## Project Overview

WordPress Pay Core is a WordPress payment processing library that makes payments and integrations with payment providers easier to set up and maintain within WordPress. The code complies with WordPress Coding Standards and uses WordPress APIs.

## Tech Stack

- **PHP**: 8.2+ (Platform requirement)
- **WordPress**: 6.8+
- **JavaScript/Node.js**: For build tools and frontend assets
- **Testing**: PHPUnit with WordPress test suite (wp-phpunit)
- **Build Tools**: Grunt, Composer, npm
- **CI/CD**: GitHub Actions

## Key Dependencies

- `pronamic/wp-coding-standards`: WordPress coding standards ruleset
- `pronamic/wp-datetime`, `pronamic/wp-html`, `pronamic/wp-http`, `pronamic/wp-money`, `pronamic/wp-number`: Pronamic WordPress libraries
- `automattic/jetpack-autoloader`: Autoloader for WordPress plugins
- `woocommerce/action-scheduler`: Background task scheduling

## Coding Standards

### PHP Standards

- **MUST** follow **WordPress Coding Standards** as enforced by `pronamic/wp-coding-standards`
- **MUST** use `snake_case` for function and variable names
- **MUST** use `StudlyCaps` for class names (PSR-1)
- **MUST** use clear namespaces: `Pronamic\WordPress\Pay\...`
- **NEVER** introduce global functions without a proper prefix
- **ALWAYS** prefer WordPress APIs over custom abstractions
- **ALWAYS** use early returns to reduce nesting and improve readability
- **NEVER** add unnecessary comments or explain obvious code
- **ONLY** add comments to explain intent, non-obvious decisions, or workarounds
- Text domain for translations: `pronamic_ideal`

### Code Organization

- **PSR-4 Autoloading**: `Pronamic\WordPress\Pay\` → `src/`
- **Functions**: Additional functions in `includes/functions.php`
- **Views**: Template files in `views/` directory
- **Assets**: JS in `js/`, CSS in `css/`, SCSS in `scss/`

### Code Quality

- **ALWAYS** keep functions small and focused
- **ALWAYS** use type hints and return types (PHP 8.2+)
- **ALWAYS** validate and sanitize user input
- **ALWAYS** escape output properly
- **NEVER** use `serialize()` except in specific legacy contexts (see phpcs.xml.dist)

## Commands

### Testing
```bash
composer phpunit              # Run all tests
composer coverage             # Run tests with coverage report
composer coverage-html        # Generate HTML coverage report
```

### Code Quality
```bash
composer phplint              # Lint PHP files
composer phpcs                # Check coding standards
composer phpcbf               # Auto-fix coding standards
composer phpstan              # Run static analysis
composer psalm                # Run Psalm static analysis
composer phpmd                # Run PHP Mess Detector
```

### Build
```bash
composer build                # Build production-ready plugin
npm run lint                  # Lint JS and SASS
npm run js-build              # Build JavaScript files
npm run sass                  # Compile SASS to CSS
```

### Complete CI Check
```bash
composer ci                   # Run all CI checks: phplint, phpcs, coverage, phpstan
```

## Project Structure

- `src/` - Main source code (PSR-4 autoloaded)
  - `Admin/` - WordPress admin interface components
  - `Core/` - Core payment processing logic
  - `Blocks/` - WordPress block editor integration
  - `Banks/` - Bank-specific implementations
  - `Payments/` - Payment entity and processing
  - `Subscriptions/` - Subscription handling
  - `Gateways/` - Payment gateway integrations
- `includes/` - Additional function files
- `views/` - PHP template files
- `tests/` - PHPUnit tests
- `js/` - JavaScript source and built files
- `css/`, `scss/` - Stylesheets
- `images/` - Image assets

## Testing Guidelines

- **ALWAYS** write PHPUnit tests for new features
- **ALWAYS** use `Yoast\PHPUnitPolyfills` for cross-version compatibility
- **ALWAYS** follow existing test patterns in `tests/` directory
- **ALWAYS** run tests before committing: `composer phpunit`
- **NEVER** commit failing tests

## WordPress Integration

- **ALWAYS** use WordPress hooks (actions/filters) for extensibility
- **ALWAYS** use WordPress database APIs (`$wpdb`, `get_post_meta`, etc.)
- **ALWAYS** use WordPress HTTP API for remote requests
- **ALWAYS** properly prefix custom post types and taxonomies
- **ALWAYS** check user capabilities before privileged operations
- Custom capabilities: `edit_payments`

## Security

- **ALWAYS** validate and sanitize user input
- **ALWAYS** escape output using WordPress functions (`esc_html`, `esc_attr`, `esc_url`, etc.)
- **ALWAYS** use nonces for form submissions
- **ALWAYS** check user capabilities
- **NEVER** expose sensitive data (API keys, credentials) in code or logs
- **NEVER** use `serialize()` for untrusted data

## Documentation

- **ALWAYS** use PHPDoc blocks for classes and public methods
- **ALWAYS** document action and filter hooks
- **ALWAYS** update CHANGELOG.md for notable changes
- Hook documentation is auto-generated: `composer build-docs`

## Git Workflow

- **NEVER** commit dependencies (`vendor/`, `node_modules/`)
- **NEVER** commit built assets directly (use build process)
- **ALWAYS** keep commits focused and atomic
- **ALWAYS** write clear commit messages

## Common Patterns

### Payment Processing
- Use `Payment` entity from `Pronamic\WordPress\Pay\Payments\Payment`
- Use `Gateway` abstract class for payment gateway integrations
- Use WordPress custom post types for storing payments and subscriptions

### Data Storage
- Extend `AbstractDataStoreCPT` for custom post type data stores
- Use post meta for storing entity data
- Use proper sanitization and validation

### Money Handling
- Use `pronamic/wp-money` library for money/currency operations
- Never perform floating-point arithmetic on monetary values

## Never Do

- **NEVER** introduce breaking changes without major version bump
- **NEVER** use WordPress VIP restricted functions without documented reason
- **NEVER** use slow database queries without caching
- **NEVER** modify core WordPress files or other plugin files
- **NEVER** hardcode URLs or paths
- **NEVER** ignore phpcs, phpstan, or psalm errors

## References

- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/
- Pronamic WP Coding Standards: https://github.com/pronamic/wp-coding-standards
- WordPress Plugin Handbook: https://developer.wordpress.org/plugins/
- WP Pay Documentation: https://www.wp-pay.org/
