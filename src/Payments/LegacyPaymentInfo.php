<?php
/**
 * Legacy payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Customer;

/**
 * Legacy payment.
 *
 * Legacy and deprecated functions are here to keep the Payment class clean.
 * This class will be removed in future versions.
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.1.0
 *
 * @property string|null $email
 * @property string|null $customer_name
 * @property int|string|null $user_id
 * @property mixed $user_agent
 * @property mixed $user_ip
 * @property mixed $consumer_name
 * @property mixed $consumer_account_number
 * @property mixed $consumer_iban
 * @property mixed $consumer_bic
 * @property mixed $consumer_city
 */
abstract class LegacyPaymentInfo extends PaymentInfo {

}
