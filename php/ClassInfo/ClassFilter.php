<?php

namespace PHPCD\ClassInfo;

class ClassFilter
{
    private $criteria = [];

    private $fields = [
        'isAbstractClass',
        'isFinal',
        'isTrait',
        'isInstantiable',
        'isInterface'
    ];

    public function __construct(array $criteria)
    {
        foreach ($this->fields as $field) {
            if (isset($criteria[$field])) {
                $this->validateField($criteria[$field]);
                $this->criteria[$field] = $criteria[$field];
            } else {
                 $criteria[$field] = null;
            }
        }

        // @todo Disable it to normal usage
        $this->validate();
    }

    public function getFieldNames()
    {
        return $this->fields;
    }

    private function validateField($field)
    {
        if ($field !== true && $field !== false && $field !== null) {
            $message = sprintf('%s must be set to true, false or null.', (string)$field);
            throw new \InvalidArgumentException($message);
        }

        return true;
    }

    /**
     * Probably usable only as remainder
     * develop calling requests the client
     */
    private function validate()
    {
        $wrongCombinations = [
            ['isAbstractClass' => true, 'isTrait' => true],
            ['isAbstractClass' => true, 'isInterface' => true],
            ['isAbstractClass' => true, 'isFinal' => true],
            ['isAbstractClass' => true, 'isInstantiable' => true],
            ['isInstantiable' => true, 'isTrait' => true],
            ['isInstantiable' => true, 'isInterface' => true],
            ['isTrait' => true, 'isInterface' => true],
            ['isTrait' => true, 'isFinal' => true],
            ['isInterface' => true, 'isFinal' => true],
            ['isInstantiable' => false, 'isFinal' => true]
        ];

        foreach ($wrongCombinations as $combination) {
            $fields = [];
            foreach ($combination as $field => $value) {
                if ($this->criteria[$field] === $value) {
                    $fields[] = $field;
                }
            }

            if (count($fields) === count($combination)) {
                $message = sprintf(
                    'Bad search criteria: [%s] used at once.',
                    implode(',', $fields)
                );
                throw new \InvalidArgumentException($message);
            }
        }

        return true;
    }

    /**
     * @return bool|null
     */
    public function isAbstractClass()
    {
        return $this->criteria['isAbstractClass'];
    }

    /**
     * @return bool|null
     */
    public function isFinal()
    {
        return $this->criteria['isFinal'];
    }

    /**
     * @return bool|null
     */
    public function isTrait()
    {
        return $this->criteria['isTrait'];
    }

    /**
     * @return bool|null
     */
    public function isInstantiable()
    {
        return $this->criteria['isInstantiable'];
    }

    /**
     * @return bool|null
     */
    public function isInterface()
    {
        return $this->criteria['isInterface'];
    }
}
