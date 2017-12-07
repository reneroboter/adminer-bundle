<?php
/**
 * This file is part of the TRIOTECH adminer-bundle project.
 *
 * @copyright TRIOTECH <open-source@triotech.fr>
 * @license https://joinup.ec.europa.eu/page/eupl-text-11-12 EUPL v1.2 or higher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Triotech\AdminerBundle\Adminer;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use function Adminer\xxtea\encrypt_string;

class DatabaseExtractor
{
    use AdminerPathAwareTrait;

    protected const ADMINER_PERMANENT_COOKIE = 'adminer_permanent';
    protected const ADMINER_KEY_COOKIE = 'adminer_key';

    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @param RegistryInterface $doctrine
     * @param string $adminerPath
     */
    public function __construct(RegistryInterface $doctrine, $adminerPath)
    {
        $this->doctrine = $doctrine;
        $this->setAdminerPath($adminerPath);
    }

    /**
     * @param Request $request
     *
     * @return null|RedirectResponse
     */
    public function updateAdminerDatabases(Request $request): ?RedirectResponse
    {
        $databases = $this->readAdminerDatabases($request);
        $edited = false;

        foreach ($this->doctrine->getConnections() as $connection) {
            $this->storeAdminerPassword($request, $connection);
            $key = $this->buildAdminerKey($connection);
            $val = implode(':', [$key, urlencode($this->encryptAdminerPassword($connection))]);

            if (!array_key_exists($key, $databases) || $databases[$key] !== $val) {
                $edited = true;
                $databases[$key] = $val;
            }
        }

        if (!$edited) {
            return null;
        }

        $uri = $request->getBaseUrl() . $request->getPathInfo();
        $response = new RedirectResponse($request->getUri());

        $response->headers->setCookie(new Cookie(static::ADMINER_PERMANENT_COOKIE, implode(' ', $databases), new \DateTime('next month'), $uri));

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function readAdminerDatabases(Request $request): array
    {
        $cookie = $request->cookies->get(static::ADMINER_PERMANENT_COOKIE);
        $databases = [];

        if ($cookie) {
            foreach (explode(' ', $cookie) as $val) {
                [$key] = explode(':', $val);
                $databases[$key] = $val;
            }
        }

        return $databases;
    }

    /**
     * @param Connection $connection
     *
     * @return string
     */
    protected function buildAdminerKey(Connection $connection): string
    {
        /** @var string[] $params */
        $params = $connection->getParams();

        return implode('-', [
            base64_encode($this->getAdminerDriver($connection)),
            base64_encode($this->getAdminerHost($connection)),
            base64_encode($params['user']),
            base64_encode($params['dbname']),
        ]);
    }

    /**
     * @param Connection $connection
     *
     * @return string
     */
    protected function getAdminerDriver(Connection $connection): string
    {
        $dbName = $connection->getDatabasePlatform()->getName();

        switch ($dbName) {
            case 'mysql':
                return 'server';
            case 'postgresql':
                return 'pgsql';
        }

        return $dbName;
    }

    /**
     * @param Connection $connection
     *
     * @return string
     */
    protected function getAdminerHost(Connection $connection): string
    {
        $host = $connection->getHost();
        $port = (int)$connection->getPort();
        $defaultPort = 0;

        if (!$host) {
            $host = 'localhost';
        }

        switch ($connection->getDatabasePlatform()->getName()) {
            case 'mysql':
                $defaultPort = 3006;
                break;
            case 'postgresql':
                $defaultPort = 5432;
                break;
        }

        return $port === 0 || $port === $defaultPort ? $host : "{$host}:{$port}";
    }

    /**
     * @param Connection $connection
     *
     * @return string
     */
    protected function encryptAdminerPassword(Connection $connection): string
    {
        $private = Functions::passwordFile(true);

        return $private ? encrypt_string($connection->getPassword(), $private) : '';
    }

    /**
     * @param Request $request
     * @param Connection $connection
     */
    protected function storeAdminerPassword(Request $request, Connection $connection): void
    {
        @session_start();
        $driver = $this->getAdminerDriver($connection);
        $server = $this->getAdminerHost($connection);
        $db = $connection->getDatabase();
        $username = $connection->getUsername();
        $password = $request->cookies->has(static::ADMINER_KEY_COOKIE) ? encrypt_string($connection->getPassword(), $request->cookies->get(static::ADMINER_KEY_COOKIE)) : '';

        $_SESSION['pwds'][$driver][$server][$username] = [$password];
        $_SESSION['db'][$driver][$server][$username][$db] = true;
    }
}
