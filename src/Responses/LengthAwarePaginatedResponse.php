<?php

namespace TestMonitor\DevOps\Responses;

class LengthAwarePaginatedResponse
{
    /**
     * The items being paginated.
     *
     * @var array
     */
    protected array $items;

    /**
     * The total number of items being paginated.
     *
     * @var int
     */
    protected int $total;

    /**
     * The number of items shown per page.
     *
     * @var int
     */
    protected int $perPage;

    /**
     * The current item offset.
     *
     * @var int
     */
    protected int $offset;

    /**
     * Create a new paginated response instance.
     *
     * @param array $items
     * @param int $total
     * @param int $perPage
     * @param int $offset
     */
    public function __construct(array $items, int $total, int $perPage, int $offset = 0)
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->offset = $offset;
    }

    /**
     * Get the items being paginated.
     *
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the current item offset.
     *
     * @return int
     */
    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * Determine the current page.
     *
     * @return int
     */
    public function currentPage(): int
    {
        if ($this->offset === 0 || $this->perPage === 0) {
            return 1;
        }

        return (int) floor($this->offset / $this->perPage) + 1;
    }
}
