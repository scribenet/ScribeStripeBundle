# ScribeStripeBundle

Symfony bundle to handle interaction with stripe.com api for payments

## Requirements

- You must register for an account and receive an API key from http://stripe.com/
- PHP5
- Curl PHP module

## Installation

Add the following to your composer.json file in the `require` block:

```
"scribe/stripe-bundle": "dev-master"
```

Issue a `composer.phar update` to download your new package (this command will also update any outdated packages).

To register the bundle within your application, you must add the bundle to the `AppKernel.php` file within the `$bundles` array:

```
new Scribe\StripeBundle\ScribeStripeBundle()
```

## Configuration

Edit your symfony config.yml file and add, at a minimum, the following lines:

```
scribe_stripe:
  api_key: your-api-key-goes-here
```

You may optionally configure the following items as well (show with their default values):

```
scribe_stripe:
  api_key: your-api-key-goes-here
  verify_ssl_certificates: true
  log_activity: false
```

## License

Please see the LICENSE file distributed with this software.