<?php
/*
 * Payment components (only slightly convoluted)
 */

namespace Sakura;

use \PayPal\Api\Amount;
use \PayPal\Api\Details;
use \PayPal\Api\Item;
use \PayPal\Api\ItemList;
use \PayPal\Api\Payer;
use \PayPal\Api\Payment;
use \PayPal\Api\PaymentExecution;
use \PayPal\Api\RedirectUrls;
use \PayPal\Api\Transaction;

class Payments
{
    // Container for PayPal API
    private static $paypal;

    // Initialise PayPal API
    public static function init()
    {

        // Set PayPal object
        try {
            self::$paypal = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    Configuration::getConfig('paypal_client_id'),
                    Configuration::getConfig('paypal_secret')
                )
            );
        } catch (Exception $e) {
            return false;
        }

        return true;

    }

    // Create transaction
    public static function createTransaction($total, $itemName, $transDescription, $returnUrl)
    {

        // Create the payer object
        $payer = new Payer();

        // Set the method
        $payer->setPaymentMethod('paypal');

        // Create the item
        $item = new Item();

        // Set the item details
        $item->setName($itemName)
            ->setCurrency('EUR')
            ->setQuantity(1)
            ->setPrice($total);

        // Create itemlist
        $list = new ItemList();

        // Add the items
        $list->setItems([$item]);

        // Create details
        $details = new Details();

        // Set details
        $details->setSubtotal($total);

        // Create amount
        $amount = new Amount();

        // Set amount data
        $amount->setCurrency('EUR')
            ->setTotal($total)
            ->setDetails($details);

        // Create transaction
        $trans = new Transaction();

        // Set transaction data (aka shit we already set but whatever who cares we need to set it again 500 times over again anyway, YAY TECHNOLOGY!)
        $trans->setAmount($amount)
            ->setItemList($list)
            ->setDescription($transDescription)
            ->setInvoiceNumber(uniqid());

        // Create redirect url object
        $redir = new RedirectUrls();

        // Set redirect url data
        $redir->setReturnUrl($returnUrl . '?mode=finish&success=true')
            ->setCancelUrl($returnUrl . '?mode=finish&success=false');

        // Create payment object
        $payment = new Payment();

        // Set payment data (finally)
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redir)
            ->setTransactions([$trans]);

        // Try to create payment
        try {
            $payment->create(self::$paypal);
        } catch (Exception $ex) {
            return false;
        }

        // Return the approval link if everything is gucci
        return $payment->getApprovalLink();

    }

    // Complete the PayPal transaction
    public static function completeTransaction($paymentId, $payerId)
    {

        // Attempt to get the payment
        $payment = Payment::get($paymentId, self::$paypal);

        // Create payment execution object
        $execute = new PaymentExecution();

        // Set the payer ID
        $execute->setPayerId($payerId);

        // Attempt to charge the fucker
        try {
            $payment->execute($execute, self::$paypal);
        } catch (Exception $ex) {
            return false;
        }

        // If everything was cute return true
        return true;

    }
}
