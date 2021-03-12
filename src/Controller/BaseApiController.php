<?php

namespace App\Controller;

use App\Exceptions\ValidatorException;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

class BaseApiController implements ContainerAwareInterface
{
    /**
     * Set groups for use groups in serializer.
     */
    protected ?array $groups = null;

    private ?ContainerInterface $container;
    protected SerializerInterface $serializer;

    public const CODE_OK = Response::HTTP_OK;
    public const CODE_CREATED = Response::HTTP_CREATED;
    public const CODE_FORBIDDEN = Response::HTTP_FORBIDDEN;
    public const CODE_BAD_REQUEST = Response::HTTP_BAD_REQUEST;
    public const CODE_UNPROCESSABLE = Response::HTTP_UNPROCESSABLE_ENTITY;

    private const HTTP_CODES = [
        Response::HTTP_CONTINUE,
        Response::HTTP_SWITCHING_PROTOCOLS,
        Response::HTTP_PROCESSING,
        Response::HTTP_EARLY_HINTS,
        Response::HTTP_OK,
        Response::HTTP_CREATED,
        Response::HTTP_ACCEPTED,
        Response::HTTP_NON_AUTHORITATIVE_INFORMATION,
        Response::HTTP_NO_CONTENT,
        Response::HTTP_RESET_CONTENT,
        Response::HTTP_PARTIAL_CONTENT,
        Response::HTTP_MULTI_STATUS,
        Response::HTTP_ALREADY_REPORTED,
        Response::HTTP_IM_USED,
        Response::HTTP_MULTIPLE_CHOICES,
        Response::HTTP_MOVED_PERMANENTLY,
        Response::HTTP_FOUND,
        Response::HTTP_SEE_OTHER,
        Response::HTTP_NOT_MODIFIED,
        Response::HTTP_USE_PROXY,
        Response::HTTP_RESERVED,
        Response::HTTP_TEMPORARY_REDIRECT,
        Response::HTTP_PERMANENTLY_REDIRECT,
        Response::HTTP_BAD_REQUEST,
        Response::HTTP_UNAUTHORIZED,
        Response::HTTP_PAYMENT_REQUIRED,
        Response::HTTP_FORBIDDEN,
        Response::HTTP_NOT_FOUND,
        Response::HTTP_METHOD_NOT_ALLOWED,
        Response::HTTP_NOT_ACCEPTABLE,
        Response::HTTP_PROXY_AUTHENTICATION_REQUIRED,
        Response::HTTP_REQUEST_TIMEOUT,
        Response::HTTP_CONFLICT,
        Response::HTTP_GONE,
        Response::HTTP_LENGTH_REQUIRED,
        Response::HTTP_PRECONDITION_FAILED,
        Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
        Response::HTTP_REQUEST_URI_TOO_LONG,
        Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
        Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE,
        Response::HTTP_EXPECTATION_FAILED,
        Response::HTTP_I_AM_A_TEAPOT,
        Response::HTTP_MISDIRECTED_REQUEST,
        Response::HTTP_UNPROCESSABLE_ENTITY,
        Response::HTTP_LOCKED,
        Response::HTTP_FAILED_DEPENDENCY,
        Response::HTTP_TOO_EARLY,
        Response::HTTP_UPGRADE_REQUIRED,
        Response::HTTP_PRECONDITION_REQUIRED,
        Response::HTTP_TOO_MANY_REQUESTS,
        Response::HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE,
        Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS,
        Response::HTTP_INTERNAL_SERVER_ERROR,
        Response::HTTP_NOT_IMPLEMENTED,
        Response::HTTP_BAD_GATEWAY,
        Response::HTTP_SERVICE_UNAVAILABLE,
        Response::HTTP_GATEWAY_TIMEOUT,
        Response::HTTP_VERSION_NOT_SUPPORTED,
        Response::HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL,
        Response::HTTP_INSUFFICIENT_STORAGE,
        Response::HTTP_LOOP_DETECTED,
        Response::HTTP_NOT_EXTENDED,
        Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED,
    ];

    /**
     * @internal
     * @required
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    protected function success(
        array $data,
        string $message,
        int $code = self::CODE_OK,
        #[ExpectedValues(self::HTTP_CODES)] int $status = Response::HTTP_OK,
        array $headers = [],
        SerializerInterface $serializer = null,
    ): JsonResponse {
        return $this->json(
            ['success' => true, 'code' => $code, 'message' => $message, 'data' => $data],
            $status,
            $headers,
            $serializer
        );
    }

    protected function error(
        array $data,
        string $message,
        int $code,
        #[ExpectedValues(self::HTTP_CODES)] int $status,
        array $headers = [],
        SerializerInterface $serializer = null,
    ): JsonResponse {
        return $this->json(
            ['success' => false, 'code' => $code, 'message' => $message, 'data' => $data],
            $status,
            $headers,
            $serializer
        );
    }

    protected function ok(array $data = [], string $message = 'ok', int $code = self::CODE_OK, array $headers = []): JsonResponse
    {
        return $this->success($data, $message, $code, Response::HTTP_OK, $headers);
    }

    protected function created(array $data = [], string $message = 'Created', int $code = self::CODE_CREATED, array $headers = []): JsonResponse
    {
        return $this->success($data, $message, $code, Response::HTTP_CREATED, $headers);
    }

    #[NoReturn]
    protected function forbidden(string $message = 'Forbidden access to resource', int $code = self::CODE_OK): void
    {
        throw new AccessDeniedHttpException($message, code: $code);
    }

    #[NoReturn]
    protected function badRequest(string $message = 'Bad request', $code = self::CODE_BAD_REQUEST): void
    {
        throw new BadRequestHttpException($message, code: $code);
    }

    #[NoReturn]
    protected function unprocessable(string $message = 'Unprocessable entity', int $code = self::CODE_UNPROCESSABLE): void
    {
        throw new UnprocessableEntityHttpException($message, code: $code);
    }

    #[NoReturn]
    protected function badInput(string $message = 'Invalid input', array $errors = []): void
    {
        throw (new ValidatorException($message))->setErrors($errors);
    }

    protected function json($data, int $status = 200, array $headers = [], SerializerInterface $serializer = null, array $context = []): JsonResponse
    {
        /* @psalm-suppress RedundantPropertyInitializationCheck */
        if (null === $serializer && isset($this->serializer)) {
            $serializer = $this->serializer;
        }
        if (null === $serializer && null !== $this->container && $this->container->has('serializer')) {
            $serializer = $this->container->get('serializer');
        }
        if (null === $serializer) {
            // use json_encode
            return new JsonResponse($data, $status, $headers);
        }
        if (isset($this->groups)) {
            $context['groups'] = $this->groups;
        }
        $json = $serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @param $attribute
     */
    protected function denyAccessUnlessGranted($attribute, object $subject = null, string $message = 'Access Denied.'): void
    {
        if (!$this->isGranted($attribute, $subject)) {
            $exception = $this->createAccessDeniedException($message);
            $exception->setAttributes($attribute);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     * @param $attribute
     */
    private function isGranted($attribute, object $subject = null): bool
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        return $this->container->get('security.authorization_checker')->isGranted($attribute, $subject);
    }

    private function createAccessDeniedException(string $message = 'Access Denied.', \Throwable $previous = null): AccessDeniedException
    {
        if (!class_exists(AccessDeniedException::class)) {
            throw new \LogicException(
                'You can not use the "createAccessDeniedException" method if the Security component is not available. '.'Try running "composer require symfony/security-bundle".'
            );
        }

        return new AccessDeniedException($message, $previous);
    }

    protected function forward(string $controller, array $path = [], array $query = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
