# `RequestDtoResolver` extension

* [About](#about)
* [Features](#features)
* [How it works](#how-it-works)
* [Advantages over Symfony's `MapRequestPayload` & `MapQueryString` attributes](#advantages-over-symfonys-maprequestpayload--mapquerystring-attributes)
* [Usage](#usage)
  * [Parameter attribute](#parameter-attribute)
  * [Class attribute](#class-attribute)
  * [Parsing and collecting data for models](#parsing-and-collecting-data-for-models)
  * [Route and query parameters](#route-and-query-parameters)
  * [Error handling](#error-handling)
  * [Using an exception listener/subscriber](#using-an-exception-listenersubscriber)
  * [`ContextAwareProvider`](#contextawareprovider)

## About

In Symfony, the framework offers a powerful feature called
[argument resolvers](https://symfony.com/doc/current/controller/argument_value_resolver.html). These resolvers allow
developers to manipulate and assign values to controller action arguments before the actions are executed. For example,
you can use the built-in `RequestValueResolver`, which automatically injects the current request as an argument into the
invoked action. For more specific use cases, we've developed a custom argument resolver that goes beyond simple object
injection, providing additional functionality and capabilities.

## Features

Our RequestDtoResolver can be used to map request data directly to objects. Instead of manually retrieving all the
information from your request and placing it in an object or, heaven forbid, passing around generic data arrays, this
class leverages the Symfony [Serializer](https://symfony.com/doc/current/components/serializer.html) to map requests to
objects. This enables you to use custom objects as data transfer objects (DTOs) to transport the request data from your
controller to your business logic. Additionally, it will validate the resulting object using the Symfony
[Validator component](https://symfony.com/doc/current/components/validator.html) if you set validation constraints.

- Mapping will happen for parameters accompanied by the [`Fusonic\HttpKernelBundle\Attribute\FromRequest`
  attribute](/src/Attribute/FromRequest.php). Alternatively the attribute can also be set on the class of the parameter
  (see example below).
- The request body will be used for `PUT`, `POST`, `PATCH` and `DELETE` requests. JSON request bodies are typed, so
  strong type checks will be enforced and it will result in an error if the types in the request body don't match the
  expected ones in the DTO.
- Route and query parameters always come in as strings and will be converted to the types of the DTO (see
  [Route and query parameters](#route-and-query-parameters)).
- The request body will be combined with route parameters for `PUT`, `POST`, `PATCH` and `DELETE` requests (query
  parameters will be ignored in this case).
- The query parameters will be combined with route parameters for all other requests (request body will be ignored in
  this case).
- Route parameters will always override query parameters or request body values with the same name.
- After deserializing the request to an object, validation will take place.
- A `BadRequestHttpException` will be thrown when
    - the resulting DTO object is invalid according to Symfony Validation
    - the request body can't be deserialized
    - the request contains invalid JSON
    - the request contains valid JSON but the hierarchy levels exceeds 512
- If you are using the [ConstraintViolationErrorHandler](/src/ErrorHandler/ConstraintViolationErrorHandler.php) error
  handler, a [ConstraintViolationException](/src/Exception/ConstraintViolationException.php) will be thrown if the
  validation of your object fails. You can also implement your own handler by implementing the
  [ErrorHandlerInterface](/src/ErrorHandler/ErrorHandlerInterface.php).
- Depending on the given content type it will either parse the request body as a regular form or parse the content as JSON
  if the content type is set accordingly.

## How it works

1. The `RequestDtoResolver` checks if the controller argument is supported (it has the `FromRequest` attribute on the
   parameter or the class).
2. The `RequestDataCollectorInterface` (the `RequestDataCollector` or the `StrictRequestDataCollector`, see
   [Route and query parameters](#route-and-query-parameters)) collects the data from the request:
   - the request body is parsed with a `RequestBodyParserInterface` depending on the content type (JSON or form)
   - route and query parameters are added, depending on the implementation converted to the types of the DTO
3. The collected data is denormalized into the DTO with the Symfony Serializer, using the denormalization context of
   the `RequestDataCollectorInterface`.
4. All `ContextAwareProviderInterface` implementations that support the DTO add their data.
5. The DTO is validated with the Symfony Validator.

Errors during denormalization (invalid types, invalid enum values, missing constructor arguments, ...) and validation
are passed to the `ErrorHandlerInterface` (by default the `ConstraintViolationErrorHandler`), which maps them to a
`ConstraintViolationException`. This way all errors have the same format.

## Advantages over Symfony's `MapRequestPayload` & `MapQueryString` attributes

Since Symfony 6.3, the
[`MapRequestPayload` & `MapQueryString` attributes](https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects)
provide a very similar functionality compared to the `RequestDtoResolver` and the `FromRequest` attribute.

However, Symfony's current implementation has a few disadvantages when compared to this extension:
- Route parameters are not injected as properties into DTOs
- Error messages are fundamentally based on the `ConstraintViolationListInterface` interface, are however always thrown
  as an `HttpException` with minimal information only
- The type checks are not as strict as the ones from this extension, especially with non-scalar data types

## Usage

> [!NOTE]
> The bundle performs necessary configuration adjustments automatically (see [config/services.php](/config/services.php)).

Create your DTO like the `UpdateFooDto` example below (using `public readonly` properties is one way, a getter/setter
combination, or `private` constructor properties with a getter work as well):

```php
// ...
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateFooDto {
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Positive]
        public int $id,

        #[Assert\NotBlank]
        public string $clientVersion,

        #[Assert\NotNull]
        public array $browserInfo,
    ) {
    }
}
```

### Parameter attribute

Finally, add the DTO alongside the `FromRequest` attribute to your controller action. Routing requirements are optional.

```php
// ...
use Fusonic\HttpKernelBundle\Attribute\FromRequest;

final class FooController extends AbstractController
{
    #[Route(path: '/{id}/update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function updateAction(#[FromRequest] UpdateFooDto $dto): Response
    {
        // do something with your $dto here
    }
}
```

### Class attribute

Alternatively you can also add the attribute to the DTO class itself instead of the parameter in the controller action,
if you prefer it this way.

```php
// ...
use Fusonic\HttpKernelBundle\Attribute\FromRequest;

#[FromRequest]
final readonly class UpdateFooDto
{
// ...
}
```

```php
// ...

final class FooController extends AbstractController
{
    #[Route(path: '/{id}/update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function updateAction(UpdateFooDto $dto): Response
    {
        // do something with your $dto here
    }
}
```

### Parsing and collecting data for models
By default, any `json` or `form` request body types will be parsed accordingly. To override this behaviour you could
inject your own request body parsers (by implementing `Fusonic\HttpKernelBundle\Request\BodyParser\RequestBodyParserInterface`)
into an implementation of `Fusonic\HttpKernelBundle\Request\RequestDataCollectorInterface`, which is injected into the
`Fusonic\HttpKernelBundle\Controller\RequestDtoResolver`. Inside the `RequestDataCollectorInterface` you can
also modify the behaviour of how and which values are used from the `Request` object.

### Route and query parameters

Route and query parameters always come in as strings. There are two implementations of the
`RequestDataCollectorInterface` that handle them differently:

- `RequestDataCollector` (default): route parameters that look like integers are converted to integers and type
  enforcement of the serializer is disabled for requests without a body, so query parameters are converted by PHP.
- `StrictRequestDataCollector`: route parameters, query parameters and form request bodies are converted to the types
  of the DTO. Invalid values result in a `ConstraintViolationException` with the property path of the invalid value
  (e.g. `filter.ids[1]`), the same way as for a JSON request body.

The `StrictRequestDataCollector` is not enabled by default to not break existing projects. To enable it:

```yaml
# config/packages/fusonic_http_kernel.yaml
fusonic_http_kernel:
    strict: true # default: false
```

The `StrictRequestDataCollector` uses the types of the DTO properties (including PHPDoc types like `array<int>`) to
convert the values. The following types are supported:

- `int`, `float`, `bool` and `string`
- backed and unit enums (passed as a string to the serializer)
- `\DateTimeInterface` implementations (passed as a string to the serializer)
- arrays, including typed arrays like `array<int>` or `ExampleEnum[]`
- objects and arrays of objects, by using nested parameters like `?filter[name]=foo&filter[ids][]=1`
- union types of the types above, like `int|string`. The most restrictive type is tried first, so `?id=1` with
  `int|string` results in the integer `1`. Union types with objects or arrays are not supported.

Union types that contain objects or arrays throw an `UnionTypeNotSupportedException`.

By default the `FilterVarUrlParser` is used, which converts the values with `filter_var`. To change the parsing, you can
create your own implementation of `Fusonic\HttpKernelBundle\Request\UrlParser\UrlParserInterface` and pass it to the
`StrictRequestDataCollector`. For example:

- `isNull()`: which values are considered `null` for nullable properties. By default this is an empty string, but
  you could also treat the string `null` as `null`.
- `handleArrayParameter()`: how arrays are passed in the url. By default regular url arrays (`?ids[]=1&ids[]=2`) are
  used, but you could also split comma separated values (`?ids=1,2`). It is up to the implementation to decide whether
  a value with a comma is a list or a single value.
- `handleFailure()`: what happens with invalid values. By default a `NotNormalizableValueException` is thrown, which
  results in a `ConstraintViolationException`. You could also do nothing and let a later validation step handle this.

### Error handling

The bundle provides a default error handler (`http-kernel-bundle/src/ErrorHandler/ConstraintViolationErrorHandler.php`)
which handles common de-normalization errors that should be considered type errors. It will create a
`Fusonic\HttpKernelBundle\Exception\ConstraintViolationException` [ConstraintViolationException](/src/Exception/ConstraintViolationException.php)
which can be used with the provided `Fusonic\HttpKernelBundle\Normalizer\ConstraintViolationExceptionNormalizer` [ConstraintViolationExceptionNormalizer](/src/Normalizer/ConstraintViolationExceptionNormalizer.php).
This normalizer is uses on Symfony's built-in `Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer`
and enhances it with extra information: an `errorCode`. Useful for parsing validation  errors on the client side. If
that does not match your needs you can simply provide your own error handler by implementing the
`Fusonic\HttpKernelBundle\ErrorHandler\ErrorHandlerInterface` and passing it to the `RequestDtoResolver`.

### Using an exception listener/subscriber

In Symfony, you can use an exception listener or subscriber to eventually convert the `ConstraintViolationException`
into an actual response using the `Fusonic\HttpKernelBundle\Normalizer\ConstraintViolationExceptionNormalizer`.
For example:

```php
// ...
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionSubscriber implements EventSubscriberInterface {

    public function __construct(private readonly NormalizerInterface $normalizer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof ConstraintViolationException) {
            $data = $this->normalizer->normalize($throwable);
            $event->setResponse(new JsonResponse($data, 422));
        }
    }
}
```

Check Symfony's [Events and Event Listeners](https://symfony.com/doc/current/event_dispatcher.html) documentation for more details.

### `ContextAwareProvider`

There are cases where you want to add data to your DTOs but not through the consumer of the API but, for example,
depending on the currently logged-in user. You could do that manually after you received your DTO in the controller,
get the user, set the user for the DTO and then move on with the processing. As you set it after the creation of the DTO
you cannot work with the validation and have to make it nullable as well. And you might have to do some additional
checks in your business logic afterward to ensure everything you need is set.

Or you just create and register a provider, implement (and test) it once and be done with it. All providers will be
called by the `RequestDtoResolver`, retrieve the needed data for the supported DTO, set it in your DTO and then the
validation will take place. By the time you get it in your controller it's complete and validated. How do you do that?

1. Create a provider and implement the two methods of the `ContextAwareProvideInterface`.

```php
// ...
use Fusonic\HttpKernelBundle\Provider\ContextAwareProviderInterface;

final readonly class UserIdAwareProvider implements ContextAwareProviderInterface
{
    public function __construct(private UserProviderInterface $userProvider)
    {
    }

    public function supports(object $dto): bool
    {
        return $dto instanceof UserIdAwareInterface;
    }

    public function provide(object $dto): void
    {
        if(!($dto instanceof UserIdAwareInterface)) {
            throw new \LogicException('Object is no instance of '.UserIdAwareInterface::class);
        }

        $user = $this->userProvider->getUser();
        $dto->withUserId($user->getId());
    }
}
```

2. Create the interface to mark the class you support and set the data.

```php
//...
interface UserIdAwareInterface
{
    public function withUserId(int $id): void;
}
```

3. Implement the interface in the DTO.

> [!NOTE]
> The `ContextAwareProviderInterface` is internally autoconfigured and makes use of Symfony's `TaggedIterator`
> attribute. You therefore don't have to add any additional configuration for custom providers to work.
