<?php
/**
 * Retailer Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use function esc_html;

/**
 * Class Retailer
 *
 * @package Book_Database
 * @since 1.3 Namespace changed.
 */
class Retailer extends Model
{

    /**
     * @var string Name of the retailer
     */
    protected $name = '';

    /**
     * @var string Template, for use in the book layout
     */
    protected $template = '';

    /**
     * Get the name of the retailer
     *
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Get the template
     *
     * @return string
     */
    public function get_template(): string
    {
        return $this->template;
    }

    /**
     * Build a link
     *
     * This injects the provided URL into the template, replacing the `[url]` placeholder.
     *
     * @param  string  $url
     *
     * @return string
     */
    public function build_link(string $url): string
    {

        $template = $this->get_template();

        if (empty($template)) {
            $template = '<a href="[url]" target="_blank">'.esc_html($this->get_name()).'</a>';
        }

        return str_replace('[url]', esc_url($url), $template);

    }

}
