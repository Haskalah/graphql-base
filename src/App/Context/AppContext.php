<?php
namespace App\Context;

/**
 * Class AppContext
 * Instance available in all GraphQL resolvers as 3rd argument
 *
 * @package GraphQL\Examples\Blog
 */
class AppContext
{
    /**
     * @var string
     */
    public $rootUrl;

    /**
     * @var mixed
     */
    public $user;

    /**
     * @var mixed
     */
    public $request;
}
