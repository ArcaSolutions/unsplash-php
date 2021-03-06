# PHP Unsplash Wrapper

[![Build Status](https://travis-ci.org/unsplash/unsplash-php.svg?branch=master)](https://travis-ci.org/unsplash/unsplash-php)

A PHP client for the [Unsplash API](https://unsplash.com/documentation).

- [Official documentation](https://unsplash.com/documentation)
- [Changelog](https://github.com/unsplash/unsplash-pHP/blob/master/CHANGELOG.md)

Quick links to methods you're likely to care about:

- [Get a list of new photos](#photo-all) 🎉
- [Get a random photo](#photo-random) 🎑
- [Trigger a photo download](#photo-download) 📡
- [Search for a photo by keyword](#search-photos) 🕵️‍♂️

**Note:** Every application must abide by the [API Guidelines](https://medium.com/unsplash/unsplash-api-guidelines-28e0216e6daa). Specifically, remember to [hotlink images](https://medium.com/unsplash/unsplash-api-guidelines-hotlinking-images-6c6b51030d2a) and [trigger a download when appropriate](https://medium.com/unsplash/unsplash-api-guidelines-triggering-a-download-c39b24e99e02).

## Installation

`unsplash-php` uses [Composer](https://getcomposer.org/). To use it, require the library

```
composer require arcasolutions/unsplash
```

## Usage

### Configuration

Before using, configure the client with your application ID and secret. If you don't have an application ID and secret, follow the steps from the [Unsplash API](https://unsplash.com/documentation#creating-a-developer-account) to register your application.

Note that if you're just using actions that require the [public permission scope](#permission-scopes), only the `applicationId` is required.

Note that if utmSource is omitted from $credentials a notice will be raised

```php
Crew\Unsplash\HttpClient::init([
	'applicationId'	=> 'YOUR APPLICATION ID',
	'secret'		=> 'YOUR APPLICATION SECRET',
	'callbackUrl'	=> 'https://your-application.com/oauth/callback',
	'utmSource' => 'NAME OF YOUR APPLICATION'
]);
```
### Authorization workflow
To access actions that are non-public (i.e. uploading a photo to a specific account), you'll need a user's permission to access their data.

An example of this flow can be found in /examples/oauth-flow.php

Direct them to an authorization URL (configuring any scopes before generating the authorization URL):

```php
$scopes = ['public', 'write_user'];
Crew\Unsplash\HttpClient::$connection->getConnectionUrl($scopes);
```

Upon authorization, Unsplash will return to you an authentication code via your OAuth
callback handler. Use it to generate an access token:

```php
Crew\Unsplash\HttpClient::$connection->generateToken($code);
```

With the token you can now access any additional non-public actions available for the authorized user.


#### Permission Scopes

The current permission scopes defined by the [Unsplash API](https://unsplash.com/documentation#authorization) are:

- `public` (Access a user's public data)
- `read_user` (Access a user's private data)
- `write_user` (Edit and create user data)
- `read_photos` (Access private information from a user's photos)
- `write_photos` (Post and edit photos for a user)
- `write_likes` (Like a photo for a user)

----

### API methods

For more information about the the responses for each call, refer to the [official documentation](https://unsplash.com/documentation).

Some parameters are identical across all methods:

  param              | Description
---------------------|-----------------------------------------------------
`$filters`          |Filters used by the API
`$returnArrayObject` | Defines if method should return ArrayObject. *Default true*

*Note: The methods that return multiple objects return an `ArrayObject`, which acts like a normal stdClass.*

*If `$returnArrayObject` is set to `false` they return `PageResult` which contains array of results, total number of elements, page and total number of pages.*
 
*There are three exceptions: `Search::collections`, `Search::photos` and `Search::users` always returns `PageResult`.*

----

### Search

<div id="search-photos" />

#### Crew\Unsplash\Search::photos($filters)

Retrieve a single page of photo results depending on search results.

**Arguments**

  Argument     | Type   | Opt/Required
---------------|--------|--------------
`$filters`      | array | Required

**Example**


```php
$filters  = [
    'query' => 'Some query',
    'page' => 3,
    'per_page' => 15,
    'orientation' => 'landscape'
];

Crew\Unsplash\Search::photos($filters);
```

----

#### Crew\Unsplash\Search::collections($filters)

Retrieve a single page of collection results depending on search results.

**Arguments**

  Argument     | Type   | Opt/Required
---------------|--------|--------------
`$filters`      | array | Required

**Example**


```php
$filters  = [
    'query' => 'Some query',
    'page' => 3,
    'per_page' => 15,
];

Crew\Unsplash\Search::collections($filters);
```

----

#### Crew\Unsplash\Search::users($search, $page, $per_page)

Retrieve a single page of user results depending on search results.

**Arguments**

  Argument     | Type   | Opt/Required
---------------|--------|--------------
`$filters`      | array | Required

**Example**


```php
$filters  = [
    'query' => 'Some query',
    'page' => 3,
    'per_page' => 15,
];

Crew\Unsplash\Search::users($filters);
```

----

### Curated Collection

#### Crew\Unsplash\CuratedCollection::all($filters, $returnArrayObject)
Retrieve the list of curated collections.

**Arguments**

  Argument           | Type | Opt/Required
---------------------|------|--------------
`$filters`      | array | Required
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**


```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
];

Crew\Unsplash\CuratedCollection::all($page, $per_page);
```

**Example for returning PageResult instead of ArrayObject**

```php
Crew\Unsplash\CuratedCollection::all($page, $per_page, false);
```

----

#### Crew\Unsplash\CuratedCollection::find($id)
Retrieve a specific curated collection.

**Arguments**

  Argument     | Type | Opt/Required
---------------|------|--------------
`$id`          | int  | Required

**Example**

```php
Crew\Unsplash\CuratedCollection::find(integer $id);
```

----

#### Crew\Unsplash\CuratedCollection::photos($returnArrayObject)
Retrieve photos from a curated collection.

*Note:* You need to instantiate a curated collection object first.

**Arguments**

  Argument           | Type | Opt/Required
---------------------|------|--------------
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**

```php
$collection = Crew\Unsplash\CuratedCollection::find(integer $id);
$photos = $collection->photos();
```

**Example for returning PageResult instead of ArrayObject**

```php
$collection = Crew\Unsplash\CuratedCollection::find(integer $id);
$photos = $collection->photos(false);
```

----

### Collection

#### Crew\Unsplash\Collection::all($filters, $returnArrayObject)
Retrieve the list of collections.

**Arguments**

  Argument           | Type | Opt/Required
---------------------|------|--------------
`$filters`      | array | Required
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**


```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
];


Crew\Unsplash\Collection::all($filters);
```

**Example for returning PageResult instead of ArrayObject**

```php
Crew\Unsplash\Collection::all($filters, false);
```

----

#### Crew\Unsplash\Collection::featured($filters, $returnArrayObject)
Retrieve list of featured collections.

**Arguments**

  Argument           | Type | Opt/Required
---------------------|------|--------------
`$filters`      | array | Required
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**


```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
];


Crew\Unsplash\Collection::featured($filters);
```

**Example for returning PageResult instead of ArrayObject**

```php
Crew\Unsplash\Collection::featured($filters, false);
```

----

#### Crew\Unsplash\Collection::related($returnArrayObject)
Retrieve list of featured collections.

*Note* You must instantiate a collection first

**Arguments**

  Argument           | Type | Opt/Required
---------------------|------|--------------
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**


```php
$collection = Crew\Unsplash\Collection::find($id);
$collection->related();
```

**Example for returning PageResult instead of ArrayObject**

```php
$collection = Crew\Unsplash\Collection::find($id);
$collection->related(false);
```

----

#### Crew\Unsplash\Collection::find($id)
Retrieve a specific collection.

**Arguments**

  Argument     | Type | Opt/Required
---------------|------|--------------
`$id`          | int  | Required

**Example**

```php
Crew\Unsplash\Collection::find(integer $id);
```

----

#### Crew\Unsplash\Collection::photos($filters, $returnArrayObject)
Retrieve photos from a collection.

*Note:* You need to instantiate a collection object first.

**Arguments**

  Argument           | Type | Opt/Required
---------------------|------|--------------
`$filters`      | array | Required
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
];

$collection = Crew\Unsplash\Collection::find(integer $id);
$photos = $collection->photos($filters);
```

**Example for returning PageResult instead of ArrayObject**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
];

$collection = Crew\Unsplash\Collection::find(integer $id);
$photos = $collection->photos($filters, false);
```

----

#### Crew\Unsplash\Collection::create($title, $description, $private)
Create a collection on the user's behalf.

*Note:* You need the `write_collections` permission scope

**Arguments**

  Argument     | Type    | Opt/Required
---------------|---------|--------------
`$title`       | string  | Required
`$description` | string  | Opt *(Default: '')*
`$private`     | boolean | Opt *(Default: false)*

**Example**

```php
$collection = Crew\Unsplash\Collection::create($title);
```

----

#### Crew\Unsplash\Collection::update($parameters)
Update a collection on the user's behalf.

*Note:* You need to instantiate a collection object first

*Note:* You need the `write_collections` permission scope

**Arguments**

  Argument     | Type    | Opt/Required | Note
---------------|---------|--------------|-------
`$parameters`  | array   | Required     | The following keys can be set in the array : `title`, `description`, `private`

**Example**

```php
$collection = Crew\Unsplash\Collection::find(int $id);
$collection->update(['private' => true])
```

----

#### Crew\Unsplash\Collection::destroy()
Delete a collection on the user's behalf.

*Note:* You need to instantiate a collection object first

*Note:* You need the `write_collections` permission scope

**Example**

```php
$collection = Crew\Unsplash\Collection::find(int $id);
$collection->destroy()
```

----

#### Crew\Unsplash\Collection::add($photo_id)
Add a photo in the collection on the user's behalf.

*Note:* You need to instantiate a collection object first

*Note:* You need the `write_collections` permission scope

**Arguments**

  Argument     | Type    | Opt/Required |
---------------|---------|---------------
`$photo_id`    | integer | Required     |

**Example**

```php
$collection = Crew\Unsplash\Collection::find(int $id);
$collection->add(int $photo_id)
```

----

#### Crew\Unsplash\Collection::remove($photo_id)
Remove a photo from the collection on the user's behalf.

*Note:* You need to instantiate a collection object first

*Note:* You need the `write_collections` permission scope

**Arguments**

  Argument     | Type    | Opt/Required |
---------------|---------|---------------
`$photo_id`    | integer | Required     |

**Example**

```php
$collection = Crew\Unsplash\Collection::find(int $id);
$collection->remove(int $photo_id)
```

----


### Photo

<div id="photo-all" />

#### Crew\Unsplash\Photo::all($filters, $returnArrayObject)
Retrieve a list of photos.

**Arguments**

  Argument           | Type   | Opt/Required
---------------------|--------|--------------
`$filters`      | array | Required
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
    'order_by' => 'latest',
];

Crew\Unsplash\Photo::all($filters);
```

**Example for returning PageResult instead of ArrayObject**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
    'order_by' => 'latest',
];

Crew\Unsplash\Photo::all($filters, false);
```


----

#### Crew\Unsplash\Photo::curated($filters, $returnArrayObject)
Retrieve a list of curated photos.

**Arguments**

  Argument           | Type   | Opt/Required
---------------------|--------|--------------
`$filters`      | array | Required
`$returnArrayObject` | bool | Opt *(Default: true)*

**Example**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
    'order_by' => 'latest',
];

Crew\Unsplash\Photo::curated($page, $per_page, $order_by);
```

**Example for returning PageResult instead of ArrayObject**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
    'order_by' => 'latest',
];

Crew\Unsplash\Photo::curated($page, $per_page, $order_by, false);
```

----

#### Crew\Unsplash\Photo::find($id)
Retrieve a specific photo.

**Arguments**

  Argument     | Type | Opt/Required
---------------|------|--------------
`$id`          | int  | Required

**Example**

```php
Crew\Unsplash\Photo::find($id);
```

----

#### Crew\Unsplash\Photo::create($file_path)
Post a photo on the user's behalf.

*Note:* You need the `write_photos` permission scope

**Arguments**

  Argument     | Type   | Opt/Required
---------------|--------|--------------
`$file_path`   | string | Required

**Example**

```php
Crew\Unsplash\Photo::create( $file_path);
```

----

#### Crew\Unsplash\Photo::update($parameters = [])
Post a photo on the user's behalf.

*Note:* You need the `write_photos` permission scope
You need to instantiate the Photo object first

**Arguments**

  Argument     | Type   | Opt/Required
---------------|--------|--------------
`$parameters`   | array | Required

**Example**

```php
$photo = Crew\Unsplash\Photo::find(string $id)
$photo->update(array $parameters);
```

----

#### Crew\Unsplash\Photo::photographer()
Retrieve the photo's photographer.

*Note:* You need to instantiate a photo object first

**Arguments**

*N/A*

**Example**


```php
$photo = Crew\Unsplash\Photo::find(string $id);
$photo->photographer();
```

----

<div id="photo-random" />

#### Crew\Unsplash\Photo::random($filters)
Retrieve a random photo from specified filters. For more information regarding filtering, [refer to the Offical documentation](https://unsplash.com/documentation#get-a-random-photo).

*Note:* An array needs to be passed as a parameter.

**Arguments**

  Argument     | Type | Opt/Required
---------------|------|--------------
`$filters`      | array | Required


**Example**


```php

// Or apply some optional filters by passing a key value array of filters
$filters = [
    'featured' => true,
    'username' => 'andy_brunner',
    'query'    => 'coffee',
];

Crew\Unsplash\Photo::random($filters);
```

----

#### Crew\Unsplash\Photo::like()
Like a photo on the user's behalf.

*Note:* You need to instantiate a photo object first

*Note:* You need the `like_photos` permission scope

**Arguments**

*N/A*

**Example**


```php
$photo = Crew\Unsplash\Photo::find(string $id);
$photo->like();
```

----

#### Crew\Unsplash\Photo::unlike()
Unlike a photo on the user's behalf.

*Note:* You need to instantiate a photo object first

*Note:* You need the `like_photos` permission scope

**Arguments**

*N/A*

**Example**


```php
$photo = Crew\Unsplash\Photo::find(string $id);
$photo->unlike();
```

----

#### Crew\Unsplash\Photo::statistics(string $resolution, int $quantity)
Retrieve total number of downloads, views and likes of a single photo, as well as the historical breakdown of these stats in a specific timeframe (default is 30 days).

*Note:* You must instantiate a Photo object first

**Arguments**


  Argument     | Type | Opt/Required
---------------|------|--------------
resolution | string | Opt *(Accepts only days currently)*
quantity | int | Opt *(Defaults to 30, can be between 1 and 30)*


**Example**


```php


$photo = Crew\Unsplash\Photo::find($id);
$photo->statistics('days', 7);
```

----

<div id="photo-download" />

#### Crew\Unsplash\Photo::download()
Trigger a download for a photo. This is needed to follow the ['trigger a download' API Guideline](https://medium.com/unsplash/unsplash-api-guidelines-triggering-a-download-c39b24e99e02).

*Note:* You must instantiate a Photo object first

**Arguments**


  Argument     | Type | Opt/Required
---------------|------|--------------


**Example**


```php
$photo = Crew\Unsplash\Photo::find();
$photo->download();
```

----

### User

#### Crew\Unsplash\User::find($username)
Retrieve a user's information.

**Arguments**

  Argument     | Type   | Opt/Required
---------------|--------|--------------
`$username`    | string | Required

**Example**

```php
Crew\Unsplash\User::find($username)
```

----

#### Crew\Unsplash\User::portfolio($username)
Retrieve a link to the user's portfolio page.

**Arguments**

  Argument     | Type   | Opt/Required
---------------|--------|--------------
`$username`    | string | Required

**Example**

```php
Crew\Unsplash\User::portfolio($username)
```

----

#### Crew\Unsplash\User::current()
Retrieve the user's private information.

*Note:* You need the *read_user* permission scope

**Arguments**

*N/A*

**Example**

```php
$user = Crew\Unsplash\User::current();
```

----

#### Crew\Unsplash\User::photos($filters)
Retrieve user's photos.

*Note:* You need to instantiate a user object first

**Arguments**

  Argument     | Type | Opt/Required
---------------|------|--------------
`$filters`      | array | Required

**Example**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
    'order_by' => 'latest',
];

$user = Crew\Unsplash\User::find($username);
$user->photos($filters);
```

----


#### Crew\Unsplash\User::collections($filters)
Retrieve user's collections.

*Note:* You need to instantiate a user object first
*Note:* You need the *read_collections* permission scope to retrieve user's private collections

**Arguments**

  Argument     | Type | Opt/Required
---------------|------|--------------
`$filters`      | array | Required

**Example**

```php
$filters  = [
    'page' => 3,
    'per_page' => 15,
    'order_by' => 'latest',
];

$user = Crew\Unsplash\User::find($username);
$user->collections($filters);
```

----

#### Crew\Unsplash\User::likes($filters)
Retrieve user's collections.

*Note:* You need to instantiate a user object first

**Arguments**

  Argument     | Type | Opt/Required
---------------|------|--------------
`$filters`      | array | Required


**Example**

```php
$user = Crew\Unsplash\User::find($username);
$user->likes($page, $per_page, $order_by);
```

----


#### Crew\Unsplash\User::update([$key => value])
Update current user's fields. Multiple fields can be passed in the array.

*Note:* You need to instantiate a user object first

*Note:* You need the *write_user* permission scope.

**Arguments**

  Argument     | Type   | Opt/Required | Note  |
---------------|--------|--------------|-------|
`$key`         | string | Required     | The following keys are accepted: `username`, `first_name`, `last_name`, `email`, `url`, `location`, `bio`, `instagram_username`
`$value`       | mixed  | required

```php
$user = Crew\Unsplash\User::current();
$user->update(['first_name' => 'Elliot', 'last_name' => 'Alderson']);
```

#### Crew\Unsplash\User::statistics(string $resolution, int $quantity)
Retrieve total number of downloads, views and likes for a user, as well as the historical breakdown of these stats in a specific timeframe (default is 30 days).

*Note:* You must instantiate the User object first

**Arguments**


  Argument     | Type | Opt/Required
---------------|------|--------------
resolution | string | Opt *(Accepts only days currently)*
quantity | int | Opt *(Defaults to 30, can be between 1 and 30)*


**Example**


```php
$user = Crew\Unsplash\User::find($id);
$user->statistics('days', 7);
```

----

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/arcasolutions/unsplash-php. This project is intended to be a safe, welcoming space for collaboration, and contributors are expected to adhere to the [Contributor Covenant](http://contributor-covenant.org/) code of conduct.
