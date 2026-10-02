<?php

namespace App\Services\Sms;

use RuntimeException;

/** The SMS could not be sent (provider down, wrong login, text not approved, …) */
class SmsException extends RuntimeException {}
