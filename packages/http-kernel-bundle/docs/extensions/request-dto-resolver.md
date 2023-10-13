# `RequestDtoResolver` extension

* [About](#about)
* [Features](#features)
* [Advantages over Symfony's `MapRequestPayload` & `MapQueryString` attributes](#advantages-over-symfonys-maprequestpayload--mapquerystring-attributes)
* [Usage](#usage)
  * [Parameter attribute](#parameter-attribute)
  * [Class attribute](#class-attribute)
  * [Parsing and collecting data for models](#parsing-and-collecting-data-for-models)
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
- Strong type checks will be enforced for `PUT`, `POST`, `PATCH` and `DELETE` during serialization, and it will result
  in an error if the types in the request body don't match the expected ones in the DTO.
- Type enforcement will be disabled for all other requests e.g. `GET` as query parameters will always be transferred as
  string.
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

Data that originates from the route attributes and query parameters are also validated against the model. By default
it will use the `filter_var` function with the types based on the model to convert the values. To override the parsing
you can create your own implementation of `Fusonic\HttpKernelBundle\Request\UrlParser\UrlParserInterface`.

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
