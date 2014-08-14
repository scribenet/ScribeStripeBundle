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

## Usage

Minimal usage:

```php
$charge = $container->get('scribe.stripe.charge');

try {
	$charge
		->setAmount(10, 00)
		->setCardNumber('4242424242424242')
		->setCardExperation(01, 16)
		->setCardCvc('000')
		->charge()
	;
} catch(\Scribe\StripeBundle\Exception\StripeException $e) {
	// handle exception
}

$response = $charge->getResponse();
```

You can also use a collection of other fields (https://stripe.com/docs/api/php#create_charge) to pass additional data to Stripe.

```php
$charge = $container->get('scribe.stripe.charge');

try {
    $charge
        ->setAmount(10, 20)
        ->setCardNumber('4242424242424242')
        ->setCardExperation(1, 2016)
        ->setCardCvc('000')
        ->setName('First Last')
        ->setAddressLine01('123 Address Ln.')
        ->setAddressLine02('Suite 666')
        ->setCity('City')
        ->setState('State')
        ->setZip('12345')
        ->setCountry('USA')
        ->setMetadata([
            'my_client_id'   => '123456',
            'something_else' => 'more info about the customer',
        ])
        ->setDescription('A test Stripe charge!')
        ->setStatementDescription('TEST CHARGE')
        ->setReceiptEmail('someone@somwhere.com')
        ->charge()
    ;
} catch(\Scribe\StripeBundle\Exception\StripeException $e) {
    // handle exception
}

$response = $charge->getResponse();
```

## License

Please see the LICENSE file distributed with this software.
