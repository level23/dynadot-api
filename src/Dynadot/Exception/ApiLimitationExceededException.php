<?php
/**
 * Created by PhpStorm.
 * User: niek
 * Date: 15-12-16
 * Time: 10:50
 */

namespace Level23\Dynadot\Exception;

use Level23\Dynadot\Exception\DynadotApiException;

/**
 * Class ApiLimitationExceededException
 *
 * A limitation of the API was exceeded in the request performed.
 * (e.g. a search for more than 100 domains was performed)
 *
 * @package Level23\Dynadot\Exception
 */
class ApiLimitationExceededException extends DynadotApiException
{

}
