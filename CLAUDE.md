# Vy PHP - Development Guide

## Commands
- **Setup**: `composer install`
- **Run Tests**: `composer test`
- **Run Single Test**: `composer exec -- phpunit --filter TestName`
- **Lint Code**: `composer dev:lint`
- **Fix Lint Issues**: `composer dev:lint:fix`
- **Static Analysis**: `composer dev:analyze`
- **List Dev Commands**: `composer list dev`

## Code Style
- PHP 8.2+ required with strict typing
- Follows PSR-12 plus Ramsey coding standard
- Uses PSR-4 autoloading (`StefanFisk\Vy` namespace)
- Elements follow React component patterns
- Custom exceptions in `src/Errors/`
- Commits must follow Conventional Commits spec

## Quality Standards
- PHPStan at max level
- Psalm at level 1
- 100% test coverage expected
- Error handling via typed exceptions
- Element naming exceptions: Classes in the `StefanFisk\Vy\Elements` namespace don't require CamelCase
