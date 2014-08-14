<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\StripeBundle\Component;

use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;
use Scribe\StripeBundle\Exception\StripeException;

/**
 * Stripe class
 */
abstract class Stripe implements ContainerAwareInterface
{
    /**
     * bundle version
     */
    const BUNDLE_VERSION = '0.5.0';

    /**
     * api version
     */
    const API_VERSION = '2014-01-31';

    /**
     * the base url for all api calls
     */
    const API_URL_BASE = 'api.stripe.com/';

    /**
     * the api protocol
     */
    const API_URL_PROTOCOL = 'https';

    /**
     * api get request
     */
    const API_REQUEST_GET = 'get';

    /**
     * api post request
     */
    const API_REQUEST_POST = 'post';

    /**
     * api delete request
     */
    const API_REQUEST_DELETE = 'delete';

    /**
     * api charge method
     */
    const API_METHOD_CHARGES = 'charges';

    /**
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var boolean
     */
    private $verify_ssl_certificates;

    /**
     * @var boolean
     */
    private $log_activity;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->setContainer($container);
        
        $this->api_key                 = $container->getParameter('scribe_stripe.api_key');
        $this->verify_ssl_certificates = $container->getParameter('scribe_stripe.verify_ssl_certificates');
        $this->log_activity            = $container->getParameter('scribe_stripe.log_activity');
    }

    /**
     * @param  ContainerInterface $container
     * @return Stripe
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param  string $method
     * @return string
     */
    private function buildApiBaseUrl($method, $get = null)
    {
        $url = self::API_URL_PROTOCOL . '://' . self::API_URL_BASE . 'v1/' . $method;

        if ($get !== null) {
            $url = $url . '/' . $get;
        }

        return $url;
    }

    /**
     * @param  array       $data
     * @param  null|string $prefix
     * @return string
     */
    private function urlEncodeData(array $data = array(), $prefix = null)
    {
        $parameters = [];

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if ($prefix !== null && $key && !is_int($key)) {
                $key = $prefix . '[' . $key . ']';
            } else if ($prefix !== null) {
                $key = $prefix . '[]';
            }

            if (is_array($value)) {
                $parameters[] = $this->urlEncodeData($value, $key);
            } else {
                $parameters[] = urlencode($key) . '=' . urlencode($value);
            }
        }

        return implode('&', $parameters);
    }

    /**
     * @param  string $response
     * @param  mixed  $code
     * @return string
     */
    private function interpretResponse($response, $code)
    {
        try {
            $response_decoded = json_decode($response, true);
        } catch (\Exception $e) {
            throw new StripeException('Invalid response body from API (HTTP code ' . $code . '): ' . $response);
        }

        if ($code < 200 || $code >= 300) {
            $this->handleApiError($response, $response_decoded, $code);
        }

        return $response_decoded;
    }

    /**
     * @param string $response
     * @param object $response_decoded
     * @param mixed  $code
     */
    private function handleApiError($response, $response_decoded, $code)
    {
        if (!is_array($response_decoded) || !isset($response_decoded['error'])) {
            throw new StripeException('Invalid response object from API (HTTP code ' . $code . ')');
        }

        $error         = $response_decoded['error'];
        $error_message = isset($error['message']) ? $error['message'] : 'No additional details...';

        switch ($code) {
            case 400:
            case 404:
                throw new StripeException('Invalid request: ' . $error_message);
                break;
            case 401:
                throw new StripeException('Authentication error: ' . $error_message);
                break;
            case 402:
                throw new StripeException('Card error: ' . $error_message);
                break;
            default:
                throw new StripeException('General API Error: ' . $error_message);
                break;
        }
    }

    /**
     * @throws StripeException
     * @param mixed  $error_number
     * @param string $error_message
     */
    private function handleCurlError($error_number, $error_message)
    {
        switch ($error_number) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $display_message = 'Could not connect to stripe. Please check your internet connection and try again.';
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
            case 77:
                $display_message = 'Error during the request with the peer certificate or CA cert.';
                break;
            default:
                $display_message = 'Unexpected error communicating with Stripe.';
                break;
        }

        throw new StripeException('Curl network error #' . $error_number . ': ' . $display_message . ' (' . $error_message . ')');
    }

    /**
     * @throws StripeException
     * @param  string $method
     * @param  string $type
     * @param  array  $data
     * @return array
     */
    protected function request($method, $type, array $data = array(), $get = null) 
    {
        $user_agent = [
            'bindings_version' => self::BUNDLE_VERSION,
            'lang'             => 'php',
            'lang_version'     => PHP_VERSION,
            'publisher'        => 'scribe',
            'uname'            => php_uname(),
        ];

        $headers = [
            'X-Stripe-Client-User-Agent: '              . json_encode($user_agent),
            'User-Agent: Stripe/v1 ScribeStripeBundle/' . self::BUNDLE_VERSION,
            'Authorization: Bearer '                    . $this->api_key,
            'Stripe-Version: '                          . self::API_VERSION
        ];

        list($response, $code)
            = $this->curlRequest($method, $type, $data, $get, $headers)
        ;

        return $this->interpretResponse($response, $code);
    }

    /**
     * @throws StripeException
     * @param  string $method
     * @param  string $type
     * @param  array  $data
     * @param  array  $headers
     * @return array
     */
    private function curlRequest($method, $type, array $data = array(), $get = null, array $headers = array()) 
    {
        if (!extension_loaded('curl')) {
            throw new StripeException('Stripe requires the Curl PHP module is loaded');
        }

        $url = $this->buildApiBaseUrl($method, $get);

        $handle = curl_init($url);

        if ($type === self::API_REQUEST_GET) {
            curl_setopt($handle, CURLOPT_HTTPGET, 1);
            if (count($data) > 0) {
                $url = $url . '?' . $this->urlEncodeData($data);
            }
        } else if ($type === self::API_REQUEST_POST) {
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $this->urlEncodeData($data));
        } else if ($type === self::API_REQUEST_DELETE) {
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');

            if (count($data) > 0) {
                $url = $url . '?' . $this->urlEncodeData($data);
            }
        } else {
            throw new StripeException('Unknown API request type ' . $type);
        }

        curl_setopt($handle, CURLOPT_URL,            $url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_TIMEOUT       , 90);
        curl_setopt($handle, CURLOPT_HTTPHEADER,     $headers);

        if ($this->verify_ssl_certificates === false) {
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response      = curl_exec($handle);
        $response_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error_number  = curl_errno($handle);
        $error_message = curl_error($handle);

        if ($response === false) {
            $this->handleCurlError($error_number, $error_message);
        }

        curl_close($handle);

        return [
            $response,
            $response_code,
        ];
    }
}