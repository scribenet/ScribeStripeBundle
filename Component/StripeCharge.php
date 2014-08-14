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

use Scribe\StripeBundle\Exception\StripeException;

/**
 * StripeCharge class
 */
class StripeCharge extends Stripe
{
    /**
     * @var string|null
     */
    private $charge_id = null;

    /**
     * @var int|null
     */
    private $charge_amount = null;

    /**
     * @var string
     */
    private $charge_currency = 'usd';

    /**
     * @var string|int|null
     */
    private $charge_card_number = null;

    /**
     * @var string|int|null
     */
    private $charge_card_experation_month = null;

    /**
     * @var string|int|null
     */
    private $charge_card_experation_year = null;

    /**
     * @var string|int|null
     */
    private $charge_card_cvc = null;

    /**
     * @var array
     */
    private $charge_metadata = [];

    /**
     * @var string|null
     */
    private $charge_description = null;

    /**
     * @var bool
     */
    private $charge_capture = true;

    /**
     * @var string|null
     */
    private $charge_statement_description = null;

    /**
     * @var string|null
     */
    private $charge_receipt_email = null;

    /**
     * @var array|null
     */
    private $charge_response = null;

    /**
     * @param  string|null
     * @return StripeCharge
     */
    public function setId($id = null)
    {
        $this->charge_id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->charge_id;
    }

    /**
     * @param  int|null
     * @return StripeCharge
     */
    public function setAmount($amount = null)
    {
        $this->charge_amount = $amount;

        return $this;
    }

    /**
     * @param  string|null
     * @return StripeCharge
     */
    public function setCurrency($currency = 'usd')
    {
        $this->charge_currency = $currency;

        return $this;
    }

    /**
     * @param  string|int|null
     * @return StripeCharge
     */
    public function setCardNumber($card_number = null)
    {
        if ($card_number !== null && strlen($card_number) !== 16) {
            throw new StripeException('Credit card number must be null or 16 digits.');
        }

        $this->charge_card_number = $card_number;

        return $this;
    }

    /**
     * @param  string|int|null
     * @return StripeCharge
     */
    public function setCardExperation($card_experation_month = null, $card_experation_year = null)
    {
        $this->charge_card_experation_month = $card_experation_month;
        $this->charge_card_experation_year  = $card_experation_year;

        return $this;
    }

    /**
     * @param  string|int|null
     * @return StripeCharge
     */
    public function setCardCvc($card_cvc = null)
    {
        $this->charge_card_cvc = $card_cvc;

        return $this;
    }

    /**
     * @param  array
     * @return StripeCharge
     */
    public function setMetadata($metadata = array())
    {
        if (!is_array($metadata)) {
            throw new StripeException('Charge metadata must be an array of key -> value pairs.');
        }

        $this->charge_metadata = $metadata;

        return $this;
    }

    /**
     * @param  string|null
     * @return StripeCharge
     */
    public function setDescription($description = null)
    {
        $this->charge_description = $description;

        return $this;
    }

    /**
     * @param  bool
     * @return StripeCharge
     */
    public function setCapture($capture = true)
    {
        $this->charge_capture = (bool)$capture;

        return $this;
    }

    /**
     * @param  string|null
     * @return StripeCharge
     */
    public function setStatementDescription($statement_description = null)
    {
        if ($statement_description !== null && strlen($statement_description) > 15) {
            $statement_description = substr($statement_description, 0, 15);
        }

        $this->charge_statement_description = $statement_description;

        return $this;
    }

    /**
     * @param  string|null
     * @return StripeCharge
     */
    public function setReceiptEmail($receipt_email = null)
    {
        if (!filter_var($receipt_email, FILTER_VALIDATE_EMAIL)) {
            throw new StripeException('The email receipt must contain a valid e-mail address.');
        }

        $this->charge_receipt_email = $receipt_email;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getResponse()
    {
        return $this->charge_response;
    }

    /**
     * @return array
     */
    public function charge() 
    {
        if ($this->charge_amount === null || $this->charge_card_number === null || 
            $this->charge_card_experation_month === null || $this->charge_card_experation_year ||
            $this->charge_card_cvc === null) 
        {
            throw new StripeException('The following fields are required: amount, card_number, card_experation_month, card_experation_year, card_cvc.');
        }

        $data = [
            'amount'   => $this->charge_amount,
            'currency' => $this->charge_currency,
            'capture'  => $this->charge_capture,
            'card'     => [
                'number'    => $this->charge_card_number,
                'exp_month' => $this->charge_card_experation_month,
                'exp_year'  => $this->charge_card_experation_year,
                'cvc'       => $this->charge_card_cvc,
            ],
        ];

        if ($this->charge_description !== null) {
            $data['description'] = $this->charge_description;
        }

        if ($this->charge_metadata !== null && is_array($this->charge_metadata)) {
            $data['metadata'] = $this->charge_metadata;
        }

        if ($this->charge_statement_description !== null) {
            $data['statement_description'] = $this->charge_statement_description;
        }

        if ($this->charge_receipt_email !== null) {
            $data['receipt_email'] = $this->charge_receipt_email;
        }

        $this->charge_response = $this->request(
            Stripe::API_METHOD_CHARGES, 
            Stripe::API_REQUEST_POST, 
            $data
        );

        return $this->charge_response;
    }

    /**
     * @return array
     */
    public function retrieve()
    {
        if ($this->charge_id === null) {
            throw new StripeException('Please set an ID to retrieve a charge.');
        }

        $this->charge_response = $this->request(
            Stripe::API_METHOD_CHARGES, 
            Stripe::API_REQUEST_GET, 
            [],
            $this->charge_id
        );

        return $this->charge_response;
    }

    /**
     * @return array
     */
    public function update()
    {
        if ($this->charge_id === null) {
            throw new StripeException('Please set an ID to retrieve a charge.');
        }

        $data = [];

        if ($this->charge_description !== null) {
            $data['description'] = $this->charge_description;
        }

        if ($this->charge_metadata !== null && is_array($this->charge_metadata)) {
            $data['metadata'] = $this->charge_metadata;
        }

        $this->charge_response = $this->request(
            Stripe::API_METHOD_CHARGES, 
            Stripe::API_REQUEST_POST, 
            $data,
            $this->charge_id
        );

        return $this->charge_response;
    }
}