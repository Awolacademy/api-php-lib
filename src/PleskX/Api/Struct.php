<?php
// Copyright 1999-2016. Parallels IP Holdings GmbH.

namespace PleskX\Api;

abstract class Struct
{

    public function __set($property, $value)
    {
        throw new \Exception("Try to set an undeclared property '$property'.");
    }

    /**
     * Initialize list of scalar properties by response
     *
     * @param \SimpleXMLElement $apiResponse
     * @param array $properties
     * @throws \Exception
     */
    protected function _initScalarProperties($apiResponse, array $properties)
    {
        foreach ($properties as $property) {
            if (is_array($property)) {
                $classPropertyName = current($property);
                $value = $apiResponse->{key($property)};
            } else {
                $classPropertyName = $this->_underToCamel(str_replace('-', '_', $property));
                $value = $apiResponse->$property;
            }

            $reflectionProperty = new \ReflectionProperty($this, $classPropertyName);
            $docBlock = $reflectionProperty->getDocComment();

            /* There seems to be a bug in the api when it encounters a docBlock with a strlen of 0.
             * Continue if so.
             */
            if(strlen($docBlock) == 0)
                continue;

            $propertyType = preg_replace('/^.+ @var ([a-z]+) .+$/', '\1', $docBlock);
            if ('string' == $propertyType) {
                $value = (string)$value;
            } else if ('integer' == $propertyType) {
                $value = (int)$value;
            } else if ('boolean' == $propertyType) {
                $value = in_array((string)$value, ['true', 'on', 'enabled']);
            } else {
                throw new \Exception("Unknown property type '$propertyType'.");
            }

            $this->$classPropertyName = $value;
        }
    }

    /**
     * Convert underscore separated words into camel case
     *
     * @param string $under
     * @return string
     */
    private function _underToCamel($under)
    {
        $under = '_' . str_replace('_', ' ', strtolower($under));
        return ltrim(str_replace(' ', '', ucwords($under)), '_');
    }

}