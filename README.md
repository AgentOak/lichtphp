## lichtphp

Yet another modern PHP framework. For PHP 8.1+ only.
Features PSR-11 dependency injection with autowiring.

Currently in development with many pending TODOs littered throughout the code.

### Usage

Applications utilizing this library should follow the [PHP-PDS directory
structure](https://github.com/php-pds/skeleton).

### Development

First, install development dependencies through composer:

```
composer install --dev
```

Run static analysis:

```
composer analyze
```

Run tests:

```
composer test
```

### Feature table

| PSR | Title                       | Status       | Implementation                                        |
|-----|-----------------------------|--------------|-------------------------------------------------------|
| 0   | Autoloading Standard        | Deprecated   |                                                       |
| 1   | Basic Coding Standard       | **Accepted** | Enforced by PHPCS                                     |
| 2   | Coding Style Guide          | Deprecated   | Enforced by PHPCS                                     |
| 3   | Logger Interface            | **Accepted** | *No implementation*                                   |
| 4   | Autoloading Standard        | **Accepted** | Implemented by Composer                               |
| 5   | PHPDoc Standard             | Draft        |                                                       |
| 6   | Caching Interface           | **Accepted** | *No implementation*, in favor of PSR-16               |
| 7   | HTTP Message Interface      | **Accepted** | *No implementation*                                   |
| 8   | Huggable Interface          | Abandoned    |                                                       |
| 9   | Security Advisories         | Abandoned    |                                                       |
| 10  | Security Reporting Process  | Abandoned    |                                                       |
| 11  | Container Interface         | **Accepted** | Implemented in `src/Container/`, with autowiring      |
| 12  | Extended Coding Style Guide | **Accepted** | Enforced by PHPCS, with exceptions                    |
| 13  | Hypermedia Links            | **Accepted** | *No implementation*                                   |
| 14  | Event Dispatcher            | **Accepted** | *No implementation*                                   |
| 15  | HTTP Handlers               | **Accepted** | *No implementation*                                   |
| 16  | Simple Cache                | **Accepted** | Implemented in `src/SimpleCache/`, with Redis backend |
| 17  | HTTP Factories              | **Accepted** | *No implementation*                                   |
| 18  | HTTP Client                 | **Accepted** | *No implementation*                                   |
| 19  | PHPDoc tags                 | Draft        |                                                       |
| 20  | Clock                       | **Accepted** | Implemented in `src/Clock/`                           |
| 21  | Internationalization        | Draft        |                                                       |
| 22  | Application Tracing         | Draft        |                                                       |

### License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
details.

You should have received a copy of the GNU Lesser General Public License along with this program. If not,
see <https://www.gnu.org/licenses/>.
