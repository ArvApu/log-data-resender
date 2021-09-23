<?php

declare(strict_types=1);

namespace App\LogParsers;

abstract class LogParser
{
    public abstract function parse(array $data): array;

    /**
     * Decodes every given parameter of object
     *
     * @param array $parameters
     * @return array
     */
    protected final function decodeParametersFromObject(array $parameters): array
    {
        foreach ($parameters as $parameter => $value) {
            if (!is_string($value)) {
                continue;
            }

            $decodedParameterValue = json_decode($value);

            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            if (is_array($decodedParameterValue)) { // Array means that there are more values that can be objects
                $decodedParameterValue = $this->decodeParametersForObjects($decodedParameterValue);
            }

            if (is_object($decodedParameterValue)) {
                $decodedParameterValue = $this->decodeParametersFromObject((array)$decodedParameterValue);
            }

            $parameters[$parameter] = $decodedParameterValue;
        }

        return $parameters;
    }

    /**
     * Decodes every object's in given array values, if these values can be decoded.
     *
     * @param array $objects
     * @return array
     */
    protected final function decodeParametersForObjects(array $objects): array
    {
        foreach ($objects as $key => $value) {
            $objects[$key] = $this->decodeParametersFromObject((array)$value);
        }

        return $objects;
    }
}