<?php

/**
 * This file contains the base authentication functionality for Firebase Cloud Messaging Push Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use BadMethodCallException;
use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Builder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use UnexpectedValueException;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Session;

/**
 * Firebase Cloud Messaging Push Notification Base Api class.
 */
abstract class FCMBaseApi
{
    /**
     * Oauth token.
     * @var string
     */
    protected ?string $oauth_token;

    /**
     * FCM id of the project.
     * @var ?string
     */
    protected ?string $project_id;

    /**
     * FCM client email of the project.
     * @var ?string
     */
    protected ?string $client_email;

    /**
     * FCM id of the project.
     * @var ?string
     */
    protected ?string $private_key;

    /**
     * Shared instance of the Requests\Session class.
     * @var Session
     */
    protected Session $http;

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Url to fetch the OAuth2 token.
     * @var string
     */
    private const GOOGLE_OAUTH_URL = 'https://oauth2.googleapis.com/token';

    /**
     * Default lifetime for the OAuth token.
     * @var string
     */
    private const DEFAULT_OAUTH_LIFETIME = '+10 minutes';

    /**
     * Maximum number of endpoints allowed in one push.
     * @var int
     */
    protected const BATCH_SIZE = 1000;

    /**
     * Constructor.
     *
     * @param Session         $http   Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger Shared instance of a Logger.
     */
    public function __construct(Session $http, LoggerInterface $logger)
    {
        $this->http         = $http;
        $this->logger       = $logger;
        $this->oauth_token  = NULL;
        $this->project_id   = NULL;
        $this->client_email = NULL;
        $this->private_key  = NULL;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->oauth_token);
        unset($this->project_id);
        unset($this->client_email);
        unset($this->private_key);
        unset($this->http);
        unset($this->logger);
    }

    /**
     * Set the FCM project id for sending notifications.
     *
     * @param string $project_id The id of the FCM project
     *
     * @return $this
     */
    public function set_project_id(string $project_id): static
    {
        $this->project_id = $project_id;

        return $this;
    }

    /**
     * Set the FCM client email for sending notifications.
     *
     * @param string $client_email The client email of the FCM project
     *
     * @return $this
     */
    public function set_client_email(string $client_email): static
    {
        $this->client_email = $client_email;

        return $this;
    }

    /**
     * Set the FCM private key for sending notifications.
     *
     * @param string $private_key The private key of the FCM project
     *
     * @return $this
     */
    public function set_private_key(string $private_key): static
    {
        $this->private_key = $private_key;

        return $this;
    }

    /**
     * Set the FCM private key for sending notifications from a file.
     *
     * @param string $private_key_file The file with the private key of the FCM project
     *
     * @return $this
     */
    public function set_private_key_from_file(string $private_key_file): static
    {
        $private_key = file_get_contents($private_key_file);

        if ($private_key === FALSE)
        {
            throw new RuntimeException('File does not exists or is not readable!');
        }

        $this->private_key = $private_key;

        return $this;
    }

    /**
     * Set a token to authenticate with.
     *
     * @param string $token The OAuth token to use
     *
     * @return $this
     */
    public function set_oauth_token(string $token): static
    {
        $this->oauth_token = $token;

        return $this;
    }

    /**
     * Request and set an oauth token from FCM.
     *
     * @param string $oauth_lifetime Relative time as a string for strtotime() to parse into an expiry timestamp.
     *
     * @see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
     *
     * @return $this
     */
    public function configure_oauth_token(string $oauth_lifetime = self::DEFAULT_OAUTH_LIFETIME): static
    {
        $this->set_oauth_token($this->get_oauth_token($oauth_lifetime));

        return $this;
    }

    /**
     * Get the oauth token for the http headers.
     *
     * @param string $oauth_lifetime Relative time as a string for strtotime() to parse into an expiry timestamp
     *
     * @see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
     *
     * @return string The OAuth_token
     */
    private function get_oauth_token(string $oauth_lifetime = self::DEFAULT_OAUTH_LIFETIME): string
    {
        if (strtotime($oauth_lifetime) === FALSE)
        {
            throw new InvalidArgumentException('Invalid oauth lifetime!');
        }

        if ($this->client_email === NULL)
        {
            throw new BadMethodCallException('Requesting token failed: No client email provided');
        }

        if ($this->private_key === NULL)
        {
            throw new BadMethodCallException('Requesting token failed: No private key provided');
        }

        $issued_at = new DateTimeImmutable();

        $token_builder = new Builder(new JoseEncoder(), ChainedFormatter::default());

        $token = $token_builder->issuedBy($this->client_email)
                               ->permittedFor('https://oauth2.googleapis.com/token')
                               ->issuedAt($issued_at)
                               ->expiresAt($issued_at->modify($oauth_lifetime))
                               ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                               ->withHeader('alg', 'RS2256')
                               ->withHeader('typ', 'JWT')
                               ->getToken(new Sha256(), InMemory::plainText($this->private_key));

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $token->toString(),
        ];

        try
        {
            $http_response = $this->http->post(self::GOOGLE_OAUTH_URL, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), []);
        }
        catch (RequestsException $e)
        {
            $context = [ 'message' => $e->getMessage() ];
            $this->logger->warning('Fetching OAuth token for FCM notification(s) failed: {message}', $context);

            throw new RuntimeException('Fetching OAuth token for FCM notification(s) failed', 0, $e);
        }

        $response_body = json_decode($http_response->body, TRUE);

        if (json_last_error() !== JSON_ERROR_NONE)
        {
            $context = [ 'message' => json_last_error_msg() ];
            $this->logger->warning('Processing json response for fetching OAuth token for FCM notification(s) failed: {message}', $context);

            $message = 'Processing json response for fetching OAuth token for FCM notification(s) failed: ' . $context['message'];
            throw new UnexpectedValueException($message);
        }

        if (!array_key_exists('access_token', $response_body))
        {
            $error_msg = $response_body['error_description'] ?? 'No access token in the response body';

            $context = [ 'error' => $error_msg ];
            $this->logger->warning('Fetching OAuth token for FCM notification(s) failed: {error}', $context);

            throw new UnexpectedValueException('Fetching OAuth token for FCM notification(s) failed: ' . $error_msg);
        }

        return $response_body['access_token'];
    }

}

?>
