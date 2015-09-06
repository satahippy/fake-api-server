# Fake API Server

## Usage

All that you need is ... Data Provider! It's provide a data depend on a request

## Data Providers

Each Data Provider implements interface `Sata\FakeServerApi\DataProvider\IDataProvider`
Now there is only one method `data` that receive `Psr\Http\Message\ServerRequestInterface` and returns some data


### RouterDataProvider

It's an compose data provider. That retrieves routes with data providers
It uses [nikic/FastRoute](https://github.com/nikic/FastRoute) under the hood, so every route should meet the requirements.

#### Example
```php
$provider = new RouterDataProvider([
    // limit and offset parameters only make sense
    '/local/articles[/]' => new PathDataProvider($filesystem, ['limit', 'offset']),
    // all other local data get from local data folder
    '/local/{trail:.*}' => new PathDataProvider($filesystem),
    // all r get from reddit (try request /r/PHP/hot.json)
    // requested every time cause of VoidCache
    '/r/{stub:.*}.json' => new ProxyDataProvider($redditGuzzle, $voidCache),
    // all r get from reddit (try request /get?your=paramter)
    '/get{stub:.*}' => new ProxyDataProvider($httpbinGuzzle, $cache),
    // all r get from reddit (try request /post?your=paramter with POST)
    '/post{stub:.*}' => new ProxyDataProvider($httpbinGuzzle, $cache)
]);

$data = $provider->data($request);
```

#### Features

After request satisfy the route, new instance of a request (with matched parameters) pass to the target data provider.


### FileDataProvider

Just returns content of specified file.

#### Example

```php
$provider = new FileDataProvider($filesystem, 'path/to/the/file.json');
$data = $provider->data($request);
```

#### Features

It's receives an instance of `League\Flysystem\Filesystem`.
About Flysystem read more here - [thephpleague/flysystem](https://github.com/thephpleague/flysystem).


### PathDataProvider

Like a `FileDataProvider` it's returns a file content, but with some additional bahavior.

First of it's mount some folder (via Flysystem) to the request (or a visa versa).
Also it can handle significant parameters.

#### Example

For example, if you have an posts API, you can mount `data/posts/` folder to all `/posts` requests.
And of course, your API has a pagination logic. Like a `/posts?page=1`, `/posts?page=2`.

So by here is map of your request to the files
- `/posts` -> `data/posts/default.json`
- `/posts?page=1` -> `data/posts/page_1.json`
- `/posts?page=2` -> `data/posts/page_2.json`
- `/posts?some=parameter` -> `data/posts/default.json`

By default, if the file is not found the default file will be mounted.

```php
$filesystem = new Filesystem(new Local(__DIR__ . '/data'));
$provider = new PathDataProvider($filesystem, ['page']);
$data = $provider->data($request);
```


### ProxyDataProvider

Primarily this data provider used for a slow connections.

It's receives an instances of [Guzzle Client](https://github.com/guzzle/guzzle) and [Doctrine Cache](https://github.com/doctrine/cache)

So every request will be proxied to the remote server and saved in cache.

#### Example

```php
$cache = new FilesystemCache(__DIR__ . '/cache');
$voidCache = new VoidCache();
$filesystem = new Filesystem(new Local(__DIR__ . '/data'));
$redditGuzzle = new GuzzleHttp\Client(['base_uri' => 'https://www.reddit.com']);
$httpbinGuzzle = new GuzzleHttp\Client(['base_uri' => 'http://httpbin.org']);

// request everytime cause of VoidCache
$redditProvider = new ProxyDataProvider($redditGuzzle, $voidCache);
// request only once an save to the FilesystemCache
$httpbinProvider = new ProxyDataProvider($httpbinGuzzle, $cache);
```


## Restrictions

There are really bad work with other response/request parameters.
If your application relies on http statuses, so it's may be a huge problem.