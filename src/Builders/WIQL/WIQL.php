<?php

namespace TestMonitor\DevOps\Builders\WIQL;

use Closure;

class WIQL
{
    /**
     * @var string[]
     */
    protected $selects = [Field::ID];

    /**
     * @var string
     */
    protected $from = 'WorkItems';

    /**
     * @var array
     */
    protected $wheres = [];

    /**
     * @var array
     */
    protected $orders = [];

    /**
     * Set field selection.
     *
     * @param array $fields
     * @return \TestMonitor\DevOps\Builders\WIQL\WIQL
     */
    public function select(array $fields): self
    {
        $this->selects = $fields;

        return $this;
    }

    /**
     * Set query source (WorkItems, workItemLinks).
     *
     * @param array $fields
     * @return \TestMonitor\DevOps\Builders\WIQL\WIQL
     */
    public function from(string $source): self
    {
        $this->from = $source;

        return $this;
    }

    /**
     * Add a new where condition.
     *
     * @param string $field
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return \TestMonitor\DevOps\Builders\WIQL\WIQL
     */
    public function where(
        string $field,
        mixed $operator = Operator::EQUALS,
        mixed $value = null,
        string $boolean = Keyword::AND
    ): self {
        $this->wheres[] = compact('field', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * Add a new where condition (using OR).
     *
     * @param string $field
     * @param mixed $operator
     * @param mixed $value
     * @return \TestMonitor\DevOps\Builders\WIQL\WIQL
     */
    public function orWhere(string $field, mixed $operator = Operator::EQUALS, mixed $value = null): self
    {
        return $this->where($field, $operator, $value, Keyword::OR);
    }

    /**
     * Add new sort criteria.
     *
     * @param string $field
     * @param string $direction
     * @return \TestMonitor\DevOps\Builders\WIQL\WIQL
     */
    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->orders[] = compact('field', 'direction');

        return $this;
    }

    /**
     * Executes the callback when value is true.
     *
     * @param mixed $value
     * @param callable $callback
     * @return \TestMonitor\DevOps\Builders\WIQL\WIQL
     */
    public function when(mixed $value, callable $callback): self
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if ($value) {
            return $callback($this, $value) ?? $this;
        }

        return $this;
    }

    /**
     * Quotes a value based on its operator.
     *
     * @param string $operator
     * @param mixed $value
     * @return string
     */
    protected function quote(string $operator, mixed $value): string
    {
        if (in_array($operator, [Operator::IN, Operator::NOT_IN], true)) {
            $values = implode(
                ', ',
                array_map(
                    fn ($value) => "'$value'",
                    (array) $value
                )
            );

            return "($values)";
        }

        return "'{$value}'";
    }

    /**
     * Generates the WIQL query.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return trim(
            $this->buildSelect() . ' ' .
            $this->buildFrom() . ' ' .
            $this->buildWhere() . ' ' .
            $this->buildOrder()
        );
    }

    /**
     * Generate the SELECT part of the query.
     *
     * @return string
     */
    protected function buildSelect(): string
    {
        return 'SELECT ' . implode(', ', $this->selects);
    }

    /**
     * Generate the FROM part of the query.
     *
     * @return string
     */
    protected function buildFrom(): string
    {
        return "FROM {$this->from}";
    }

    /**
     * Generate the WHERE part of the query.
     *
     * @return string
     */
    protected function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $wiql = 'WHERE ';

        foreach ($this->wheres as $key => $condition) {
            $values = $this->quote($condition['operator'], $condition['value']);

            $wiql .= $key !== array_key_first($this->wheres) ? " {$condition['boolean']} " : '';

            $wiql .= "{$condition['field']} {$condition['operator']} {$values}";
        }

        return $wiql;
    }

    /**
     * Generate the ORDER BY part of the query.
     *
     * @return string
     */
    protected function buildOrder(): string
    {
        if (empty($this->orders)) {
            return '';
        }

        $criteria = array_map(
            fn ($sort) => trim("{$sort['field']} {$sort['direction']}"),
            $this->orders
        );

        return 'ORDER BY ' . implode(', ', $criteria);
    }
}
