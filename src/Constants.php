<?php


namespace App;

/**
 * Class Constants
 *
 * These are also in config/packages/twig.yaml
 *
 * @package App
 */
class Constants
{

    /**
     * This value is saved to the database and so can NOT be changed without a data migration.
     * It's used to make the code more readable, not to make it easy to change!
     */
    const PRIVACY_LEVEL_PUBLIC = 0;

    /**
     * This value is saved to the database and so can NOT be changed without a data migration.
     * It's used to make the code more readable, not to make it easy to change!
     */
    const PRIVACY_LEVEL_ONLY_FOLLOWERS = 5000;

    /**
     * This value is saved to the database and so can NOT be changed without a data migration.
     * It's used to make the code more readable, not to make it easy to change!
     */
    const PRIVACY_LEVEL_PRIVATE = 10000;
}
