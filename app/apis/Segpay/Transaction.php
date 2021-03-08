<?php

namespace App\Apis\Segpay;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;


/**
 * Model of a SegPay transaction
 */
class Transaction
{
    use HasAttributes;

    /** cspell:disable */
    /**
     * Map to normalize naming
     */
    protected $hookMap = [
        /** The action that generated this postback. See Enums\Action */
        'action'           => 'action',
        /** Type of transaction. See: Enums\TransactionType */
        'trantype'         => 'transactionType',
        /** PurchaseID of transaction. */
        'purchaseid'       => 'purchaseId',
        /** TransactionID of transaction. */
        'tranid'           => 'transactionId',
        /** Transaction amount. */
        'price'            => 'price',
        /** The currency used for the transaction. */
        'currencycode'     => 'currencyCode',
        /** Consumer’s IP address. */
        'ipaddress'        => 'ipAddress',
        /** Transaction ID of the original sale. Only available for refund, void, chargeback and revoke transactions. */
        'relatedtranid'    => 'relatedTransactionId',
        /** PackageID:BillConfigID (Signup and Stand-In only). */
        'eticketid'        => 'eTicketId',
        /** Initial transaction amount authorized for the sale. */
        'ival'             => 'initialTransactionValue',
        /** Length, in days, of the Initial billing period (trial). */
        'iint'             => 'initialBillingPeriodLength',
        /** Recurring billing amount. 0 if no recurring amount. */
        'rval'             => 'recurringBillingAmount',
        /** Length, in days, of the recurring billing period. */
        'rint'             => 'recurringBillingPeriodLength',
        /** Bill configuration description. */
        'desc'             => 'description',
        /** Username collected on the pay page. */
        'extra username'   => 'username',
        /** Password collected on the pay page. */
        'extra password'   => 'password',
        /** Consumer’s first and last name. */
        'billname'         => 'billingName',
        /** Consumer’s first name */
        'billnamefirst'    => 'billingFirstName',
        /** Consumer’s last name */
        'billnamelast'     => 'billingLastName',
        /** Consumer’s e-mail address. */
        'billemail'        => 'billingEmail',
        /**
         * Consumer’s phone number. Collected on the pay page or passed to Segpay from you. Only collected on check
         * transactions.
         */
        'billphone'        => 'billingPhone',
        /** Consumer’s billing street address. Collected on the pay page or passed to Segpay from you. */
        'billaddr'         => 'billingAddress',
        /** Consumer’s billing city. Collected on the pay page or passed to Segpay from you. */
        'billcity'         => 'billingCity',
        /** Consumer’s billing state. Collected on the pay page or passed to Segpay from you. */
        'billstate'        => 'billingState',
        /** Consumer’s billing zip code. Collected on the pay page or passed to Segpay from you. */
        'billzip'          => 'billingZip',
        /**
         * Consumer’s billing country, represented by a two-character ISO code. Collected on the pay page or passed to
         * Segpay from you.
         */
        'billcntry'        => 'billingCountry',
        /**
         * Affiliate ID passed to Segpay from you when the transaction executed. Useful if you use your own affiliate
         * program and want to track sales through Segpay.
         */
        'extra merchantpartnerid' => 'merchantPartnerId',
        /**
         * The transaction Global Unique Identifier (GUID) assigned to the transaction by Segpay. Used for instant
         * conversions.
         */
        'transguid'        => 'transactionGuid',
        /**
         * -1 = Stand-In not supported.
         * 0 = No stand-in occurred.
         * 1 = Stand-in occurred.
         * NOTE: This parameter is programmatically added to a post for all instant conversion transactions.
         */
        'standin'          => 'standIn',
        /**
         * 0 = Main transaction.
         * 1 = First cross sell.
         * 2 = Second cross sell.
         */
        'xsellnum'         => 'xSellNumber',
        /**
         * Date and Time (in GMT) the transaction occurred. Sent URL-encoded.
         */
        'transtime'        => 'transactionTime',
        /** Date and Time (in GMT) the reactivation occurred. Sent URL-encoded. */
        'reactivationtimestamp' => 'reactivationTimestamp',
        /** Next rebill date for re-activated subscription: mm/dd/yyyy */
        'nextbilldate'     => 'nextBillDate',
        /** The last date the re-activated subscription was billed: mm/dd/yyyy */
        'lastbilldate'     => 'lastBillDate',
        /**
         * Ref Variables are merchant reference variables. Segpay will store these variables along with the
         * transaction, and they can be retrieved at a later time. Unlike user-defined variables, Ref values are
         * encrypted in our database and passed back in all reports.
         */
        'extra ref1'       => 'reference1',
        'extra ref2'       => 'reference2',
        'extra ref3'       => 'reference3',
        'extra ref4'       => 'reference4',
        'extra ref5'       => 'reference5',
        'extra ref6'       => 'reference6',
        'extra ref7'       => 'reference7',
        'extra ref8'       => 'reference8',
        'extra ref9'       => 'reference9',
        'extra ref10'      => 'reference10',

        /**
         * First 6 digits of the card number (the BIN number).
         * The merchant needs to be configured to be able to receive this variable.
         */
        'ccfirst6'         => 'ccFirst6',
        /**
         * Last 4 digits of the card number.
         * The merchant needs to be configured to be able to receive this variable.
         */
        'cclast4'          => 'ccLast4',
        /**
         * Represents the response code for a transaction. Should use the normalized bank response table to return the
         * appropriate decline message to the merchant.
         */
        'authcode'         => 'authCode',
        /** Two-character ISO code representing the country associated with the credit card BIN value. */
        'ccbincountry'     => 'ccBinCountryCode',
        /**
         * Reason code the user chose when refunding the transaction. Only passed back for refund and void transactions.
         */
        'refundreasoncode' => 'refundReasonCode',
        /**
         * The additional comment entered when a refund or void is processed. Only passed back for refund and void
         * transactions.
         */
        'refundcomment'    => 'refundComment',
        /** Name of the consumer that refunded the transaction. Only passed back for refund and void transactions. */
        'refundedby'       => 'refundedBy',
        /**
         * Reason code the consumer chose when refunding the transaction. Only passed back for refund and void
         * transactions.
         */
        'cancelreasoncode' => 'cancelReasonCode',
        /**
         * The additional comment entered when a cancellation is processed. Only available in the cancellation postback.
         */
        'cancelcomment'    => 'cancelComment',
        /** Name of the consumer that cancelled the transaction. Only available in the cancellation postback. */
        'cancelledby'      => 'canceledBy',
        /** Available values are: Visa, MasterCard, JCB, Discover, eCheck and DirectDebit. See: Enums\CardType */
        'cardtype'         => 'cardType',
        /**
         * Browser type identified at the time of the transaction. Can include a variety of values as there are many
         * different browser types.
         */
        'extra browsertype' => 'browserType',
        /**
         * Browser version identified at the time of the transaction.
         * Example: Mozilla%2f5.0+(Windows+NT+6.3%3b+WOW64)+AppleWebKit%2f53
         */
        'extra browserversion' => 'browserVersion',
        /** Two-character ISO country code associated with the IP address for the transaction. */
        'extra ipcountry' => 'ipCountry',
        /** Values are: True or False, to indicate if transaction originated on a mobile device. */
        'extra ismobiledevice' => 'isMobileDevice',
        /** The platform identified at the time of the transaction. */
        'extra platform' => 'platform',
        /** Paypage template associated with the package for the transaction. */
        'extra template' => 'template',
        /** Values are: Y or N, to indicate if payment was made via a prepaid card. */
        'prepaidindicator' => 'prepaidIndicator',
        /** Numeric value representing the website ID in the Segpay system. */
        'urlid' => 'urlId',
        /**
         * Values are: Yes or No, to indicate whether a sale is associated with a single-use promotion.
         * NOTE: This parameter is programmatically added to a postback for all single use promo transactions.
         */
        'singleusepromo' => 'singleUsePromo',
    ];

    /** cspell:enable */

    public function __construct($attributes = [])
    {
        $this->fill($attributes);
    }

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = Str::lower($value);
    }

    public function setCardTypeAttribute($value)
    {
        $this->attributes['cardType'] = Str::lower($value);
    }

    /**
     * Fill object
     */
    public function fill(array $attributes, $fromHook = false)
    {
        foreach($attributes as $key => $value) {
            if (isset($this->hookMap[$key])) {
                $this->setAttribute($this->hookMap[$key], $value);
            } else {
                // Remove `extra ` from attributes
                if ( Str::contains($key, 'extra ') ) {
                    $this->setAttribute(Str::replaceFirst('extra ', '', $key), $value);
                } else {
                    $this->setAttribute($key, $value);
                }
            }
        }
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}
