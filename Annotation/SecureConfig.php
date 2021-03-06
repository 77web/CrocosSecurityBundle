<?php

namespace Crocos\SecurityBundle\Annotation;

/**
 * @SecureConfig annotation.
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 *
 * @Annotation
 */
class SecureConfig extends Annotation
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $auth;

    /**
     * @var string
     */
    protected $forward;

    /**
     * @var string|array
     */
    protected $basic;

    /**
     * @return string
     */
    public function domain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function auth()
    {
        return $this->auth;
    }

    /**
     * @return string
     */
    public function forward()
    {
        return $this->forward;
    }

    /**
     * @return string|array
     */
    public function basic()
    {
        return $this->basic;
    }
}
