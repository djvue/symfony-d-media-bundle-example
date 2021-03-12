<?php

namespace App\Validator;

use App\Exceptions\ValidatorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidator
{
    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    abstract protected function getConstraint(): Collection;

    public function validate(array $requestFields): array
    {
        $errors = [];

        foreach ($this->validator->validate($requestFields, $this->getConstraint()) as $violation) {
            $field = preg_replace(['/\]\[/', '/\[|\]/'], ['.', ''], $violation->getPropertyPath());
            $errors[$field] = $violation->getMessage();
        }

        return $errors;
    }

    public function filterFields(array $data): array
    {
        $allowedKeys = array_keys($this->getConstraint()->fields);

        return array_filter($data, static fn($key) => in_array($key, $allowedKeys, true), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @throws ValidatorException
     */
    public function handleRequest(Request $request): array
    {
        $data = $request->request->all();
        $data = $this->filterFields($data);
        $errors = $this->validate($data);
        if (count($errors) > 0) {
            $exception = new ValidatorException();
            $exception->setErrors($errors);
            throw $exception;
        }
        $data = $this->transformData($data);

        return $data;
    }

    protected function transformData(array $data): array
    {
        return $data;
    }
}
