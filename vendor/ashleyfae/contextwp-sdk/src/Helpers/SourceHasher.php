<?php
/**
 * SourceHash.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Helpers;

use Exception;

class SourceHasher
{
    /** @var string Name of the option where the hash is stored. */
    const HASH_OPTION_KEY = 'contextwp_source_hash';

    /**
     * Retrieves the hash for this source. If it doesn't already exist then it is created and saved.
     *
     * @return string
     * @throws Exception
     */
    public function getHash(): string
    {
        if ($hash = $this->getStoredHash()) {
            return $hash;
        }

        $hash = $this->hash($this->normalizeSiteUrl());

        $this->saveHash($hash);

        return $hash;
    }

    /**
     * Retrieves the saved hash.
     *
     * @return string|null
     */
    protected function getStoredHash(): ?string
    {
        $hash = get_option(static::HASH_OPTION_KEY);

        return ! empty($hash) ? (string) $hash : null;
    }

    /**
     * Saves the hash.
     *
     * @param  string  $hash
     *
     * @return void
     */
    protected function saveHash(string $hash): void
    {
        update_option(static::HASH_OPTION_KEY, $hash);
    }

    /**
     * Retrieves the un-normalized URL for this site.
     *
     * @return string
     */
    protected function getSiteUrl(): string
    {
        return get_bloginfo('url');
    }

    /**
     * Normalizes the site URL.
     *
     * @return string Site host and path only.
     */
    protected function normalizeSiteUrl(): string
    {
        $pieces = parse_url(trim($this->getSiteUrl()));

        // we don't care about the protocol
        $url = ($pieces['host'] ?? '').($pieces['path'] ?? '');

        // trim spaces (again) & slashes
        $url = trim(trim($url), '/');

        return strtolower($url);
    }

    /**
     * Hashes the normalized site URL.
     *
     * @param  string  $normalizedUrl
     *
     * @return string
     * @throws Exception
     */
    protected function hash(string $normalizedUrl): string
    {
        return hash_hmac('sha256', $normalizedUrl, $this->makeSecret());
    }

    /**
     * Creates a random secret.
     *
     * @return string
     * @throws Exception
     */
    protected function makeSecret(): string
    {
        return bin2hex(random_bytes(16));
    }
}
