<?php
/**
 * Pagination Helper Class
 * Handles pagination logic for database queries
 */

class Pagination
{
    private $total_items;
    private $items_per_page;
    private $current_page;
    private $total_pages;

    /**
     * Initialize pagination
     *
     * @param int $total_items Total number of items
     * @param int $items_per_page Items to display per page (default: 12)
     * @param int $current_page Current page number (from GET or default 1)
     */
    public function __construct($total_items, $items_per_page = 12, $current_page = 1)
    {
        $this->total_items = max(0, (int) $total_items);
        $this->items_per_page = max(1, (int) $items_per_page);
        $this->current_page = max(1, (int) $current_page);
        $this->total_pages = ceil($this->total_items / $this->items_per_page);

        // Ensure current page doesn't exceed total pages
        if ($this->current_page > $this->total_pages && $this->total_pages > 0) {
            $this->current_page = $this->total_pages;
        }
    }

    /**
     * Get offset for database query
     *
     * @return int
     */
    public function getOffset()
    {
        return ($this->current_page - 1) * $this->items_per_page;
    }

    /**
     * Get limit for database query
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->items_per_page;
    }

    /**
     * Get current page number
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * Get total number of pages
     *
     * @return int
     */
    public function getTotalPages()
    {
        return $this->total_pages;
    }

    /**
     * Get total number of items
     *
     * @return int
     */
    public function getTotalItems()
    {
        return $this->total_items;
    }

    /**
     * Check if there is a previous page
     *
     * @return bool
     */
    public function hasPreviousPage()
    {
        return $this->current_page > 1;
    }

    /**
     * Check if there is a next page
     *
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->current_page < $this->total_pages;
    }

    /**
     * Get previous page number
     *
     * @return int|null
     */
    public function getPreviousPage()
    {
        return $this->hasPreviousPage() ? $this->current_page - 1 : null;
    }

    /**
     * Get next page number
     *
     * @return int|null
     */
    public function getNextPage()
    {
        return $this->hasNextPage() ? $this->current_page + 1 : null;
    }

    /**
     * Get array of page numbers for pagination links
     *
     * @param int $range Number of pages to show around current (default: 2)
     * @return array
     */
    public function getPageRange($range = 2)
    {
        $start = max(1, $this->current_page - $range);
        $end = min($this->total_pages, $this->current_page + $range);

        $pages = [];
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }

    /**
     * Get start item number for current page
     *
     * @return int
     */
    public function getStartItem()
    {
        return $this->total_items > 0 ? $this->getOffset() + 1 : 0;
    }

    /**
     * Get end item number for current page
     *
     * @return int
     */
    public function getEndItem()
    {
        return min($this->getOffset() + $this->items_per_page, $this->total_items);
    }

    /**
     * Generate pagination HTML (Bootstrap 5)
     *
     * @param string $base_url Base URL for pagination links
     * @param string $page_param Parameter name for page (default: 'page')
     * @param array $extra_params Extra parameters to include in links
     * @return string HTML
     */
    public function render($base_url, $page_param = 'page', $extra_params = [])
    {
        if ($this->total_pages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';

        // Build query string for extra params
        $query_string = '';
        if (!empty($extra_params)) {
            $query_string = '&' . http_build_query($extra_params);
        }

        // Previous button
        if ($this->hasPreviousPage()) {
            $prev_page = $this->getPreviousPage();
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($base_url) . '?' . $page_param . '=' . $prev_page . $query_string . '">← Sebelumnya</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">← Sebelumnya</span></li>';
        }

        // Page numbers
        $page_range = $this->getPageRange(2);
        if ($page_range[0] > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($base_url) . '?' . $page_param . '=1' . $query_string . '">1</a></li>';
            if ($page_range[0] > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        foreach ($page_range as $page) {
            if ($page == $this->current_page) {
                $html .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($base_url) . '?' . $page_param . '=' . $page . $query_string . '">' . $page . '</a></li>';
            }
        }

        if ($page_range[count($page_range) - 1] < $this->total_pages) {
            if ($page_range[count($page_range) - 1] < $this->total_pages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($base_url) . '?' . $page_param . '=' . $this->total_pages . $query_string . '">' . $this->total_pages . '</a></li>';
        }

        // Next button
        if ($this->hasNextPage()) {
            $next_page = $this->getNextPage();
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($base_url) . '?' . $page_param . '=' . $next_page . $query_string . '">Berikutnya →</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Berikutnya →</span></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }
}
?>