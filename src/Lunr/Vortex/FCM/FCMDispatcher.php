<?php

/**
 * This file contains functionality to dispatch Firebase Cloud Messaging Push Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
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
use Lunr\Vortex\PushNotificationMultiDispatcherInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use UnexpectedValueException;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * Firebase Cloud Messaging Push Notification Dispatcher.
 */
class FCMDispatcher implements PushNotificationMultiDispatcherInterface
{
    /**
     * Maximum number of endpoints allowed in one push.
     * @var integer
     */
    private const BATCH_SIZE = 1000;

    /**
     * Push Notification Oauth token.
     * @var string
     */
    protected ?string $oauthToken;

    /**
     * FCM id of the project.
     * @var ?string
     */
    protected ?string $projectID;

    /**
     * FCM client email of the project.
     * @var ?string
     */
    protected ?string $clientEmail;

    /**
     * FCM id of the project.
     * @var ?string
     */
    protected ?string $privateKey;

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
     * Url to send the FCM push notification to.
     * @var string
     */
    private const GOOGLE_SEND_URL = 'https://fcm.googleapis.com/v1/projects/';

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
     * Constructor.
     *
     * @param Session         $http   Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger Shared instance of a Logger.
     */
    public function __construct(Session $http, LoggerInterface $logger)
    {
        $this->http        = $http;
        $this->logger      = $logger;
        $this->oauthToken  = NULL;
        $this->projectID   = NULL;
        $this->clientEmail = NULL;
        $this->privateKey  = NULL;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->oauthToken);
        unset($this->projectID);
        unset($this->clientEmail);
        unset($this->privateKey);
        unset($this->http);
        unset($this->logger);
    }

    /**
     * Set the FCM project id for sending notifications.
     *
     * @param string $projectID The id of the FCM project
     *
     * @return $this
     */
    public function set_project_id(string $projectID): static
    {
        $this->projectID = $projectID;

        return $this;
    }

    /**
     * Set the FCM client email for sending notifications.
     *
     * @param string $clientEmail The client email of the FCM project
     *
     * @return $this
     */
    public function set_client_email(string $clientEmail): static
    {
        $this->clientEmail = $clientEmail;

        return $this;
    }

    /**
     * Set the FCM private key for sending notifications.
     *
     * @param string $privateKey The private key of the FCM project
     *
     * @return $this
     */
    public function set_private_key(string $privateKey): static
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * Set the FCM private key for sending notifications from a file.
     *
     * @param string $privateKeyFile The file with the private key of the FCM project
     *
     * @return $this
     */
    public function set_private_key_from_file(string $privateKeyFile): static
    {
        $privateKey = file_get_contents($privateKeyFile);

        if ($privateKey === FALSE)
        {
            throw new RuntimeException('File does not exists or is not readable!');
        }

        $this->privateKey = $privateKey;

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
        $this->oauthToken = $token;

        return $this;
    }

    /**
     * Request and set an oauth token from FCM.
     *
     * @param string $oauthLifetime Relative time as a string for strtotime() to parse into an expiry timestamp.
     *
     * @see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
     *
     * @return $this
     */
    public function configure_oauth_token(string $oauthLifetime = self::DEFAULT_OAUTH_LIFETIME): static
    {
        $this->set_oauth_token($this->get_oauth_token($oauthLifetime));

        return $this;
    }

    /**
     * Getter for FCMResponse.
     *
     * @return FCMResponse
     */
    public function get_response(): FCMResponse
    {
        return new FCMResponse();
    }

    /**
     * Getter for FCMBatchResponse.
     *
     * @param array<string|int,Response|RequestsException> $httpResponses Array of Requests\Response object.
     * @param LoggerInterface                              $logger        Shared instance of a Logger.
     * @param string[]                                     $endpoints     The endpoints the message was sent to.
     *
     * @return FCMBatchResponse
     */
    public function get_batch_response(array $httpResponses, LoggerInterface $logger, array $endpoints): FCMBatchResponse
    {
        return new FCMBatchResponse($httpResponses, $logger, $endpoints);
    }

    /**
     * Push the notification.
     *
     * @param object   $payload   Payload object
     * @param string[] $endpoints Endpoints to send to in this batch
     *
     * @return FCMResponse Response object
     */
    public function push(object $payload, array &$endpoints): FCMResponse
    {
        if (!$payload instanceof FCMPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        if ($endpoints === [] && !$payload->is_broadcast())
        {
            throw new InvalidArgumentException('No target provided!');
        }

        $fcmResponse = $this->get_response();

        if ($this->oauthToken === NULL || $this->projectID === NULL)
        {
            if ($this->oauthToken === NULL)
            {
                $httpCode     = 401;
                $errorMessage = 'Tried to push FCM notification but wasn\'t authenticated.';
            }
            else
            {
                $httpCode     = 400;
                $errorMessage = 'Tried to push FCM notification but project id is not provided.';
            }

            $this->logger->warning($errorMessage);

            $httpResponse = $this->get_new_response_object_for_failed_request($httpCode);

            $fcmResponse->add_batch_response($this->get_batch_response([ $httpResponse ], $this->logger, $endpoints), $endpoints);

            return $fcmResponse;
        }

        if ($payload->is_broadcast())
        {
            $batchResponse = $this->push_batch($payload, $endpoints);

            $fcmResponse->add_broadcast_response($batchResponse);

            return $fcmResponse;
        }

        foreach (array_chunk($endpoints, self::BATCH_SIZE) as &$batch)
        {
            $batchResponse = $this->push_batch($payload, $batch);

            $fcmResponse->add_batch_response($batchResponse, $batch);

            unset($batchResponse);
        }

        unset($batch);

        return $fcmResponse;
    }

    /**
     * Push the notification to a batch of endpoints.
     *
     * @param object   $payload   Payload object
     * @param string[] $endpoints Endpoints to send to in this batch
     *
     * @return FCMBatchResponse Response object
     */
    public function push_batch(object $payload, array &$endpoints): FCMBatchResponse
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->oauthToken,
        ];

        $options = [
            'timeout'          => 30, // timeout in seconds
            'connect_timeout'  => 30, // timeout in seconds
            'protocol_version' => 2.0,
        ];

        $url = self::GOOGLE_SEND_URL . $this->projectID . '/messages:send';

        $responses = [];

        if ($payload->is_broadcast())
        {
            try
            {
                $responses[] = $this->http->post(
                    $url,
                    $headers,
                    $payload->get_json_payload(JSON_UNESCAPED_UNICODE),
                    $options
                );
            }
            catch (RequestsException $e)
            {
                $responses[] = $e;
            }
        }
        else
        {
            foreach ($endpoints as $endpoint)
            {
                try
                {
                    $responses[$endpoint] = $this->http->post(
                        $url,
                        $headers,
                        $payload->set_token($endpoint)->get_json_payload(JSON_UNESCAPED_UNICODE),
                        $options
                    );
                }
                catch (RequestsException $e)
                {
                    $responses[$endpoint] = $e;
                }
            }
        }

        return $this->get_batch_response($responses, $this->logger, $endpoints);
    }

    /**
     * Get the oauth token for the http headers.
     *
     * @param string $oauthLifetime Relative time as a string for strtotime() to parse into an expiry timestamp
     *
     * @see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
     *
     * @return string The OAuth_token
     */
    public function get_oauth_token(string $oauthLifetime = self::DEFAULT_OAUTH_LIFETIME): string
    {
        if (strtotime($oauthLifetime) === FALSE)
        {
            throw new InvalidArgumentException('Invalid oauth lifetime!');
        }

        if ($this->clientEmail === NULL)
        {
            throw new BadMethodCallException('Requesting token failed: No client email provided');
        }

        if ($this->privateKey === NULL)
        {
            throw new BadMethodCallException('Requesting token failed: No private key provided');
        }

        $issuedAt = new DateTimeImmutable();

        $tokenBuilder = new Builder(new JoseEncoder(), ChainedFormatter::default());

        $token = $tokenBuilder->issuedBy($this->clientEmail)
                              ->permittedFor('https://oauth2.googleapis.com/token')
                              ->issuedAt($issuedAt)
                              ->expiresAt($issuedAt->modify($oauthLifetime))
                              ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                              ->withHeader('alg', 'RS2256')
                              ->withHeader('typ', 'JWT')
                              ->getToken(new Sha256(), InMemory::plainText($this->privateKey));

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $token->toString(),
        ];

        try
        {
            $httpResponse = $this->http->post(self::GOOGLE_OAUTH_URL, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), []);
        }
        catch (RequestsException $e)
        {
            $context = [ 'message' => $e->getMessage() ];
            $this->logger->warning('Fetching OAuth token for FCM notification(s) failed: {message}', $context);

            throw new RuntimeException('Fetching OAuth token for FCM notification(s) failed', 0, $e);
        }

        $responseBody = json_decode($httpResponse->body, TRUE);

        if (json_last_error() !== JSON_ERROR_NONE)
        {
            $context = [ 'message' => json_last_error_msg() ];
            $this->logger->warning('Processing json response for fetching OAuth token for FCM notification(s) failed: {message}', $context);

            $message = 'Processing json response for fetching OAuth token for FCM notification(s) failed: ' . $context['message'];
            throw new UnexpectedValueException($message);
        }

        if (!array_key_exists('access_token', $responseBody))
        {
            $errorMessage = $responseBody['error_description'] ?? 'No access token in the response body';

            $context = [ 'error' => $errorMessage ];
            $this->logger->warning('Fetching OAuth token for FCM notification(s) failed: {error}', $context);

            throw new UnexpectedValueException('Fetching OAuth token for FCM notification(s) failed: ' . $errorMessage);
        }

        return $responseBody['access_token'];
    }

    /**
     * Get a Requests\Response object for a failed request.
     *
     * @param int $httpCode Set http code for the request.
     *
     * @return Response New instance of a Requests\Response object.
     */
    protected function get_new_response_object_for_failed_request(?int $httpCode = NULL): Response
    {
        $httpResponse = new Response();

        $httpResponse->url = self::GOOGLE_SEND_URL . $this->projectID . '/messages:send';

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $httpResponse->status_code = $httpCode ?? FALSE;

        return $httpResponse;
    }

}

?>
