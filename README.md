# laravel-singular-resource-routes

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.txt)
[![Build Status][ico-github-actions]][link-github-actions]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This Laravel package adds support for singular resource routes to Laravel's router.

Sometimes you need to deal with a resource that does not need to be referenced by the id. For example, you may have a `profile` resource that belongs to the currently authenticated user. Currently, if you wanted to `show` the user's profile, the route would be `/profile/{profile}`. However, since you don't need the id to look up the current user's profile, it would be nice to be able to simply have the `show` route be `/profile`. This is the same for the `edit`, `update`, and `destroy` routes, as well.

The singular resource route generates the following routes:

| Verb | Path | Action | Route Name |
| -- | -- | -- | -- |
| GET | /profile/create | create | profile.create |
| POST | /profile | store | profile.store |
| GET | /profile | show | profile.show |
| GET | /profile/edit | edit | profile.edit |
| PUT/PATCH | /profile | update | profile.update |
| DELETE | /profile | destroy | profile.destroy |

These routes differ from the current resource routes in two ways:
1. The `show`, `edit`, `update`, and `destroy` routes do not need a route parameter.
2. Since this is a singular resource, there is no `index` action.

## Install

Via Composer

``` bash
$ composer require shiftonelabs/laravel-singular-resource-routes
```

Service Provider

This package supports Laravel package autodiscovery. So, if using Laravel 5.5+, there is no need to add the server provider. If using Laravel 5.0 - 5.4, add the service provider to your `config/app.php` file:

``` php
'providers' => [
    ...
    ShiftOneLabs\SingularResourceRoutes\SingularResourceRoutesServiceProvider::class,
];
```

## Usage

In order to create a new singular resource route, a new `singular` option is added to the resource route definition. This option can be set to three different values:
1. The boolean `true`. This is mainly for non-nested resource definitions. If it is used on a nested resource definition, only the last resource in the chain will be treated as singular.
2. A string containing the name of the singular resource. This can be used when there is only one resource that is singular in a nested resource definition. The string must contain the name of the singular resource.
3. An array of strings containing the names of the singular resources. This format must be used when there are multiple singular resources in a nested resource definition, but it can be used in any scenario.

#### Code Example

**A singular resource (`/profile`):**
```
// Since there is only one singular resource, and it is the last one,
// all three options work in this scenario.

Route::resource('profile', 'ProfileController', ['singular' => true]);
Route::resource('profile', 'ProfileController', ['singular' => 'profile']);
Route::resource('profile', 'ProfileController', ['singular' => ['profile']]);
```

**A singular resource nested under a plural resource (`/users/{user}/profile`):**
```
// Since there is only one singular resource, and it is the last one,
// all three options work in this scenario.

Route::resource('users.profile', 'ProfileController', ['singular' => true]);
Route::resource('users.profile', 'ProfileController', ['singular' => 'profile']);
Route::resource('users.profile', 'ProfileController', ['singular' => ['profile']]);
```

**A singular resource nested under a singular resource (`/profile/avatar`):**
```
// Since there are multiple resources that are singular, only the array
// syntax can be used to specify all the singular resources.

Route::resource('profile.avatar', 'ProfileAvatarController', ['singular' => ['profile', 'avatar']]);
```

**A plural resource nested under a singular resource (`/profile/phones/{phone}`):**
```
// Since there is a parent resource that is singular, the "true" option cannot be used.
// But since there is only one singular resource, the string option can be used.

Route::resource('profile.phones', 'PhoneController', ['singular' => 'profile']);
Route::resource('profile.phones', 'PhoneController', ['singular' => ['profile']]);
```

**A singular resource nested under a plural resource nested under a singular resource (`/profile/phones/{phone}/type`):**
```
// Since there are multiple resources that are singular, only the array
// syntax can be used to specify all the singular resources.

Route::resource('profile.phones.type', 'PhoneTypeController', ['singular' => ['profile', 'type']]);
```

## Contributing

Contributions are welcome. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email patrick@shiftonelabs.com instead of using the issue tracker.

## Credits

- [Patrick Carlo-Hickman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.txt) for more information.

[ico-version]: https://img.shields.io/packagist/v/shiftonelabs/laravel-singular-resource-routes.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-github-actions]: https://img.shields.io/github/actions/workflow/status/shiftonelabs/laravel-singular-resource-routes/.github/workflows/phpunit.yml?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/shiftonelabs/laravel-singular-resource-routes.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shiftonelabs/laravel-singular-resource-routes.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/shiftonelabs/laravel-singular-resource-routes.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/shiftonelabs/laravel-singular-resource-routes
[link-github-actions]: https://github.com/shiftonelabs/laravel-singular-resource-routes/actions
[link-scrutinizer]: https://scrutinizer-ci.com/g/shiftonelabs/laravel-singular-resource-routes/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/shiftonelabs/laravel-singular-resource-routes
[link-downloads]: https://packagist.org/packages/shiftonelabs/laravel-singular-resource-routes
[link-author]: https://github.com/patrickcarlohickman
[link-contributors]: ../../contributors
