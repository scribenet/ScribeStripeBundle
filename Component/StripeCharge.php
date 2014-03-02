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
     * @param  integer $amount
     * @param  string  $card_number
     * @param  integer $card_experation_month
     * @param  integer $card_experation_year
     * @param  integer $card_cvc
     * @param  array   $metadata
     * @return array
     */
    public function charge($amount, $card_number, $card_experation_month, $card_experation_year, $card_cvc, array $metadata = array()) 
    {
        $data = [
            'amount'   => $amount,
            'currency' => 'usd',
            'card' => [
                'number'    => $card_number,
                'exp_month' => $card_experation_month,
                'exp_year'  => $card_experation_year,
                'cvc'       => $card_cvc,
            ],
            'metadata' => $metadata
        ];

        $response = $this->request(Stripe::API_METHOD_CHARGES, Stripe::API_REQUEST_POST, $data);

        return $response;
    }
}